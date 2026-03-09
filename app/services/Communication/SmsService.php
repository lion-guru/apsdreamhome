<?php

namespace App\Services\Communication;

use App\Core\Database;
use Psr\Log\LoggerInterface;

/**
 * Modern SMS Service
 * Handles SMS communications with multiple providers and proper MVC patterns
 */
class SmsService
{
    private Database $db;
    private LoggerInterface $logger;
    private array $config;
    private array $providers;

    public function __construct(Database $db, LoggerInterface $logger, array $config = [])
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->config = array_merge([
            'default_provider' => 'twilio',
            'max_retry_attempts' => 3,
            'retry_delay' => 300, // 5 minutes
            'rate_limit' => [
                'messages_per_minute' => 60,
                'messages_per_hour' => 1000
            ],
            'providers' => [
                'twilio' => [
                    'account_sid' => '',
                    'auth_token' => '',
                    'phone_number' => '',
                    'enabled' => false
                ],
                'nexmo' => [
                    'api_key' => '',
                    'api_secret' => '',
                    'phone_number' => '',
                    'enabled' => false
                ],
                'aws_sns' => [
                    'access_key' => '',
                    'secret_key' => '',
                    'region' => 'us-east-1',
                    'enabled' => false
                ]
            ]
        ], $config);

        $this->providers = $this->config['providers'];
    }

    /**
     * Send SMS message
     */
    public function sendSms(string $to, string $message, array $options = []): array
    {
        try {
            // Validate phone number
            if (!$this->validatePhoneNumber($to)) {
                return [
                    'success' => false,
                    'message' => 'Invalid phone number format'
                ];
            }

            // Check rate limiting
            if (!$this->checkRateLimit($to)) {
                return [
                    'success' => false,
                    'message' => 'Rate limit exceeded. Please try again later.'
                ];
            }

            // Validate message
            $validation = $this->validateMessage($message);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Message validation failed',
                    'errors' => $validation['errors']
                ];
            }

            // Get provider
            $provider = $options['provider'] ?? $this->config['default_provider'];
            if (!isset($this->providers[$provider]) || !$this->providers[$provider]['enabled']) {
                return [
                    'success' => false,
                    'message' => 'SMS provider not available'
                ];
            }

            // Create SMS record
            $smsId = $this->createSmsRecord($to, $message, $provider, $options);

            // Send SMS
            $result = $this->sendViaProvider($provider, $to, $message, $options);

            // Update SMS record
            $this->updateSmsRecord($smsId, $result);

            if ($result['success']) {
                $this->logger->info('SMS sent successfully', [
                    'sms_id' => $smsId,
                    'to' => $to,
                    'provider' => $provider,
                    'message_length' => strlen($message)
                ]);

                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'sms_id' => $smsId,
                    'provider' => $provider,
                    'message_id' => $result['message_id'] ?? null
                ];
            } else {
                $this->logger->error('SMS sending failed', [
                    'sms_id' => $smsId,
                    'to' => $to,
                    'provider' => $provider,
                    'error' => $result['error'] ?? 'Unknown error'
                ]);

                return [
                    'success' => false,
                    'message' => $result['error'] ?? 'SMS sending failed',
                    'sms_id' => $smsId
                ];
            }

        } catch (\Exception $e) {
            $this->logger->error('SMS service error', [
                'to' => $to,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'SMS service error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send bulk SMS
     */
    public function sendBulkSms(array $recipients, string $message, array $options = []): array
    {
        try {
            $results = [];
            $successCount = 0;
            $failureCount = 0;

            foreach ($recipients as $recipient) {
                $phone = is_array($recipient) ? $recipient['phone'] : $recipient;
                $recipientOptions = is_array($recipient) ? array_merge($options, $recipient) : $options;

                $result = $this->sendSms($phone, $message, $recipientOptions);
                
                $results[] = [
                    'phone' => $phone,
                    'result' => $result
                ];

                if ($result['success']) {
                    $successCount++;
                } else {
                    $failureCount++;
                }

                // Small delay to avoid rate limiting
                usleep(100000); // 0.1 second
            }

            return [
                'success' => true,
                'message' => "Bulk SMS completed. Success: {$successCount}, Failures: {$failureCount}",
                'total_sent' => count($recipients),
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'results' => $results
            ];

        } catch (\Exception $e) {
            $this->logger->error('Bulk SMS error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Bulk SMS failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get SMS delivery status
     */
    public function getSmsStatus(int $smsId): ?array
    {
        try {
            $sql = "SELECT * FROM sms_logs WHERE id = ?";
            $sms = $this->db->fetchOne($sql, [$smsId]);
            
            if ($sms) {
                $sms['metadata'] = json_decode($sms['metadata'] ?? '{}', true) ?? [];
                
                // Check external provider status if needed
                if ($sms['status'] === 'sent' && !empty($sms['provider_message_id'])) {
                    $externalStatus = $this->getProviderStatus($sms['provider'], $sms['provider_message_id']);
                    if ($externalStatus) {
                        $sms['external_status'] = $externalStatus;
                    }
                }
            }
            
            return $sms;

        } catch (\Exception $e) {
            $this->logger->error('Failed to get SMS status', ['sms_id' => $smsId, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get SMS statistics
     */
    public function getSmsStats(array $filters = []): array
    {
        try {
            $stats = [];

            // Total SMS sent
            $sql = "SELECT COUNT(*) as total FROM sms_logs";
            $params = [];
            
            if (!empty($filters['date_from'])) {
                $sql .= " WHERE created_at >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= (empty($params) ? " WHERE" : " AND") . " created_at <= ?";
                $params[] = $filters['date_to'];
            }
            
            $stats['total_sent'] = $this->db->fetchOne($sql, $params) ?? 0;

            // SMS by status
            $statusSql = "SELECT status, COUNT(*) as count FROM sms_logs";
            $statusParams = [];
            
            if (!empty($filters['date_from'])) {
                $statusSql .= " WHERE created_at >= ?";
                $statusParams[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $statusSql .= (empty($statusParams) ? " WHERE" : " AND") . " created_at <= ?";
                $statusParams[] = $filters['date_to'];
            }
            
            $statusSql .= " GROUP BY status";
            
            $statusStats = $this->db->fetchAll($statusSql, $statusParams);
            $stats['by_status'] = [];
            foreach ($statusStats as $stat) {
                $stats['by_status'][$stat['status']] = $stat['count'];
            }

            // SMS by provider
            $providerSql = "SELECT provider, COUNT(*) as count FROM sms_logs";
            $providerParams = [];
            
            if (!empty($filters['date_from'])) {
                $providerSql .= " WHERE created_at >= ?";
                $providerParams[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $providerSql .= (empty($providerParams) ? " WHERE" : " AND") . " created_at <= ?";
                $providerParams[] = $filters['date_to'];
            }
            
            $providerSql .= " GROUP BY provider";
            
            $providerStats = $this->db->fetchAll($providerSql, $providerParams);
            $stats['by_provider'] = [];
            foreach ($providerStats as $stat) {
                $stats['by_provider'][$stat['provider']] = $stat['count'];
            }

            // Recent SMS
            $recentSql = "SELECT * FROM sms_logs ORDER BY created_at DESC LIMIT 10";
            $stats['recent'] = $this->db->fetchAll($recentSql);

            return $stats;

        } catch (\Exception $e) {
            $this->logger->error('Failed to get SMS stats', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Schedule SMS for later delivery
     */
    public function scheduleSms(string $to, string $message, string $scheduledAt, array $options = []): array
    {
        try {
            // Validate scheduled time
            $scheduledTimestamp = strtotime($scheduledAt);
            if ($scheduledTimestamp <= time()) {
                return [
                    'success' => false,
                    'message' => 'Scheduled time must be in the future'
                ];
            }

            // Create scheduled SMS record
            $sql = "INSERT INTO scheduled_sms 
                    (to_phone, message, scheduled_at, provider, options, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())";
            
            $this->db->execute($sql, [
                $to,
                $message,
                date('Y-m-d H:i:s', $scheduledTimestamp),
                $options['provider'] ?? $this->config['default_provider'],
                json_encode($options)
            ]);

            $scheduledId = $this->db->lastInsertId();

            $this->logger->info('SMS scheduled', [
                'scheduled_id' => $scheduledId,
                'to' => $to,
                'scheduled_at' => $scheduledAt
            ]);

            return [
                'success' => true,
                'message' => 'SMS scheduled successfully',
                'scheduled_id' => $scheduledId,
                'scheduled_at' => $scheduledAt
            ];

        } catch (\Exception $e) {
            $this->logger->error('Failed to schedule SMS', ['to' => $to, 'error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to schedule SMS: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process scheduled SMS
     */
    public function processScheduledSms(): array
    {
        try {
            $sql = "SELECT * FROM scheduled_sms 
                    WHERE scheduled_at <= NOW() AND status = 'pending' 
                    ORDER BY scheduled_at ASC";
            
            $scheduledSms = $this->db->fetchAll($sql);
            
            $processed = 0;
            $successCount = 0;
            $failureCount = 0;

            foreach ($scheduledSms as $sms) {
                $options = json_decode($sms['options'] ?? '{}', true) ?? [];
                
                $result = $this->sendSms($sms['to_phone'], $sms['message'], $options);
                
                // Update scheduled SMS status
                $updateSql = "UPDATE scheduled_sms 
                             SET status = ?, processed_at = NOW(), result = ? 
                             WHERE id = ?";
                
                $status = $result['success'] ? 'sent' : 'failed';
                $this->db->execute($updateSql, [$status, json_encode($result), $sms['id']]);
                
                $processed++;
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failureCount++;
                }
            }

            return [
                'success' => true,
                'message' => "Processed {$processed} scheduled SMS",
                'processed' => $processed,
                'success_count' => $successCount,
                'failure_count' => $failureCount
            ];

        } catch (\Exception $e) {
            $this->logger->error('Failed to process scheduled SMS', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to process scheduled SMS: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Private helper methods
     */
    private function validatePhoneNumber(string $phone): bool
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Check if phone number is valid (basic validation)
        return preg_match('/^\+?[1-9]\d{6,14}$/', $phone);
    }

    private function validateMessage(string $message): array
    {
        $errors = [];
        $valid = true;

        if (empty($message)) {
            $errors[] = 'Message cannot be empty';
            $valid = false;
        }

        if (strlen($message) > 1600) { // SMS limit
            $errors[] = 'Message too long (max 1600 characters)';
            $valid = false;
        }

        return ['valid' => $valid, 'errors' => $errors];
    }

    private function checkRateLimit(string $phone): bool
    {
        $sql = "SELECT COUNT(*) as count FROM sms_logs 
                WHERE to_phone = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)";
        
        $count = $this->db->fetchOne($sql, [$phone]) ?? 0;
        
        return $count < $this->config['rate_limit']['messages_per_minute'];
    }

    private function createSmsRecord(string $to, string $message, string $provider, array $options): int
    {
        $sql = "INSERT INTO sms_logs 
                (to_phone, message, provider, status, options, created_at) 
                VALUES (?, ?, ?, 'pending', ?, NOW())";
        
        $this->db->execute($sql, [$to, $message, $provider, json_encode($options)]);
        
        return $this->db->lastInsertId();
    }

    private function updateSmsRecord(int $smsId, array $result): void
    {
        $sql = "UPDATE sms_logs 
                SET status = ?, provider_message_id = ?, error_message = ?, updated_at = NOW() 
                WHERE id = ?";
        
        $this->db->execute($sql, [
            $result['success'] ? 'sent' : 'failed',
            $result['message_id'] ?? null,
            $result['error'] ?? null,
            $smsId
        ]);
    }

    private function sendViaProvider(string $provider, string $to, string $message, array $options): array
    {
        switch ($provider) {
            case 'twilio':
                return $this->sendTwilioSms($to, $message, $options);
            case 'nexmo':
                return $this->sendNexmoSms($to, $message, $options);
            case 'aws_sns':
                return $this->sendAwsSnsSms($to, $message, $options);
            default:
                return ['success' => false, 'error' => 'Unknown provider'];
        }
    }

    private function sendTwilioSms(string $to, string $message, array $options): array
    {
        try {
            $config = $this->providers['twilio'];
            
            if (!$config['enabled'] || empty($config['account_sid']) || empty($config['auth_token'])) {
                return ['success' => false, 'error' => 'Twilio not configured'];
            }

            // Mock implementation - in real scenario, use Twilio SDK
            // $client = new Client($config['account_sid'], $config['auth_token']);
            // $result = $client->messages->create($to, [
            //     'from' => $config['phone_number'],
            //     'body' => $message
            // ]);
            
            // For demo purposes, simulate success
            return [
                'success' => true,
                'message_id' => 'twilio_' . uniqid(),
                'provider' => 'twilio'
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Twilio error: ' . $e->getMessage()];
        }
    }

    private function sendNexmoSms(string $to, string $message, array $options): array
    {
        try {
            $config = $this->providers['nexmo'];
            
            if (!$config['enabled'] || empty($config['api_key']) || empty($config['api_secret'])) {
                return ['success' => false, 'error' => 'Nexmo not configured'];
            }

            // Mock implementation
            return [
                'success' => true,
                'message_id' => 'nexmo_' . uniqid(),
                'provider' => 'nexmo'
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Nexmo error: ' . $e->getMessage()];
        }
    }

    private function sendAwsSnsSms(string $to, string $message, array $options): array
    {
        try {
            $config = $this->providers['aws_sns'];
            
            if (!$config['enabled'] || empty($config['access_key']) || empty($config['secret_key'])) {
                return ['success' => false, 'error' => 'AWS SNS not configured'];
            }

            // Mock implementation
            return [
                'success' => true,
                'message_id' => 'sns_' . uniqid(),
                'provider' => 'aws_sns'
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'AWS SNS error: ' . $e->getMessage()];
        }
    }

    private function getProviderStatus(string $provider, string $messageId): ?array
    {
        // Mock implementation - would check actual provider status
        return [
            'status' => 'delivered',
            'delivered_at' => date('Y-m-d H:i:s')
        ];
    }
}

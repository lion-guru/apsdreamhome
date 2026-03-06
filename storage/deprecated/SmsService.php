<?php

namespace App\Services\Legacy;
// Advanced SMS Notification Service

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

use Twilio\Rest\Client;

class SMSService {
    // SMS Provider Configuration
    private $config = [
        'provider' => 'twilio', // Default provider
        'twilio_account_sid' => '',
        'twilio_auth_token' => '',
        'twilio_phone_number' => '',
        'max_retry_attempts' => 3,
        'retry_delay' => 300 // 5 minutes
    ];

    // SMS Providers
    private const PROVIDERS = [
        'twilio' => 'sendTwilioSMS',
        'nexmo' => 'sendNexmoSMS',
        'aws_sns' => 'sendAWSSNSSMS'
    ];

    // Dependencies
    private $logger;
    private $db;

    public function __construct($logger = null, $db = null) {
        $this->logger = $logger ?? new class { 
            public function log($msg, $level = 'info', $channel = 'app') { 
                error_log("[$level][$channel] $msg"); 
            } 
        };
        
        // Initialize DB
        if ($db) {
            $this->db = $db;
        } else {
            /** @noinspection PhpUndefinedClassInspection */
            $this->db = \App\Core\App::database();
        }

        // Load SMS configuration
        $this->loadConfiguration();
    }

    /**
     * Load SMS configuration
     */
    private function loadConfiguration() {
        $this->config['twilio_account_sid'] = getenv('SMS_TWILIO_SID') ?: $this->config['twilio_account_sid'];
        $this->config['twilio_auth_token'] = getenv('SMS_TWILIO_TOKEN') ?: $this->config['twilio_auth_token'];
        $this->config['twilio_phone_number'] = getenv('SMS_TWILIO_NUMBER') ?: $this->config['twilio_phone_number'];

        // Optionally load from database configuration
        try {
            $sql = "SELECT `key`, `value` FROM sms_config";
            $rows = $this->db->fetchAll($sql);
            
            foreach ($rows as $row) {
                if (isset($this->config[$row['key']])) {
                    $this->config[$row['key']] = $row['value'];
                }
            }
        } catch (\Exception $e) {
            $this->logger->log(
                "SMS config load error: " . $e->getMessage(), 
                'warning', 
                'sms'
            );
        }
    }

    /**
     * Send SMS
     * @param string $to Recipient phone number
     * @param string $message SMS message
     * @param array $options Additional options
     * @return bool
     */
    public function send($to, $message, $options = []) {
        // Validate phone number
        $to = $this->normalizePhoneNumber($to);
        if (!$this->validatePhoneNumber($to)) {
            $this->logger->log(
                "Invalid phone number: {$to}", 
                'warning', 
                'sms'
            );
            return false;
        }

        // Determine SMS provider
        $provider = $options['provider'] ?? $this->config['provider'];
        $method = self::PROVIDERS[$provider] ?? null;

        if (!$method || !method_exists($this, $method)) {
            $this->logger->log(
                "SMS provider not supported: {$provider}", 
                'error', 
                'sms'
            );
            return false;
        }

        // Send SMS and queue if needed
        try {
            $sent = $this->$method($to, $message, $options);

            if (!$sent) {
                $this->queueSMS($to, $message, $options);
            }

            return $sent;
        } catch (\Exception $e) {
            $this->logger->log(
                "SMS send error: " . $e->getMessage(), 
                'error', 
                'sms'
            );
            $this->queueSMS($to, $message, $options);
            return false;
        }
    }

    /**
     * Send SMS via Twilio
     * @param string $to Recipient phone number
     * @param string $message SMS message
     * @param array $options Additional options
     * @return bool
     */
    private function sendTwilioSMS($to, $message, $options = []) {
        try {
            // Validate Twilio configuration
            if (!$this->config['twilio_account_sid'] || 
                !$this->config['twilio_auth_token'] || 
                !$this->config['twilio_phone_number']) {
                throw new \Exception("Twilio configuration incomplete");
            }

            // Initialize Twilio client
            if (!class_exists('\Twilio\Rest\Client')) {
                throw new \Exception("Twilio SDK not found. Please install via composer: composer require twilio/sdk");
            }

            /** @var \Twilio\Rest\Client $client */
            $client = new \Twilio\Rest\Client(
                (string)$this->config['twilio_account_sid'], 
                (string)$this->config['twilio_auth_token']
            );

            // Send SMS
            $client->messages->create(
                $to,
                [
                    'from' => $this->config['twilio_phone_number'],
                    'body' => $message
                ]
            );

            // Log successful SMS
            $this->logger->log(
                "SMS sent to: {$to} via Twilio", 
                'info', 
                'sms'
            );

            return true;
        } catch (\Exception $e) {
            $this->logger->log(
                "Twilio SMS error: " . $e->getMessage(), 
                'error', 
                'sms'
            );
            return false;
        }
    }

    /**
     * Send SMS via Nexmo (Vonage)
     * @param string $to Recipient phone number
     * @param string $message SMS message
     * @param array $options Additional options
     * @return bool
     */
    private function sendNexmoSMS($to, $message, $options = []) {
        // Placeholder for Nexmo SMS implementation
        $this->logger->log(
            "Nexmo SMS not implemented", 
            'warning', 
            'sms'
        );
        return false;
    }

    /**
     * Send SMS via AWS SNS
     * @param string $to Recipient phone number
     * @param string $message SMS message
     * @param array $options Additional options
     * @return bool
     */
    private function sendAWSSNSSMS($to, $message, $options = []) {
        // Placeholder for AWS SNS SMS implementation
        $this->logger->log(
            "AWS SNS SMS not implemented", 
            'warning', 
            'sms'
        );
        return false;
    }

    /**
     * Queue SMS for later sending
     * @param string $to Recipient phone number
     * @param string $message SMS message
     * @param array $options Additional options
     * @return bool
     */
    private function queueSMS($to, $message, $options = []) {
        try {
            $sql = "INSERT INTO sms_queue 
                    (recipient, message, status, provider, scheduled_at, created_at, attempts) 
                    VALUES (?, ?, ?, ?, ?, NOW(), 0)";

            $status = 'pending';
            $provider = $options['provider'] ?? $this->config['provider'];
            $scheduled_at = $options['scheduled_at'] ?? date('Y-m-d H:i:s');

            $this->db->execute($sql, [$to, $message, $status, $provider, $scheduled_at]);
            return true;
        } catch (\Exception $e) {
            $this->logger->log(
                "SMS queue error: " . $e->getMessage(), 
                'error', 
                'sms'
            );
            return false;
        }
    }

    /**
     * Process queued SMS
     */
    public function processSMSQueue() {
        try {
            // Fetch pending SMS
            $sql = "SELECT id, recipient, message, provider, attempts 
                    FROM sms_queue 
                    WHERE status = 'pending' 
                      AND scheduled_at <= NOW() 
                      AND attempts < ?
                    LIMIT 50";
            
            $sms_list = $this->db->fetchAll($sql, [$this->config['max_retry_attempts']]);

            foreach ($sms_list as $sms) {
                // Attempt to send SMS
                $sent = $this->send(
                    $sms['recipient'], 
                    $sms['message'], 
                    ['provider' => $sms['provider']]
                );

                // Update SMS status
                $status = $sent ? 'sent' : 'failed';
                $sql = "UPDATE sms_queue 
                        SET status = ?, 
                            sent_at = NOW(), 
                            attempts = attempts + 1,
                            next_retry_at = DATE_ADD(NOW(), INTERVAL ? SECOND)
                        WHERE id = ?";
                
                $this->db->execute($sql, [$status, $this->config['retry_delay'], $sms['id']]);
            }
        } catch (\Exception $e) {
            $this->logger->log(
                "SMS queue processing error: " . $e->getMessage(), 
                'error', 
                'sms'
            );
        }
    }

    /**
     * Normalize phone number
     * @param string $phone Phone number
     * @return string Normalized phone number
     */
    private function normalizePhoneNumber($phone) {
        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Add country code if missing
        if (substr($phone, 0, 1) !== '1') {
            $phone = '1' . $phone; // Assume US/Canada by default
        }

        return '+' . $phone;
    }

    /**
     * Validate phone number
     * @param string $phone Phone number
     * @return bool
     */
    private function validatePhoneNumber($phone) {
        // Basic phone number validation
        return preg_match('/^\+1[2-9]\d{2}[2-9]\d{2}\d{4}$/', $phone) === 1;
    }
}

// Helper function for dependency injection
function getSMSService() {
    // If a global container function exists, use it
    if (function_exists('container')) {
        try {
            $container = container();
            $logger = $container->resolve('logger');
            $db = $container->resolve('db_connection');
            return new SMSService($logger, $db);
        } catch (\Exception $e) {
            // Fallback to default instantiation if resolution fails
        }
    }
    
    // Default instantiation with internal defaults
    return new SMSService();
}

return getSMSService();

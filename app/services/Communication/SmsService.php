<?php

namespace App\Services\Communication;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use \App\Traits\ServiceTenantTrait;

/**
 * Unified SMS Service
 * Merges SMSService (MSG91, OTP, templates) + SmsSenderService (multi-provider, queue)
 * Single entry point for all SMS operations with multi-provider and queue support.
 */
class SmsService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $provider;
    private $apiKey;
    private $senderId;
    private $templateId;
    private $apiUrl = 'https://api.msg91.com/api/v5/flow/';
    private $otpApiUrl = 'https://control.msg91.com/api/v5/otp/';

    // Provider constants
    const PROVIDER_LOG = 'log';
    const PROVIDER_TWILIO = 'twilio';
    const PROVIDER_MSG91 = 'msg91';

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->provider = $_ENV['SMS_PROVIDER'] ?? $_ENV['MSG91_PROVIDER'] ?? 'log';
        $this->apiKey = $_ENV['SMS_API_KEY'] ?? $_ENV['MSG91_AUTH_KEY'] ?? '';
        $this->senderId = $_ENV['SMS_SENDER_ID'] ?? $_ENV['MSG91_SENDER_ID'] ?? 'APSDHM';
        $this->templateId = $_ENV['SMS_TEMPLATE_ID'] ?? $_ENV['MSG91_TEMPLATE_ID'] ?? '';
    }

    // ==================== CORE SEND METHODS ====================

    /**
     * Send SMS using configured provider (supports queue)
     * Alias for sendSMS() to maintain backward compatibility
     */
    public function sendSMS($phone, $message, $useQueue = false)
    {
        return $this->send($phone, $message, $useQueue);
    }

    /**
     * Send SMS using configured provider (supports queue)
     */
    public function send($phone, $message, $useQueue = false)
    {
        $phone = $this->cleanPhone($phone);

        try {
            if ($useQueue) {
                return $this->queue($phone, $message);
            }

            $result = match ($this->provider) {
                self::PROVIDER_TWILIO => $this->sendViaTwilio($phone, $message),
                self::PROVIDER_MSG91 => $this->sendViaMsg91($phone, $message),
                default => $this->sendViaLog($phone, $message),
            };

            $this->logToQueue($phone, $message, $result['status'] ?? 'sent', $result['error'] ?? null);
            return $result;
        } catch (\Throwable $e) {
            error_log("SmsService: Send failed: " . $e->getMessage());
            $this->logToQueue($phone, $message, 'failed', $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Queue SMS for async processing
     */
    public function queue($phone, $message, $priority = 'normal'): array
    {
        try {
            $tid = $this->tenantId();
            $this->db->insert('sms_queue', [
                'tenant_id' => $tid,
                'recipient' => $phone,
                'message' => substr($message, 0, 500),
                'priority' => $priority,
                'status' => 'pending',
                'attempts' => 0,
                'provider' => $this->provider,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            return ['success' => true, 'queued' => true];
        } catch (\Throwable $e) {
            error_log("SmsService: Queue failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ==================== PROVIDER IMPLEMENTATIONS ====================

    private function sendViaLog($phone, $message): array
    {
        $logFile = defined('STORAGE_PATH') ? STORAGE_PATH . '/logs/sms.log' : __DIR__ . '/../../../storage/logs/sms.log';
        $dir = dirname($logFile);
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $line = date('Y-m-d H:i:s') . " | TO: $phone | MSG: " . substr($message, 0, 100) . "\n";
        @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
        return ['success' => true, 'status' => 'sent', 'provider' => 'log'];
    }

    private function sendViaTwilio($phone, $message): array
    {
        try {
            $twilio = new \App\Services\Gateway\TwilioService();
            $result = $twilio->sendSms($phone, $message);
            if (!empty($result['success'])) {
                return ['success' => true, 'status' => 'sent', 'provider' => 'twilio', 'sid' => $result['sid'] ?? null];
            }
            return ['success' => false, 'status' => 'failed', 'error' => $result['error'] ?? 'Twilio send failed', 'provider' => 'twilio'];
        } catch (\Throwable $e) {
            return ['success' => false, 'status' => 'failed', 'error' => 'TwilioService unavailable: ' . $e->getMessage(), 'provider' => 'twilio'];
        }
    }

    private function sendViaMsg91($phone, $message): array
    {
        if (!$this->apiKey) return ['success' => false, 'status' => 'failed', 'error' => 'MSG91 not configured'];
        $flowId = $_ENV['MSG91_FLOW_ID'] ?? '';
        $payload = [
            'sender' => $this->senderId,
            'route' => '4',
            'country' => '91',
            'sms' => [['message' => $message, 'to' => [$phone]]],
        ];
        $url = 'https://api.msg91.com/api/v5/flow/';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'authkey: ' . $this->apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resp = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http === 200) return ['success' => true, 'status' => 'sent', 'provider' => 'msg91'];
        return ['success' => false, 'status' => 'failed', 'error' => "MSG91 HTTP $http: $resp"];
    }

    // ==================== QUEUE PROCESSING ====================

    public function processQueue($limit = 10): array
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $sql = "SELECT * FROM sms_queue
                WHERE status = 'pending' AND attempts < 3" . $tenantFilter . "
                ORDER BY
                    CASE priority WHEN 'high' THEN 1 WHEN 'normal' THEN 2 ELSE 3 END,
                    created_at ASC
                LIMIT ?";
        $params = [$limit];
        if ($tid > 1) $params[] = $tid;
        $items = $this->db->fetchAll($sql, $params);
        $results = ['processed' => 0, 'sent' => 0, 'failed' => 0];
        foreach ($items as $item) {
            $ok = $this->sendFromQueue($item['id']);
            $results['processed']++;
            if ($ok) $results['sent']++; else $results['failed']++;
        }
        return $results;
    }

    public function sendFromQueue(int $queueId): bool
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $sql = "SELECT * FROM sms_queue WHERE id = ? AND status = 'pending'" . $tenantFilter;
        $params = [$queueId];
        if ($tid > 1) $params[] = $tid;
        $sms = $this->db->fetchOne($sql, $params);
        if (!$sms) return false;

        $updParams = [$queueId];
        if ($tid > 1) $updParams[] = $tid;
        $this->db->query("UPDATE sms_queue SET attempts = attempts + 1 WHERE id = ?" . $tenantFilter, $updParams);

        try {
            $result = $this->send($sms['recipient'], $sms['message']);
            $status = ($result['success'] ?? false) ? 'sent' : 'failed';
            $updParams2 = [$status, $result['error'] ?? null, $queueId];
            if ($tid > 1) $updParams2[] = $tid;
            $this->db->query(
                "UPDATE sms_queue SET status = ?, sent_at = NOW() WHERE id = ?" . $tenantFilter,
                [$updParams2[0], $updParams2[2]]
            );
            return $result['success'] ?? false;
        } catch (\Throwable $e) {
            $updParams3 = [$e->getMessage(), $queueId];
            if ($tid > 1) $updParams3[] = $tid;
            $this->db->query(
                "UPDATE sms_queue SET status = 'failed' WHERE id = ?" . $tenantFilter,
                [$updParams3[1]]
            );
            return false;
        }
    }

    // ==================== OTP MANAGEMENT (from SMSService) ====================

    public function sendOTP($mobile, $otp = null, $templateId = null): array
    {
        try {
            if (!$otp) {
                $otp = $this->generateOTP();
            }

            $mobile = $this->cleanPhone($mobile);
            $this->saveOTP($mobile, $otp);

            $effectiveTemplateId = $templateId ?? $this->templateId;

            if (empty($effectiveTemplateId)) {
                error_log("SMS OTP skipped for {$mobile}: MSG91_TEMPLATE_ID not configured (DLT template required). OTP generated locally: valid in DB.");
                return [
                    'success' => false,
                    'otp' => $otp,
                    'error' => 'SMS template not configured',
                    'skipped' => true
                ];
            }

            $payload = [
                'template_id' => $effectiveTemplateId,
                'short_url' => '0',
                'realTimeResponse' => 'true',
                'recipients' => [
                    [
                        'mobiles' => $mobile,
                        'OTP' => $otp
                    ]
                ]
            ];

            $result = $this->callAPI($this->otpApiUrl, $payload);

            $this->logSMS($mobile, 'OTP', "Your OTP is: {$otp}", $result['type'] ?? 'unknown');

            return [
                'success' => true,
                'otp' => $otp,
                'message_id' => $result['message_id'] ?? null
            ];

        } catch (\Throwable $e) {
            error_log("SMS OTP send failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function verifyOTP($mobile, $otp): array
    {
        try {
            $mobile = $this->cleanPhone($mobile);

            $record = $this->db->fetchOne(
                "SELECT * FROM otp_verifications
                 WHERE identifier = ? AND otp_code = ? AND used_at IS NULL
                 AND expires_at > NOW()
                 ORDER BY created_at DESC LIMIT 1",
                [$mobile, $otp]
            );

            if (!$record) {
                return ['success' => false, 'error' => 'OTP expired, invalid, or already used'];
            }

            $this->db->query(
                "UPDATE otp_verifications SET used_at = NOW() WHERE id = ?",
                [$record['id']]
            );

            return ['success' => true];

        } catch (\Throwable $e) {
            error_log("OTP verification failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ==================== HIGH-LEVEL SMS METHODS (from SMSService) ====================

    public function sendWelcomeSMS($mobile, $name, $userType = 'customer'): array
    {
        $message = $userType === 'associate'
            ? "Hi {$name}, Welcome to APS Dream Home Associate Program! Your referral journey starts now. Login: " . BASE_URL . "/associate/login"
            : "Hi {$name}, Welcome to APS Dream Home! Your dream property awaits. Start browsing: " . BASE_URL . "/properties";

        return $this->send($mobile, $message, 'WELCOME');
    }

    public function sendPaymentConfirmationSMS($mobile, $amount, $bookingId): array
    {
        $message = "Payment Successful! Amount: Rs.{$amount} received for Booking #{$bookingId}. Thank you for choosing APS Dream Home.";
        return $this->send($mobile, $message, 'PAYMENT');
    }

    public function sendCommissionSMS($mobile, $amount, $walletBalance): array
    {
        $message = "Commission Credited! Rs.{$amount} added to your wallet. Current Balance: Rs.{$walletBalance}. Keep referring!";
        return $this->send($mobile, $message, 'COMMISSION');
    }

    public function sendPropertyApprovalSMS($mobile, $propertyTitle): array
    {
        $message = "Your property listing '{$propertyTitle}' has been approved and is now live on APS Dream Home!";
        return $this->send($mobile, $message, 'PROPERTY');
    }

    public function sendVisitReminderSMS($mobile, $propertyTitle, $visitDate, $visitTime): array
    {
        $formattedDate = date('d M Y', strtotime($visitDate));
        $message = "Reminder: Site visit scheduled for {$propertyTitle} on {$formattedDate} at {$visitTime}. Our executive will meet you there.";
        return $this->send($mobile, $message, 'VISIT_REMINDER');
    }

    public function sendPayoutSMS($mobile, $amount, $transactionId): array
    {
        $message = "Payout Processed! Rs.{$amount} has been transferred to your bank account. Transaction ID: {$transactionId}";
        return $this->send($mobile, $message, 'PAYOUT');
    }

    public function sendLoginAlertSMS($mobile, $name, $ip, $device, $time): array
    {
        $message = "APS Dream Home: New login to your account at {$time} from {$device}. ";
        $message .= "If this wasn't you, change your password immediately. ";
        $message .= "Contact support: " . BASE_URL . "/support";
        return $this->send($mobile, $message, 'LOGIN_ALERT');
    }

    public function sendLowStockAlert($mobile, $productName, $quantity, $reorderLevel): array
    {
        $message = "Low Stock Alert: {$productName} is low on stock. Current: {$quantity}, Reorder level: {$reorderLevel}. Please reorder.";
        return $this->send($mobile, $message, 'LOW_STOCK');
    }

    public function sendOverdueReminderSMS($mobile, $plotNumber, $colonyName, $amount): array
    {
        $message = "Payment Reminder: Maintenance due for plot {$plotNumber} in {$colonyName} is overdue. Outstanding: ₹" . number_format($amount, 2) . ". Please pay to avoid penalties.";
        return $this->send($mobile, $message, 'OVERDUE');
    }

    // ==================== OTP MANAGEMENT ====================

    private function callAPI($url, $payload): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'authkey: ' . $this->apiKey,
            'accept: application/json',
            'content-type: application/json'
        ]);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("CURL Error: " . $error);
        }

        $result = json_decode($response, true);

        if (isset($result['type']) && $result['type'] === 'error') {
            throw new \Exception("MSG91 Error: " . ($result['message'] ?? 'Unknown error'));
        }

        return $result;
    }

    private function generateOTP($length = 6): string
    {
        return str_pad(mt_rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }

    private function cleanPhone($phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) === 10) $phone = '91' . $phone;
        return $phone;
    }

    private function saveOTP($mobile, $otp): void
    {
        try {
            $this->db->query(
                "UPDATE otp_verifications SET expires_at = NOW() WHERE identifier = ? AND used_at IS NULL AND expires_at > NOW()",
                [$mobile]
            );
        } catch (\Throwable $e) {
            error_log("SmsService saveOTP invalidate failed: " . $e->getMessage());
        }

        try {
            $tid = method_exists($this, 'tenantId') ? $this->tenantId() : 1;
            $this->db->insert('otp_verifications', [
                'tenant_id' => $tid,
                'identifier' => $mobile,
                'otp_code' => $otp,
                'type' => 'sms',
                'purpose' => 'account_verification',
                'expires_at' => date('Y-m-d H:i:s', strtotime('+10 minutes')),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Throwable $e) {
            error_log("SmsService saveOTP insert failed: " . $e->getMessage());
        }
    }

    private function logSMS($mobile, $type, $message, $status): void
    {
        try {
            $tid = method_exists($this, 'tenantId') ? $this->tenantId() : 1;
            $this->db->insert('notifications_unified', [
                'tenant_id' => $tid,
                'recipient' => $mobile,
                'notification_type' => 'sms',
                'subject' => $type,
                'message' => substr($message, 0, 500),
                'status' => $status,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Throwable $e) {
            error_log("SMS logging failed: " . $e->getMessage());
        }
    }

    // ==================== LOGGING & QUEUE HELPERS ====================

    private function logToQueue($phone, $message, $status, $error = null): void
    {
        try {
            $this->db->insert('sms_queue', [
                'tenant_id' => $this->tenantId(),
                'recipient' => $phone,
                'message' => substr($message, 0, 500),
                'status' => $status,
                'provider' => $this->provider,
                'attempts' => $status === 'sent' ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s'),
                'sent_at' => $status === 'sent' ? date('Y-m-d H:i:s') : null,
            ]);
        } catch (\Throwable $e) {
            error_log("SmsService: Queue log failed: " . $e->getMessage());
        }
    }

    public function getStats(int $days = 30): array
    {
        return [
            'total_sent' => $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM notifications_unified WHERE status = 'sent' AND notification_type = 'sms' AND created_at > DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$days]
            )['count'] ?? 0,
            'total_failed' => $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM notifications_unified WHERE status = 'failed' AND notification_type = 'sms' AND created_at > DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$days]
            )['count'] ?? 0,
            'otp_sent' => $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM otp_verifications WHERE created_at > DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$days]
            )['count'] ?? 0
        ];
    }

    public function getQueueStats(): array
    {
        $stats = ['pending' => 0, 'sent' => 0, 'failed' => 0];
        try {
            $tid = $this->tenantId();
            $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
            $rows = $this->db->fetchAll("SELECT status, COUNT(*) as cnt FROM sms_queue WHERE 1=1" . $tenantFilter . " GROUP BY status", $tid > 1 ? [$tid] : []);
            foreach ($rows as $r) $stats[$r['status']] = (int)$r['cnt'];
        } catch (\Throwable $e) {
            error_log("SmsService.php: " . $e->getMessage());
        }
        return $stats;
    }

    public function getQueueItems($status = null, $limit = 50, $offset = 0): array
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $where = $status ? "WHERE status = ?" : "";
        $params = $status ? [$status] : [];
        if ($tid > 1) $params[] = $tid;
        try {
            return $this->db->fetchAll(
                "SELECT * FROM sms_queue $where" . $tenantFilter . " ORDER BY created_at DESC LIMIT ? OFFSET ?",
                array_merge($params, [$limit, $offset])
            );
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function tenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable) {
            return 1;
        }
    }
}
<?php
/**
 * NotificationService
 * Centralizes outbound notifications (email for Phase 4 MVP) and logs events in mlm_notification_log.
 */

namespace App\Services;

use App\Models\Database;
use PDO;
use Throwable;

class NotificationService
{
    private ?PDO $conn;
    private array $emailConfig;
    private string $defaultFromAddress;
    private string $defaultFromName;
    private string $adminEmail;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
        
        // Load email config from environment variables or defaults
        $this->emailConfig = [
            'from_email' => getenv('MAIL_FROM_ADDRESS') ?: 'noreply@apsdreamhome.com',
            'from_name' => getenv('MAIL_FROM_NAME') ?: 'APS Dream Home',
            'admin_email' => getenv('MAIL_ADMIN') ?: 'admin@apsdreamhome.com',
            'reply_to' => getenv('MAIL_REPLY_TO'),
        ];
        
        $this->defaultFromAddress = $this->emailConfig['from_email'];
        $this->defaultFromName = $this->emailConfig['from_name'];
        $this->adminEmail = $this->emailConfig['admin_email'];
    }

    /**
     * Send an email notification and log the outcome.
     */
    public function sendEmail(string $to, string $subject, string $body, string $type, ?int $userId = null, array $payload = []): bool
    {
        $logId = $this->logNotification('email', $type, $subject, $body, $userId, $payload);

        try {
            $headers = $this->buildEmailHeaders();
            $sent = mail($to, $subject, $body, $headers);

            if ($sent) {
                $this->updateNotificationStatus($logId, 'sent');
                return true;
            }

            $this->updateNotificationStatus($logId, 'failed', 'mail() returned false');
            return false;
        } catch (Throwable $e) {
            $this->updateNotificationStatus($logId, 'failed', $e->getMessage());
            error_log('NotificationService email error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Convenience wrapper for admin alerts.
     */
    public function notifyAdmin(string $subject, string $body, string $type, array $payload = []): bool
    {
        return $this->sendEmail($this->adminEmail, $subject, $body, $type, null, $payload);
    }

    public function notifyFinance(string $subject, string $body, string $type, array $payload = []): bool
    {
        $financeEmail = $this->emailConfig['finance_email'] ?? $this->adminEmail;
        return $this->sendEmail($financeEmail, $subject, $body, $type, null, $payload);
    }

    /**
     * Insert pending record into mlm_notification_log.
     */
    private function logNotification(string $channel, string $type, ?string $subject, ?string $message, ?int $userId, array $payload): ?int
    {
        if (!$this->conn) {
            return null;
        }

        $sql = "INSERT INTO mlm_notification_log (user_id, channel, type, subject, message, payload, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())";
        $stmt = $this->conn->prepare($sql);
        $payloadJson = !empty($payload) ? json_encode($payload, JSON_UNESCAPED_UNICODE) : null;
        
        try {
            $stmt->execute([$userId, $channel, $type, $subject, $message, $payloadJson]);
            return (int)$this->conn->lastInsertId();
        } catch (Throwable $e) {
            error_log('NotificationService log error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update notification status (sent/failed) and optional error message.
     */
    private function updateNotificationStatus(?int $logId, string $status, ?string $errorMessage = null): void
    {
        if (!$this->conn || !$logId) {
            return;
        }

        $sql = "UPDATE mlm_notification_log SET status = ?, error_message = ?, sent_at = CASE WHEN ? = 'sent' THEN NOW() ELSE sent_at END WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $statusForSent = $status;
        
        try {
            $stmt->execute([$status, $errorMessage, $statusForSent, $logId]);
        } catch (Throwable $e) {
            error_log('NotificationService update status error: ' . $e->getMessage());
        }
    }

    /**
     * Build standard email headers using configuration.
     */
    private function buildEmailHeaders(): string
    {
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=UTF-8';
        $headers[] = 'From: ' . $this->defaultFromName . ' <' . $this->defaultFromAddress . '>';
        if (!empty($this->emailConfig['reply_to'])) {
            $headers[] = 'Reply-To: ' . $this->emailConfig['reply_to'];
        }
        $headers[] = 'X-Mailer: APS Dream Home';

        return implode("\r\n", $headers);
    }
}


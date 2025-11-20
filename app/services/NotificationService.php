<?php
/**
 * NotificationService
 * Centralizes outbound notifications (email for Phase 4 MVP) and logs events in mlm_notification_log.
 */

class NotificationService
{
    private ?mysqli $conn;
    private array $emailConfig;
    private string $defaultFromAddress;
    private string $defaultFromName;
    private string $adminEmail;

    public function __construct()
    {
        $appConfig = AppConfig::getInstance();
        $this->conn = $appConfig->getDatabaseConnection();
        $this->emailConfig = $appConfig->get('email', []);
        $this->defaultFromAddress = $this->emailConfig['from_email'] ?? 'noreply@apsdreamhome.com';
        $this->defaultFromName = $this->emailConfig['from_name'] ?? 'APS Dream Home';
        $this->adminEmail = $this->emailConfig['admin_email'] ?? 'admin@apsdreamhome.com';
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

        $sql = "INSERT INTO mlm_notification_log (user_id, channel, type, subject, message, payload, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')";
        $stmt = $this->conn->prepare($sql);
        $payloadJson = !empty($payload) ? json_encode($payload, JSON_UNESCAPED_UNICODE) : null;
        $stmt->bind_param('isssss', $userId, $channel, $type, $subject, $message, $payloadJson);
        $stmt->execute();
        $logId = $stmt->insert_id;
        $stmt->close();

        return $logId;
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
        $stmt->bind_param('sssi', $status, $errorMessage, $statusForSent, $logId);
        $stmt->execute();
        $stmt->close();
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

<?php

namespace App\Services\Communication;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use \App\Traits\ServiceTenantTrait;

/**
 * Unified Email Service
 * Merges EmailService (templates, notifications) + EmailSenderService (queue processing)
 * Single entry point for all email operations with queue support.
 */
class EmailService
{
    use \App\Traits\ServiceTenantTrait;

    private $mailer;
    private $db;
    private $config = [];
    private $fromEmail;
    private $fromName;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->loadConfig();
        $this->initMailer();
    }

    private function loadConfig()
    {
        $this->config = [
            'host' => $_ENV['SMTP_HOST'] ?? $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com',
            'port' => (int)($_ENV['SMTP_PORT'] ?? $_ENV['MAIL_PORT'] ?? 587),
            'user' => $_ENV['SMTP_USER'] ?? $_ENV['MAIL_USERNAME'] ?? '',
            'pass' => $_ENV['SMTP_PASS'] ?? $_ENV['MAIL_PASSWORD'] ?? '',
            'encryption' => $_ENV['SMTP_ENCRYPTION'] ?? $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
            'from_email' => $_ENV['SMTP_FROM_EMAIL'] ?? $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@apsdreamhome.com',
            'from_name' => $_ENV['SMTP_FROM_NAME'] ?? $_ENV['MAIL_FROM_NAME'] ?? 'APS Dream Home',
        ];

        $this->fromEmail = $this->config['from_email'];
        $this->fromName = $this->config['from_name'];
    }

    private function initMailer()
    {
        $this->mailer = new PHPMailer(true);
        try {
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['user'];
            $this->mailer->Password = $this->config['pass'];
            $this->mailer->SMTPSecure = $this->config['encryption'];
            $this->mailer->Port = $this->config['port'];
            $this->mailer->setFrom($this->config['from_email'], $this->config['from_name']);
            $this->mailer->isHTML(true);
        } catch (\Throwable $e) {
            error_log("EmailService: Mailer init failed: " . $e->getMessage());
        }
    }

    // ==================== CORE SEND METHODS ====================

    /**
     * Send email immediately (bypasses queue)
     */
    public function send($to, $subject, $bodyHtml, $bodyText = '', $attachments = [])
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $bodyHtml;
            $this->mailer->AltBody = $bodyText ?: strip_tags($bodyHtml);

            // Add attachments
            $attachments = (array) $attachments;
            foreach ($attachments as $attachment) {
                if (isset($attachment['path']) && file_exists($attachment['path'])) {
                    $this->mailer->addAttachment(
                        $attachment['path'],
                        $attachment['name'] ?? basename($attachment['path'])
                    );
                }
            }

            // Add CC if configured
            $adminEmail = $_ENV['ADMIN_EMAIL'] ?? null;
            if ($adminEmail && $adminEmail !== $to) {
                $this->mailer->addBCC($adminEmail);
            }

            $result = $this->mailer->send();

            // Log to database
            $this->logEmail($to, $subject, $bodyHtml, $result ? 'sent' : 'failed');

            return $result;
        } catch (PHPMailerException $e) {
            error_log("EmailService: Send failed: " . $e->getMessage());
            $this->logEmail($to, $subject, $bodyHtml, 'failed', $e->getMessage());
            return false;
        } catch (\Throwable $e) {
            error_log("EmailService: Unexpected error: " . $e->getMessage());
            $this->logEmail($to, $subject, $bodyHtml, 'failed', $e->getMessage());
            return false;
        }
    }

    /**
     * Queue email for async processing
     */
    public function queue($to, $subject, $bodyHtml, $bodyText = '', $attachments = [], $priority = 'normal')
    {
        try {
            $tid = $this->tenantId();
            $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";

            $this->db->insert('email_queue', [
                'tenant_id' => $this->tenantId(),
                'to_email' => $to,
                'subject' => $subject,
                'body_html' => $bodyHtml,
                'body_text' => $bodyText ?: strip_tags($bodyHtml),
                'attachments' => json_encode($attachments),
                'priority' => $priority,
                'status' => 'pending',
                'attempts' => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            return true;
        } catch (\Throwable $e) {
            error_log("EmailService: Queue failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send using template
     */
    public function sendTemplate($to, $templateCode, array $vars = [], $priority = 'normal', $attachments = [])
    {
        $rendered = $this->renderTemplate($templateCode, $vars);
        if (!$rendered['ok']) {
            error_log("EmailService: Template render failed for {$templateCode}: " . ($rendered['error'] ?? 'unknown'));
            return false;
        }

        return $this->send(
            $to,
            $rendered['subject'],
            $rendered['html'],
            $rendered['text'] ?? '',
            [],
            $priority
        );
    }

    /**
     * Queue email using template
     */
    public function queueTemplate($to, $templateCode, array $vars = [], $priority = 'normal', $attachments = [])
    {
        $rendered = $this->renderTemplate($templateCode, $vars);
        if (!$rendered['ok']) {
            error_log("EmailService: Template render failed for {$templateCode}: " . ($rendered['error'] ?? 'unknown'));
            return false;
        }

        return $this->queue($to, $rendered['subject'], $rendered['html'], $rendered['text'] ?? '', $attachments, $priority);
    }

    // ==================== QUEUE PROCESSING ====================

    /**
     * Process email queue
     */
    public function processQueue($limit = 10)
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $sql = "SELECT * FROM email_queue
                WHERE status = 'pending' AND attempts < 3" . $tenantFilter . "
                ORDER BY
                    CASE priority WHEN 'high' THEN 1 WHEN 'normal' THEN 2 ELSE 3 END,
                    created_at ASC
                LIMIT ?";
        $params = [$limit];
        if ($tid > 1) $params[] = $tid;
        $emails = $this->db->fetchAll($sql, $params);
        $results = ['processed' => 0, 'sent' => 0, 'failed' => 0];

        foreach ($emails as $email) {
            $ok = $this->sendFromQueue($email['id']);
            $results['processed']++;
            if ($ok) $results['sent']++; else $results['failed']++;
        }
        return $results;
    }

    

    // ==================== TEMPLATE RENDERING ====================

    /**
     * Render template by code
     */
    private function renderTemplate(string $code, array $vars = []): array
    {
        try {
            if (!class_exists('App\Services\Communication\TemplateService')) {
                return ['ok' => false, 'error' => 'TemplateService not available'];
            }
            $svc = new \App\Services\Communication\TemplateService();
            $result = $svc->renderHtmlTemplate($code, $vars);
            if (!($result['ok'] ?? false)) {
                return ['ok' => false, 'error' => $result['error'] ?? 'Render failed'];
            }
            return [
                'ok' => true,
                'subject' => $result['subject'] ?? 'Notification',
                'html' => $result['html'] ?? '',
                'text' => $result['text'] ?? '',
            ];
        } catch (\Throwable $t) {
            error_log('EmailService: renderTemplate failed: ' . $t->getMessage());
            return ['ok' => false, 'error' => $t->getMessage()];
        }
    }

    // ==================== CONVENIENCE METHODS (from old EmailService) ====================

    public function sendWelcomeEmail($userId)
    {
        try {
            $user = $this->db->fetchOne(
                "SELECT * FROM users WHERE id = ?",
                [$userId]
            );
            if (!$user) return false;

            return $this->sendTemplate($user['email'], 'welcome', [
                'name' => $user['name'],
                'login_url' => BASE_URL . '/login',
                'logo_url' => BASE_URL . '/assets/images/logo.png',
                'unsubscribe_url' => BASE_URL . '/unsubscribe',
                'preferences_url' => BASE_URL . '/email-preferences',
            ]);
        } catch (\Throwable $e) {
            error_log("Welcome email failed: " . $e->getMessage());
            return false;
        }
    }

    public function sendAssociateWelcomeEmail($userId, $referralCode)
    {
        try {
            $user = $this->db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
            if (!$user) return false;

            return $this->sendTemplate($user['email'], 'associate_welcome', [
                'name' => $user['name'],
                'email' => $user['email'],
                'associate_id' => $user['customer_id'] ?? $user['id'],
                'referral_code' => $referralCode,
                'referral_link' => BASE_URL . '/register?ref=' . $referralCode,
                'dashboard_url' => BASE_URL . '/associate/dashboard',
                'network_url' => BASE_URL . '/associate/network',
            ]);
        } catch (\Throwable $e) {
            error_log("Associate welcome email failed: " . $e->getMessage());
            return false;
        }
    }

    public function sendPaymentConfirmation($paymentId)
    {
        try {
            $tid = $this->tenantId();
            $tenantSql = $tid > 1 ? " AND u.tenant_id = ?" : "";
            $payment = $this->db->fetchOne(
                "SELECT p.*, u.name, u.email, b.property_id, pr.title as property_title
                 FROM payments p
                 JOIN users u ON p.user_id = u.id{$tenantSql}
                 JOIN bookings b ON p.booking_id = b.id
                 JOIN properties pr ON b.property_id = pr.id
                 WHERE p.id = ?",
                $tid > 1 ? [$paymentId, $this->tenantId()] : [$paymentId]
            );

            if (!$payment) return false;

            return $this->sendTemplate($payment['email'], 'payment_confirmation', [
                'name' => $payment['name'],
                'amount' => number_format($payment['amount'], 2),
                'transaction_id' => $payment['transaction_id'],
                'booking_id' => $payment['booking_id'],
                'property_title' => $payment['property_title'],
                'payment_date' => date('d M Y, h:i A', strtotime($payment['created_at'])),
                'dashboard_url' => BASE_URL . '/user/dashboard',
            ]);
        } catch (\Throwable $e) {
            error_log("Payment confirmation email failed: " . $e->getMessage());
            return false;
        }
    }

    public function sendPasswordResetEmail($email, $token)
    {
        $resetUrl = BASE_URL . '/reset-password?token=' . $token;
        return $this->sendTemplate($email, 'password_reset', [
            'reset_url' => $resetUrl,
            'expiry_hours' => 24,
        ]);
    }

    public function sendOTPEmail($email, $otp, $purpose = 'verification')
    {
        return $this->sendTemplate($email, 'otp', [
            'otp' => $otp,
            'purpose' => $purpose,
            'expiry_minutes' => 10,
        ]);
    }

    public function sendDailyReport($adminEmail)
    {
        $tid = $this->tenantId();
        $tenantSql = $this->tenantId() > 1 ? " AND tenant_id = ?" : "";
        $stats = $this->db->fetchOne(
            "SELECT
                (SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()) as new_users,
                (SELECT COUNT(*) FROM leads WHERE DATE(created_at) = CURDATE()) as new_leads,
                (SELECT COUNT(*) FROM bookings WHERE DATE(created_at) = CURDATE()) as new_bookings,
                (SELECT SUM(amount) FROM payments WHERE DATE(created_at) = CURDATE() AND status = 'completed') as revenue
            ",
            $this->tenantId() > 1 ? [$this->tenantId()] : []
        );

        return $this->sendTemplate($adminEmail, 'daily_report', [
            'date' => date('d M Y'),
            'new_users' => $stats['new_users'] ?? 0,
            'new_leads' => $stats['new_leads'] ?? 0,
            'new_bookings' => $stats['new_bookings'] ?? 0,
            'revenue' => number_format($stats['revenue'] ?? 0, 2),
            'admin_url' => BASE_URL . '/admin/dashboard',
        ]);
    }

    // ==================== QUEUE MANAGEMENT ====================

    private function sendFromQueue(int $queueId): bool
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $sql = "SELECT * FROM email_queue WHERE id = ? AND status = 'pending'" . $tenantFilter;
        $params = [$queueId];
        if ($tid > 1) $params[] = $tid;
        $email = $this->db->fetchOne($sql, $params);
        if (!$email) return false;

        $updParams = [$queueId];
        if ($tid > 1) $updParams[] = $tid;
        $this->db->query("UPDATE email_queue SET status = 'processing', attempts = attempts + 1 WHERE id = ?" . $tenantFilter, $updParams);

        try {
            $sent = $this->send(
                $email['to_email'],
                $email['subject'],
                $email['body_html'],
                $email['body_text'] ?? '',
                $email['attachments'] ? json_decode($email['attachments'], true) : []
            );
            $status = $sent ? 'sent' : 'failed';
            $error = $sent ? null : 'Send method returned false';
            $updParams2 = [$status, $error, $queueId];
            if ($tid > 1) $updParams2[] = $tid;
            $this->db->query(
                "UPDATE email_queue SET status = ?, sent_at = NOW(), error_message = ? WHERE id = ?" . $tenantFilter,
                $updParams2
            );
            return $sent;
        } catch (\Throwable $e) {
            $updParams3 = [$e->getMessage(), $queueId];
            if ($tid > 1) $updParams3[] = $tid;
            $this->db->query(
                "UPDATE email_queue SET status = 'failed', error_message = ? WHERE id = ?" . $tenantFilter,
                $updParams3
            );
            return false;
        }
    }

    /**
     * Log email to database
     */
    private function logEmail($to, $subject, $body, $status, $error = null): void
    {
        try {
            $this->db->insert('notifications_unified', [
                'tenant_id' => $this->tenantId(),
                'recipient' => $to,
                'subject' => $subject,
                'message' => substr($body, 0, 1000),
                'status' => $status,
                'error_message' => $error,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            error_log("Email logging failed: " . $e->getMessage());
        }
    }

    // ==================== QUEUE MANAGEMENT API ====================

    public function getQueueStats(): array
    {
        $stats = ['pending' => 0, 'processing' => 0, 'sent' => 0, 'failed' => 0, 'cancelled' => 0];
        try {
            $tid = $this->tenantId();
            $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
            $rows = $this->db->fetchAll(
                "SELECT status, COUNT(*) as cnt FROM email_queue WHERE 1=1" . $tenantFilter . " GROUP BY status",
                $tid > 1 ? [$tid] : []
            );
            foreach ($rows as $r) $stats[$r['status']] = (int)$r['cnt'];
        } catch (\Throwable $e) {
            error_log("EmailService: getQueueStats failed: " . $e->getMessage());
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
            $sql = "SELECT * FROM email_queue $where" . $tenantFilter . " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            return $this->db->fetchAll($sql, array_merge($params, [$limit, $offset]));
        } catch (\Throwable $e) {
            return [];
        }
    }

    // ==================== HELPER METHODS ====================

    private function tenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable) {
            return 1;
        }
    }

    // ==================== BACKWARD COMPATIBILITY ALIASES ====================

    /**
     * Alias for getQueueStats() - backward compatibility with EmailQueueService
     */
    public function getStats(): array
    {
        return $this->getQueueStats();
    }

    /**
     * Retry failed emails - backward compatibility with EmailQueueService
     */
    public function retryFailed(): int
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $maxRetries = 3;
        $params = [$maxRetries];
        if ($tid > 1) $params[] = $tid;

        try {
            $stmt = $this->db->prepare(
                "UPDATE email_queue
                 SET status = 'pending', attempts = 0, error_message = NULL
                 WHERE status = 'failed' AND attempts < ?" . $tenantFilter
            );
            $stmt->execute($params);
            return (int)$stmt->rowCount();
        } catch (\Throwable $e) {
            error_log("EmailService: retryFailed failed: " . $e->getMessage());
            return 0;
        }
    }

    // ==================== TEMPLATE-SPECIFIC CONVENIENCE METHODS ====================
    // These methods replace App\Services\EmailTemplateService template-specific calls.
    // Each method looks up the user, merges data, renders the view template, and sends.

    /**
     * Send agreement pending notification
     * Template: agreement_pending
     */
    public function sendAgreementPending(int $userId, array $data = []): bool
    {
        return $this->sendTemplateByUser($userId, 'agreement_pending', 'Agreement Ready for Signing - APS Dream Home', $data, [
            'booking_number' => '', 'plot_number' => '', 'colony_name' => '',
            'total_amount' => '', 'token_amount' => '', 'agreement_url' => '',
        ]);
    }

    /**
     * Send agreement signed notification
     * Template: agreement_signed
     */
    public function sendAgreementSigned(int $userId, array $data = []): bool
    {
        return $this->sendTemplateByUser($userId, 'agreement_signed', 'Agreement Signed Successfully - APS Dream Home', $data, [
            'booking_number' => '', 'plot_number' => '', 'colony_name' => '',
            'signed_date' => '', 'signed_ip' => '', 'booking_url' => '',
        ]);
    }

    /**
     * Send KYC approved notification
     * Template: kyc_approved
     */
    public function sendKycApproved(int $userId, array $data = []): bool
    {
        return $this->sendTemplateByUser($userId, 'kyc_approved', 'KYC Verified Successfully - APS Dream Home', $data, [
            'pan_number' => 'XXXXX1234X', 'aadhaar_last4' => 'XXXX',
            'verified_date' => date('d F Y'), 'dashboard_url' => BASE_URL . '/user/dashboard',
        ]);
    }

    /**
     * Send KYC rejected notification
     * Template: kyc_rejected
     */
    public function sendKycRejected(int $userId, array $data = []): bool
    {
        return $this->sendTemplateByUser($userId, 'kyc_rejected', 'KYC Requires Update - APS Dream Home', $data, [
            'rejection_reason' => 'Document quality issue', 'kyc_url' => BASE_URL . '/user/kyc',
        ]);
    }

    /**
     * Send support ticket created notification
     * Template: support_ticket_created
     */
    public function sendSupportTicketCreated(int $userId, array $data = []): bool
    {
        return $this->sendTemplateByUser($userId, 'support_ticket_created', 'Support Ticket #' . ($data['ticket_id'] ?? '') . ' Created', $data, [
            'ticket_id' => '0000', 'subject' => 'Support Request', 'category' => 'General',
            'priority' => 'Medium', 'created_date' => date('d F Y, h:i A'),
            'ticket_url' => BASE_URL . '/user/support/tickets/',
        ]);
    }

    /**
     * Send support ticket reply notification
     * Template: support_ticket_reply
     */
    public function sendSupportTicketReply(int $userId, array $data = []): bool
    {
        return $this->sendTemplateByUser($userId, 'support_ticket_reply', 'New Reply on Ticket #' . ($data['ticket_id'] ?? ''), $data, [
            'ticket_id' => '0000', 'subject' => 'Support Request', 'agent_name' => 'Support Team',
            'reply_message' => '', 'reply_date' => date('d F Y, h:i A'),
            'ticket_url' => BASE_URL . '/user/support/tickets/',
        ]);
    }

    /**
     * Send EMI reminder notification
     * Template: emi_reminder
     */
    public function sendEmiReminder(int $userId, array $data = []): bool
    {
        $dueDate = $data['due_date'] ?? date('Y-m-d', strtotime('+7 days'));
        $daysUntil = max(0, (int)((strtotime($dueDate) - time()) / 86400));
        $data = array_merge($data, [
            'due_date' => date('d F Y', strtotime($dueDate)),
            'days_until_due' => $daysUntil,
            'pay_url' => BASE_URL . '/user/bookings/' . ($data['booking_id'] ?? ''),
        ]);
        return $this->sendTemplateByUser($userId, 'emi_reminder', 'EMI Payment Reminder - Installment #' . ($data['installment_no'] ?? ''), $data, [
            'installment_no' => '', 'emi_amount' => '', 'booking_number' => '',
            'plot_number' => '', 'colony_name' => '',
        ]);
    }

    /**
     * Send booking cancellation notification
     * Template: booking_cancellation
     */
    public function sendBookingCancellation(int $userId, array $data = []): bool
    {
        $totalPaid = (float)($data['total_paid'] ?? 0);
        $charge = (float)($data['cancellation_charge'] ?? 0);
        $refund = max(0, $totalPaid - $charge);
        $data = array_merge($data, [
            'refund_amount' => number_format($refund),
            'cancellation_charge' => number_format($charge),
            'refund_method' => $data['refund_method'] ?? 'Original payment method',
        ]);
        return $this->sendTemplateByUser($userId, 'booking_cancellation', 'Booking Cancelled - ' . ($data['booking_number'] ?? ''), $data, [
            'booking_number' => '', 'plot_number' => '', 'colony_name' => '',
            'cancellation_reason' => 'Customer request',
        ]);
    }

    /**
     * Send referral commission notification
     * Template: referral_commission
     */
    public function sendReferralCommission(int $userId, array $data = []): bool
    {
        return $this->sendTemplateByUser($userId, 'referral_commission', 'Referral Commission Credited - APS Dream Home', $data, [
            'referred_name' => 'a friend', 'booking_number' => '', 'plot_number' => '',
            'colony_name' => '', 'booking_value' => '', 'commission_amount' => '',
            'commission_rate' => '1', 'credited_date' => date('d F Y'),
            'referral_url' => BASE_URL . '/user/referral', 'referral_code' => '',
        ]);
    }

    /**
     * Send site visit confirmed notification
     * Template: site_visit_confirmed
     */
    public function sendSiteVisitConfirmed(int $userId, array $data = []): bool
    {
        return $this->sendTemplateByUser($userId, 'site_visit_confirmed', 'Site Visit Confirmed - APS Dream Home', $data, [
            'plot_number' => '', 'colony_name' => '', 'colony_address' => '',
            'visit_date' => date('d F Y'), 'visit_time' => '10:00 AM',
            'agent_name' => 'APS Agent', 'agent_phone' => '+91 92771 21112',
            'map_url' => 'https://maps.google.com',
        ]);
    }

    // ==================== PRIVATE HELPERS ====================

    /**
     * Look up user by ID, merge data, render template view, and send email.
     */
    private function sendTemplateByUser(int $userId, string $templateCode, string $subject, array $data, array $defaults): bool
    {
        $user = $this->db->fetchOne("SELECT id, name, email, phone FROM users WHERE id = ?", [$userId]);
        if (!$user || empty($user['email'])) {
            return false;
        }

        // Merge defaults first, then user-provided data (user data takes precedence)
        $merged = array_merge($defaults, $data);
        $merged['user_name'] = $user['name'] ?? 'Customer';

        // Render HTML from view template
        $templateFile = __DIR__ . '/../../views/emails/' . $templateCode . '.php';
        if (!file_exists($templateFile)) {
            error_log("EmailService: Template not found: {$templateCode}");
            return false;
        }

        extract($merged, EXTR_SKIP);
        ob_start();
        include $templateFile;
        $html = ob_get_clean();

        // Replace {{var}} placeholders
        foreach ($merged as $key => $value) {
            $html = str_replace('{{' . $key . '}}', htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'), $html);
        }

        return $this->send($user['email'], $subject, $html);
    }
}
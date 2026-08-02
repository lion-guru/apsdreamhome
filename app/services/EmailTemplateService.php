<?php

namespace App\Services;

use App\Core\Database\Database;
use \App\Traits\ServiceTenantTrait;

/**
 * EmailTemplateService - Renders and sends production-quality HTML emails
 * from view templates in app/views/emails/
 * 
 * Usage:
 *   $svc = new EmailTemplateService();
 *   $svc->sendKycApproved($userId, ['user_name' => '...', ...]);
 *   $svc->sendSupportTicketCreated($userId, ['ticket_id' => 123, ...]);
 */
class EmailTemplateService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $fromEmail;
    private $fromName;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->fromEmail = $_ENV['SMTP_FROM_EMAIL'] ?? 'notifications@apsdreamhome.com';
        $this->fromName = $_ENV['SMTP_FROM_NAME'] ?? 'APS Dream Home';
    }

    // ──────────────────────────────────────────────
    // Public API — one method per template
    // ──────────────────────────────────────────────

    public function sendKycApproved(int $userId, array $data = []): bool
    {
        $user = $this->getUser($userId);
        $data = array_merge($data, [
            'user_name' => $user['name'] ?? 'Customer',
            'pan_number' => $data['pan_number'] ?? 'XXXXX1234X',
            'aadhaar_last4' => $data['aadhaar_last4'] ?? 'XXXX',
            'verified_date' => date('d F Y'),
            'dashboard_url' => BASE_URL . '/user/dashboard',
            'year' => date('Y'),
        ]);
        return $this->send($user['email'], 'KYC Verified Successfully - APS Dream Home', 'kyc_approved', $data, (int)$userId);
    }

    public function sendKycRejected(int $userId, array $data = []): bool
    {
        $user = $this->getUser($userId);
        $data = array_merge($data, [
            'user_name' => $user['name'] ?? 'Customer',
            'rejection_reason' => $data['rejection_reason'] ?? 'Document quality issue',
            'kyc_url' => BASE_URL . '/user/kyc',
            'year' => date('Y'),
        ]);
        return $this->send($user['email'], 'KYC Requires Update - APS Dream Home', 'kyc_rejected', $data, (int)$userId);
    }

    public function sendSupportTicketCreated(int $userId, array $data = []): bool
    {
        $user = $this->getUser($userId);
        $data = array_merge($data, [
            'user_name' => $user['name'] ?? 'Customer',
            'ticket_id' => $data['ticket_id'] ?? '0000',
            'subject' => $data['subject'] ?? 'Support Request',
            'category' => $data['category'] ?? 'General',
            'priority' => ucfirst($data['priority'] ?? 'Medium'),
            'created_date' => date('d F Y, h:i A'),
            'ticket_url' => BASE_URL . '/user/support/tickets/' . ($data['ticket_id'] ?? ''),
            'year' => date('Y'),
        ]);
        return $this->send($user['email'], 'Support Ticket #' . ($data['ticket_id'] ?? '') . ' Created', 'support_ticket_created', $data, (int)$userId);
    }

    public function sendSupportTicketReply(int $userId, array $data = []): bool
    {
        $user = $this->getUser($userId);
        $data = array_merge($data, [
            'user_name' => $user['name'] ?? 'Customer',
            'ticket_id' => $data['ticket_id'] ?? '0000',
            'subject' => $data['subject'] ?? 'Support Request',
            'agent_name' => $data['agent_name'] ?? 'Support Team',
            'reply_message' => $data['reply_message'] ?? '',
            'reply_date' => date('d F Y, h:i A'),
            'ticket_url' => BASE_URL . '/user/support/tickets/' . ($data['ticket_id'] ?? ''),
            'year' => date('Y'),
        ]);
        return $this->send($user['email'], 'New Reply on Ticket #' . ($data['ticket_id'] ?? ''), 'support_ticket_reply', $data, (int)$userId);
    }

    public function sendAgreementPending(int $userId, array $data = []): bool
    {
        $user = $this->getUser($userId);
        $data = array_merge($data, [
            'user_name' => $user['name'] ?? 'Customer',
            'booking_number' => $data['booking_number'] ?? '',
            'plot_number' => $data['plot_number'] ?? '',
            'colony_name' => $data['colony_name'] ?? '',
            'total_amount' => $data['total_amount'] ?? '',
            'token_amount' => $data['token_amount'] ?? '',
            'agreement_url' => BASE_URL . '/user/agreements/' . ($data['agreement_id'] ?? ''),
            'year' => date('Y'),
        ]);
        return $this->send($user['email'], 'Agreement Ready for Signing - APS Dream Home', 'agreement_pending', $data, (int)$userId);
    }

    public function sendAgreementSigned(int $userId, array $data = []): bool
    {
        $user = $this->getUser($userId);
        $data = array_merge($data, [
            'user_name' => $user['name'] ?? 'Customer',
            'booking_number' => $data['booking_number'] ?? '',
            'plot_number' => $data['plot_number'] ?? '',
            'colony_name' => $data['colony_name'] ?? '',
            'signed_date' => date('d F Y, h:i A'),
            'signed_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'booking_url' => BASE_URL . '/user/bookings/' . ($data['booking_id'] ?? ''),
            'year' => date('Y'),
        ]);
        return $this->send($user['email'], 'Agreement Signed Successfully - APS Dream Home', 'agreement_signed', $data, (int)$userId);
    }

    public function sendEmiReminder(int $userId, array $data = []): bool
    {
        $user = $this->getUser($userId);
        $dueDate = $data['due_date'] ?? date('Y-m-d', strtotime('+7 days'));
        $daysUntil = max(0, (int)((strtotime($dueDate) - time()) / 86400));
        $data = array_merge($data, [
            'user_name' => $user['name'] ?? 'Customer',
            'installment_no' => $data['installment_no'] ?? '',
            'emi_amount' => number_format((float)($data['emi_amount'] ?? 0)),
            'due_date' => date('d F Y', strtotime($dueDate)),
            'days_until_due' => $daysUntil,
            'booking_number' => $data['booking_number'] ?? '',
            'plot_number' => $data['plot_number'] ?? '',
            'colony_name' => $data['colony_name'] ?? '',
            'pay_url' => BASE_URL . '/user/bookings/' . ($data['booking_id'] ?? ''),
            'year' => date('Y'),
        ]);
        return $this->send($user['email'], 'EMI Payment Reminder - Installment #' . ($data['installment_no'] ?? ''), 'emi_reminder', $data, (int)$userId);
    }

    public function sendBookingCancellation(int $userId, array $data = []): bool
    {
        $user = $this->getUser($userId);
        $totalPaid = (float)($data['total_paid'] ?? 0);
        $charge = (float)($data['cancellation_charge'] ?? 0);
        $refund = max(0, $totalPaid - $charge);
        $data = array_merge($data, [
            'user_name' => $user['name'] ?? 'Customer',
            'booking_number' => $data['booking_number'] ?? '',
            'plot_number' => $data['plot_number'] ?? '',
            'colony_name' => $data['colony_name'] ?? '',
            'cancellation_reason' => $data['cancellation_reason'] ?? 'Customer request',
            'refund_amount' => number_format($refund),
            'cancellation_charge' => number_format($charge),
            'refund_method' => $data['refund_method'] ?? 'Original payment method',
            'year' => date('Y'),
        ]);
        return $this->send($user['email'], 'Booking Cancelled - ' . ($data['booking_number'] ?? ''), 'booking_cancellation', $data, (int)$userId);
    }

    public function sendReferralCommission(int $userId, array $data = []): bool
    {
        $user = $this->getUser($userId);
        $data = array_merge($data, [
            'user_name' => $user['name'] ?? 'Customer',
            'referred_name' => $data['referred_name'] ?? 'a friend',
            'booking_number' => $data['booking_number'] ?? '',
            'plot_number' => $data['plot_number'] ?? '',
            'colony_name' => $data['colony_name'] ?? '',
            'booking_value' => number_format((float)($data['booking_value'] ?? 0)),
            'commission_amount' => number_format((float)($data['commission_amount'] ?? 0)),
            'commission_rate' => $data['commission_rate'] ?? '1',
            'credited_date' => date('d F Y'),
            'referral_url' => BASE_URL . '/user/referral',
            'referral_code' => $data['referral_code'] ?? '',
            'year' => date('Y'),
        ]);
        return $this->send($user['email'], 'Referral Commission Credited - APS Dream Home', 'referral_commission', $data, (int)$userId);
    }

    public function sendSiteVisitConfirmed(int $userId, array $data = []): bool
    {
        $user = $this->getUser($userId);
        $data = array_merge($data, [
            'user_name' => $user['name'] ?? 'Customer',
            'plot_number' => $data['plot_number'] ?? '',
            'colony_name' => $data['colony_name'] ?? '',
            'colony_address' => $data['colony_address'] ?? '',
            'visit_date' => $data['visit_date'] ?? date('d F Y'),
            'visit_time' => $data['visit_time'] ?? '10:00 AM',
            'agent_name' => $data['agent_name'] ?? 'APS Agent',
            'agent_phone' => $data['agent_phone'] ?? '+91 92771 21112',
            'map_url' => $data['map_url'] ?? 'https://maps.google.com',
            'year' => date('Y'),
        ]);
        return $this->send($user['email'], 'Site Visit Confirmed - APS Dream Home', 'site_visit_confirmed', $data, (int)$userId);
    }

    // ──────────────────────────────────────────────
    // Template listing for admin
    // ──────────────────────────────────────────────

    public function listTemplates(): array
    {
        $dir = dirname(__DIR__, 2) . '/views/emails';
        $templates = [];
        foreach (glob($dir . '/*.php') as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            $templates[] = [
                'code' => $name,
                'file' => basename($file),
                'description' => $this->describe($name),
            ];
        }
        return $templates;
    }

    // ──────────────────────────────────────────────
    // Internal helpers
    // ──────────────────────────────────────────────

    private function send(string $email, string $subject, string $templateCode, array $data, int $userId = 0): bool
    {
        if (empty($email)) return false;

        $html = $this->render($templateCode, $data);
        if (!$html) return false;

        // Try EmailSenderService first
        try {
            $sender = new \App\Services\Communication\EmailSenderService();
            $result = $sender->send($email, $subject, $html);
        } catch (\Throwable $e) {
            error_log("[EmailTemplateService] EmailSender failed: " . $e->getMessage());
            $result = false;
        }

        // Fallback to mail()
        if (!$result) {
            try {
                $headers = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
                $headers .= "From: {$this->fromName} <{$this->fromEmail}>\r\n";
                $result = @mail($email, $subject, $html, $headers);
            } catch (\Throwable $e) {
                error_log("[EmailTemplateService] mail() fallback failed: " . $e->getMessage());
                $result = false;
            }
        }

        // Log to communication log
        $this->log($userId, 'email', $subject, $result);

        return $result;
    }

    private function render(string $templateCode, array $data): string
    {
        $file = dirname(__DIR__, 2) . "/views/emails/{$templateCode}.php";
        if (!file_exists($file)) {
            error_log("[EmailTemplateService] Template not found: {$templateCode}");
            return '';
        }

        // Inject site settings for dynamic content (phone, email, company name)
        if (!isset($data['settings'])) {
            $data['settings'] = \App\Services\SiteContentService::getInstance()->getSection('settings');
        }

        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        $html = ob_get_clean();

        // Replace {{var}} placeholders
        foreach ($data as $key => $value) {
            $html = str_replace('{{' . $key . '}}', htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'), $html);
        }

        return $html;
    }

    private function getUser(int $userId): array
    {
        if ($userId <= 0) return ['email' => '', 'name' => ''];
        try {
            $row = $this->db->fetchOne("SELECT id, name, email, phone FROM users WHERE id = ?", [$userId]);
            return $row ?: ['email' => '', 'name' => ''];
        } catch (\Throwable $e) {
            error_log("[EmailTemplateService] getUser() failed: " . $e->getMessage());
            return ['email' => '', 'name' => ''];
        }
    }

    private function log(int $userId, string $channel, string $subject, bool $success): void
    {
        if ($userId <= 0) return;
        try {
            $this->db->insert('customer_communication_log', [
                'user_id' => $userId,
                'channel' => $channel,
                'direction' => 'outbound',
                'subject' => mb_substr($subject, 0, 255),
                'message' => '',
                'status' => $success ? 'sent' : 'failed',
                'sent_at' => $success ? date('Y-m-d H:i:s') : null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            error_log("[EmailTemplateService] log() failed: " . $e->getMessage());
        }
    }

    private function describe(string $code): string
    {
        return match($code) {
            'welcome' => 'New user welcome email',
            'password_reset' => 'Password reset link',
            'booking_confirmation' => 'Booking confirmed notification',
            'payment_success' => 'Payment receipt',
            'property_approved' => 'Property listing approved',
            'property_inquiry' => 'Property inquiry acknowledgment',
            'admin_notification' => 'Internal admin notification',
            'kyc_approved' => 'KYC verification approved',
            'kyc_rejected' => 'KYC verification needs update',
            'support_ticket_created' => 'Support ticket creation confirmation',
            'support_ticket_reply' => 'Support ticket reply notification',
            'agreement_pending' => 'Agreement ready for signing',
            'agreement_signed' => 'Agreement signed confirmation',
            'emi_reminder' => 'Upcoming EMI installment reminder',
            'booking_cancellation' => 'Booking cancellation notice',
            'referral_commission' => 'Referral commission credited',
            'site_visit_confirmed' => 'Site visit appointment confirmed',
            default => ucfirst(str_replace('_', ' ', $code)),
        };
    }
}

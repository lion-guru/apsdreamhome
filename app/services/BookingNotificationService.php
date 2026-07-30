<?php

namespace App\Services;

use App\Core\Database\Database;

class BookingNotificationService
{
    private $db;
    private $fromEmail;
    private $fromName;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->fromEmail = $_ENV['SMTP_FROM_EMAIL'] ?? 'notifications@apsdreamhome.com';
        $this->fromName = $_ENV['SMTP_FROM_NAME'] ?? 'APS Dream Home';
    }

    /**
     * Send booking confirmation notification (email + SMS)
     */
    public function sendBookingConfirmation(array $booking, array $user, array $plot, array $colony): array
    {
        $result = ['email' => false, 'sms' => false];

        $plotNo = $plot['plot_number'] ?? $plot['plot_no'] ?? 'N/A';
        $block = $plot['block'] ?? $plot['block_name'] ?? '';
        $colonyName = $colony['name'] ?? 'N/A';
        $userName = $user['name'] ?? 'Customer';
        $bookingNumber = $booking['booking_number'] ?? 'N/A';
        $totalAmount = $booking['total_plot_value'] ?? $booking['total_amount'] ?? 0;
        $tokenAmount = $booking['booking_amount'] ?? $booking['token_amount'] ?? 0;
        $areaSqft = $plot['area_sqft'] ?? 'N/A';

        // Email
        $subject = "Booking Confirmed - {$colonyName} Plot {$plotNo}";
        $html = $this->buildBookingConfirmationEmail($booking, $user, $plot, $colony);
        $result['email'] = $this->sendEmail($user['email'] ?? '', $subject, $html);

        // SMS
        $sms = "Dear {$userName}, your booking {$bookingNumber} for Plot {$plotNo} at {$colonyName} is confirmed. Token: " . number_format($tokenAmount) . " - APS Dream Home";
        $result['sms'] = $this->sendSms($user['phone'] ?? '', $sms);

        // Log
        $this->logCommunication((int)$user['id'], 'booking_confirmation', 'email', $result['email'], $subject, $sms);
        $this->logCommunication((int)$user['id'], 'booking_confirmation', 'sms', $result['sms'], 'Booking Confirmation SMS', $sms);

        return $result;
    }

    /**
     * Send payment receipt notification
     */
    public function sendPaymentReceipt(array $booking, array $user, float $amount, string $transactionId): array
    {
        $result = ['email' => false, 'sms' => false];

        $userName = $user['name'] ?? 'Customer';
        $bookingNumber = $booking['booking_number'] ?? 'N/A';
        $plotNo = $booking['plot_number'] ?? 'N/A';
        $colonyName = $booking['colony_name'] ?? 'N/A';

        // Email
        $subject = "Payment Receipt - {$bookingNumber} - " . number_format($amount);
        $html = $this->buildPaymentReceiptEmail($booking, $user, $amount, $transactionId);
        $result['email'] = $this->sendEmail($user['email'] ?? '', $subject, $html);

        // SMS
        $sms = "Dear {$userName}, payment of " . number_format($amount) . " received for booking {$bookingNumber} (Plot {$plotNo}, {$colonyName}). Txn: {$transactionId} - APS Dream Home";
        $result['sms'] = $this->sendSms($user['phone'] ?? '', $sms);

        // Log
        $this->logCommunication((int)$user['id'], 'payment_receipt', 'email', $result['email'], $subject, $sms);
        $this->logCommunication((int)$user['id'], 'payment_receipt', 'sms', $result['sms'], 'Payment Receipt SMS', $sms);

        return $result;
    }

    /**
     * Send status change notification
     */
    public function sendStatusChange(array $booking, array $user, string $oldStatus, string $newStatus): array
    {
        $result = ['email' => false, 'sms' => false];

        $userName = $user['name'] ?? 'Customer';
        $bookingNumber = $booking['booking_number'] ?? 'N/A';
        $oldLabel = ucwords(str_replace('_', ' ', $oldStatus));
        $newLabel = ucwords(str_replace('_', ' ', $newStatus));

        // Email
        $subject = "Booking Status Updated - {$bookingNumber} - {$newLabel}";
        $html = $this->buildStatusChangeEmail($booking, $user, $oldStatus, $newStatus);
        $result['email'] = $this->sendEmail($user['email'] ?? '', $subject, $html);

        // SMS
        $sms = "Dear {$userName}, your booking {$bookingNumber} status changed from {$oldLabel} to {$newLabel}. - APS Dream Home";
        $result['sms'] = $this->sendSms($user['phone'] ?? '', $sms);

        // Log
        $this->logCommunication((int)$user['id'], 'status_change', 'email', $result['email'], $subject, $sms);
        $this->logCommunication((int)$user['id'], 'status_change', 'sms', $result['sms'], 'Status Change SMS', $sms);

        return $result;
    }

    /**
     * Send demand letter reminder for overdue installments
     */
    public function sendDemandLetterReminder(array $booking, array $user, array $installment): array
    {
        $result = ['email' => false, 'sms' => false];

        $userName = $user['name'] ?? 'Customer';
        $bookingNumber = $booking['booking_number'] ?? 'N/A';
        $instNo = $installment['installment_no'] ?? 'N/A';
        $amount = $installment['emi_amount'] ?? $installment['amount'] ?? 0;
        $dueDate = $installment['due_date'] ?? 'N/A';
        $daysOverdue = $installment['days_overdue'] ?? 0;

        // Email
        $subject = "Payment Overdue - Installment #{$instNo} - {$bookingNumber}";
        $html = $this->buildDemandLetterEmail($booking, $user, $installment);
        $result['email'] = $this->sendEmail($user['email'] ?? '', $subject, $html);

        // SMS
        $sms = "Dear {$userName}, Installment #{$instNo} of " . number_format($amount) . " for booking {$bookingNumber} is overdue by {$daysOverdue} days. Please pay immediately. - APS Dream Home";
        $result['sms'] = $this->sendSms($user['phone'] ?? '', $sms);

        // Log
        $this->logCommunication((int)$user['id'], 'demand_letter', 'email', $result['email'], $subject, $sms);
        $this->logCommunication((int)$user['id'], 'demand_letter', 'sms', $result['sms'], 'Demand Letter SMS', $sms);

        return $result;
    }

    /**
     * Get booking communication log with filters
     */
    public function getBookingLog(array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['type'])) {
            $where[] = 'ccl.related_entity_type = ?';
            $params[] = $filters['type'];
        }
        if (!empty($filters['channel'])) {
            $where[] = 'ccl.channel = ?';
            $params[] = $filters['channel'];
        }
        if (!empty($filters['status'])) {
            $where[] = 'ccl.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = 'ccl.created_at >= ?';
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'ccl.created_at <= ?';
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        if (!empty($filters['search'])) {
            $where[] = '(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR ccl.subject LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $limit = (int)($filters['limit'] ?? 50);
        $offset = (int)($filters['offset'] ?? 0);

        $sql = "SELECT ccl.*, u.name as user_name, u.email as user_email, u.phone as user_phone
                FROM customer_communication_log ccl
                LEFT JOIN users u ON ccl.user_id = u.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY ccl.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get communication log stats
     */
    public function getLogStats(): array
    {
        $stats = [
            'total' => 0,
            'email_sent' => 0,
            'sms_sent' => 0,
            'failed' => 0,
            'today' => 0,
        ];

        try {
            $row = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM customer_communication_log");
            $stats['total'] = (int)($row['cnt'] ?? 0);
        } catch (\Throwable $e) { error_log($e->getMessage()); }

        try {
            $row = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM customer_communication_log WHERE channel = 'email' AND status = 'sent'");
            $stats['email_sent'] = (int)($row['cnt'] ?? 0);
        } catch (\Throwable $e) { error_log($e->getMessage()); }

        try {
            $row = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM customer_communication_log WHERE channel = 'sms' AND status = 'sent'");
            $stats['sms_sent'] = (int)($row['cnt'] ?? 0);
        } catch (\Throwable $e) { error_log($e->getMessage()); }

        try {
            $row = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM customer_communication_log WHERE status = 'failed'");
            $stats['failed'] = (int)($row['cnt'] ?? 0);
        } catch (\Throwable $e) { error_log($e->getMessage()); }

        try {
            $row = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM customer_communication_log WHERE DATE(created_at) = CURDATE()");
            $stats['today'] = (int)($row['cnt'] ?? 0);
        } catch (\Throwable $e) { error_log($e->getMessage()); }

        return $stats;
    }

    // ──────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────

    private function sendEmail(string $to, string $subject, string $html): bool
    {
        if (empty($to)) return false;

        // Try EmailSenderService first
        try {
            $sender = new \App\Services\Communication\EmailSenderService();
            return $sender->send($to, $subject, $html);
        } catch (\Throwable $e) {
            error_log("[BookingNotificationService] EmailSender failed: " . $e->getMessage());
        }

        // Fallback to mail()
        try {
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: {$this->fromName} <{$this->fromEmail}>\r\n";
            return mail($to, $subject, $html, $headers);
        } catch (\Throwable $e) {
            error_log("[BookingNotificationService] mail() fallback failed: " . $e->getMessage());
            return false;
        }
    }

    private function sendSms(string $to, string $message): bool
    {
        if (empty($to)) return false;

        // Try SmsSenderService first
        try {
            $sender = new \App\Services\Communication\SmsSenderService();
            $result = $sender->send($to, $message);
            return $result['success'] ?? false;
        } catch (\Throwable $e) {
            error_log("[BookingNotificationService] SmsSender failed: " . $e->getMessage());
        }

        // Fallback: log to sms_queue
        try {
            $phone = preg_replace('/[^0-9]/', '', $to);
            if (strlen($phone) === 10) $phone = '91' . $phone;
            $this->db->insert('sms_queue', [
                'recipient' => $phone,
                'message' => mb_substr($message, 0, 500),
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            return true;
        } catch (\Throwable $e) {
            error_log("[BookingNotificationService] SMS queue fallback failed: " . $e->getMessage());
            return false;
        }
    }

    private function logCommunication(int $userId, string $type, string $channel, bool $success, string $subject, string $message): void
    {
        try {
            $this->db->insert('customer_communication_log', [
                'user_id' => $userId,
                'channel' => $channel,
                'direction' => 'outbound',
                'subject' => mb_substr($subject, 0, 255),
                'message' => mb_substr($message, 0, 2000),
                'status' => $success ? 'sent' : 'failed',
                'related_entity_type' => $type,
                'sent_at' => $success ? date('Y-m-d H:i:s') : null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            error_log("[BookingNotificationService] logCommunication failed: " . $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────
    // Email templates
    // ──────────────────────────────────────────────

    private function buildBookingConfirmationEmail(array $booking, array $user, array $plot, array $colony): string
    {
        $plotNo = htmlspecialchars($plot['plot_number'] ?? $plot['plot_no'] ?? 'N/A');
        $block = htmlspecialchars($plot['block'] ?? $plot['block_name'] ?? '');
        $colonyName = htmlspecialchars($colony['name'] ?? 'N/A');
        $userName = htmlspecialchars($user['name'] ?? 'Customer');
        $bookingNumber = htmlspecialchars($booking['booking_number'] ?? 'N/A');
        $totalAmount = number_format($booking['total_plot_value'] ?? $booking['total_amount'] ?? 0);
        $tokenAmount = number_format($booking['booking_amount'] ?? $booking['token_amount'] ?? 0);
        $areaSqft = htmlspecialchars($plot['area_sqft'] ?? 'N/A');
        $bookingDate = date('d F Y');

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f1f5f9;">
<div style="background: #0d9488; color: white; padding: 24px; text-align: center; border-radius: 8px 8px 0 0;">
    <h1 style="margin: 0; font-size: 24px;">APS Dream Home</h1>
    <p style="margin: 6px 0 0; font-size: 14px; opacity: 0.9;">Booking Confirmation</p>
</div>
<div style="padding: 24px; background: #ffffff;">
    <p style="font-size: 16px; color: #1e293b;">Dear <strong>{$userName}</strong>,</p>
    <p style="color: #475569; line-height: 1.6;">Your booking has been confirmed! Here are the details:</p>
    <table style="width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 14px;">
        <tr><td style="padding: 10px 12px; border: 1px solid #e2e8f0; background: #f8fafc; font-weight: bold; color: #334155;">Booking Number</td><td style="padding: 10px 12px; border: 1px solid #e2e8f0; color: #1e293b;">{$bookingNumber}</td></tr>
        <tr><td style="padding: 10px 12px; border: 1px solid #e2e8f0; background: #f8fafc; font-weight: bold; color: #334155;">Booking Date</td><td style="padding: 10px 12px; border: 1px solid #e2e8f0; color: #1e293b;">{$bookingDate}</td></tr>
        <tr><td style="padding: 10px 12px; border: 1px solid #e2e8f0; background: #f8fafc; font-weight: bold; color: #334155;">Colony</td><td style="padding: 10px 12px; border: 1px solid #e2e8f0; color: #1e293b;">{$colonyName}</td></tr>
        <tr><td style="padding: 10px 12px; border: 1px solid #e2e8f0; background: #f8fafc; font-weight: bold; color: #334155;">Plot</td><td style="padding: 10px 12px; border: 1px solid #e2e8f0; color: #1e293b;">{$block} - {$plotNo}</td></tr>
        <tr><td style="padding: 10px 12px; border: 1px solid #e2e8f0; background: #f8fafc; font-weight: bold; color: #334155;">Area</td><td style="padding: 10px 12px; border: 1px solid #e2e8f0; color: #1e293b;">{$areaSqft} sq ft</td></tr>
        <tr><td style="padding: 10px 12px; border: 1px solid #e2e8f0; background: #f8fafc; font-weight: bold; color: #334155;">Total Amount</td><td style="padding: 10px 12px; border: 1px solid #e2e8f0; color: #1e293b;">₹{$totalAmount}</td></tr>
        <tr><td style="padding: 10px 12px; border: 1px solid #e2e8f0; background: #f8fafc; font-weight: bold; color: #334155;">Token Paid</td><td style="padding: 10px 12px; border: 1px solid #e2e8f0; color: #1e293b;">₹{$tokenAmount}</td></tr>
    </table>
    <p style="color: #475569; line-height: 1.6;">Thank you for choosing APS Dream Home! Our team will contact you shortly for the next steps.</p>
    <p style="color: #475569; line-height: 1.6;">For any queries, call us at <strong style="color: #0d9488;">+91 92771 21112</strong></p>
</div>
<div style="background: #1e293b; color: #94a3b8; padding: 16px; text-align: center; font-size: 12px; border-radius: 0 0 8px 8px;">
    <p style="margin: 0;">&copy; APS Dream Home. All rights reserved.</p>
</div>
</body>
</html>
HTML;
    }

    private function buildPaymentReceiptEmail(array $booking, array $user, float $amount, string $transactionId): string
    {
        $userName = htmlspecialchars($user['name'] ?? 'Customer');
        $bookingNumber = htmlspecialchars($booking['booking_number'] ?? 'N/A');
        $plotNo = htmlspecialchars($booking['plot_number'] ?? 'N/A');
        $colonyName = htmlspecialchars($booking['colony_name'] ?? 'N/A');
        $formattedAmount = number_format($amount);
        $txnId = htmlspecialchars($transactionId);
        $paymentDate = date('d F Y, h:i A');

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f1f5f9;">
<div style="background: #059669; color: white; padding: 24px; text-align: center; border-radius: 8px 8px 0 0;">
    <h1 style="margin: 0; font-size: 24px;">APS Dream Home</h1>
    <p style="margin: 6px 0 0; font-size: 14px; opacity: 0.9;">Payment Receipt</p>
</div>
<div style="padding: 24px; background: #ffffff;">
    <p style="font-size: 16px; color: #1e293b;">Dear <strong>{$userName}</strong>,</p>
    <p style="color: #475569; line-height: 1.6;">We have received your payment. Here are the details:</p>
    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 16px; margin: 16px 0; text-align: center;">
        <p style="margin: 0; font-size: 12px; color: #166534; text-transform: uppercase; letter-spacing: 1px;">Amount Received</p>
        <p style="margin: 4px 0 0; font-size: 28px; font-weight: bold; color: #166534;">₹{$formattedAmount}</p>
    </div>
    <table style="width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 14px;">
        <tr><td style="padding: 10px 12px; border: 1px solid #e2e8f0; background: #f8fafc; font-weight: bold; color: #334155;">Booking Number</td><td style="padding: 10px 12px; border: 1px solid #e2e8f0; color: #1e293b;">{$bookingNumber}</td></tr>
        <tr><td style="padding: 10px 12px; border: 1px solid #e2e8f0; background: #f8fafc; font-weight: bold; color: #334155;">Plot</td><td style="padding: 10px 12px; border: 1px solid #e2e8f0; color: #1e293b;">{$plotNo}, {$colonyName}</td></tr>
        <tr><td style="padding: 10px 12px; border: 1px solid #e2e8f0; background: #f8fafc; font-weight: bold; color: #334155;">Transaction ID</td><td style="padding: 10px 12px; border: 1px solid #e2e8f0; color: #1e293b;">{$txnId}</td></tr>
        <tr><td style="padding: 10px 12px; border: 1px solid #e2e8f0; background: #f8fafc; font-weight: bold; color: #334155;">Payment Date</td><td style="padding: 10px 12px; border: 1px solid #e2e8f0; color: #1e293b;">{$paymentDate}</td></tr>
    </table>
    <p style="color: #475569; line-height: 1.6;">This receipt confirms your payment has been successfully processed.</p>
    <p style="color: #475569; line-height: 1.6;">For any queries, call us at <strong style="color: #0d9488;">+91 92771 21112</strong></p>
</div>
<div style="background: #1e293b; color: #94a3b8; padding: 16px; text-align: center; font-size: 12px; border-radius: 0 0 8px 8px;">
    <p style="margin: 0;">&copy; APS Dream Home. All rights reserved.</p>
</div>
</body>
</html>
HTML;
    }

    private function buildStatusChangeEmail(array $booking, array $user, string $oldStatus, string $newStatus): string
    {
        $userName = htmlspecialchars($user['name'] ?? 'Customer');
        $bookingNumber = htmlspecialchars($booking['booking_number'] ?? 'N/A');
        $oldLabel = htmlspecialchars(ucwords(str_replace('_', ' ', $oldStatus)));
        $newLabel = htmlspecialchars(ucwords(str_replace('_', ' ', $newStatus)));
        $plotNo = htmlspecialchars($booking['plot_number'] ?? 'N/A');
        $colonyName = htmlspecialchars($booking['colony_name'] ?? 'N/A');

        $statusColors = [
            'token_paid' => '#f59e0b',
            'agreement_signed' => '#3b82f6',
            'emi_active' => '#14b8a6',
            'partially_paid' => '#f97316',
            'fully_paid' => '#059669',
            'cancelled' => '#ef4444',
            'transferred' => '#6366f1',
            'registration_done' => '#10b981',
        ];
        $newColor = $statusColors[$newStatus] ?? '#0d9488';

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f1f5f9;">
<div style="background: {$newColor}; color: white; padding: 24px; text-align: center; border-radius: 8px 8px 0 0;">
    <h1 style="margin: 0; font-size: 24px;">APS Dream Home</h1>
    <p style="margin: 6px 0 0; font-size: 14px; opacity: 0.9;">Booking Status Update</p>
</div>
<div style="padding: 24px; background: #ffffff;">
    <p style="font-size: 16px; color: #1e293b;">Dear <strong>{$userName}</strong>,</p>
    <p style="color: #475569; line-height: 1.6;">Your booking status has been updated:</p>
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin: 16px 0;">
        <table style="width: 100%; font-size: 14px;">
            <tr><td style="padding: 4px 0; color: #64748b;">Booking:</td><td style="padding: 4px 0; font-weight: bold; color: #1e293b;">{$bookingNumber}</td></tr>
            <tr><td style="padding: 4px 0; color: #64748b;">Plot:</td><td style="padding: 4px 0; color: #1e293b;">{$plotNo}, {$colonyName}</td></tr>
            <tr><td style="padding: 4px 0; color: #64748b;">Previous Status:</td><td style="padding: 4px 0; color: #ef4444;">{$oldLabel}</td></tr>
            <tr><td style="padding: 4px 0; color: #64748b;">New Status:</td><td style="padding: 4px 0; font-weight: bold; color: {$newColor};">{$newLabel}</td></tr>
        </table>
    </div>
    <p style="color: #475569; line-height: 1.6;">If you have any questions, please contact our support team.</p>
    <p style="color: #475569; line-height: 1.6;">For any queries, call us at <strong style="color: #0d9488;">+91 92771 21112</strong></p>
</div>
<div style="background: #1e293b; color: #94a3b8; padding: 16px; text-align: center; font-size: 12px; border-radius: 0 0 8px 8px;">
    <p style="margin: 0;">&copy; APS Dream Home. All rights reserved.</p>
</div>
</body>
</html>
HTML;
    }

    private function buildDemandLetterEmail(array $booking, array $user, array $installment): string
    {
        $userName = htmlspecialchars($user['name'] ?? 'Customer');
        $bookingNumber = htmlspecialchars($booking['booking_number'] ?? 'N/A');
        $instNo = htmlspecialchars($installment['installment_no'] ?? 'N/A');
        $amount = number_format($installment['emi_amount'] ?? $installment['amount'] ?? 0);
        $dueDate = htmlspecialchars($installment['due_date'] ?? 'N/A');
        $daysOverdue = (int)($installment['days_overdue'] ?? 0);
        $totalDue = number_format(($installment['emi_amount'] ?? $installment['amount'] ?? 0) + ($installment['accrued_penalty'] ?? 0));

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f1f5f9;">
<div style="background: #dc2626; color: white; padding: 24px; text-align: center; border-radius: 8px 8px 0 0;">
    <h1 style="margin: 0; font-size: 24px;">APS Dream Home</h1>
    <p style="margin: 6px 0 0; font-size: 14px; opacity: 0.9;">Payment Overdue Reminder</p>
</div>
<div style="padding: 24px; background: #ffffff;">
    <p style="font-size: 16px; color: #1e293b;">Dear <strong>{$userName}</strong>,</p>
    <p style="color: #475569; line-height: 1.6;">This is a reminder that your installment payment is overdue.</p>
    <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 16px; margin: 16px 0;">
        <table style="width: 100%; font-size: 14px;">
            <tr><td style="padding: 4px 0; color: #64748b;">Booking:</td><td style="padding: 4px 0; font-weight: bold; color: #1e293b;">{$bookingNumber}</td></tr>
            <tr><td style="padding: 4px 0; color: #64748b;">Installment:</td><td style="padding: 4px 0; color: #1e293b;">#{$instNo}</td></tr>
            <tr><td style="padding: 4px 0; color: #64748b;">Amount Due:</td><td style="padding: 4px 0; font-weight: bold; color: #dc2626;">₹{$amount}</td></tr>
            <tr><td style="padding: 4px 0; color: #64748b;">Due Date:</td><td style="padding: 4px 0; color: #1e293b;">{$dueDate}</td></tr>
            <tr><td style="padding: 4px 0; color: #64748b;">Days Overdue:</td><td style="padding: 4px 0; font-weight: bold; color: #dc2626;">{$daysOverdue} days</td></tr>
            <tr><td style="padding: 4px 0; color: #64748b;">Total Due (with penalty):</td><td style="padding: 4px 0; font-weight: bold; color: #dc2626;">₹{$totalDue}</td></tr>
        </table>
    </div>
    <p style="color: #475569; line-height: 1.6;">Please make the payment at the earliest to avoid further penalties.</p>
    <p style="color: #475569; line-height: 1.6;">For any queries, call us at <strong style="color: #0d9488;">+91 92771 21112</strong></p>
</div>
<div style="background: #1e293b; color: #94a3b8; padding: 16px; text-align: center; font-size: 12px; border-radius: 0 0 8px 8px;">
    <p style="margin: 0;">&copy; APS Dream Home. All rights reserved.</p>
</div>
</body>
</html>
HTML;
    }
}

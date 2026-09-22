<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;

class BookingNotificationService
{
    use ServiceTenantTrait;
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
        $result = ['email' => false, 'sms' => false, 'whatsapp' => false];

        $plotNo = $plot['plot_number'] ?? $plot['plot_no'] ?? 'N/A';
        $block = $plot['block'] ?? $plot['block_name'] ?? '';
        $colonyName = $colony['name'] ?? 'N/A';
        $userName = $user['name'] ?? 'Customer';
        $bookingNumber = $booking['booking_number'] ?? 'N/A';
        $totalAmount = $booking['total_plot_value'] ?? $booking['total_amount'] ?? 0;
        $tokenAmount = $booking['booking_amount'] ?? $booking['token_amount'] ?? 51000;
        $areaSqft = $plot['area_sqft'] ?? 'N/A';
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';

        // Email
        $subject = "Booking Confirmed - {$colonyName} Plot {$plotNo}";
        $html = $this->buildBookingConfirmationEmail($booking, $user, $plot, $colony);
        $result['email'] = $this->sendEmail($user['email'] ?? '', $subject, $html);

        // SMS (Explicit Non-Refundable Token Notice)
        $sms = "Dear {$userName}, your booking {$bookingNumber} for Plot {$plotNo} ({$colonyName}) is confirmed. Token Paid: Rs." . number_format($tokenAmount) . ". IMPORTANT: As per Master Deed (Sec 2.1 & 2.9), booking token amount is strictly non-refundable (टोकन राशि वापस नहीं होगी). - APS Dream Home";
        $result['sms'] = $this->sendSms($user['phone'] ?? '', $sms);

        // WhatsApp (Explicit Non-Refundable Token Notice + Link)
        $wa = "Dear *{$userName}*,\n\n"
            . "🎉 Your plot booking *#{$bookingNumber}* for Plot *#{$plotNo}* at *{$colonyName}* is confirmed!\n\n"
            . "💰 *Token Paid:* ₹" . number_format($tokenAmount) . " *(Non-Refundable)*\n"
            . "📐 *Area:* {$areaSqft} sq ft\n"
            . "🏷️ *Total Plot Value:* ₹" . number_format($totalAmount) . "\n\n"
            . "⚠️ *IMPORTANT LEGAL NOTICE (Master Deed Sec 2.1 & 2.9):*\n"
            . "The token booking amount of *₹" . number_format($tokenAmount) . "* is strictly *NON-REFUNDABLE* (टोकन बुकिंग राशि गैर-वापसी योग्य है - वापस नहीं होगी)।\n\n"
            . "📌 *Next Steps:* Please deposit the mandatory 25% allotment consideration within 15 calendar days to execute the registered Agreement for Sale.\n\n"
            . "🔗 View booking details: {$baseUrl}/user/bookings\n"
            . "Helpline: +91 92771 21112\n"
            . "APS Dream Home Pvt. Ltd.";
        $result['whatsapp'] = $this->sendWhatsapp($user['phone'] ?? '', $wa);

        // Log
        $this->logCommunication((int)($user['id'] ?? 0), 'booking_confirmation', 'email', $result['email'], $subject, $sms);
        $this->logCommunication((int)($user['id'] ?? 0), 'booking_confirmation', 'sms', $result['sms'], 'Booking Confirmation SMS', $sms);
        $this->logCommunication((int)($user['id'] ?? 0), 'booking_confirmation', 'whatsapp', $result['whatsapp'], 'Booking Confirmation WhatsApp', $wa);

        return $result;
    }

    /**
     * Send payment receipt notification (email + SMS + WhatsApp).
     * Optional $extra: ['balance' => float outstanding after this payment,
     *                    'receipt_url' => string public receipt link].
     */
    public function sendPaymentReceipt(array $booking, array $user, float $amount, string $transactionId, array $extra = []): array
    {
        $result = ['email' => false, 'sms' => false, 'whatsapp' => false];

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

        // WhatsApp (graceful when unconfigured — service returns success=false)
        $waText = "Dear {$userName}, received Rs." . number_format($amount)
            . " against Plot #{$plotNo} ({$colonyName}). Receipt: {$transactionId}.";
        if (isset($extra['balance']) && is_numeric($extra['balance'])) {
            $waText .= " Balance: Rs." . number_format((float)$extra['balance']) . ".";
        }
        if (!empty($extra['receipt_url'])) {
            $waText .= " View receipt: {$extra['receipt_url']}";
        }
        $result['whatsapp'] = $this->sendWhatsapp($user['phone'] ?? '', $waText);

        // Log
        $this->logCommunication((int)$user['id'], 'payment_receipt', 'email', $result['email'], $subject, $sms);
        $this->logCommunication((int)$user['id'], 'payment_receipt', 'sms', $result['sms'], 'Payment Receipt SMS', $sms);
        $this->logCommunication((int)$user['id'], 'payment_receipt', 'whatsapp', $result['whatsapp'], 'Payment Receipt WhatsApp', $waText);

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
     * Send Agreement Executed notification (email + SMS + WhatsApp)
     */
    public function sendAgreementExecutedNotification(array $booking, array $user, array $agreement = []): array
    {
        $result = ['email' => false, 'sms' => false, 'whatsapp' => false];

        $userName = $user['name'] ?? 'Customer';
        $bookingNumber = $booking['booking_number'] ?? 'N/A';
        $plotNo = $booking['plot_number'] ?? $booking['plot_no'] ?? 'N/A';
        $colonyName = $booking['colony_name'] ?? 'N/A';
        $agreementNo = $agreement['agreement_number'] ?? 'N/A';

        // Email
        $subject = "Agreement Executed - Booking {$bookingNumber} - Plot {$plotNo}";
        $html = $this->buildAgreementExecutedEmail($booking, $user, $agreement);
        $result['email'] = $this->sendEmail($user['email'] ?? '', $subject, $html);

        // SMS
        $sms = "Dear {$userName}, Agreement for Sale for booking {$bookingNumber} (Plot {$plotNo}, {$colonyName}) has been executed. Check your customer portal. - APS Dream Home";
        $result['sms'] = $this->sendSms($user['phone'] ?? '', $sms);

        // WhatsApp
        $wa = "Dear {$userName}, Agreement for Sale (Doc #{$agreementNo}) for Plot #{$plotNo} at {$colonyName} is successfully executed. View documents in your customer portal: " . (defined('BASE_URL') ? BASE_URL : '') . "/user/bookings";
        $result['whatsapp'] = $this->sendWhatsapp($user['phone'] ?? '', $wa);

        // Log
        $this->logCommunication((int)$user['id'], 'agreement_executed', 'email', $result['email'], $subject, $sms);
        $this->logCommunication((int)$user['id'], 'agreement_executed', 'sms', $result['sms'], 'Agreement Executed SMS', $sms);
        $this->logCommunication((int)$user['id'], 'agreement_executed', 'whatsapp', $result['whatsapp'], 'Agreement Executed WhatsApp', $wa);

        return $result;
    }

    /**
     * Send NOC Approved notification (email + SMS + WhatsApp)
     */
    public function sendNocApprovedNotification(array $booking, array $user, array $noc = []): array
    {
        $result = ['email' => false, 'sms' => false, 'whatsapp' => false];

        $userName = $user['name'] ?? 'Customer';
        $bookingNumber = $booking['booking_number'] ?? 'N/A';
        $plotNo = $booking['plot_number'] ?? $booking['plot_no'] ?? 'N/A';
        $colonyName = $booking['colony_name'] ?? 'N/A';

        // Email
        $subject = "NOC Approved - Booking {$bookingNumber} - Plot {$plotNo}";
        $html = $this->buildNocApprovedEmail($booking, $user, $noc);
        $result['email'] = $this->sendEmail($user['email'] ?? '', $subject, $html);

        // SMS
        $sms = "Dear {$userName}, No Objection Certificate (NOC) has been approved for booking {$bookingNumber} (Plot {$plotNo}, {$colonyName}). Registry scheduling next. - APS Dream Home";
        $result['sms'] = $this->sendSms($user['phone'] ?? '', $sms);

        // WhatsApp
        $wa = "Dear {$userName}, NOC clearance is completed for Plot #{$plotNo} at {$colonyName}! Your property is now cleared for registry appointment scheduling.";
        $result['whatsapp'] = $this->sendWhatsapp($user['phone'] ?? '', $wa);

        // Log
        $this->logCommunication((int)$user['id'], 'noc_approved', 'email', $result['email'], $subject, $sms);
        $this->logCommunication((int)$user['id'], 'noc_approved', 'sms', $result['sms'], 'NOC Approved SMS', $sms);
        $this->logCommunication((int)$user['id'], 'noc_approved', 'whatsapp', $result['whatsapp'], 'NOC Approved WhatsApp', $wa);

        return $result;
    }

    /**
     * Send Registry Appointment Scheduled notification (email + SMS + WhatsApp)
     */
    public function sendRegistryScheduledNotification(array $booking, array $user, string $date, string $office = ''): array
    {
        $result = ['email' => false, 'sms' => false, 'whatsapp' => false];

        $userName = $user['name'] ?? 'Customer';
        $bookingNumber = $booking['booking_number'] ?? 'N/A';
        $plotNo = $booking['plot_number'] ?? $booking['plot_no'] ?? 'N/A';
        $colonyName = $booking['colony_name'] ?? 'N/A';
        $office = !empty($office) ? $office : 'Sub-Registrar Office';
        $formattedDate = date('d F Y', strtotime($date));

        // Email
        $subject = "Registry Appointment Scheduled - {$bookingNumber} - {$formattedDate}";
        $html = $this->buildRegistryScheduledEmail($booking, $user, $date, $office);
        $result['email'] = $this->sendEmail($user['email'] ?? '', $subject, $html);

        // SMS
        $sms = "Dear {$userName}, registry appointment for booking {$bookingNumber} (Plot {$plotNo}) is scheduled on {$formattedDate} at {$office}. Bring original Aadhaar & PAN. - APS Dream Home";
        $result['sms'] = $this->sendSms($user['phone'] ?? '', $sms);

        // WhatsApp
        $wa = "Dear {$userName}, your Sub-Registrar appointment for Plot #{$plotNo} ({$colonyName}) is confirmed for *{$formattedDate}* at *{$office}*. Please carry original KYC documents and 4 passport photos.";
        $result['whatsapp'] = $this->sendWhatsapp($user['phone'] ?? '', $wa);

        // Log
        $this->logCommunication((int)$user['id'], 'registry_scheduled', 'email', $result['email'], $subject, $sms);
        $this->logCommunication((int)$user['id'], 'registry_scheduled', 'sms', $result['sms'], 'Registry Scheduled SMS', $sms);
        $this->logCommunication((int)$user['id'], 'registry_scheduled', 'whatsapp', $result['whatsapp'], 'Registry Scheduled WhatsApp', $wa);

        return $result;
    }

    /**
     * Send Registry Completed notification (email + SMS + WhatsApp)
     */
    public function sendRegistryCompletedNotification(array $booking, array $user, string $registryNo = '', string $deedUrl = ''): array
    {
        $result = ['email' => false, 'sms' => false, 'whatsapp' => false];

        $userName = $user['name'] ?? 'Customer';
        $bookingNumber = $booking['booking_number'] ?? 'N/A';
        $plotNo = $booking['plot_number'] ?? $booking['plot_no'] ?? 'N/A';
        $colonyName = $booking['colony_name'] ?? 'N/A';

        // Email
        $subject = "Registry Completed - Congratulations! - Booking {$bookingNumber}";
        $html = $this->buildRegistryCompletedEmail($booking, $user, $registryNo, $deedUrl);
        $result['email'] = $this->sendEmail($user['email'] ?? '', $subject, $html);

        // SMS
        $sms = "Dear {$userName}, Congratulations! Property registry for booking {$bookingNumber} (Plot {$plotNo}) is completed. Reg No: " . ($registryNo ?: 'Issued') . ". - APS Dream Home";
        $result['sms'] = $this->sendSms($user['phone'] ?? '', $sms);

        // WhatsApp
        $wa = "Heartiest Congratulations {$userName}! The registry deed for Plot #{$plotNo} at {$colonyName} is executed (Reg #{$registryNo}). Welcome to the APS Dream Home family!";
        $result['whatsapp'] = $this->sendWhatsapp($user['phone'] ?? '', $wa);

        // Log
        $this->logCommunication((int)$user['id'], 'registry_completed', 'email', $result['email'], $subject, $sms);
        $this->logCommunication((int)$user['id'], 'registry_completed', 'sms', $result['sms'], 'Registry Completed SMS', $sms);
        $this->logCommunication((int)$user['id'], 'registry_completed', 'whatsapp', $result['whatsapp'], 'Registry Completed WhatsApp', $wa);

        return $result;
    }

    /**
     * Send Possession Handed Over notification (email + SMS + WhatsApp)
     */
    public function sendPossessionHandedOverNotification(array $booking, array $user, string $letterNo = '', string $possessionDate = ''): array
    {
        $result = ['email' => false, 'sms' => false, 'whatsapp' => false];

        $userName = $user['name'] ?? 'Customer';
        $bookingNumber = $booking['booking_number'] ?? 'N/A';
        $plotNo = $booking['plot_number'] ?? $booking['plot_no'] ?? 'N/A';
        $colonyName = $booking['colony_name'] ?? 'N/A';
        $pDate = $possessionDate ? date('d F Y', strtotime($possessionDate)) : date('d F Y');

        // Email
        $subject = "Possession Handed Over - Welcome Home! - Booking {$bookingNumber}";
        $html = $this->buildPossessionHandedOverEmail($booking, $user, $letterNo, $pDate);
        $result['email'] = $this->sendEmail($user['email'] ?? '', $subject, $html);

        // SMS
        $sms = "Dear {$userName}, physical possession of Plot {$plotNo} at {$colonyName} has been handed over on {$pDate}. Letter: {$letterNo}. Welcome home! - APS Dream Home";
        $result['sms'] = $this->sendSms($user['phone'] ?? '', $sms);

        // WhatsApp
        $wa = "Dear {$userName}, Congratulations! Physical possession of Plot #{$plotNo} ({$colonyName}) has been formally handed over to you on {$pDate}. Possession Letter: {$letterNo}. We wish you happiness in your new property!";
        $result['whatsapp'] = $this->sendWhatsapp($user['phone'] ?? '', $wa);

        // Log
        $this->logCommunication((int)$user['id'], 'possession_handed_over', 'email', $result['email'], $subject, $sms);
        $this->logCommunication((int)$user['id'], 'possession_handed_over', 'sms', $result['sms'], 'Possession Handed Over SMS', $sms);
        $this->logCommunication((int)$user['id'], 'possession_handed_over', 'whatsapp', $result['whatsapp'], 'Possession Handed Over WhatsApp', $wa);

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
                WHERE " . implode(' AND ', $where) . $this->tenantSql() . "
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
            $row = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM customer_communication_log" . $this->tenantSql());
            $stats['total'] = (int)($row['cnt'] ?? 0);
        } catch (\Throwable $e) { error_log($e->getMessage()); }

        try {
            $row = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM customer_communication_log WHERE channel = 'email' AND status = 'sent'" . $this->tenantSql());
            $stats['email_sent'] = (int)($row['cnt'] ?? 0);
        } catch (\Throwable $e) { error_log($e->getMessage()); }

        try {
            $row = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM customer_communication_log WHERE channel = 'sms' AND status = 'sent'" . $this->tenantSql());
            $stats['sms_sent'] = (int)($row['cnt'] ?? 0);
        } catch (\Throwable $e) { error_log($e->getMessage()); }

        try {
            $row = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM customer_communication_log WHERE status = 'failed'" . $this->tenantSql());
            $stats['failed'] = (int)($row['cnt'] ?? 0);
        } catch (\Throwable $e) { error_log($e->getMessage()); }

        try {
            $row = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM customer_communication_log WHERE DATE(created_at) = CURDATE()" . $this->tenantSql());
            $stats['today'] = (int)($row['cnt'] ?? 0);
        } catch (\Throwable $e) { error_log($e->getMessage()); }

        return $stats;
    }

    // â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€
    // Private helpers
    // â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€

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
            $smsData = [
                'recipient' => $phone,
                'message' => mb_substr($message, 0, 500),
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
            ];
            if ($this->tenantId() > 1) {
                $smsData['tenant_id'] = $this->tenantId();
            }
            $this->db->insert('sms_queue', $smsData);
            return true;
        } catch (\Throwable $e) {
            error_log("[BookingNotificationService] SMS queue fallback failed: " . $e->getMessage());
            return false;
        }
    }

    private function sendWhatsapp(string $to, string $message): bool
    {
        if (empty($to) || empty($message)) return false;
        try {
            $wa = new \App\Services\Communication\WhatsAppService();
            if (!$wa->isConfigured()) return false;
            $result = $wa->sendTextMessage($to, $message);
            return (bool)($result['success'] ?? false);
        } catch (\Throwable $e) {
            error_log("[BookingNotificationService] WhatsApp failed: " . $e->getMessage());
            return false;
        }
    }

    private function logCommunication(int $userId, string $type, string $channel, bool $success, string $subject, string $message): void
    {
        try {
            $data = [
                'user_id' => $userId,
                'channel' => $channel,
                'direction' => 'outbound',
                'subject' => mb_substr($subject, 0, 255),
                'message' => mb_substr($message, 0, 2000),
                'status' => $success ? 'sent' : 'failed',
                'related_entity_type' => $type,
                'sent_at' => $success ? date('Y-m-d H:i:s') : null,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            if ($this->tenantId() > 1) {
                $data['tenant_id'] = $this->tenantId();
            }
            $this->db->insert('customer_communication_log', $data);
        } catch (\Throwable $e) {
            error_log("[BookingNotificationService] logCommunication failed: " . $e->getMessage());
        }
    }

    // â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€
    // Email templates
    // â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€â"€

    private function buildBookingConfirmationEmail(array $booking, array $user, array $plot, array $colony): string
    {
        $plotNo = htmlspecialchars($plot['plot_number'] ?? $plot['plot_no'] ?? 'N/A');
        $block = htmlspecialchars($plot['block'] ?? $plot['block_name'] ?? '');
        $colonyName = htmlspecialchars($colony['name'] ?? 'N/A');
        $userName = htmlspecialchars($user['name'] ?? 'Customer');
        $bookingNumber = htmlspecialchars($booking['booking_number'] ?? 'N/A');
        $totalAmount = number_format($booking['total_plot_value'] ?? $booking['total_amount'] ?? 0);
        $tokenAmount = number_format($booking['booking_amount'] ?? $booking['token_amount'] ?? 51000);
        $areaSqft = htmlspecialchars($plot['area_sqft'] ?? 'N/A');
        $bookingDate = date('d F Y');

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"><meta charset="utf-8"></head>
<body class="style-26942">
<div class="style-34817">
    <h1 class="style-85930">APS Dream Home</h1>
    <p class="style-8849">Booking Confirmation</p>
</div>
<div class="style-38030">
    <p class="style-33752">Dear <strong>{$userName}</strong>,</p>
    <p class="style-10698">Your plot booking has been confirmed! Here are the details:</p>
    <table class="style-73344">
        <tr><td class="style-20120">Booking Number</td><td class="style-85150">{$bookingNumber}</td></tr>
        <tr><td class="style-20120">Booking Date</td><td class="style-85150">{$bookingDate}</td></tr>
        <tr><td class="style-20120">Colony</td><td class="style-85150">{$colonyName}</td></tr>
        <tr><td class="style-20120">Plot</td><td class="style-85150">{$block} - {$plotNo}</td></tr>
        <tr><td class="style-20120">Area</td><td class="style-85150">{$areaSqft} sq ft</td></tr>
        <tr><td class="style-20120">Total Amount</td><td class="style-85150">₹{$totalAmount}</td></tr>
        <tr><td class="style-20120">Token Paid</td><td class="style-85150">₹{$tokenAmount} <span style="display:inline-block;background:#dc3545;color:#ffffff;font-size:11px;font-weight:700;padding:2px 8px;border-radius:4px;margin-left:6px;">Non-Refundable / गैर-वापसी योग्य</span></td></tr>
    </table>

    <div style="background:#fff3cd; border: 1px solid #ffeeba; border-left: 5px solid #dc3545; padding: 14px 18px; margin: 20px 0; border-radius: 6px; color: #721c24;">
        <h4 style="margin: 0 0 8px 0; color: #dc3545; font-size: 15px; font-weight: 700;">
            ⚠️ वैधानिक सूचना / Important Legal Notice (Master Deed Section 2.1 & 2.9):
        </h4>
        <p style="margin: 0 0 6px 0; font-size: 13px; line-height: 1.5;">
            <strong>टोकन बुकिंग राशि (₹{$tokenAmount}) पूर्णतः गैर-वापसी योग्य (Non-Refundable) है।</strong> किसी भी स्थिति में आवंटन निरस्त होने अथवा रद्दीकरण (Cancellation) पर यह टोकन राशि वापस नहीं की जाएगी।
        </p>
        <p style="margin: 0; font-size: 12px; color: #555; line-height: 1.4;">
            As per Section 2.1 & 2.9 of the Tripartite Master Legal Deed, the token booking amount is strictly non-refundable upon cancellation. The applicant must deposit the mandatory 25% allotment consideration within 15 calendar days to execute the registered Agreement for Sale.
        </p>
    </div>

    <p class="style-10698">Thank you for choosing APS Dream Home! Our team will contact you shortly for the next steps.</p>
    <p class="style-10698">For any queries, call us at <strong class="style-22019">+91 92771 21112</strong></p>
</div>
<div class="style-1322">
    <p class="style-85082">&copy; APS Dream Home. All rights reserved.</p>
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
<body class="style-26942">
<div class="style-58159">
    <h1 class="style-85930">APS Dream Home</h1>
    <p class="style-8849">Payment Receipt</p>
</div>
<div class="style-38030">
    <p class="style-33752">Dear <strong>{$userName}</strong>,</p>
    <p class="style-10698">We have received your payment. Here are the details:</p>
    <div class="style-84747">
        <p class="style-63380">Amount Received</p>
        <p class="style-19032">₹{$formattedAmount}</p>
    </div>
    <table class="style-73344">
        <tr><td class="style-20120">Booking Number</td><td class="style-85150">{$bookingNumber}</td></tr>
        <tr><td class="style-20120">Plot</td><td class="style-85150">{$plotNo}, {$colonyName}</td></tr>
        <tr><td class="style-20120">Transaction ID</td><td class="style-85150">{$txnId}</td></tr>
        <tr><td class="style-20120">Payment Date</td><td class="style-85150">{$paymentDate}</td></tr>
    </table>
    <p class="style-10698">This receipt confirms your payment has been successfully processed.</p>
    <p class="style-10698">For any queries, call us at <strong class="style-22019">+91 92771 21112</strong></p>
</div>
<div class="style-1322">
    <p class="style-85082">&copy; APS Dream Home. All rights reserved.</p>
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
<body class="style-26942">
<div class="style-87330">
    <h1 class="style-85930">APS Dream Home</h1>
    <p class="style-8849">Booking Status Update</p>
</div>
<div class="style-38030">
    <p class="style-33752">Dear <strong>{$userName}</strong>,</p>
    <p class="style-10698">Your booking status has been updated:</p>
    <div class="style-467">
        <table class="style-61075">
            <tr><td class="style-89250">Booking:</td><td class="style-79766">{$bookingNumber}</td></tr>
            <tr><td class="style-89250">Plot:</td><td class="style-40961">{$plotNo}, {$colonyName}</td></tr>
            <tr><td class="style-89250">Previous Status:</td><td class="style-45989">{$oldLabel}</td></tr>
            <tr><td class="style-89250">New Status:</td><td class="style-79176">{$newLabel}</td></tr>
        </table>
    </div>
    <p class="style-10698">If you have any questions, please contact our support team.</p>
    <p class="style-10698">For any queries, call us at <strong class="style-22019">+91 92771 21112</strong></p>
</div>
<div class="style-1322">
    <p class="style-85082">&copy; APS Dream Home. All rights reserved.</p>
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
<body class="style-26942">
<div class="style-10137">
    <h1 class="style-85930">APS Dream Home</h1>
    <p class="style-8849">Payment Overdue Reminder</p>
</div>
<div class="style-38030">
    <p class="style-33752">Dear <strong>{$userName}</strong>,</p>
    <p class="style-10698">This is a reminder that your installment payment is overdue.</p>
    <div class="style-30515">
        <table class="style-61075">
            <tr><td class="style-89250">Booking:</td><td class="style-79766">{$bookingNumber}</td></tr>
            <tr><td class="style-89250">Installment:</td><td class="style-40961">#{$instNo}</td></tr>
            <tr><td class="style-89250">Amount Due:</td><td class="style-34699">₹{$amount}</td></tr>
            <tr><td class="style-89250">Due Date:</td><td class="style-40961">{$dueDate}</td></tr>
            <tr><td class="style-89250">Days Overdue:</td><td class="style-34699">{$daysOverdue} days</td></tr>
            <tr><td class="style-89250">Total Due (with penalty):</td><td class="style-34699">₹{$totalDue}</td></tr>
        </table>
    </div>
    <p class="style-10698">Please make the payment at the earliest to avoid further penalties.</p>
    <p class="style-10698">For any queries, call us at <strong class="style-22019">+91 92771 21112</strong></p>
</div>
<div class="style-1322">
    <p class="style-85082">&copy; APS Dream Home. All rights reserved.</p>
</body>
</html>
HTML;
    }

    private function buildAgreementExecutedEmail(array $booking, array $user, array $agreement): string
    {
        $userName = htmlspecialchars($user['name'] ?? 'Customer');
        $bookingNumber = htmlspecialchars($booking['booking_number'] ?? 'N/A');
        $plotNo = htmlspecialchars($booking['plot_number'] ?? $booking['plot_no'] ?? 'N/A');
        $colonyName = htmlspecialchars($booking['colony_name'] ?? 'N/A');
        $agreementNo = htmlspecialchars($agreement['agreement_number'] ?? 'Executed');
        $date = date('d F Y');

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body class="style-26942">
<div class="style-58159">
    <h1 class="style-85930">APS Dream Home</h1>
    <p class="style-8849">Agreement for Sale Executed</p>
</div>
<div class="style-38030">
    <p class="style-33752">Dear <strong>{$userName}</strong>,</p>
    <p class="style-10698">We are pleased to inform you that the Agreement for Sale for your property has been successfully executed.</p>
    <table class="style-73344">
        <tr><td class="style-20120">Booking Number</td><td class="style-85150">{$bookingNumber}</td></tr>
        <tr><td class="style-20120">Agreement Number</td><td class="style-85150">{$agreementNo}</td></tr>
        <tr><td class="style-20120">Plot</td><td class="style-85150">Plot {$plotNo}, {$colonyName}</td></tr>
        <tr><td class="style-20120">Execution Date</td><td class="style-85150">{$date}</td></tr>
    </table>
    <p class="style-10698">You can view and download your executed agreement from the customer portal under My Bookings &gt; Journey.</p>
    <p class="style-10698">For any queries, call us at <strong class="style-22019">+91 92771 21112</strong></p>
</div>
<div class="style-1322">
    <p class="style-85082">&copy; APS Dream Home. All rights reserved.</p>
</div>
</body>
</html>
HTML;
    }

    private function buildNocApprovedEmail(array $booking, array $user, array $noc): string
    {
        $userName = htmlspecialchars($user['name'] ?? 'Customer');
        $bookingNumber = htmlspecialchars($booking['booking_number'] ?? 'N/A');
        $plotNo = htmlspecialchars($booking['plot_number'] ?? $booking['plot_no'] ?? 'N/A');
        $colonyName = htmlspecialchars($booking['colony_name'] ?? 'N/A');
        $date = date('d F Y');

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body class="style-26942">
<div class="style-58159">
    <h1 class="style-85930">APS Dream Home</h1>
    <p class="style-8849">NOC Verification Approved</p>
</div>
<div class="style-38030">
    <p class="style-33752">Dear <strong>{$userName}</strong>,</p>
    <p class="style-10698">The No Objection Certificate (NOC) has been reviewed and approved for your booking.</p>
    <table class="style-73344">
        <tr><td class="style-20120">Booking Number</td><td class="style-85150">{$bookingNumber}</td></tr>
        <tr><td class="style-20120">Property</td><td class="style-85150">Plot {$plotNo}, {$colonyName}</td></tr>
        <tr><td class="style-20120">NOC Status</td><td class="style-85150" style="color:#059669;font-weight:bold;">Approved</td></tr>
        <tr><td class="style-20120">Approval Date</td><td class="style-85150">{$date}</td></tr>
    </table>
    <p class="style-10698">Your property is now cleared for the Sub-Registrar appointment and deed execution.</p>
    <p class="style-10698">For any queries, call us at <strong class="style-22019">+91 92771 21112</strong></p>
</div>
<div class="style-1322">
    <p class="style-85082">&copy; APS Dream Home. All rights reserved.</p>
</div>
</body>
</html>
HTML;
    }

    private function buildRegistryScheduledEmail(array $booking, array $user, string $date, string $office): string
    {
        $userName = htmlspecialchars($user['name'] ?? 'Customer');
        $bookingNumber = htmlspecialchars($booking['booking_number'] ?? 'N/A');
        $plotNo = htmlspecialchars($booking['plot_number'] ?? $booking['plot_no'] ?? 'N/A');
        $colonyName = htmlspecialchars($booking['colony_name'] ?? 'N/A');
        $officeName = htmlspecialchars($office);
        $formattedDate = date('d F Y', strtotime($date));

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body class="style-26942">
<div class="style-34817">
    <h1 class="style-85930">APS Dream Home</h1>
    <p class="style-8849">Registry Appointment Scheduled</p>
</div>
<div class="style-38030">
    <p class="style-33752">Dear <strong>{$userName}</strong>,</p>
    <p class="style-10698">Your Sub-Registrar registry appointment has been scheduled with details below:</p>
    <table class="style-73344">
        <tr><td class="style-20120">Booking Number</td><td class="style-85150">{$bookingNumber}</td></tr>
        <tr><td class="style-20120">Property</td><td class="style-85150">Plot {$plotNo}, {$colonyName}</td></tr>
        <tr><td class="style-20120">Appointment Date</td><td class="style-85150" style="color:#2563eb;font-weight:bold;">{$formattedDate}</td></tr>
        <tr><td class="style-20120">Registry Office</td><td class="style-85150">{$officeName}</td></tr>
    </table>
    <div style="background:#f8fafc;border-left:4px solid #2563eb;padding:12px;margin:15px 0;">
        <strong>Required Documents Checklist:</strong>
        <ul style="margin:5px 0 0 18px;padding:0;">
            <li>Original Aadhaar Card &amp; PAN Card</li>
            <li>4 Passport size photographs</li>
            <li>Booking allotment letter &amp; payment receipts</li>
            <li>Two witnesses with valid ID proofs</li>
        </ul>
    </div>
    <p class="style-10698">Our legal representative will be present at the registry office to assist you.</p>
    <p class="style-10698">For assistance, contact our registry coordinator at <strong class="style-22019">+91 92771 21112</strong></p>
</div>
<div class="style-1322">
    <p class="style-85082">&copy; APS Dream Home. All rights reserved.</p>
</div>
</body>
</html>
HTML;
    }

    private function buildRegistryCompletedEmail(array $booking, array $user, string $registryNo, string $deedUrl): string
    {
        $userName = htmlspecialchars($user['name'] ?? 'Customer');
        $bookingNumber = htmlspecialchars($booking['booking_number'] ?? 'N/A');
        $plotNo = htmlspecialchars($booking['plot_number'] ?? $booking['plot_no'] ?? 'N/A');
        $colonyName = htmlspecialchars($booking['colony_name'] ?? 'N/A');
        $regNumber = htmlspecialchars($registryNo ?: 'Issued');
        $date = date('d F Y');

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body class="style-26942">
<div class="style-58159">
    <h1 class="style-85930">APS Dream Home</h1>
    <p class="style-8849">Registry Completed - Congratulations!</p>
</div>
<div class="style-38030">
    <p class="style-33752">Dear <strong>{$userName}</strong>,</p>
    <p class="style-10698">Heartiest congratulations! The sale deed for your property has been registered successfully at the Sub-Registrar office.</p>
    <table class="style-73344">
        <tr><td class="style-20120">Booking Number</td><td class="style-85150">{$bookingNumber}</td></tr>
        <tr><td class="style-20120">Property</td><td class="style-85150">Plot {$plotNo}, {$colonyName}</td></tr>
        <tr><td class="style-20120">Registration Number</td><td class="style-85150" style="color:#059669;font-weight:bold;">{$regNumber}</td></tr>
        <tr><td class="style-20120">Registration Date</td><td class="style-85150">{$date}</td></tr>
    </table>
    <p class="style-10698">The next milestone is mutation and physical possession handover. Our operations team will contact you for the site visit and possession letter.</p>
    <p class="style-10698">For any queries, call us at <strong class="style-22019">+91 92771 21112</strong></p>
</div>
<div class="style-1322">
    <p class="style-85082">&copy; APS Dream Home. All rights reserved.</p>
</div>
</body>
</html>
HTML;
    }

    private function buildPossessionHandedOverEmail(array $booking, array $user, string $letterNo, string $possessionDate): string
    {
        $userName = htmlspecialchars($user['name'] ?? 'Customer');
        $bookingNumber = htmlspecialchars($booking['booking_number'] ?? 'N/A');
        $plotNo = htmlspecialchars($booking['plot_number'] ?? $booking['plot_no'] ?? 'N/A');
        $colonyName = htmlspecialchars($booking['colony_name'] ?? 'N/A');
        $letter = htmlspecialchars($letterNo ?: 'Handed Over');

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body class="style-26942">
<div class="style-58159">
    <h1 class="style-85930">APS Dream Home</h1>
    <p class="style-8849">Possession Handed Over - Welcome Home!</p>
</div>
<div class="style-38030">
    <p class="style-33752">Dear <strong>{$userName}</strong>,</p>
    <p class="style-10698">Congratulations! Physical possession of your property has been handed over.</p>
    <table class="style-73344">
        <tr><td class="style-20120">Booking Number</td><td class="style-85150">{$bookingNumber}</td></tr>
        <tr><td class="style-20120">Property</td><td class="style-85150">Plot {$plotNo}, {$colonyName}</td></tr>
        <tr><td class="style-20120">Possession Letter #</td><td class="style-85150" style="color:#059669;font-weight:bold;">{$letter}</td></tr>
        <tr><td class="style-20120">Possession Date</td><td class="style-85150">{$possessionDate}</td></tr>
    </table>
    <p class="style-10698">We are thrilled to welcome you as a proud owner in {$colonyName}. If you have any feedback or defect queries, you can log them anytime through your customer portal.</p>
    <p class="style-10698">Customer Care: <strong class="style-22019">+91 92771 21112</strong></p>
</div>
<div class="style-1322">
    <p class="style-85082">&copy; APS Dream Home. All rights reserved.</p>
</div>
</body>
</html>
HTML;
    }
}

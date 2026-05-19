<?php
namespace App\Services\Notification;

class BookingNotificationService
{
    private $db;

    public function __construct()
    {
        $dbInstance = \App\Core\Database\Database::getInstance();
        $this->db = $dbInstance->getConnection();
    }

    public function notifyBookingCreated(int $bookingId, array $booking, $user = null): void
    {
        $subject = 'Booking Request Submitted - APS Dream Home';
        $this->logNotification($bookingId, 'booking_created', $user['id'] ?? 0, $subject, 'customer');
        $this->logNotification($bookingId, 'booking_created', 1, $subject, 'admin');
    }

    public function notifyBookingApproved(int $bookingId, array $booking): void
    {
        $customerId = $booking['customer_id'] ?? 0;
        $subject = 'Booking #' . $bookingId . ' Approved - APS Dream Home';
        $message = "Congratulations! Your booking for Plot #{$booking['plot_number']} in {$booking['colony_name']} has been approved. Please complete the remaining payment as per the schedule.";
        $this->logNotification($bookingId, 'booking_approved', $customerId, $subject, 'customer', $message);

        if (!empty($booking['customer_email'])) {
            $this->sendEmailNotification($booking['customer_email'], $subject, $message);
        }
        if (!empty($booking['customer_phone'])) {
            $this->sendSmsNotification($booking['customer_phone'], $message);
        }
    }

    public function notifyBookingRejected(int $bookingId, array $booking, string $reason = ''): void
    {
        $customerId = $booking['customer_id'] ?? 0;
        $subject = 'Booking #' . $bookingId . ' Status Update - APS Dream Home';
        $message = "Your booking for Plot #{$booking['plot_number']} has been " . ($reason ?: "reviewed") . ". Please contact us for more details.";
        $this->logNotification($bookingId, 'booking_rejected', $customerId, $subject, 'customer', $message);

        if (!empty($booking['customer_email'])) {
            $this->sendEmailNotification($booking['customer_email'], $subject, $message);
        }
    }

    public function notifyPaymentReceived(int $bookingId, array $booking, float $amount, string $mode): void
    {
        $customerId = $booking['customer_id'] ?? 0;
        $subject = 'Payment Received - Booking #' . $bookingId;
        $message = "We have received your payment of ₹" . number_format($amount, 2) . " via " . str_replace('_', ' ', $mode) . " for Booking #{$bookingId}.";
        $this->logNotification($bookingId, 'payment_received', $customerId, $subject, 'customer', $message);

        $this->logNotification($bookingId, 'payment_received', 1, $subject, 'admin');

        if (!empty($booking['customer_email'])) {
            $this->sendEmailNotification($booking['customer_email'], $subject, $message);
        }
    }

    public function notifyCommissionProcessed(int $bookingId, int $agentId, float $amount): void
    {
        $subject = 'Commission Credited - Booking #' . $bookingId;
        $message = "Commission of ₹" . number_format($amount, 2) . " has been credited to your wallet for Booking #{$bookingId}.";
        $this->logNotification($bookingId, 'commission_paid', $agentId, $subject, 'agent', $message);
    }

    private function logNotification(int $bookingId, string $type, int $userId, string $subject, string $userType = 'customer', string $message = ''): void
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO notifications (user_id, user_type, type, title, message, reference_type, reference_id, is_read, created_at) VALUES (?, ?, ?, ?, ?, 'booking', ?, 0, NOW())");
            $stmt->execute([$userId, $userType, $type, $subject, $message, $bookingId]);
        } catch (\Exception $e) {
            error_log("BookingNotificationService: Failed to log notification - " . $e->getMessage());
        }
    }

    private function sendEmailNotification(string $email, string $subject, string $message): void
    {
        try {
            $headers = "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\nFrom: APS Dream Home <noreply@apsdreamhome.com>";
            mail($email, $subject, $message, $headers);
        } catch (\Exception $e) {
            error_log("BookingNotificationService: Email failed - " . $e->getMessage());
        }
    }

    private function sendSmsNotification(string $phone, string $message): void
    {
        try {
            $logMsg = "[SMS] To: {$phone} | {$message}";
            error_log($logMsg);

            $stmt = $this->db->prepare("INSERT INTO sms_log (phone, message, status, created_at) VALUES (?, ?, 'logged', NOW())");
            $stmt->execute([$phone, $message]);
        } catch (\Exception $e) {
            error_log("BookingNotificationService: SMS failed - " . $e->getMessage());
        }
    }

    public function ensureNotificationsTable(): void
    {
        try {
            $this->db->exec("CREATE TABLE IF NOT EXISTS notifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL DEFAULT 0,
                user_type VARCHAR(50) DEFAULT 'customer',
                type VARCHAR(50) NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT,
                reference_type VARCHAR(50) DEFAULT 'booking',
                reference_id INT DEFAULT 0,
                is_read TINYINT(1) DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user (user_id, user_type, is_read),
                INDEX idx_reference (reference_type, reference_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (\Exception $e) {
            error_log("BookingNotificationService: Table creation failed - " . $e->getMessage());
        }
    }
}
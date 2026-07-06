<?php
namespace App\Services\Notification;

/**
 * @deprecated Use App\Services\BookingNotificationService (root) instead.
 * This is now a thin wrapper that delegates to the canonical services.
 */
class BookingNotificationService
{
    private \App\Services\NotificationService $notifier;

    public function __construct()
    {
        $db = \App\Core\Database\Database::getInstance();
        $this->notifier = new \App\Services\NotificationService($db);
    }

    /** @deprecated Use BookingNotificationService::sendBookingConfirmation() directly */
    public function notifyBookingCreated(int $bookingId, array $booking, $user = null): void
    {
        $userId = $user['id'] ?? $booking['customer_id'] ?? 0;
        $plotNum = $booking['plot_number'] ?? '';
        if ($userId) {
            $this->notifier->notify('booking', "Booking request submitted for Plot #{$plotNum}", $userId, '/admin/bookings/' . $bookingId, 'Booking Submitted');
        }
        $this->notifier->notify('booking', "New booking request for Plot #{$plotNum}", 1, '/admin/bookings/' . $bookingId, 'New Booking');
    }

    /** @deprecated Use BookingNotificationService::sendStatusChange() directly */
    public function notifyBookingApproved(int $bookingId, array $booking): void
    {
        $customerId = $booking['customer_id'] ?? 0;
        $plotNum = $booking['plot_number'] ?? '';
        $colonyName = $booking['colony_name'] ?? '';
        if ($customerId) {
            $subject = 'Booking #' . $bookingId . ' Approved';
            $message = "Congratulations! Your booking for Plot #{$plotNum} in {$colonyName} has been approved.";
            $this->notifier->send($customerId, 'email', $subject, $message, ['event_type' => 'booking', 'booking_id' => $bookingId]);
            $this->notifier->send($customerId, 'sms', $subject, $message, ['event_type' => 'booking', 'booking_id' => $bookingId]);
        }
    }

    /** @deprecated Use BookingNotificationService::sendStatusChange() directly */
    public function notifyBookingRejected(int $bookingId, array $booking, string $reason = ''): void
    {
        $customerId = $booking['customer_id'] ?? 0;
        $plotNum = $booking['plot_number'] ?? '';
        if ($customerId) {
            $subject = 'Booking #' . $bookingId . ' Status Update';
            $message = "Your booking for Plot #{$plotNum} has been " . ($reason ?: "reviewed") . ".";
            $this->notifier->send($customerId, 'email', $subject, $message, ['event_type' => 'booking', 'booking_id' => $bookingId]);
        }
    }

    /** @deprecated Use BookingNotificationService::sendPaymentReceipt() directly */
    public function notifyPaymentReceived(int $bookingId, array $booking, float $amount, string $mode): void
    {
        $customerId = $booking['customer_id'] ?? 0;
        if ($customerId) {
            $this->notifier->sendPaymentReceived($bookingId, $amount);
        }
        $this->notifier->notify('payment', "Payment received: ₹" . number_format($amount) . " for Booking #{$bookingId}", 1, '/admin/bookings/' . $bookingId, 'Payment Received');
    }

    /** @deprecated */
    public function notifyCommissionProcessed(int $bookingId, int $agentId, float $amount): void
    {
        $subject = 'Commission Credited - Booking #' . $bookingId;
        $message = "Commission of ₹" . number_format($amount, 2) . " has been credited to your wallet.";
        $this->notifier->send($agentId, 'email', $subject, $message, ['event_type' => 'commission', 'booking_id' => $bookingId]);
    }

    /** @deprecated No longer needed — tables exist */
    public function ensureNotificationsTable(): void
    {
        // No-op — tables are managed by migrations
    }
}

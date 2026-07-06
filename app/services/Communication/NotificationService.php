<?php
namespace App\Services\Communication;

use App\Core\Database\Database;

/**
 * @deprecated Use App\Services\NotificationService (root) instead.
 * This is now a thin wrapper that delegates to the canonical NotificationService.
 * DO NOT add new features here. All new notification logic goes to App\Services\NotificationService.
 */
class NotificationService
{
    private \App\Services\NotificationService $notifier;

    public function __construct()
    {
        $db = Database::getInstance();
        $this->notifier = new \App\Services\NotificationService($db);
    }

    /** @deprecated Use NotificationService::sendNotification() directly */
    public function sendNotification($userId, $channel, $title, $message, $data = [])
    {
        return $this->notifier->sendNotification((int)$userId, $channel, $title, $message, $data);
    }

    /** @deprecated Use NotificationService::sendBookingConfirmed() directly */
    public function sendBookingConfirmed($bookingId)
    {
        $this->notifier->sendBookingConfirmed((int)$bookingId);
    }

    /** @deprecated Use NotificationService::sendBookingConfirmedEmail() directly */
    public function sendBookingConfirmedEmail($bookingId)
    {
        $this->notifier->sendBookingConfirmedEmail((int)$bookingId);
    }

    /** @deprecated Use NotificationService::sendAgreementGenerated() directly */
    public function sendAgreementGenerated($bookingId, $agreementType)
    {
        $this->notifier->sendAgreementGenerated((int)$bookingId, $agreementType);
    }

    /** @deprecated Use NotificationService::sendPaymentReceived() directly */
    public function sendPaymentReceived($bookingId, $amount)
    {
        $this->notifier->sendPaymentReceived((int)$bookingId, (float)$amount);
    }

    /** @deprecated Use NotificationService::sendRegistryUpdate() directly */
    public function sendRegistryUpdate($bookingId, $status)
    {
        $this->notifier->sendRegistryUpdate((int)$bookingId, $status);
    }

    /** @deprecated Use NotificationService::sendPossessionScheduled() directly */
    public function sendPossessionScheduled($bookingId, $date)
    {
        $this->notifier->sendPossessionScheduled((int)$bookingId, $date);
    }

    /** @deprecated Use NotificationService::sendPossessionCompleted() directly */
    public function sendPossessionCompleted($bookingId)
    {
        $this->notifier->sendPossessionCompleted((int)$bookingId);
    }

    /** @deprecated Use NotificationService::getCustomerNotifications() directly */
    public function getCustomerNotifications($userId, $limit = 20)
    {
        return $this->notifier->getCustomerNotifications((int)$userId, $limit);
    }

    /** @deprecated Use NotificationService::getUnreadCount() directly */
    public function getUnreadCount($userId)
    {
        return $this->notifier->getUnreadCount((int)$userId);
    }

    /** @deprecated Use NotificationService::markAsRead() directly */
    public function markAsRead($notificationId)
    {
        $this->notifier->markAsRead((int)$notificationId);
    }

    /** @deprecated Use NotificationService::markAllAsRead() directly */
    public function markAllAsRead($userId)
    {
        $this->notifier->markAllAsRead((int)$userId);
    }
}

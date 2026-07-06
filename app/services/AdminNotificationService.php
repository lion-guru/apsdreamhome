<?php

namespace App\Services;

/**
 * @deprecated Use App\Services\NotificationService directly.
 * This is now a thin wrapper that delegates to the canonical NotificationService.
 */
class AdminNotificationService
{
    private NotificationService $notifier;

    public function __construct()
    {
        $db = \App\Core\Database\Database::getInstance();
        $this->notifier = new NotificationService($db);
    }

    /** @deprecated Use NotificationService::notify() directly */
    public function notify($type, $message, $userId = null, $actionUrl = null, $title = null)
    {
        return $this->notifier->notify($type, $message, $userId, $actionUrl, $title);
    }

    /** @deprecated Use NotificationService::getUnread() directly */
    public function getUnread($userId = null, $limit = 20)
    {
        return $this->notifier->getUnread($userId, $limit);
    }

    /** @deprecated Use NotificationService::getRecent() directly */
    public function getRecent($userId = null, $limit = 50)
    {
        return $this->notifier->getRecent($userId, $limit);
    }

    /** @deprecated Use NotificationService::markRead() directly */
    public function markRead($id)
    {
        return $this->notifier->markRead($id);
    }

    /** @deprecated Use NotificationService::markAllRead() directly */
    public function markAllRead($userId = null)
    {
        return $this->notifier->markAllRead($userId);
    }

    /** @deprecated Use NotificationService::getUnreadCount() directly */
    public function getUnreadCount($userId = null)
    {
        return $this->notifier->getUnreadCount($userId);
    }

    /** @deprecated Use NotificationService::newLead() directly */
    public function newLead($leadId, $leadName)
    {
        return $this->notifier->newLead($leadId, $leadName);
    }

    /** @deprecated Use NotificationService::newProperty() directly */
    public function newProperty($propertyId, $propertyTitle)
    {
        return $this->notifier->newProperty($propertyId, $propertyTitle);
    }

    /** @deprecated Use NotificationService::newRegistration() directly */
    public function newRegistration($userId, $userName)
    {
        return $this->notifier->newRegistration($userId, $userName);
    }

    /** @deprecated Use NotificationService::newBooking() directly */
    public function newBooking($bookingId, $buyerName)
    {
        return $this->notifier->newBooking($bookingId, $buyerName);
    }

    /** @deprecated Use NotificationService::paymentReceived() directly */
    public function paymentReceived($transactionId, $amount)
    {
        return $this->notifier->paymentReceived($transactionId, $amount);
    }
}

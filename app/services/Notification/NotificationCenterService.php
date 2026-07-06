<?php

namespace App\Services\Notification;

use App\Core\Database\Database;

/**
 * @deprecated Use App\Services\NotificationService (root) instead.
 * This is now a thin wrapper that delegates to the canonical NotificationService.
 */
class NotificationCenterService
{
    private \App\Services\NotificationService $notifier;

    public function __construct()
    {
        $db = Database::getInstance();
        $this->notifier = new \App\Services\NotificationService($db);
    }

    /** @deprecated Use NotificationService::send() directly */
    public function send(int $userId, string $userType, string $type, array $data = [], array $options = []): array
    {
        $channel = $options['channel'] ?? 'whatsapp';
        $subject = $data['title'] ?? ucfirst(str_replace('_', ' ', $type));
        $message = $data['message'] ?? $subject;
        return $this->notifier->send($userId, $channel, $subject, $message, array_merge($data, ['notification_type' => $type]));
    }

    /** @deprecated Use NotificationService::getCustomerNotifications() or getUnread() directly */
    public function getNotifications(int $userId, string $userType, array $filters = []): array
    {
        $limit = $filters['limit'] ?? 50;
        return $this->notifier->getCustomerNotifications($userId, $limit);
    }

    /** @deprecated Use NotificationService::markAsRead() directly */
    public function markAsRead(int $notificationId, int $userId, string $userType): bool
    {
        return $this->notifier->markAsRead($notificationId);
    }

    /** @deprecated Use NotificationService::markAllAsRead() directly */
    public function markAllAsRead(int $userId, string $userType): int
    {
        return $this->notifier->markAllRead($userId) ? 1 : 0;
    }

    /** @deprecated Use NotificationService::getUnreadCount() directly */
    public function getUnreadCount(int $userId, string $userType): int
    {
        return $this->notifier->getUnreadCount($userId);
    }

    /** @deprecated */
    public function delete(int $notificationId, int $userId, string $userType): bool
    {
        try {
            $db = Database::getInstance()->getConnection();
            $st = $db->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
            $st->execute([$notificationId, $userId]);
            return $st->rowCount() > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** @deprecated Use NotificationService::updateSetting() directly */
    public function setPreference(int $userId, string $userType, string $channel, bool $enabled): bool
    {
        $this->notifier->updateSetting($userId, $channel, $enabled);
        return true;
    }

    /** @deprecated */
    public function getStats(string $dateFrom = null, string $dateTo = null): array
    {
        try {
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT COUNT(*) as total, SUM(is_read = 0) as unread FROM notifications WHERE 1=1";
            $params = [];
            if ($dateFrom) { $sql .= " AND created_at >= ?"; $params[] = $dateFrom; }
            if ($dateTo) { $sql .= " AND created_at <= ?"; $params[] = $dateTo; }
            $st = $db->prepare($sql);
            $st->execute($params);
            return $st->fetch(\PDO::FETCH_ASSOC) ?: ['total' => 0, 'unread' => 0];
        } catch (\Throwable $e) {
            return ['total' => 0, 'unread' => 0];
        }
    }

    /** @deprecated Use NotificationService::cleanup() directly */
    public function cleanup(int $days = 30): int
    {
        return $this->notifier->cleanup($days);
    }
}

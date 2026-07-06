<?php
namespace App\Services;

/**
 * @deprecated Use App\Services\NotificationService directly.
 * This is now a thin wrapper that delegates to the canonical NotificationService.
 */
class NotificationCenter
{
    private NotificationService $notifier;

    public function __construct($db = null)
    {
        if ($db === null) {
            $db = \App\Core\Database\Database::getInstance();
        }
        $this->notifier = new NotificationService($db);
    }

    /** @deprecated Use NotificationService::publish() directly */
    public function publish(string $channel, string $eventType, ?int $userId, array $payload, ?int $ttlSeconds = null): int
    {
        return $this->notifier->publish($channel, $eventType, $userId, $payload, $ttlSeconds);
    }

    /** @deprecated Use NotificationService::fetchPending() directly */
    public function fetchPending(int $userId, string $channel = 'global', int $limit = 20, ?int $sinceId = null): array
    {
        return $this->notifier->fetchPending($userId, $channel, $limit, $sinceId);
    }

    /** @deprecated Use NotificationService::markDelivered() directly */
    public function markDelivered(array $ids): int
    {
        return $this->notifier->markDelivered($ids);
    }

    /** @deprecated Use NotificationService::markRead() directly */
    public function markRead(int $userId, array $ids): int
    {
        if (empty($ids)) return 0;
        // Delegate each ID to the canonical markRead
        $count = 0;
        foreach ($ids as $id) {
            if ($this->notifier->markRead((int)$id)) $count++;
        }
        return $count;
    }

    /** @deprecated Use NotificationService::getUnreadCount() directly */
    public function getUnreadCount(int $userId, string $channel = 'global'): int
    {
        return $this->notifier->getUnreadCount($userId);
    }

    /** @deprecated Use NotificationService::cleanup() directly */
    public function cleanup(int $daysToKeep = 30): int
    {
        return $this->notifier->cleanup($daysToKeep);
    }

    /** @deprecated WebSocket broadcast is handled internally by NotificationService::publish() */
    public function broadcastNotification(array $notification): void
    {
        // No-op — broadcast is handled by publish()
    }
}

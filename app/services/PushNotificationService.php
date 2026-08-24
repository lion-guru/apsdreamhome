<?php
namespace App\Services;

/**
 * @deprecated Use App\Services\Communication\PushNotificationService instead.
 * This is now a thin wrapper that delegates to the canonical FCM v1 implementation.
 */
class PushNotificationService
{
    use \App\Traits\ServiceTenantTrait;

    private \App\Services\Communication\PushNotificationService $fcm;

    public function __construct()
    {
        $this->fcm = new \App\Services\Communication\PushNotificationService();
    }

    /** @deprecated Use Communication\PushNotificationService::subscribeToTopic() or registerDevice() */
    public function subscribe(int $userId, string $endpoint, string $p256dh, string $auth): array
    {
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tid = $this->isTenantScoped() ? $this->tenantId() : null;
            $cols = 'user_id, endpoint, p256dh_key, auth_key, active, created_at';
            $vals = '?, ?, ?, ?, 1, NOW()';
            if ($tid) { $cols .= ', tenant_id'; $vals .= ', ?'; }
            $st = $db->prepare("INSERT INTO push_subscriptions ($cols) VALUES ($vals) ON DUPLICATE KEY UPDATE active = 1, p256dh_key = VALUES(p256dh_key), auth_key = VALUES(auth_key)" . ($tid ? ', tenant_id = VALUES(tenant_id)' : ''));
            $params = [$userId, $endpoint, $p256dh, $auth];
            if ($tid) { $params[] = $tid; }
            $st->execute($params);
            return ['success' => true];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /** @deprecated */
    public function unsubscribe(int $userId, string $endpoint): array
    {
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tid = $this->isTenantScoped() ? $this->tenantId() : null;
            $sql = "UPDATE push_subscriptions SET is_active = 0 WHERE user_id = ? AND endpoint = ?";
            $params = [$userId, $endpoint];
            if ($tid) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $st = $db->prepare($sql);
            $st->execute($params);
            return ['success' => true];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /** @deprecated */
    public function getSubscriptions(int $userId): array
    {
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $st = $db->prepare("SELECT * FROM push_subscriptions WHERE user_id = ? AND is_active = 1");
            $st->execute([$userId]);
            return $st->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** @deprecated Use Communication\PushNotificationService::sendToUser() */
    public function send(int $userId, string $title, string $body, array $data = []): array
    {
        return $this->fcm->sendToUser($userId, ['title' => $title, 'body' => $body] + $data);
    }

    /** @deprecated Use Communication\PushNotificationService::sendBroadcast() */
    public function broadcast(string $title, string $body, array $data = []): array
    {
        return $this->fcm->sendBroadcast(['title' => $title, 'body' => $body] + $data);
    }

    /** @deprecated */
    public function getStats(): array
    {
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $total = $db->query("SELECT COUNT(*) FROM push_subscriptions WHERE is_active = 1")->fetchColumn();
            $today = $db->query("SELECT COUNT(*) FROM push_notifications WHERE DATE(created_at) = CURDATE()")->fetchColumn();
            return ['active_subscriptions' => (int)$total, 'sent_today' => (int)$today];
        } catch (\Throwable $e) {
            return ['active_subscriptions' => 0, 'sent_today' => 0];
        }
    }

    /** @deprecated */
    public function getLog(int $limit = 50): array
    {
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $st = $db->prepare("SELECT * FROM push_notifications ORDER BY created_at DESC LIMIT ?");
            $st->execute([$limit]);
            return $st->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }
}

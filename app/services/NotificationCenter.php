<?php
namespace App\Services;

use PDO;

class NotificationCenter
{
    private $db;
    private $pdo;

    public function __construct($db)
    {
        $this->db = $db;
        $this->pdo = is_object($db) && method_exists($db, 'getPdo') ? $db->getPdo() : $db;
    }

    public function publish(string $channel, string $eventType, ?int $userId, array $payload, ?int $ttlSeconds = null): int
    {
        $st = $this->db->prepare("
            INSERT INTO realtime_notifications (channel_name, user_id, event_type, payload, expires_at)
            VALUES (:c, :u, :e, :p, :exp)
        ");
        $expires = $ttlSeconds ? date('Y-m-d H:i:s', time() + $ttlSeconds) : null;
        $st->execute([
            ':c' => $channel,
            ':u' => $userId,
            ':e' => $eventType,
            ':p' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ':exp' => $expires
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function fetchPending(int $userId, string $channel = 'global', int $limit = 20, ?int $sinceId = null): array
    {
        $sql = "SELECT * FROM realtime_notifications WHERE channel_name = :c AND delivered_at IS NULL AND (user_id IS NULL OR user_id = :u)";
        $params = [':c' => $channel, ':u' => $userId];
        if ($sinceId) { $sql .= " AND id > :sid"; $params[':sid'] = $sinceId; }
        $sql .= " ORDER BY id ASC LIMIT :lim";
        try {
            $st = $this->db->prepare($sql);
            foreach ($params as $k => $v) $st->bindValue($k, $v);
            $st->bindValue(':lim', $limit, PDO::PARAM_INT);
            $st->execute();
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function markDelivered(array $ids): int
    {
        if (empty($ids)) return 0;
        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $st = $this->db->prepare("UPDATE realtime_notifications SET delivered_at = NOW() WHERE id IN ($placeholders) AND delivered_at IS NULL");
            $st->execute($ids);
            return $st->rowCount();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function markRead(int $userId, array $ids): int
    {
        if (empty($ids)) return 0;
        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $st = $this->db->prepare("UPDATE realtime_notifications SET read_at = NOW() WHERE id IN ($placeholders) AND (user_id = ? OR user_id IS NULL) AND read_at IS NULL");
            $params = array_merge($ids, [$userId]);
            $st->execute($params);
            return $st->rowCount();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function getUnreadCount(int $userId, string $channel = 'global'): int
    {
        try {
            $st = $this->db->prepare("SELECT COUNT(*) FROM realtime_notifications WHERE channel_name = :c AND read_at IS NULL AND (user_id IS NULL OR user_id = :u)");
            $st->execute([':c' => $channel, ':u' => $userId]);
            return (int)$st->fetchColumn();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function cleanup(int $daysToKeep = 30): int
    {
        try {
            $st = $this->db->prepare("DELETE FROM realtime_notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL :d DAY)");
            $st->bindValue(':d', $daysToKeep, PDO::PARAM_INT);
            $st->execute();
            return $st->rowCount();
        } catch (\Throwable $e) {
            return 0;
        }
    }
}

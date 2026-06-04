<?php
namespace App\Services;

use PDO;

class AuditService
{
    private $db;
    private $pdo;

    public function __construct($db)
    {
        $this->db = $db;
        $this->pdo = is_object($db) && method_exists($db, 'getPdo') ? $db->getPdo() : $db;
    }

    public function log(string $action, ?int $userId = null, ?string $userRole = null, ?string $entityType = null, ?int $entityId = null, ?string $description = null, array $changes = [], string $status = 'success'): int
    {
        try {
            $st = $this->db->prepare("
                INSERT INTO audit_log (user_id, user_role, action, entity_type, entity_id, description, ip_address, user_agent, request_method, request_url, changes, status)
                VALUES (:uid, :role, :act, :et, :eid, :desc, :ip, :ua, :method, :url, :changes, :status)
            ");
            $st->execute([
                ':uid' => $userId,
                ':role' => $userRole,
                ':act' => $action,
                ':et' => $entityType,
                ':eid' => $entityId,
                ':desc' => $description,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                ':ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
                ':method' => $_SERVER['REQUEST_METHOD'] ?? null,
                ':url' => substr($_SERVER['REQUEST_URI'] ?? '', 0, 500),
                ':changes' => json_encode($changes, JSON_UNESCAPED_UNICODE),
                ':status' => $status
            ]);
            return (int)$this->db->lastInsertId();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function getRecent(int $limit = 50, ?string $action = null, ?string $entityType = null, ?int $userId = null): array
    {
        $sql = "SELECT a.*, u.name as user_name FROM audit_log a LEFT JOIN users u ON a.user_id = u.id WHERE 1=1";
        $params = [];
        if ($action) { $sql .= " AND a.action = :act"; $params[':act'] = $action; }
        if ($entityType) { $sql .= " AND a.entity_type = :et"; $params[':et'] = $entityType; }
        if ($userId) { $sql .= " AND a.user_id = :uid"; $params[':uid'] = $userId; }
        $sql .= " ORDER BY a.created_at DESC LIMIT :lim";
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

    public function getStats(int $days = 7): array
    {
        $stats = ['total' => 0, 'by_action' => [], 'by_user' => [], 'failures' => 0];
        try {
            $st = $this->db->prepare("SELECT COUNT(*) FROM audit_log WHERE created_at >= DATE_SUB(NOW(), INTERVAL :d DAY)");
            $st->bindValue(':d', $days, PDO::PARAM_INT);
            $st->execute();
            $stats['total'] = (int)$st->fetchColumn();

            $st = $this->db->prepare("SELECT action, COUNT(*) as cnt FROM audit_log WHERE created_at >= DATE_SUB(NOW(), INTERVAL :d DAY) GROUP BY action ORDER BY cnt DESC LIMIT 10");
            $st->bindValue(':d', $days, PDO::PARAM_INT);
            $st->execute();
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $stats['by_action'][$r['action']] = (int)$r['cnt'];
            }

            $st = $this->db->prepare("SELECT user_id, user_role, COUNT(*) as cnt FROM audit_log WHERE created_at >= DATE_SUB(NOW(), INTERVAL :d DAY) AND user_id IS NOT NULL GROUP BY user_id, user_role ORDER BY cnt DESC LIMIT 10");
            $st->bindValue(':d', $days, PDO::PARAM_INT);
            $st->execute();
            $stats['by_user'] = $st->fetchAll(PDO::FETCH_ASSOC);

            $st = $this->db->prepare("SELECT COUNT(*) FROM audit_log WHERE created_at >= DATE_SUB(NOW(), INTERVAL :d DAY) AND status = 'failure'");
            $st->bindValue(':d', $days, PDO::PARAM_INT);
            $st->execute();
            $stats['failures'] = (int)$st->fetchColumn();
        } catch (\Throwable $e) {}
        return $stats;
    }

    public function cleanup(int $daysToKeep = 90): int
    {
        try {
            $st = $this->db->prepare("DELETE FROM audit_log WHERE created_at < DATE_SUB(NOW(), INTERVAL :d DAY)");
            $st->bindValue(':d', $daysToKeep, PDO::PARAM_INT);
            $st->execute();
            return $st->rowCount();
        } catch (\Throwable $e) {
            return 0;
        }
    }
}

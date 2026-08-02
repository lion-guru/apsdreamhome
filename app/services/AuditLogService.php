<?php

namespace App\Services;

use App\Core\Database\Database;
use \App\Traits\ServiceTenantTrait;

class AuditLogService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;

    public function __construct(Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * Log an audit event
     */
    public function log(array $data): int
    {
        $default = [
            'user_id' => 0,
            'user_role' => 'unknown',
            'action' => 'unknown',
            'action_type' => 'update',
            'entity_type' => null,
            'entity_id' => null,
            'description' => null,
            'old_values' => null,
            'new_values' => null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'request_url' => $_SERVER['REQUEST_URI'] ?? null,
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? null,
            'session_id' => session_id() ?? null,
            'status' => 'success',
            'error_message' => null,
            'metadata' => null,
        ];

        $data = array_merge($default, $data);

        try {
            $this->db->insert('audit_logs', [
                'user_id' => (int)$data['user_id'],
                'user_role' => $data['user_role'],
                'action' => $data['action'],
                'action_type' => $data['action_type'],
                'entity_type' => $data['entity_type'],
                'entity_id' => $data['entity_id'],
                'description' => $data['description'],
                'old_values' => $data['old_values'] ? json_encode($data['old_values']) : null,
                'new_values' => $data['new_values'] ? json_encode($data['new_values']) : null,
                'ip_address' => $data['ip_address'],
                'user_agent' => $data['user_agent'],
                'request_url' => $data['request_url'],
                'request_method' => $data['request_method'],
                'session_id' => $data['session_id'],
                'status' => $data['status'],
                'error_message' => $data['error_message'],
                'metadata' => $data['metadata'] ? json_encode($data['metadata']) : null,
            ]);
            return (int)$this->db->lastInsertId();
        } catch (\Throwable $e) {
            error_log("AuditLogService::log failed: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get audit logs with filters and pagination
     */
    public function getLogs(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[] = 'user_id = ?';
            $params[] = $filters['user_id'];
        }
        if (!empty($filters['user_role'])) {
            $where[] = 'user_role = ?';
            $params[] = $filters['user_role'];
        }
        if (!empty($filters['action'])) {
            $where[] = 'action = ?';
            $params[] = $filters['action'];
        }
        if (!empty($filters['entity_type'])) {
            $where[] = 'entity_type = ?';
            $params[] = $filters['entity_type'];
        }
        if (!empty($filters['entity_id'])) {
            $where[] = 'entity_id = ?';
            $params[] = $filters['entity_id'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = 'created_at >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'created_at <= ?';
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['status'])) {
            $where[] = 'status = ?';
            $params[] = $filters['status'];
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Total count
        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as cnt FROM audit_logs $whereSql",
            $params
        )['cnt'] ?? 0;

        // Data
        $params[] = $limit;
        $params[] = $offset;
        $logs = $this->db->fetchAll(
            "SELECT * FROM audit_logs $whereSql ORDER BY created_at DESC LIMIT ? OFFSET ?",
            $params
        );

        return [
            'logs' => $logs,
            'total' => (int)$total,
            'limit' => $limit,
            'offset' => $offset,
        ];
    }

    /**
     * Get timeline for a specific entity
     */
    public function getEntityTimeline(string $entityType, int $entityId, int $limit = 100): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM audit_logs WHERE entity_type = ? AND entity_id = ? ORDER BY created_at DESC LIMIT ?",
            [$entityType, $entityId, $limit]
        );
    }

    /**
     * Get user activity timeline
     */
    public function getUserTimeline(int $userId, int $limit = 100): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM audit_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }

    /**
     * Get activity stats for dashboard
     */
    public function getStats(int $days = 30): array
    {
        $since = date('Y-m-d H:i:s', strtotime("-$days days"));
        
        $byAction = $this->db->fetchAll(
            "SELECT action, COUNT(*) as cnt FROM audit_logs WHERE created_at >= ? GROUP BY action ORDER BY cnt DESC",
            [$since]
        );
        
        $byRole = $this->db->fetchAll(
            "SELECT user_role, COUNT(*) as cnt FROM audit_logs WHERE created_at >= ? GROUP BY user_role ORDER BY cnt DESC",
            [$since]
        );
        
        $byStatus = $this->db->fetchAll(
            "SELECT status, COUNT(*) as cnt FROM audit_logs WHERE created_at >= ? GROUP BY status",
            [$since]
        );
        
        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as cnt FROM audit_logs WHERE created_at >= ?",
            [$since]
        )['cnt'] ?? 0;

        return [
            'total' => (int)$total,
            'by_action' => $byAction,
            'by_role' => $byRole,
            'by_status' => $byStatus,
            'period_days' => $days,
        ];
    }
}
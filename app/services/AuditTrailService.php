<?php

namespace App\Services;

use App\Core\Database\Database;

use \App\Traits\ServiceTenantTrait;

/**
 * Audit Trail Service - Complete Activity Logging
 * Logs all system activities for compliance and debugging.
 * Canonical table: `audit_logs` (plural). Legacy `audit_log` was merged
 * and archived to `_archive_audit_log_202609` (2026-09-16).
 */
class AuditTrailService
{
    use \App\Traits\ServiceTenantTrait;

    private $database;
    private $logLevel = 'detailed'; // minimal, standard, detailed

    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->ensureTablesExist();
    }

    /**
     * Ensure audit tables exist (canonical plural schema; no-op when present)
     */
    private function ensureTablesExist(): void
    {
        try {
            // Canonical rich audit log table
            $sql = "CREATE TABLE IF NOT EXISTS audit_logs (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
                user_id INT UNSIGNED NOT NULL DEFAULT 0,
                user_role VARCHAR(50) NOT NULL DEFAULT 'system',
                action VARCHAR(100) NOT NULL,
                action_type ENUM('create','read','update','delete','login','logout','export','import','print','approve','reject','payment','commission') NOT NULL DEFAULT 'update',
                entity_type VARCHAR(100) NULL,
                entity_id BIGINT UNSIGNED NULL,
                description TEXT NULL,
                old_values LONGTEXT NULL,
                new_values LONGTEXT NULL,
                ip_address VARCHAR(45) NULL,
                user_agent TEXT NULL,
                request_url VARCHAR(500) NULL,
                request_method VARCHAR(10) NULL,
                session_id VARCHAR(128) NULL,
                status ENUM('success','failed','pending') NOT NULL DEFAULT 'success',
                error_message TEXT NULL,
                metadata LONGTEXT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_user (user_id),
                INDEX idx_role (user_role),
                INDEX idx_action (action),
                INDEX idx_entity (entity_type, entity_id),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $this->database->getConnection()->exec($sql);
        } catch (\Exception $e) {
            error_log("AuditTrailService table creation error (non-critical): " . $e->getMessage());
        }
    }

    /**
     * Log an action
     */
    public function log(string $action, ?string $entityType = null, ?int $entityId = null,
                       array $oldValues = [], array $newValues = [],
                       string $description = '', string $severity = 'info'): int
    {
        try {
            $userId = $this->getCurrentUserId();
            $userRole = $this->getCurrentUserType();

            $insertData = $this->tenantInsertData();
            $cols = "user_id, user_role, action, entity_type, entity_id, old_values, new_values,
                     description, ip_address, user_agent, request_url, request_method, session_id,
                     status, metadata" . (count($insertData) > 0 ? ', ' . implode(', ', array_keys($insertData)) : '');
            $ph = "?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?" . (count($insertData) > 0 ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '');
            $sql = "INSERT INTO audit_logs ($cols) VALUES ($ph)";

            $stmt = $this->database->prepare($sql);
            $stmt->execute(array_merge([
                $userId ?? 0,
                $userRole,
                $action,
                $entityType,
                $entityId,
                !empty($oldValues) ? json_encode($oldValues) : null,
                !empty($newValues) ? json_encode($newValues) : null,
                $description !== '' ? $description : null,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                $_SERVER['REQUEST_URI'] ?? null,
                $_SERVER['REQUEST_METHOD'] ?? null,
                session_id() ?: null,
                'success',
                json_encode(['severity' => $severity])
            ], array_values($insertData)));

            return (int)$this->database->lastInsertId();

        } catch (\Exception $e) {
            error_log("Audit log error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Log failed action
     */
    public function logFailed(string $action, string $errorMessage,
                             ?string $entityType = null, ?int $entityId = null,
                             string $description = ''): int
    {
        try {
            $userId = $this->getCurrentUserId();
            $userRole = $this->getCurrentUserType();

            $insertData = $this->tenantInsertData();
            $cols = "user_id, user_role, action, entity_type, entity_id,
                     description, status, error_message, ip_address, request_url, request_method, session_id, metadata" . (count($insertData) > 0 ? ', ' . implode(', ', array_keys($insertData)) : '');
            $ph = "?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?" . (count($insertData) > 0 ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '');
            $sql = "INSERT INTO audit_logs ($cols) VALUES ($ph)";

            $stmt = $this->database->prepare($sql);
            $stmt->execute(array_merge([
                $userId ?? 0,
                $userRole,
                $action,
                $entityType,
                $entityId,
                $description !== '' ? $description : null,
                'failed',
                $errorMessage,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['REQUEST_URI'] ?? null,
                $_SERVER['REQUEST_METHOD'] ?? null,
                session_id() ?: null,
                json_encode(['severity' => 'error'])
            ], array_values($insertData)));

            return (int)$this->database->lastInsertId();

        } catch (\Exception $e) {
            error_log("Audit log error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Query audit logs
     */
    public function query(array $filters = [], int $page = 1, int $limit = 50): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[] = 'user_id = ?';
            $params[] = $filters['user_id'];
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

        if (!empty($filters['severity'])) {
            $where[] = 'metadata LIKE ?';
            $params[] = '%"severity":"' . str_replace(['"', '%'], '', $filters['severity']) . '"%';
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'created_at >= ?';
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'created_at <= ?';
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(description LIKE ? OR action LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        $tenantFrag = $this->tenantSql();
        $whereClause = implode(' AND ', $where) . $tenantFrag;
        if ($tenantFrag !== '') {
            // tenantSql() for tid>1 inlines the id; keep symmetry if it ever uses a placeholder
            if (strpos($tenantFrag, '?') !== false) $params[] = $this->tenantId();
        }

        // Legacy-compatible select list (timestamp/user_type aliases)
        $selectList = "*, created_at AS `timestamp`, user_role AS user_type,
            JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.severity')) AS severity";

        // Get total count
        $countSql = "SELECT COUNT(*) FROM audit_logs WHERE {$whereClause}";
        $stmt = $this->database->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();

        // Get records
        $offset = ($page - 1) * $limit;
        $sql = "SELECT {$selectList} FROM audit_logs
                WHERE {$whereClause}
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->database->prepare($sql);
        $stmt->execute(array_merge($params, [$limit, $offset]));
        $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Decode JSON fields
        foreach ($records as &$record) {
            if (!empty($record['old_values'])) {
                $record['old_values'] = json_decode($record['old_values'], true);
            }
            if (!empty($record['new_values'])) {
                $record['new_values'] = json_decode($record['new_values'], true);
            }
        }

        return [
            'records' => $records,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }

    /**
     * Get entity history
     */
    public function getEntityHistory(string $entityType, int $entityId): array
    {
        $sql = "SELECT *, created_at AS `timestamp`, user_role AS user_type,
                    JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.severity')) AS severity
                FROM audit_logs
                WHERE entity_type = ? AND entity_id = ?" . $this->tenantSql() . "
                ORDER BY created_at DESC";

        $stmt = $this->database->prepare($sql);
        $params = [$entityType, $entityId];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt->execute($params);
        $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($records as &$record) {
            if (!empty($record['old_values'])) {
                $record['old_values'] = json_decode($record['old_values'], true);
            }
            if (!empty($record['new_values'])) {
                $record['new_values'] = json_decode($record['new_values'], true);
            }
        }

        return $records;
    }

    /**
     * Get user activity
     */
    public function getUserActivity(int $userId, string $userType, int $days = 30): array
    {
        $sql = "SELECT
                    DATE(created_at) as date,
                    action,
                    COUNT(*) as count
                FROM audit_logs
                WHERE user_id = ? AND user_role = ?" . $this->tenantSql() . "
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at), action
                ORDER BY date DESC, count DESC";

        $stmt = $this->database->prepare($sql);
        $params = [$userId, $userType, $days];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get activity statistics
     */
    public function getStats(string $period = 'today'): array
    {
        $dateFilter = match($period) {
            'today' => 'DATE(created_at) = CURDATE()',
            'week' => 'created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)',
            'month' => 'created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)',
            default => '1=1'
        };

        $stats = [];

        // Total actions
        $sql1 = "SELECT COUNT(*) FROM audit_logs WHERE {$dateFilter}" . $this->tenantSql();
        $params1 = [];
        if ($this->tenantId() > 1) $params1[] = $this->tenantId();
        $stats['total_actions'] = (int)$this->database->prepare($sql1)->fetchColumn($params1 === [] ? null : $params1);

        // By action type
        $sql2 = "SELECT action, COUNT(*) as count FROM audit_logs
                  WHERE {$dateFilter}" . $this->tenantSql() . " GROUP BY action ORDER BY count DESC LIMIT 10";
        $stmt2 = $this->database->prepare($sql2);
        $params2 = [];
        if ($this->tenantId() > 1) $params2[] = $this->tenantId();
        $stmt2->execute($params2);
        $stats['top_actions'] = $stmt2->fetchAll(\PDO::FETCH_ASSOC);

        // By user type
        $sql3 = "SELECT user_role AS user_type, COUNT(*) as count FROM audit_logs
                  WHERE {$dateFilter}" . $this->tenantSql() . " GROUP BY user_role";
        $stmt3 = $this->database->prepare($sql3);
        $params3 = [];
        if ($this->tenantId() > 1) $params3[] = $this->tenantId();
        $stmt3->execute($params3);
        $stats['by_user_type'] = $stmt3->fetchAll(\PDO::FETCH_ASSOC);

        // Failed actions
        $sql4 = "SELECT COUNT(*) FROM audit_logs WHERE status = 'failed' AND {$dateFilter}" . $this->tenantSql();
        $stmt4 = $this->database->prepare($sql4);
        $params4 = [];
        if ($this->tenantId() > 1) $params4[] = $this->tenantId();
        $stmt4->execute($params4);
        $stats['failed_actions'] = (int)$stmt4->fetchColumn($params4 === [] ? null : $params4);

        return $stats;
    }

    /**
     * Purge old records (the legacy `audit_log_archive` table never existed,
     * so this now purges `audit_logs` directly honoring retention).
     */
    public function archiveOldRecords(int $days = 90): int
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        try {
            $sql = "DELETE FROM audit_logs WHERE created_at < ?" . $this->tenantSql();
            $params = [$cutoff];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $stmt = $this->database->prepare($sql);
            $stmt->execute($params);

            return $stmt->rowCount();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Get current user ID
     */
    private function getCurrentUserId(): ?int
    {
        return $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? $_SESSION['associate_id'] ?? null;
    }

    /**
     * Get current user type
     */
    private function getCurrentUserType(): string
    {
        if (!empty($_SESSION['admin_id'])) return 'admin';
        if (!empty($_SESSION['user_id'])) return 'customer';
        if (!empty($_SESSION['associate_id'])) return 'associate';
        if (!empty($_SESSION['employee_id'])) return 'employee';
        return 'system';
    }
}

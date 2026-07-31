<?php

namespace App\Services;

use App\Core\Database\Database;

use \App\Traits\ServiceTenantTrait;

/**
 * Audit Trail Service - Complete Activity Logging
 * Logs all system activities for compliance and debugging
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
     * Ensure audit tables exist
     */
    private function ensureTablesExist(): void
    {
        try {
            // Main audit log table
            $sql = "CREATE TABLE IF NOT EXISTS audit_log (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                user_id INT NULL,
                user_type VARCHAR(20) DEFAULT 'system',
                user_ip VARCHAR(45) NULL,
                user_agent VARCHAR(500) NULL,
                session_id VARCHAR(64) NULL,
                action VARCHAR(100) NOT NULL,
                entity_type VARCHAR(50) NULL,
                entity_id INT NULL,
                old_values JSON NULL,
                new_values JSON NULL,
                description TEXT NULL,
                severity VARCHAR(20) DEFAULT 'info',
                status VARCHAR(20) DEFAULT 'success',
                error_message TEXT NULL,
                request_url VARCHAR(500) NULL,
                request_method VARCHAR(10) NULL,
                execution_time_ms INT NULL,
                INDEX idx_timestamp (timestamp),
                INDEX idx_user (user_id, user_type),
                INDEX idx_action (action),
                INDEX idx_entity (entity_type, entity_id),
                INDEX idx_severity (severity)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
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
            $userType = $this->getCurrentUserType();
            
            $sql = "INSERT INTO audit_log 
                    (user_id, user_type, user_ip, user_agent, session_id, action, 
                     entity_type, entity_id, old_values, new_values, description, 
                     severity, request_url, request_method) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->database->prepare($sql);
            $stmt->execute([
                $userId,
                $userType,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                session_id() ?? null,
                $action,
                $entityType,
                $entityId,
                !empty($oldValues) ? json_encode($oldValues) : null,
                !empty($newValues) ? json_encode($newValues) : null,
                $description,
                $severity,
                $_SERVER['REQUEST_URI'] ?? null,
                $_SERVER['REQUEST_METHOD'] ?? null
            ]);
            
            return $this->database->lastInsertId();
            
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
            $userType = $this->getCurrentUserType();
            
            $sql = "INSERT INTO audit_log 
                    (user_id, user_type, user_ip, action, entity_type, entity_id, 
                     description, severity, status, error_message, request_url, request_method) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->database->prepare($sql);
            $stmt->execute([
                $userId,
                $userType,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $action,
                $entityType,
                $entityId,
                $description,
                'error',
                'failed',
                $errorMessage,
                $_SERVER['REQUEST_URI'] ?? null,
                $_SERVER['REQUEST_METHOD'] ?? null
            ]);
            
            return $this->database->lastInsertId();
            
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
            $where[] = 'severity = ?';
            $params[] = $filters['severity'];
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = 'timestamp >= ?';
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = 'timestamp <= ?';
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = '(description LIKE ? OR action LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Get total count
        $countSql = "SELECT COUNT(*) FROM audit_log WHERE {$whereClause}";
        $stmt = $this->database->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        // Get records
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM audit_log 
                WHERE {$whereClause} 
                ORDER BY timestamp DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute(array_merge($params, [$limit, $offset]));
        $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Decode JSON fields
        foreach ($records as &$record) {
            if ($record['old_values']) {
                $record['old_values'] = json_decode($record['old_values'], true);
            }
            if ($record['new_values']) {
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
        $sql = "SELECT * FROM audit_log 
                WHERE entity_type = ? AND entity_id = ? 
                ORDER BY timestamp DESC";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$entityType, $entityId]);
        $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($records as &$record) {
            if ($record['old_values']) {
                $record['old_values'] = json_decode($record['old_values'], true);
            }
            if ($record['new_values']) {
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
                    DATE(timestamp) as date,
                    action,
                    COUNT(*) as count
                FROM audit_log 
                WHERE user_id = ? AND user_type = ? 
                AND timestamp >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(timestamp), action
                ORDER BY date DESC, count DESC";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$userId, $userType, $days]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get activity statistics
     */
    public function getStats(string $period = 'today'): array
    {
        $dateFilter = match($period) {
            'today' => 'DATE(timestamp) = CURDATE()',
            'week' => 'timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)',
            'month' => 'timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)',
            default => '1=1'
        };
        
        $stats = [];
        
        // Total actions
        $sql1 = "SELECT COUNT(*) FROM audit_log WHERE {$dateFilter}";
        $stats['total_actions'] = $this->database->query($sql1)->fetchColumn();
        
        // By action type
        $sql2 = "SELECT action, COUNT(*) as count FROM audit_log 
                  WHERE {$dateFilter} GROUP BY action ORDER BY count DESC LIMIT 10";
        $stats['top_actions'] = $this->database->query($sql2)->fetchAll(\PDO::FETCH_ASSOC);
        
        // By user type
        $sql3 = "SELECT user_type, COUNT(*) as count FROM audit_log 
                  WHERE {$dateFilter} GROUP BY user_type";
        $stats['by_user_type'] = $this->database->query($sql3)->fetchAll(\PDO::FETCH_ASSOC);
        
        // Failed actions
        $sql4 = "SELECT COUNT(*) FROM audit_log WHERE status = 'failed' AND {$dateFilter}";
        $stats['failed_actions'] = $this->database->query($sql4)->fetchColumn();
        
        return $stats;
    }
    
    /**
     * Archive old records
     */
    public function archiveOldRecords(int $days = 90): int
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        try {
            // Move to archive
            $sql = "INSERT INTO audit_log_archive 
                    (id, timestamp, user_id, user_type, action, entity_type, entity_id, description)
                    SELECT id, timestamp, user_id, user_type, action, entity_type, entity_id, description 
                    FROM audit_log 
                    WHERE timestamp < ?";
            $stmt = $this->database->prepare($sql);
            $stmt->execute([$cutoff]);
            $archived = $stmt->rowCount();
            
            // Delete from main table
            if ($archived > 0) {
                $sql2 = "DELETE FROM audit_log WHERE timestamp < ?";
                $stmt2 = $this->database->prepare($sql2);
                $stmt2->execute([$cutoff]);
            }
            
            return $archived;
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

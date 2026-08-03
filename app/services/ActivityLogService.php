<?php

namespace App\Services;

use App\Core\Database\Database;
use \App\Traits\ServiceTenantTrait;

/**
 * Activity Log Service
 * Logs user actions for security audit trail
 */
class ActivityLogService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance()->getConnection();
    }

    /**
     * Log a user activity
     */
    public function log(int $userId, string $action, array $details = [], ?string $ipAddress = null): bool
    {
        try {
            $insertData = $this->tenantInsertData();
            $cols = "user_id, action, details, ip_address, created_at" . (count($insertData) > 0 ? ', ' . implode(', ', array_keys($insertData)) : '');
            $ph = "?, ?, ?, ?, NOW()" . (count($insertData) > 0 ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '');
            $stmt = $this->db->prepare("
                INSERT INTO user_activity_logs_unified ($cols) VALUES ($ph)
            ");
            $params = [
                $userId,
                $action,
                json_encode($details),
                $ipAddress ?? ($_SERVER['REMOTE_ADDR'] ?? null)
            ];
            if (!empty($insertData)) $params = array_merge($params, array_values($insertData));
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('ActivityLog error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get recent activities for a user
     */
    public function getRecent(int $userId, int $limit = 50): array
    {
        try {
            $sql = "SELECT * FROM user_activity_logs_unified 
                WHERE user_id = ?" . $this->tenantSql() . "
                ORDER BY created_at DESC 
                LIMIT ?";
            $params = [$userId];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $params[] = $limit;
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('ActivityLog getRecent error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Check for suspicious activity (multiple failed logins, etc.)
     */
    public function checkSuspicious(int $userId, string $action, int $windowMinutes = 15, int $maxAttempts = 5): bool
    {
        try {
            $sql = "SELECT COUNT(*) as cnt FROM user_activity_logs_unified 
                WHERE user_id = ? AND action = ?" . $this->tenantSql() . "
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)";
            $params = [$userId, $action];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $params[] = $windowMinutes;
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return ($row['cnt'] ?? 0) >= $maxAttempts;
        } catch (\Throwable $e) {
            error_log('ActivityLog checkSuspicious error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get activity summary for admin dashboard
     */
    public function getSummary(int $hours = 24): array
    {
        try {
            $sql = "SELECT action, COUNT(*) as count, 
                       COUNT(DISTINCT user_id) as unique_users
                FROM user_activity_logs_unified 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)" . $this->tenantSql() . "
                GROUP BY action
                ORDER BY count DESC";
            $params = [$hours];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('ActivityLog getSummary error: ' . $e->getMessage());
            return [];
        }
    }
}

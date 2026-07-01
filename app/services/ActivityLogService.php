<?php

namespace App\Services;

/**
 * Activity Log Service
 * Logs user actions for security audit trail
 */
class ActivityLogService
{
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
            $stmt = $this->db->prepare("
                INSERT INTO user_activity_log (user_id, action, details, ip_address, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            return $stmt->execute([
                $userId,
                $action,
                json_encode($details),
                $ipAddress ?? ($_SERVER['REMOTE_ADDR'] ?? null)
            ]);
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
            $stmt = $this->db->prepare("
                SELECT * FROM user_activity_log 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
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
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as cnt FROM user_activity_log 
                WHERE user_id = ? AND action = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)
            ");
            $stmt->execute([$userId, $action, $windowMinutes]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return ($row['cnt'] ?? 0) >= $maxAttempts;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get activity summary for admin dashboard
     */
    public function getSummary(int $hours = 24): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT action, COUNT(*) as count, 
                       COUNT(DISTINCT user_id) as unique_users
                FROM user_activity_log 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
                GROUP BY action
                ORDER BY count DESC
            ");
            $stmt->execute([$hours]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }
}

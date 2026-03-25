<?php

namespace App\Http\Middleware;

use App\Core\Database;
use Exception;

/**
 * Admin Logger Class
 * Provides logging functionality for admin operations
 */
class AdminLogger
{
    private static $db;

    /**
     * Initialize database connection
     */
    private static function initDb()
    {
        if (!self::$db) {
            self::$db = Database::getInstance();
        }
        return self::$db;
    }

    /**
     * Log admin action
     * @param string $action Action performed
     * @param array $context Additional context
     * @return bool Success
     */
    public static function logAction($action, $context = [])
    {
        try {
            $db = self::initDb();
            
            $logEntry = [
                'admin_id' => $_SESSION['admin_id'] ?? null,
                'action' => $action,
                'context' => json_encode($context),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'created_at' => date('Y-m-d H:i:s')
            ];

            return $db->insert(
                "INSERT INTO admin_activity_log (admin_id, action, context, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?)",
                [
                    $logEntry['admin_id'],
                    $logEntry['action'],
                    $logEntry['context'],
                    $logEntry['ip_address'],
                    $logEntry['user_agent'],
                    $logEntry['created_at']
                ]
            );

        } catch (Exception $e) {
            error_log("AdminLogger Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log admin error
     * @param string $errorType Type of error
     * @param array $context Error context
     * @return bool Success
     */
    public static function logError($errorType, $context = [])
    {
        try {
            $db = self::initDb();
            
            $logEntry = [
                'admin_id' => $_SESSION['admin_id'] ?? null,
                'error_type' => $errorType,
                'context' => json_encode($context),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'created_at' => date('Y-m-d H:i:s')
            ];

            return $db->insert(
                "INSERT INTO admin_error_log (admin_id, error_type, context, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?)",
                [
                    $logEntry['admin_id'],
                    $logEntry['error_type'],
                    $logEntry['context'],
                    $logEntry['ip_address'],
                    $logEntry['user_agent'],
                    $logEntry['created_at']
                ]
            );

        } catch (Exception $e) {
            error_log("AdminLogger Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get admin activity logs
     * @param array $filters Optional filters
     * @return array Activity logs
     */
    public static function getActivityLogs($filters = [])
    {
        try {
            $db = self::initDb();
            
            $sql = "SELECT * FROM admin_activity_log WHERE 1=1";
            $params = [];

            if (!empty($filters['admin_id'])) {
                $sql .= " AND admin_id = ?";
                $params[] = $filters['admin_id'];
            }

            if (!empty($filters['action'])) {
                $sql .= " AND action = ?";
                $params[] = $filters['action'];
            }

            if (!empty($filters['date_from'])) {
                $sql .= " AND created_at >= ?";
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND created_at <= ?";
                $params[] = $filters['date_to'];
            }

            $sql .= " ORDER BY created_at DESC";

            return $db->fetchAll($sql, $params);

        } catch (Exception $e) {
            error_log("AdminLogger Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generic log method
     * @param string $message
     * @param string $level
     * @return bool
     */
    /**
     * Generic log method
     * @param string $message
     * @param string $level
     * @return bool
     */
    public static function log($message, $level = 'info')
    {
        return self::logAction($message, ['level' => $level]);
    }

    /**
     * Log a security alert
     * @param string $type
     * @param array $context
     * @return bool
     */
    public static function securityAlert($type, $context = [])
    {
        return self::logError("SECURITY_ALERT: $type", $context);
    }
}

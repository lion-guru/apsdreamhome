<?php

namespace App\Services;

use App\Core\Database\Database;

/**
 * Custom Logging Service - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 * Following APS Dream Home custom architecture patterns
 */
class LoggingService
{
    private $db;
    private $logLevels = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Log a message
     */
    public function log($level, $message, $context = [], $category = 'system')
    {
        if (!in_array($level, $this->logLevels)) {
            $level = 'info';
        }

        $this->db->insert('system_logs', [
            'level' => $level,
            'message' => $message,
            'context' => json_encode($context),
            'category' => $category,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'cli',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'cli',
            'user_id' => $this->getUserId(),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Emergency level log
     */
    public function emergency($message, $context = [], $category = 'system')
    {
        $this->log('emergency', $message, $context, $category);
    }

    /**
     * Alert level log
     */
    public function alert($message, $context = [], $category = 'system')
    {
        $this->log('alert', $message, $context, $category);
    }

    /**
     * Critical level log
     */
    public function critical($message, $context = [], $category = 'system')
    {
        $this->log('critical', $message, $context, $category);
    }

    /**
     * Error level log
     */
    public function error($message, $context = [], $category = 'system')
    {
        $this->log('error', $message, $context, $category);
    }

    /**
     * Warning level log
     */
    public function warning($message, $context = [], $category = 'system')
    {
        $this->log('warning', $message, $context, $category);
    }

    /**
     * Notice level log
     */
    public function notice($message, $context = [], $category = 'system')
    {
        $this->log('notice', $message, $context, $category);
    }

    /**
     * Info level log
     */
    public function info($message, $context = [], $category = 'system')
    {
        $this->log('info', $message, $context, $category);
    }

    /**
     * Debug level log
     */
    public function debug($message, $context = [], $category = 'system')
    {
        $this->log('debug', $message, $context, $category);
    }

    /**
     * Log security event
     */
    public function logSecurity($event, $details = [])
    {
        $this->db->insert('security_alerts', [
            'event_type' => $event,
            'details' => json_encode($details),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'user_id' => $this->getUserId(),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get log statistics
     */
    public function getLogStats($hours = 24)
    {
        $stats = [];

        foreach ($this->logLevels as $level) {
            $count = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM system_logs 
                 WHERE level = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? HOUR)",
                [$level, $hours]
            )['count'];

            $stats[$level] = $count;
        }

        // Get category stats
        $categories = $this->db->select(
            "SELECT category, COUNT(*) as count FROM system_logs 
             WHERE created_at > DATE_SUB(NOW(), INTERVAL ? HOUR)
             GROUP BY category",
            [$hours]
        );

        $stats['categories'] = [];
        foreach ($categories as $cat) {
            $stats['categories'][$cat['category']] = $cat['count'];
        }

        return $stats;
    }

    /**
     * Get logs with pagination
     */
    public function getLogs($category = 'system', $limit = 50, $offset = 0)
    {
        return $this->db->select(
            "SELECT * FROM system_logs 
             WHERE category = ? 
             ORDER BY created_at DESC 
             LIMIT ? OFFSET ?",
            [$category, $limit, $offset]
        );
    }

    /**
     * Search logs
     */
    public function searchLogs($search, $category = 'system', $limit = 50, $offset = 0)
    {
        return $this->db->select(
            "SELECT * FROM system_logs 
             WHERE category = ? AND (message LIKE ? OR context LIKE ?)
             ORDER BY created_at DESC 
             LIMIT ? OFFSET ?",
            [$category, "%$search%", "%$search%", $limit, $offset]
        );
    }

    /**
     * Get log by ID
     */
    public function getLogById($id)
    {
        return $this->db->fetchOne(
            "SELECT * FROM system_logs WHERE id = ?",
            [$id]
        );
    }

    /**
     * Get security alerts
     */
    public function getSecurityAlerts($status = 'active', $limit = 25, $offset = 0)
    {
        return $this->db->select(
            "SELECT * FROM security_alerts 
             WHERE status = ? 
             ORDER BY created_at DESC 
             LIMIT ? OFFSET ?",
            [$status, $limit, $offset]
        );
    }

    /**
     * Dismiss security alert
     */
    public function dismissAlert($alertId, $userId)
    {
        return $this->db->update('security_alerts', [
            'status' => 'dismissed',
            'dismissed_by' => $userId,
            'dismissed_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$alertId]);
    }

    /**
     * Clean old logs
     */
    public function cleanOldLogs($days = 30)
    {
        $deleted = $this->db->query(
            "DELETE FROM system_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$days]
        );

        return $deleted;
    }

    /**
     * Export logs to CSV
     */
    public function exportLogs($category = null, $startDate = null, $endDate = null)
    {
        $where = [];
        $params = [];

        if ($category) {
            $where[] = "category = ?";
            $params[] = $category;
        }

        if ($startDate) {
            $where[] = "created_at >= ?";
            $params[] = $startDate;
        }

        if ($endDate) {
            $where[] = "created_at <= ?";
            $params[] = $endDate;
        }

        $whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

        $logs = $this->db->select(
            "SELECT * FROM system_logs $whereClause ORDER BY created_at DESC",
            $params
        );

        $filename = 'logs_export_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = sys_get_temp_dir() . '/' . $filename;

        $file = fopen($filepath, 'w');
        fputcsv($file, ['ID', 'Level', 'Message', 'Context', 'Category', 'IP Address', 'User Agent', 'Created At']);

        foreach ($logs as $log) {
            fputcsv($file, [
                $log['id'],
                $log['level'],
                $log['message'],
                $log['context'],
                $log['category'],
                $log['ip_address'],
                $log['user_agent'],
                $log['created_at']
            ]);
        }

        fclose($file);

        return $filepath;
    }

    /**
     * Get current user ID from session
     */
    private function getUserId()
    {
        return $_SESSION['user_id'] ?? null;
    }
}

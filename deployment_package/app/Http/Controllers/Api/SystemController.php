<?php

namespace App\Http\Controllers\Api;

use \Exception;

class SystemController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Get system statistics
     */
    public function getStats()
    {
        try {

            $range = $this->request()->input('range', '24h');
            switch ($range) {
                case '7d':
                    $interval = '7 DAY';
                    $groupBy = 'DATE(timestamp)';
                    break;
                case '30d':
                    $interval = '30 DAY';
                    $groupBy = 'DATE(timestamp)';
                    break;
                default: // 24h
                    $interval = '24 HOUR';
                    $groupBy = 'HOUR(timestamp)';
                    break;
            }

            // Follow-up stats
            $followupStats = $this->db->fetch("
                SELECT
                    COUNT(*) as count,
                    AVG(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success_rate,
                    AVG(response_time) as avg_response
                FROM system_logs
                WHERE system = 'followup'
                  AND timestamp >= DATE_SUB(NOW(), INTERVAL $interval)
            ");

            // Visit stats
            $visitStats = $this->db->fetch("
                SELECT
                    COUNT(*) as count,
                    AVG(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as success_rate,
                    AVG(TIMESTAMPDIFF(SECOND, created_at, confirmed_at)) as avg_response
                FROM bookings
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)
            ");

            // AI stats
            $aiStats = $this->db->fetch("
                SELECT
                    COUNT(*) as count,
                    AVG(score) as avg_score,
                    AVG(processing_time) as avg_response
                FROM ai_logs
                WHERE type = 'property_recommendations'
                  AND timestamp >= DATE_SUB(NOW(), INTERVAL $interval)
            ");

            // Lead stats
            $leadStats = $this->db->fetch("
                SELECT
                    COUNT(*) as count,
                    AVG(CASE WHEN status != 'new' THEN 1 ELSE 0 END) as processed_rate,
                    AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as avg_response
                FROM leads
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)
            ");

            // Timeline data
            $timelineData = $this->db->fetchAll("
                SELECT
                    $groupBy as label,
                    COUNT(CASE WHEN system = 'followup' THEN 1 END) as followups,
                    COUNT(CASE WHEN system = 'visit' THEN 1 END) as visits,
                    COUNT(CASE WHEN system = 'ai' THEN 1 END) as ai
                FROM system_logs
                WHERE timestamp >= DATE_SUB(NOW(), INTERVAL $interval)
                GROUP BY $groupBy
                ORDER BY label ASC
            ");

            $timeline = [
                'labels' => \array_column($timelineData, 'label'),
                'followups' => \array_map('intval', \array_column($timelineData, 'followups')),
                'visits' => \array_map('intval', \array_column($timelineData, 'visits')),
                'ai' => \array_map('intval', \array_column($timelineData, 'ai'))
            ];

            return $this->jsonSuccess([
                'summary' => [
                    'followup' => $followupStats,
                    'visit' => $visitStats,
                    'ai' => $aiStats,
                    'lead' => $leadStats
                ],
                'timeline' => $timeline,
                'range' => $range,
                'generated_at' => \date('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Get system logs
     */
    public function getLogs()
    {
        try {

            $limit = (int)$this->request()->input('limit', 50);
            $offset = (int)$this->request()->input('offset', 0);
            $system = $this->request()->input('system');

            $sql = "SELECT * FROM system_logs";
            $params = [];
            if ($system) {
                $sql .= " WHERE system = ?";
                $params[] = $system;
            }
            $sql .= " ORDER BY timestamp DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $logs = $this->db->fetchAll($sql, $params);

            return $this->jsonSuccess($logs);
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Get audit logs
     */
    public function auditLogs()
    {
        try {

            $limit = (int)$this->request()->input('limit', 100);
            $action = $this->request()->input('action');

            $sql = "SELECT al.id, e.name as user, al.action, al.details, al.ip_address, al.created_at
                    FROM audit_log al
                    LEFT JOIN employees e ON al.user_id = e.id";

            $params = [];
            if ($action === 'onboarding_offboarding') {
                $sql .= " WHERE al.action IN ('Onboarding', 'Offboarding')";
            } elseif ($action === 'denials') {
                $sql .= " WHERE al.action = 'Permission Denied'";
            } elseif ($action) {
                $sql .= " WHERE al.action = ?";
                $params[] = $action;
            }

            $sql .= " ORDER BY al.id DESC LIMIT ?";
            $params[] = $limit;

            $logs = $this->db->fetchAll($sql, $params);

            return $this->jsonSuccess(['audit_logs' => $logs]);
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Get system health
     */
    public function health()
    {
        try {
            $dbStatus = 'online';
            try {
                $this->db->fetch("SELECT 1");
            } catch (\Exception $e) {
                $dbStatus = 'offline';
            }

            $diskFree = \disk_free_space(".");
            $diskTotal = \disk_total_space(".");
            $diskUsage = \round(100 - ($diskFree / $diskTotal * 100), 2);

            return $this->jsonSuccess([
                'status' => ($dbStatus === 'online') ? 'healthy' : 'degraded',
                'services' => [
                    'database' => $dbStatus,
                    'storage' => [
                        'usage_percent' => $diskUsage,
                        'free_gb' => round($diskFree / (1024 * 1024 * 1024), 2)
                    ],
                    'php_version' => PHP_VERSION,
                    'server_time' => date('Y-m-d H:i:s')
                ]
            ]);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }
}

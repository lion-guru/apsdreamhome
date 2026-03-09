<?php

namespace App\Services\Events;

use App\Core\Database;
use Psr\Log\LoggerInterface;

/**
 * Modern Event Monitor Service
 * Handles comprehensive event monitoring, security tracking, and performance analysis
 */
class EventMonitorService
{
    private Database $db;
    private LoggerInterface $logger;
    private array $alerts = [];
    private array $metrics = [];
    private array $sensitiveEventTypes = [
        'LOGIN_ATTEMPT',
        'SECURITY_BREACH',
        'ADMIN_ACTION',
        'DATA_MODIFICATION',
        'SYSTEM_ERROR',
        'PERFORMANCE_ALERT'
    ];

    // Event severity levels
    public const SEVERITY_LOW = 1;
    public const SEVERITY_MEDIUM = 2;
    public const SEVERITY_HIGH = 3;
    public const SEVERITY_CRITICAL = 4;

    public function __construct(Database $db, LoggerInterface $logger, array $config = [])
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->alerts = $config['alerts'] ?? [];
        $this->metrics = $config['metrics'] ?? [];
        $this->initializeMonitoringTables();
    }

    /**
     * Log event with monitoring
     */
    public function logEvent(string $eventName, array $data, int $severity = self::SEVERITY_LOW, array $context = []): void
    {
        try {
            $eventId = $this->createEventLog($eventName, $data, $severity, $context);
            
            // Check for security concerns
            $this->checkSecurityConcerns($eventName, $data, $severity);
            
            // Update metrics
            $this->updateEventMetrics($eventName, $severity);
            
            // Check for alerts
            $this->checkAlerts($eventName, $data, $severity);

            $this->logger->info("Event logged", [
                'event_id' => $eventId,
                'event_name' => $eventName,
                'severity' => $severity
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to log event", [
                'event' => $eventName,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get event statistics
     */
    public function getEventStats(array $filters = []): array
    {
        try {
            $stats = [];

            // Total events
            $sql = "SELECT COUNT(*) as total FROM event_logs";
            $params = [];
            
            if (!empty($filters['date_from'])) {
                $sql .= " WHERE created_at >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= (empty($params) ? " WHERE" : " AND") . " created_at <= ?";
                $params[] = $filters['date_to'];
            }
            
            $stats['total_events'] = $this->db->fetchOne($sql, $params) ?? 0;

            // Events by severity
            $severitySql = "SELECT severity, COUNT(*) as count FROM event_logs";
            $severityParams = [];
            
            if (!empty($filters['date_from'])) {
                $severitySql .= " WHERE created_at >= ?";
                $severityParams[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $severitySql .= (empty($severityParams) ? " WHERE" : " AND") . " created_at <= ?";
                $severityParams[] = $filters['date_to'];
            }
            
            $severitySql .= " GROUP BY severity";
            
            $severityStats = $this->db->fetchAll($severitySql, $severityParams);
            $stats['by_severity'] = [];
            foreach ($severityStats as $stat) {
                $stats['by_severity'][$stat['severity']] = $stat['count'];
            }

            // Events by type
            $typeSql = "SELECT event_name, COUNT(*) as count FROM event_logs";
            $typeParams = [];
            
            if (!empty($filters['date_from'])) {
                $typeSql .= " WHERE created_at >= ?";
                $typeParams[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $typeSql .= (empty($typeParams) ? " WHERE" : " AND") . " created_at <= ?";
                $typeParams[] = $filters['date_to'];
            }
            
            $typeSql .= " GROUP BY event_name ORDER BY count DESC LIMIT 10";
            
            $stats['by_type'] = $this->db->fetchAll($typeSql, $typeParams);

            // Recent events
            $recentSql = "SELECT * FROM event_logs ORDER BY created_at DESC LIMIT 50";
            $stats['recent_events'] = $this->db->fetchAll($recentSql);

            // Performance metrics
            $stats['performance'] = $this->getPerformanceMetrics($filters);

            // Security metrics
            $stats['security'] = $this->getSecurityMetrics($filters);

            return $stats;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get event stats", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get security metrics
     */
    public function getSecurityMetrics(array $filters = []): array
    {
        try {
            $security = [];

            // Security events
            $sql = "SELECT * FROM security_events";
            $params = [];
            
            if (!empty($filters['date_from'])) {
                $sql .= " WHERE created_at >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= (empty($params) ? " WHERE" : " AND") . " created_at <= ?";
                $params[] = $filters['date_to'];
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT 20";
            
            $security['recent_incidents'] = $this->db->fetchAll($sql, $params);

            // Security summary
            $summarySql = "SELECT 
                        COUNT(*) as total_incidents,
                        COUNT(CASE WHEN severity = ? THEN 1 END) as critical_incidents,
                        COUNT(CASE WHEN severity = ? THEN 1 END) as high_incidents,
                        COUNT(CASE WHEN severity = ? THEN 1 END) as medium_incidents
                    FROM security_events";
            
            $summaryParams = [];
            
            if (!empty($filters['date_from'])) {
                $summarySql .= " WHERE created_at >= ?";
                $summaryParams[] = $filters['date_from'];
            }
            
            $summarySql .= " AND created_at <= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            
            $summary = $this->db->fetchOne($summarySql, array_merge($summaryParams, [
                self::SEVERITY_CRITICAL,
                self::SEVERITY_HIGH,
                self::SEVERITY_MEDIUM
            ]));

            $security['summary'] = $summary ?? [
                'total_incidents' => 0,
                'critical_incidents' => 0,
                'high_incidents' => 0,
                'medium_incidents' => 0
            ];

            return $security;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get security metrics", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics(array $filters = []): array
    {
        try {
            $performance = [];

            // Average response times
            $avgTimeSql = "SELECT 
                        AVG(execution_time_ms) as avg_time,
                        MAX(execution_time_ms) as max_time,
                        MIN(execution_time_ms) as min_time,
                        COUNT(*) as count
                    FROM event_logs 
                    WHERE execution_time_ms IS NOT NULL";
            
            $params = [];
            
            if (!empty($filters['date_from'])) {
                $avgTimeSql .= " AND created_at >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $avgTimeSql .= " AND created_at <= ?";
                $params[] = $filters['date_to'];
            }
            
            $avgTimeSql .= " GROUP BY event_name";
            
            $performanceStats = $this->db->fetchAll($avgTimeSql, $params);
            
            $performance['by_event'] = [];
            foreach ($performanceStats as $stat) {
                $performance['by_event'][$stat['event_name']] = [
                    'avg_time_ms' => round($stat['avg_time'], 2),
                    'max_time_ms' => $stat['max_time'],
                    'min_time_ms' => $stat['min_time'],
                    'count' => $stat['count']
                ];
            }

            // Slow events
            $slowEventsSql = "SELECT * FROM event_logs 
                              WHERE execution_time_ms > ? 
                              ORDER BY execution_time_ms DESC 
                              LIMIT 10";
            
            $performance['slow_events'] = $this->db->fetchAll($slowEventsSql, [
                $this->metrics['slow_threshold'] ?? 1000 // 1 second
            ]);

            return $performance;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get performance metrics", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Create security alert
     */
    public function createSecurityAlert(string $type, string $message, array $data = [], int $severity = self::SEVERITY_HIGH): void
    {
        try {
            $sql = "INSERT INTO security_alerts 
                    (alert_type, message, alert_data, severity, status, created_at) 
                    VALUES (?, ?, ?, ?, 'active', NOW())";
            
            $this->db->execute($sql, [
                $type,
                $message,
                json_encode($data),
                $severity
            ]);

            // Send notification if configured
            if (isset($this->alerts['email'])) {
                $this->sendSecurityNotification($type, $message, $severity);
            }

            $this->logger->warning("Security alert created", [
                'type' => $type,
                'severity' => $severity
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to create security alert", [
                'type' => $type,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get system health status
     */
    public function getSystemHealth(): array
    {
        try {
            $health = [];

            // Event processing status
            $eventStatus = $this->db->fetchOne("
                SELECT 
                    COUNT(*) as total_events,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_events,
                    COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_events,
                    AVG(CASE WHEN execution_time_ms IS NOT NULL THEN execution_time_ms END) as avg_response_time
                FROM event_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ");

            $health['event_processing'] = [
                'total_events' => $eventStatus['total_events'] ?? 0,
                'completed_events' => $eventStatus['completed_events'] ?? 0,
                'failed_events' => $eventStatus['failed_events'] ?? 0,
                'success_rate' => $this->calculateSuccessRate($eventStatus),
                'avg_response_time_ms' => round($eventStatus['avg_response_time'] ?? 0, 2)
            ];

            // System resources
            $health['system_resources'] = [
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true),
                'disk_usage' => $this->getDiskUsage(),
                'cpu_load' => $this->getCpuLoad()
            ];

            // Active alerts
            $health['active_alerts'] = $this->db->fetchAll("
                SELECT * FROM security_alerts 
                WHERE status = 'active' 
                ORDER BY created_at DESC 
                LIMIT 10
            ");

            return $health;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get system health", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Clear old event logs
     */
    public function clearOldLogs(int $days = 30): array
    {
        try {
            $sql = "DELETE FROM event_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
            $deletedRows = $this->db->execute($sql, [$days]);

            $this->logger->info("Old event logs cleared", [
                'days' => $days,
                'deleted_rows' => $deletedRows
            ]);

            return [
                'success' => true,
                'message' => "Cleared logs older than {$days} days",
                'deleted_rows' => $deletedRows
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to clear old logs", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to clear old logs: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Private helper methods
     */
    private function initializeMonitoringTables(): void
    {
        $tables = [
            "CREATE TABLE IF NOT EXISTS event_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event_name VARCHAR(255) NOT NULL,
                event_data JSON,
                severity INT DEFAULT 1,
                status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
                error_message TEXT,
                execution_time_ms DECIMAL(10,2),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_event_name (event_name),
                INDEX idx_severity (severity),
                INDEX idx_status (status),
                INDEX idx_created_at (created_at)
            )",
            
            "CREATE TABLE IF NOT EXISTS security_events (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event_type VARCHAR(100) NOT NULL,
                event_data JSON,
                severity INT NOT NULL,
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_event_type (event_type),
                INDEX idx_severity (severity),
                INDEX idx_created_at (created_at)
            )",
            
            "CREATE TABLE IF NOT EXISTS security_alerts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                alert_type VARCHAR(100) NOT NULL,
                message TEXT,
                alert_data JSON,
                severity INT NOT NULL,
                status ENUM('active', 'resolved', 'dismissed') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                resolved_at TIMESTAMP NULL,
                INDEX idx_status (status),
                INDEX idx_created_at (created_at)
            )"
        ];

        foreach ($tables as $sql) {
            $this->db->execute($sql);
        }
    }

    private function createEventLog(string $eventName, array $data, int $severity, array $context): string
    {
        $sql = "INSERT INTO event_logs 
                (event_name, event_data, severity, context, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $this->db->execute($sql, [
            $eventName,
            json_encode($data),
            $severity,
            json_encode($context)
        ]);
        
        return $this->db->lastInsertId();
    }

    private function checkSecurityConcerns(string $eventName, array $data, int $severity): void
    {
        // Check for sensitive event types
        if (in_array($eventName, $this->sensitiveEventTypes)) {
            $this->createSecurityAlert('sensitive_event', "Sensitive event detected: {$eventName}", $data, $severity);
        }

        // Check for suspicious patterns
        $suspiciousPatterns = [
            'DROP', 'DELETE', 'UPDATE', 'INSERT', 'exec', 'eval',
            'UNION', 'SELECT', 'FROM', 'WHERE', 'OR', 'AND'
        ];

        $dataString = json_encode($data);
        foreach ($suspiciousPatterns as $pattern) {
            if (stripos($dataString, $pattern) !== false) {
                $this->createSecurityAlert('sql_injection_attempt', "SQL injection pattern detected in event: {$eventName}", $data, self::SEVERITY_CRITICAL);
                break;
            }
        }

        // Check for rapid succession
        if ($severity >= self::SEVERITY_HIGH) {
            $this->checkRapidEvents($eventName);
        }
    }

    private function checkRapidEvents(string $eventName): void
    {
        $sql = "SELECT COUNT(*) as count FROM event_logs 
                    WHERE event_name = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)";
        
        $count = $this->db->fetchOne($sql, [$eventName]) ?? 0;
        
        if ($count > 10) { // More than 10 events in 1 minute
            $this->createSecurityAlert('rapid_events', "Rapid events detected: {$eventName}", ['event_name' => $eventName, 'count' => $count], self::SEVERITY_HIGH);
        }
    }

    private function updateEventMetrics(string $eventName, int $severity): void
    {
        $sql = "INSERT INTO event_metrics (event_name, severity, event_count, last_occurrence) 
                    VALUES (?, ?, 1, NOW()) 
                    ON DUPLICATE KEY UPDATE 
                    event_count = event_count + 1, 
                    last_occurrence = NOW()";
        
        $this->db->execute($sql, [$eventName, $severity]);
    }

    private function checkAlerts(string $eventName, array $data, int $severity): void
    {
        foreach ($this->alerts as $alertConfig) {
            if ($this->shouldTriggerAlert($alertConfig, $eventName, $data, $severity)) {
                $this->createSecurityAlert($alertConfig['type'], $alertConfig['message'], $data, $severity);
            }
        }
    }

    private function shouldTriggerAlert(array $alertConfig, string $eventName, array $data, int $severity): bool
    {
        // Check severity threshold
        if (isset($alertConfig['severity_threshold']) && $severity < $alertConfig['severity_threshold']) {
            return false;
        }

        // Check event type
        if (isset($alertConfig['event_types']) && !in_array($eventName, $alertConfig['event_types'])) {
            return false;
        }

        // Check custom conditions
        if (isset($alertConfig['condition']) && !call_user_func($alertConfig['condition'], $data)) {
            return false;
        }

        return true;
    }

    private function sendSecurityNotification(string $type, string $message, int $severity): void
    {
        // Mock implementation - would integrate with email/SMS service
        $this->logger->critical("Security notification sent", [
            'type' => $type,
            'message' => $message,
            'severity' => $severity
        ]);
    }

    private function calculateSuccessRate(array $eventStatus): float
    {
        $total = $eventStatus['total_events'] ?? 0;
        $completed = $eventStatus['completed_events'] ?? 0;
        
        return $total > 0 ? ($completed / $total) * 100 : 0;
    }

    private function getDiskUsage(): array
    {
        $totalSpace = disk_total_space('/');
        $freeSpace = disk_free_space('/');
        
        return [
            'total' => $totalSpace,
            'free' => $freeSpace,
            'used' => $totalSpace - $freeSpace,
            'percentage' => $totalSpace > 0 ? (($totalSpace - $freeSpace) / $totalSpace) * 100 : 0
        ];
    }

    private function getCpuLoad(): array
    {
        // Mock CPU load - would use system-specific functions
        return [
            'load_1min' => sys_getloadavg()[0] ?? 0,
            'load_5min' => sys_getloadavg()[1] ?? 0,
            'load_15min' => sys_getloadavg()[2] ?? 0
        ];
    }
}

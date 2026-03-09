<?php

namespace App\Core\Performance;

use App\Core\Database\Database;
use App\Services\LoggingService;
use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * Performance Monitoring Service - APS Dream Home
 * Comprehensive system performance tracking and optimization
 * Custom MVC implementation without Laravel dependencies
 */
class PerformanceMonitoringService
{
    private $database;
    private $logger;
    private $metrics = [];
    private $slowQueryThreshold = 100; // milliseconds
    private $memoryThreshold = 50; // MB
    private $cpuThreshold = 80; // percentage

    public function __construct()
    {
        $this->database = \App\Core\Database\Database::getInstance();
        $this->logger = new LoggingService();
        $this->createPerformanceTables();
    }

    /**
     * Create performance monitoring tables
     */
    private function createPerformanceTables()
    {
        try {
            // Performance metrics table
            $sql = "CREATE TABLE IF NOT EXISTS performance_metrics (
                id INT AUTO_INCREMENT PRIMARY KEY,
                metric_type VARCHAR(50) NOT NULL,
                metric_name VARCHAR(100) NOT NULL,
                metric_value DECIMAL(10,2) NOT NULL,
                metric_unit VARCHAR(20),
                threshold_value DECIMAL(10,2),
                status ENUM('normal', 'warning', 'critical') DEFAULT 'normal',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_metric_type_name (metric_type, metric_name),
                INDEX idx_created_at (created_at),
                INDEX idx_status (status)
            )";
            $this->database->execute($sql);

            // Slow queries log
            $sql = "CREATE TABLE IF NOT EXISTS slow_queries_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                query_sql TEXT NOT NULL,
                query_params JSON,
                execution_time DECIMAL(10,2) NOT NULL,
                memory_usage DECIMAL(10,2),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_execution_time (execution_time),
                INDEX idx_created_at (created_at)
            )";
            $this->database->execute($sql);

            // Performance alerts
            $sql = "CREATE TABLE IF NOT EXISTS performance_alerts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                alert_type VARCHAR(50) NOT NULL,
                alert_message TEXT NOT NULL,
                current_value DECIMAL(10,2) NOT NULL,
                threshold_value DECIMAL(10,2) NOT NULL,
                severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
                resolved BOOLEAN DEFAULT FALSE,
                resolved_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_alert_type (alert_type),
                INDEX idx_severity (severity),
                INDEX idx_resolved (resolved),
                INDEX idx_created_at (created_at)
            )";
            $this->database->execute($sql);
        } catch (Exception $e) {
            $this->logger->log("Error creating performance tables: " . $e->getMessage(), 'error', 'performance');
        }
    }

    /**
     * Record system performance metrics
     */
    public function recordSystemMetrics()
    {
        try {
            $metrics = $this->collectSystemMetrics();

            foreach ($metrics as $type => $typeMetrics) {
                foreach ($typeMetrics as $name => $value) {
                    $this->recordMetric($type, $name, $value['value'], $value['unit'] ?? null, $value['threshold'] ?? null);
                }
            }

            $this->checkPerformanceThresholds($metrics);

            $this->logger->log("System metrics recorded successfully", 'info', 'performance');
        } catch (Exception $e) {
            $this->logger->log("Error recording system metrics: " . $e->getMessage(), 'error', 'performance');
        }
    }

    /**
     * Monitor database query performance
     */
    public function monitorQuery($sql, $params = [], $startTime = null)
    {
        if ($startTime === null) {
            $startTime = microtime(true);
        }

        $executionTime = (microtime(true) - $startTime) * 1000; // milliseconds

        if ($executionTime > $this->slowQueryThreshold) {
            $this->logSlowQuery($sql, $params, $executionTime);
        }

        return $executionTime;
    }

    /**
     * Get performance dashboard data
     */
    public function getPerformanceDashboard()
    {
        $dashboard = [
            'system_health' => $this->getSystemHealth(),
            'recent_metrics' => $this->getRecentMetrics(),
            'slow_queries' => $this->getSlowQueries(),
            'performance_alerts' => $this->getPerformanceAlerts(),
            'trends' => $this->getPerformanceTrends()
        ];

        return $dashboard;
    }

    /**
     * Collect system metrics
     */
    private function collectSystemMetrics()
    {
        $metrics = [];

        // Memory metrics
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
        $memoryPercent = ($memoryUsage / $memoryLimit) * 100;

        $metrics['memory']['usage'] = [
            'value' => round($memoryUsage / 1024 / 1024, 2), // MB
            'unit' => 'MB',
            'threshold' => $this->memoryThreshold
        ];

        $metrics['memory']['percentage'] = [
            'value' => round($memoryPercent, 2),
            'unit' => '%',
            'threshold' => 85
        ];

        // CPU metrics (if available)
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            $metrics['cpu']['load_1min'] = [
                'value' => round($load[0] * 100, 2),
                'unit' => '%',
                'threshold' => $this->cpuThreshold
            ];
        }

        // Database metrics
        $metrics['database']['connections'] = [
            'value' => $this->getDatabaseConnections(),
            'unit' => 'count',
            'threshold' => 50
        ];

        $metrics['database']['slow_queries'] = [
            'value' => $this->getSlowQueriesCount(),
            'unit' => 'count',
            'threshold' => 10
        ];

        // Disk metrics
        $diskUsage = $this->getDiskUsage();
        $metrics['disk']['usage'] = [
            'value' => round($diskUsage['used_percent'], 2),
            'unit' => '%',
            'threshold' => 90
        ];

        // Response time metrics
        $metrics['response']['average'] = [
            'value' => $this->getAverageResponseTime(),
            'unit' => 'ms',
            'threshold' => 500
        ];

        return $metrics;
    }

    /**
     * Record a metric
     */
    private function recordMetric($type, $name, $value, $unit = null, $threshold = null)
    {
        $status = 'normal';

        if ($threshold !== null) {
            if ($value >= $threshold * 1.2) {
                $status = 'critical';
            } elseif ($value >= $threshold) {
                $status = 'warning';
            }
        }

        $sql = "INSERT INTO performance_metrics (metric_type, metric_name, metric_value, metric_unit, threshold_value, status)
                VALUES (?, ?, ?, ?, ?, ?)";

        $this->database->execute($sql, [$type, $name, $value, $unit, $threshold, $status]);
    }

    /**
     * Check performance thresholds and create alerts
     */
    private function checkPerformanceThresholds($metrics)
    {
        foreach ($metrics as $type => $typeMetrics) {
            foreach ($typeMetrics as $name => $metric) {
                if ($metric['threshold'] && $metric['value'] >= $metric['threshold']) {
                    $severity = $this->calculateSeverity($metric['value'], $metric['threshold']);
                    $this->createPerformanceAlert($type, $name, $metric['value'], $metric['threshold'], $severity);
                }
            }
        }
    }

    /**
     * Create performance alert
     */
    private function createPerformanceAlert($type, $name, $value, $threshold, $severity)
    {
        $message = "Performance threshold exceeded for {$type}: {$name}. Current: {$value}, Threshold: {$threshold}";

        $sql = "INSERT INTO performance_alerts (alert_type, alert_message, current_value, threshold_value, severity)
                VALUES (?, ?, ?, ?, ?)";

        $this->database->execute($sql, [
            "{$type}_{$name}",
            $message,
            $value,
            $threshold,
            $severity
        ]);

        $this->logger->log($message, 'warning', 'performance');
    }

    /**
     * Log slow query
     */
    private function logSlowQuery($sql, $params, $executionTime)
    {
        $memoryUsage = memory_get_usage(true) / 1024 / 1024; // MB

        $sqlLog = "INSERT INTO slow_queries_log (query_sql, query_params, execution_time, memory_usage)
                   VALUES (?, ?, ?, ?)";

        $this->database->execute($sqlLog, [
            $sql,
            json_encode($params),
            $executionTime,
            $memoryUsage
        ]);

        $this->logger->log("Slow query detected: {$executionTime}ms", 'warning', 'performance');
    }

    /**
     * Get system health status
     */
    private function getSystemHealth()
    {
        $health = [
            'overall' => 'good',
            'issues' => []
        ];

        // Check recent critical alerts
        $sql = "SELECT COUNT(*) as count FROM performance_alerts 
                WHERE severity = 'critical' AND resolved = FALSE 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";

        $result = $this->database->fetchOne($sql);
        $criticalAlerts = $result['count'] ?? 0;

        if ($criticalAlerts > 0) {
            $health['overall'] = 'critical';
            $health['issues'][] = "{$criticalAlerts} critical alerts in last hour";
        } else {
            // Check warning alerts
            $sql = "SELECT COUNT(*) as count FROM performance_alerts 
                    WHERE severity IN ('medium', 'high') AND resolved = FALSE 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";

            $result = $this->database->fetchOne($sql);
            $warningAlerts = $result['count'] ?? 0;

            if ($warningAlerts > 5) {
                $health['overall'] = 'warning';
                $health['issues'][] = "{$warningAlerts} warning alerts in last hour";
            }
        }

        return $health;
    }

    /**
     * Get recent metrics
     */
    private function getRecentMetrics($limit = 50)
    {
        $sql = "SELECT * FROM performance_metrics 
                ORDER BY created_at DESC 
                LIMIT ?";

        return $this->database->fetchAll($sql, [$limit]);
    }

    /**
     * Get slow queries
     */
    private function getSlowQueries($limit = 20)
    {
        $sql = "SELECT * FROM slow_queries_log 
                ORDER BY execution_time DESC 
                LIMIT ?";

        return $this->database->fetchAll($sql, [$limit]);
    }

    /**
     * Get performance alerts
     */
    private function getPerformanceAlerts($limit = 20)
    {
        $sql = "SELECT * FROM performance_alerts 
                WHERE resolved = FALSE 
                ORDER BY severity DESC, created_at DESC 
                LIMIT ?";

        return $this->database->fetchAll($sql, [$limit]);
    }

    /**
     * Get performance trends
     */
    private function getPerformanceTrends()
    {
        $trends = [];

        // Memory usage trend (last 24 hours)
        $sql = "SELECT AVG(metric_value) as avg_value, 
                       MIN(metric_value) as min_value,
                       MAX(metric_value) as max_value,
                       DATE_FORMAT(created_at, '%H:00') as hour
                FROM performance_metrics 
                WHERE metric_type = 'memory' AND metric_name = 'usage'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY DATE_FORMAT(created_at, '%H:00')
                ORDER BY hour";

        $trends['memory'] = $this->database->fetchAll($sql);

        // CPU usage trend (last 24 hours)
        $sql = "SELECT AVG(metric_value) as avg_value, 
                       MIN(metric_value) as min_value,
                       MAX(metric_value) as max_value,
                       DATE_FORMAT(created_at, '%H:00') as hour
                FROM performance_metrics 
                WHERE metric_type = 'cpu'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY DATE_FORMAT(created_at, '%H:00')
                ORDER BY hour";

        $trends['cpu'] = $this->database->fetchAll($sql);

        return $trends;
    }

    /**
     * Get database connections count
     */
    private function getDatabaseConnections()
    {
        try {
            $result = $this->database->fetchOne("SHOW STATUS LIKE 'Threads_connected'");
            return $result['Value'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get slow queries count
     */
    private function getSlowQueriesCount()
    {
        $sql = "SELECT COUNT(*) as count FROM slow_queries_log 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";

        $result = $this->database->fetchOne($sql);
        return $result['count'] ?? 0;
    }

    /**
     * Get disk usage
     */
    private function getDiskUsage()
    {
        $totalSpace = disk_total_space('/');
        $freeSpace = disk_free_space('/');

        if ($totalSpace && $freeSpace) {
            $usedSpace = $totalSpace - $freeSpace;
            return [
                'total' => $totalSpace,
                'free' => $freeSpace,
                'used' => $usedSpace,
                'used_percent' => ($usedSpace / $totalSpace) * 100
            ];
        }

        return ['used_percent' => 0];
    }

    /**
     * Get average response time
     */
    private function getAverageResponseTime()
    {
        // This would typically be calculated from actual request logs
        // For now, return a simulated value
        return rand(50, 300);
    }

    /**
     * Calculate alert severity
     */
    private function calculateSeverity($value, $threshold)
    {
        $ratio = $value / $threshold;

        if ($ratio >= 1.5) {
            return 'critical';
        } elseif ($ratio >= 1.2) {
            return 'high';
        } elseif ($ratio >= 1.0) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Parse memory limit string
     */
    private function parseMemoryLimit($memoryLimit)
    {
        $unit = strtolower(substr($memoryLimit, -1));
        $value = (int) substr($memoryLimit, 0, -1);

        switch ($unit) {
            case 'g':
                return $value * 1024 * 1024 * 1024;
            case 'm':
                return $value * 1024 * 1024;
            case 'k':
                return $value * 1024;
            default:
                return $value;
        }
    }

    /**
     * Clean up old performance data
     */
    public function cleanupOldData($days = 30)
    {
        try {
            // Clean old metrics
            $sql = "DELETE FROM performance_metrics WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
            $this->database->execute($sql, [$days]);

            // Clean old slow queries
            $sql = "DELETE FROM slow_queries_log WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
            $this->database->execute($sql, [$days]);

            // Clean old resolved alerts
            $sql = "DELETE FROM performance_alerts WHERE resolved = TRUE AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
            $this->database->execute($sql, [$days]);

            $this->logger->log("Performance data cleanup completed", 'info', 'performance');
        } catch (Exception $e) {
            $this->logger->log("Error cleaning performance data: " . $e->getMessage(), 'error', 'performance');
        }
    }

    /**
     * Generate performance report
     */
    public function generatePerformanceReport()
    {
        $report = [
            'generated_at' => date('Y-m-d H:i:s'),
            'period' => 'Last 24 Hours',
            'summary' => $this->getPerformanceSummary(),
            'alerts' => $this->getPerformanceAlertsSummary(),
            'recommendations' => $this->getPerformanceRecommendations(),
            'trends' => $this->getPerformanceTrends()
        ];

        return $report;
    }

    /**
     * Get performance summary
     */
    private function getPerformanceSummary()
    {
        $summary = [];

        // Average metrics
        $sql = "SELECT metric_type, metric_name, AVG(metric_value) as avg_value, MAX(metric_value) as max_value
                FROM performance_metrics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY metric_type, metric_name";

        $results = $this->database->fetchAll($sql);

        foreach ($results as $row) {
            $summary[$row['metric_type']][$row['metric_name']] = [
                'average' => $row['avg_value'],
                'peak' => $row['max_value']
            ];
        }

        return $summary;
    }

    /**
     * Get performance alerts summary
     */
    private function getPerformanceAlertsSummary()
    {
        $sql = "SELECT severity, COUNT(*) as count 
                FROM performance_alerts 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY severity";

        $results = $this->database->fetchAll($sql);

        $summary = [
            'total' => 0,
            'by_severity' => []
        ];

        foreach ($results as $row) {
            $summary['by_severity'][$row['severity']] = $row['count'];
            $summary['total'] += $row['count'];
        }

        return $summary;
    }

    /**
     * Get performance recommendations
     */
    private function getPerformanceRecommendations()
    {
        $recommendations = [];

        // Check for frequent slow queries
        $slowQueryCount = $this->getSlowQueriesCount();
        if ($slowQueryCount > 10) {
            $recommendations[] = [
                'type' => 'database',
                'priority' => 'high',
                'message' => "High number of slow queries detected ({$slowQueryCount}). Consider optimizing database queries or adding indexes."
            ];
        }

        // Check memory usage
        $memoryUsage = memory_get_usage(true) / 1024 / 1024;
        if ($memoryUsage > $this->memoryThreshold) {
            $recommendations[] = [
                'type' => 'memory',
                'priority' => 'medium',
                'message' => "High memory usage detected ({$memoryUsage}MB). Consider optimizing memory usage or increasing memory limit."
            ];
        }

        // Check disk usage
        $diskUsage = $this->getDiskUsage();
        if ($diskUsage['used_percent'] > 80) {
            $recommendations[] = [
                'type' => 'disk',
                'priority' => 'high',
                'message' => "High disk usage detected ({$diskUsage['used_percent']}%). Consider cleaning up old files or expanding storage."
            ];
        }

        return $recommendations;
    }
}

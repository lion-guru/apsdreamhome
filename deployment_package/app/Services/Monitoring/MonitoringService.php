<?php

namespace App\Services\Monitoring;

/**
 * System Monitoring Service for APS Dream Home
 * Monitors system health, performance, and generates reports
 */
class MonitoringService
{
    private $db;
    private $loggingService;
    private $metrics = [];

    public function __construct($db = null)
    {
        $this->db = $db;
        $this->loggingService = new LoggingService($db);
    }

    /**
     * Collect system health metrics
     */
    public function collectSystemMetrics()
    {
        $metrics = [
            'timestamp' => date('Y-m-d H:i:s'),
            'memory_usage' => $this->getMemoryUsage(),
            'cpu_usage' => $this->getCpuUsage(),
            'disk_usage' => $this->getDiskUsage(),
            'load_average' => $this->getLoadAverage(),
            'php_version' => PHP_VERSION,
            'server_uptime' => $this->getServerUptime(),
            'database_connections' => $this->getDatabaseConnections(),
            'active_sessions' => $this->getActiveSessions()
        ];

        $this->metrics = $metrics;

        // Log system health
        $this->loggingService->systemHealth($metrics);

        // Store in database if available
        if ($this->db) {
            $this->storeMetricsInDatabase($metrics);
        }

        return $metrics;
    }

    /**
     * Get memory usage information
     */
    private function getMemoryUsage()
    {
        return [
            'current' => memory_get_usage(),
            'peak' => memory_get_peak_usage(),
            'limit' => ini_get('memory_limit')
        ];
    }

    /**
     * Get CPU usage (simplified for cross-platform)
     */
    private function getCpuUsage()
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return [
                'load_1' => $load[0] ?? 0,
                'load_5' => $load[1] ?? 0,
                'load_15' => $load[2] ?? 0
            ];
        }

        return ['load_1' => 0, 'load_5' => 0, 'load_15' => 0];
    }

    /**
     * Get disk usage
     */
    private function getDiskUsage()
    {
        $diskTotal = disk_total_space('/');
        $diskFree = disk_free_space('/');

        return [
            'total' => $diskTotal,
            'free' => $diskFree,
            'used' => $diskTotal - $diskFree,
            'used_percentage' => $diskTotal > 0 ? round((($diskTotal - $diskFree) / $diskTotal) * 100, 2) : 0
        ];
    }

    /**
     * Get system load average
     */
    private function getLoadAverage()
    {
        if (function_exists('sys_getloadavg')) {
            return sys_getloadavg();
        }
        return [0, 0, 0];
    }

    /**
     * Get server uptime (simplified)
     */
    private function getServerUptime()
    {
        if (PHP_OS === 'Linux') {
            $uptime = @file_get_contents('/proc/uptime');
            if ($uptime) {
                $parts = explode(' ', trim($uptime));
                return (int) $parts[0];
            }
        }

        // Fallback: return script start time
        return time() - $_SERVER['REQUEST_TIME'];
    }

    /**
     * Get database connection count
     */
    private function getDatabaseConnections()
    {
        if (!$this->db) {
            return 0;
        }

        try {
            $stmt = $this->db->query("SHOW PROCESSLIST");
            $connections = $stmt->fetchAll();
            return count($connections);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get active user sessions (simplified)
     */
    private function getActiveSessions()
    {
        if (!$this->db) {
            return 0;
        }

        try {
            $stmt = $this->db->query("SELECT COUNT(*) as active_sessions FROM user_sessions WHERE last_activity > DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
            $result = $stmt->fetch();
            return (int) $result['active_sessions'];
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Store metrics in database
     */
    private function storeMetricsInDatabase($metrics)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO system_metrics
                (timestamp, memory_current, memory_peak, cpu_load_1, cpu_load_5, cpu_load_15,
                 disk_total, disk_used, disk_free, server_uptime, db_connections, active_sessions, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $metrics['timestamp'],
                $metrics['memory_usage']['current'],
                $metrics['memory_usage']['peak'],
                $metrics['cpu_usage']['load_1'],
                $metrics['cpu_usage']['load_5'],
                $metrics['cpu_usage']['load_15'],
                $metrics['disk_usage']['total'],
                $metrics['disk_usage']['used'],
                $metrics['disk_usage']['free'],
                $metrics['server_uptime'],
                $metrics['database_connections'],
                $metrics['active_sessions']
            ]);
        } catch (\Exception $e) {
            $this->loggingService->error('Failed to store system metrics: ' . $e->getMessage());
        }
    }

    /**
     * Monitor performance of a function
     */
    public function monitorPerformance($functionName, callable $function)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        try {
            $result = $function();

            $endTime = microtime(true);
            $endMemory = memory_get_usage();

            $duration = round(($endTime - $startTime) * 1000, 2); // ms
            $memoryUsed = $endMemory - $startMemory;

            $this->loggingService->performance($functionName, $duration, [
                'memory_used' => $memoryUsed,
                'memory_peak' => memory_get_peak_usage(),
                'result_type' => gettype($result)
            ]);

            return $result;
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2);

            $this->loggingService->error("Performance monitoring: {$functionName} failed after {$duration}ms", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            throw $e;
        }
    }

    /**
     * Generate health report
     */
    public function generateHealthReport()
    {
        $metrics = $this->collectSystemMetrics();

        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'overall_health' => $this->calculateOverallHealth($metrics),
            'metrics' => $metrics,
            'warnings' => $this->getHealthWarnings($metrics),
            'recommendations' => $this->getHealthRecommendations($metrics)
        ];

        // Log the health report
        $this->loggingService->info('Health Report Generated', [
            'overall_health' => $report['overall_health'],
            'warnings_count' => count($report['warnings']),
            'recommendations_count' => count($report['recommendations'])
        ]);

        return $report;
    }

    /**
     * Calculate overall health score
     */
    private function calculateOverallHealth($metrics)
    {
        $score = 100;

        // Memory usage penalties
        $memoryUsagePercent = ($metrics['memory_usage']['current'] / $this->parseMemoryLimit($metrics['memory_usage']['limit'])) * 100;
        if ($memoryUsagePercent > 80) $score -= 20;
        elseif ($memoryUsagePercent > 60) $score -= 10;

        // Disk usage penalties
        if ($metrics['disk_usage']['used_percentage'] > 90) $score -= 25;
        elseif ($metrics['disk_usage']['used_percentage'] > 75) $score -= 15;

        // CPU load penalties
        if ($metrics['cpu_usage']['load_1'] > 4) $score -= 20;
        elseif ($metrics['cpu_usage']['load_1'] > 2) $score -= 10;

        // Database connections penalty
        if ($metrics['database_connections'] > 50) $score -= 10;

        return max(0, min(100, $score));
    }

    /**
     * Parse memory limit string to bytes
     */
    private function parseMemoryLimit($limit)
    {
        if (is_numeric($limit)) {
            return (int) $limit;
        }

        $unit = strtolower(substr($limit, -1));
        $value = (int) substr($limit, 0, -1);

        switch ($unit) {
            case 'g': return $value * 1024 * 1024 * 1024;
            case 'm': return $value * 1024 * 1024;
            case 'k': return $value * 1024;
            default: return $value;
        }
    }

    /**
     * Get health warnings
     */
    private function getHealthWarnings($metrics)
    {
        $warnings = [];

        if ($metrics['disk_usage']['used_percentage'] > 90) {
            $warnings[] = 'Critical: Disk usage over 90%';
        }

        if ($metrics['memory_usage']['current'] > 0.8 * $this->parseMemoryLimit($metrics['memory_usage']['limit'])) {
            $warnings[] = 'Warning: Memory usage over 80%';
        }

        if ($metrics['cpu_usage']['load_1'] > 4) {
            $warnings[] = 'Warning: High CPU load (1-minute average > 4)';
        }

        if ($metrics['database_connections'] > 50) {
            $warnings[] = 'Warning: High number of database connections';
        }

        return $warnings;
    }

    /**
     * Get health recommendations
     */
    private function getHealthRecommendations($metrics)
    {
        $recommendations = [];

        if ($metrics['disk_usage']['used_percentage'] > 75) {
            $recommendations[] = 'Consider cleaning up old log files and temporary data';
        }

        if ($metrics['memory_usage']['current'] > 0.6 * $this->parseMemoryLimit($metrics['memory_usage']['limit'])) {
            $recommendations[] = 'Monitor memory-intensive operations and consider increasing PHP memory limit';
        }

        if ($metrics['cpu_usage']['load_1'] > 2) {
            $recommendations[] = 'Investigate high CPU usage - check for inefficient queries or processes';
        }

        if ($metrics['database_connections'] < 5) {
            $recommendations[] = 'Low database connections - verify database connectivity';
        }

        return $recommendations;
    }

    /**
     * Get historical metrics
     */
    public function getHistoricalMetrics($hours = 24)
    {
        if (!$this->db) {
            return [];
        }

        try {
            $stmt = $this->db->prepare("
                SELECT * FROM system_metrics
                WHERE timestamp > DATE_SUB(NOW(), INTERVAL ? HOUR)
                ORDER BY timestamp DESC
            ");
            $stmt->execute([$hours]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            $this->loggingService->error('Failed to retrieve historical metrics: ' . $e->getMessage());
            return [];
        }
    }
}

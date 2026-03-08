<?php

namespace App\Services\Alerts;

use App\Core\Database\Database;
use App\Services\LoggingService;
use App\Services\NotificationService;
use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * Alert Manager Service - APS Dream Home
 * Comprehensive alert management system with health monitoring
 * Custom MVC implementation without Laravel dependencies
 */
class AlertManagerService
{
    private $database;
    private $logger;
    private $notificationService;
    private $escalationService;
    
    // System thresholds
    private $thresholds = [
        'response_time' => 5000,      // 5 seconds
        'error_rate' => 0.05,         // 5%
        'system_load' => 0.8,         // 80%
        'memory_usage' => 0.85,       // 85%
        'disk_usage' => 0.9,          // 90%
        'cpu_usage' => 0.8            // 80%
    ];

    public function __construct($database = null, $logger = null, $notificationService = null)
    {
        $this->database = $database ?: Database::getInstance();
        $this->logger = $logger ?: LoggingService::getInstance();
        $this->notificationService = $notificationService ?: new NotificationService();
        $this->escalationService = new AlertEscalationService($database, $logger, $notificationService);
    }

    /**
     * Check system health and generate alerts if needed
     */
    public function checkSystemHealth()
    {
        $alertsCreated = 0;
        
        try {
            // Check response time
            $responseTime = $this->getAverageResponseTime();
            if ($responseTime > $this->thresholds['response_time']) {
                $this->escalationService->createAlert(
                    'High Response Time Detected',
                    "Average response time is {$responseTime}ms (threshold: {$this->thresholds['response_time']}ms)",
                    AlertEscalationService::LEVEL_WARNING,
                    'system_monitor',
                    'performance',
                    ['response_time' => $responseTime, 'threshold' => $this->thresholds['response_time']]
                );
                $alertsCreated++;
            }
            
            // Check error rate
            $errorRate = $this->getErrorRate();
            if ($errorRate > $this->thresholds['error_rate']) {
                $this->escalationService->createAlert(
                    'High Error Rate Detected',
                    "Error rate is " . ($errorRate * 100) . "% (threshold: " . ($this->thresholds['error_rate'] * 100) . "%)",
                    AlertEscalationService::LEVEL_CRITICAL,
                    'system_monitor',
                    'errors',
                    ['error_rate' => $errorRate, 'threshold' => $this->thresholds['error_rate']]
                );
                $alertsCreated++;
            }
            
            // Check system load
            $systemLoad = $this->getSystemLoad();
            if ($systemLoad > $this->thresholds['system_load']) {
                $this->escalationService->createAlert(
                    'High System Load Detected',
                    "System load is " . ($systemLoad * 100) . "% (threshold: " . ($this->thresholds['system_load'] * 100) . "%)",
                    AlertEscalationService::LEVEL_WARNING,
                    'system_monitor',
                    'performance',
                    ['system_load' => $systemLoad, 'threshold' => $this->thresholds['system_load']]
                );
                $alertsCreated++;
            }
            
            // Check memory usage
            $memoryUsage = $this->getMemoryUsage();
            if ($memoryUsage > $this->thresholds['memory_usage']) {
                $this->escalationService->createAlert(
                    'High Memory Usage Detected',
                    "Memory usage is " . ($memoryUsage * 100) . "% (threshold: " . ($this->thresholds['memory_usage'] * 100) . "%)",
                    AlertEscalationService::LEVEL_WARNING,
                    'system_monitor',
                    'performance',
                    ['memory_usage' => $memoryUsage, 'threshold' => $this->thresholds['memory_usage']]
                );
                $alertsCreated++;
            }
            
            // Check disk usage
            $diskUsage = $this->getDiskUsage();
            if ($diskUsage > $this->thresholds['disk_usage']) {
                $this->escalationService->createAlert(
                    'High Disk Usage Detected',
                    "Disk usage is " . ($diskUsage * 100) . "% (threshold: " . ($this->thresholds['disk_usage'] * 100) . "%)",
                    AlertEscalationService::LEVEL_CRITICAL,
                    'system_monitor',
                    'storage',
                    ['disk_usage' => $diskUsage, 'threshold' => $this->thresholds['disk_usage']]
                );
                $alertsCreated++;
            }
            
            // Check CPU usage
            $cpuUsage = $this->getCpuUsage();
            if ($cpuUsage > $this->thresholds['cpu_usage']) {
                $this->escalationService->createAlert(
                    'High CPU Usage Detected',
                    "CPU usage is " . ($cpuUsage * 100) . "% (threshold: " . ($this->thresholds['cpu_usage'] * 100) . "%)",
                    AlertEscalationService::LEVEL_WARNING,
                    'system_monitor',
                    'performance',
                    ['cpu_usage' => $cpuUsage, 'threshold' => $this->thresholds['cpu_usage']]
                );
                $alertsCreated++;
            }
            
            $this->logger->log("System health check completed. Created $alertsCreated alerts.", 'info', 'alerts');
            
        } catch (Exception $e) {
            $this->logger->log("Error during system health check: " . $e->getMessage(), 'error', 'alerts');
        }
        
        return $alertsCreated;
    }

    /**
     * Process automated notifications
     */
    public function processAutomatedNotifications()
    {
        $processed = 0;
        
        try {
            // Process pending escalations
            $processed += $this->escalationService->processEscalations();
            
            // Send summary notifications for critical alerts
            $criticalAlerts = $this->escalationService->getAlertsByStatus('active', 50, 0);
            $criticalCount = 0;
            
            foreach ($criticalAlerts as $alert) {
                if (in_array($alert['level'], [AlertEscalationService::LEVEL_CRITICAL, AlertEscalationService::LEVEL_EMERGENCY])) {
                    $criticalCount++;
                }
            }
            
            if ($criticalCount > 0) {
                $this->sendCriticalAlertSummary($criticalCount);
                $processed++;
            }
            
            $this->logger->log("Processed $processed automated notifications", 'info', 'alerts');
            
        } catch (Exception $e) {
            $this->logger->log("Error processing automated notifications: " . $e->getMessage(), 'error', 'alerts');
        }
        
        return $processed;
    }

    /**
     * Get comprehensive system metrics
     */
    public function getSystemMetrics()
    {
        $metrics = [];
        
        try {
            $metrics['response_time'] = $this->getAverageResponseTime();
            $metrics['error_rate'] = $this->getErrorRate();
            $metrics['system_load'] = $this->getSystemLoad();
            $metrics['memory_usage'] = $this->getMemoryUsage();
            $metrics['disk_usage'] = $this->getDiskUsage();
            $metrics['cpu_usage'] = $this->getCpuUsage();
            $metrics['active_connections'] = $this->getActiveConnections();
            $metrics['database_size'] = $this->getDatabaseSize();
            $metrics['uptime'] = $this->getSystemUptime();
            
            // Health score (0-100)
            $metrics['health_score'] = $this->calculateHealthScore($metrics);
            
        } catch (Exception $e) {
            $this->logger->log("Error getting system metrics: " . $e->getMessage(), 'error', 'alerts');
        }
        
        return $metrics;
    }

    /**
     * Get alert dashboard data
     */
    public function getDashboardData()
    {
        $data = [];
        
        try {
            // Alert statistics
            $data['alert_stats'] = $this->escalationService->getAlertStats();
            
            // Recent alerts
            $data['recent_alerts'] = $this->escalationService->getAlertsByStatus('active', 10, 0);
            
            // System metrics
            $data['system_metrics'] = $this->getSystemMetrics();
            
            // Health status
            $data['health_status'] = $this->getHealthStatus();
            
            // Pending escalations
            $data['pending_escalations'] = $this->getPendingEscalations();
            
        } catch (Exception $e) {
            $this->logger->log("Error getting dashboard data: " . $e->getMessage(), 'error', 'alerts');
        }
        
        return $data;
    }

    /**
     * Create custom alert
     */
    public function createCustomAlert($title, $description, $level, $source = null, $category = null, $metadata = [])
    {
        return $this->escalationService->createAlert($title, $description, $level, $source, $category, $metadata);
    }

    /**
     * Get average response time
     */
    private function getAverageResponseTime()
    {
        // This would typically measure actual response times
        // For now, return a simulated value
        return rand(100, 2000); // ms
    }

    /**
     * Get error rate
     */
    private function getErrorRate()
    {
        try {
            $sql = "SELECT COUNT(*) as total, SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as errors
                    FROM request_logs 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
            
            $result = $this->database->fetchOne($sql);
            
            if ($result && $result['total'] > 0) {
                return $result['errors'] / $result['total'];
            }
            
        } catch (Exception $e) {
            // If table doesn't exist, return simulated value
        }
        
        return rand(0, 10) / 100; // 0-10%
    }

    /**
     * Get system load
     */
    private function getSystemLoad()
    {
        // Get system load average (Linux)
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return $load[0] / 4; // Assuming 4 cores, convert to percentage
        }
        
        return rand(20, 80) / 100; // 20-80%
    }

    /**
     * Get memory usage
     */
    private function getMemoryUsage()
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        
        if ($memoryLimit === '-1') {
            return $memoryUsage / (1024 * 1024 * 1024); // Usage in GB
        }
        
        $limitBytes = $this->parseMemoryLimit($memoryLimit);
        return $memoryUsage / $limitBytes;
    }

    /**
     * Get disk usage
     */
    private function getDiskUsage()
    {
        $totalSpace = disk_total_space('/');
        $freeSpace = disk_free_space('/');
        
        if ($totalSpace && $freeSpace) {
            return ($totalSpace - $freeSpace) / $totalSpace;
        }
        
        return rand(50, 95) / 100; // 50-95%
    }

    /**
     * Get CPU usage
     */
    private function getCpuUsage()
    {
        // This would typically use system commands to get CPU usage
        // For now, return a simulated value
        return rand(10, 90) / 100; // 10-90%
    }

    /**
     * Get active connections
     */
    private function getActiveConnections()
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM active_sessions WHERE last_activity >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
            $result = $this->database->fetchOne($sql);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return rand(10, 100);
        }
    }

    /**
     * Get database size
     */
    private function getDatabaseSize()
    {
        try {
            $sql = "SELECT SUM(data_length + index_length) / 1024 / 1024 as size_mb
                    FROM information_schema.tables 
                    WHERE table_schema = DATABASE()";
            
            $result = $this->database->fetchOne($sql);
            return round($result['size_mb'] ?? 0, 2);
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get system uptime
     */
    private function getSystemUptime()
    {
        if (function_exists('shell_exec')) {
            $uptime = shell_exec('uptime -p 2>/dev/null');
            if ($uptime) {
                return trim($uptime);
            }
        }
        
        return 'Unknown';
    }

    /**
     * Calculate health score
     */
    private function calculateHealthScore($metrics)
    {
        $score = 100;
        
        // Deduct points for each threshold exceeded
        if ($metrics['response_time'] > $this->thresholds['response_time']) {
            $score -= 20;
        }
        
        if ($metrics['error_rate'] > $this->thresholds['error_rate']) {
            $score -= 30;
        }
        
        if ($metrics['system_load'] > $this->thresholds['system_load']) {
            $score -= 15;
        }
        
        if ($metrics['memory_usage'] > $this->thresholds['memory_usage']) {
            $score -= 15;
        }
        
        if ($metrics['disk_usage'] > $this->thresholds['disk_usage']) {
            $score -= 20;
        }
        
        if ($metrics['cpu_usage'] > $this->thresholds['cpu_usage']) {
            $score -= 15;
        }
        
        return max(0, $score);
    }

    /**
     * Get health status
     */
    private function getHealthStatus()
    {
        $metrics = $this->getSystemMetrics();
        $score = $metrics['health_score'] ?? 100;
        
        if ($score >= 90) {
            return 'excellent';
        } elseif ($score >= 70) {
            return 'good';
        } elseif ($score >= 50) {
            return 'warning';
        } else {
            return 'critical';
        }
    }

    /**
     * Get pending escalations
     */
    private function getPendingEscalations()
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM alert_escalations WHERE status = 'pending'";
            $result = $this->database->fetchOne($sql);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Send critical alert summary
     */
    private function sendCriticalAlertSummary($criticalCount)
    {
        try {
            $subject = "Critical Alert Summary - $criticalCount Active Alerts";
            $message = "There are currently $criticalCount critical or emergency alerts requiring attention.\n\n";
            $message .= "Please check the alert dashboard for details.\n";
            $message .= "Dashboard: " . BASE_URL . "/admin/dashboard\n";
            
            $this->notificationService->sendNotification([
                'type' => 'email',
                'to' => 'admin@example.com',
                'subject' => $subject,
                'message' => $message,
                'priority' => 'high'
            ]);
            
        } catch (Exception $e) {
            $this->logger->log("Error sending critical alert summary: " . $e->getMessage(), 'error', 'alerts');
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
     * Proxy methods for backward compatibility
     */
    public function processEscalations()
    {
        return $this->escalationService->processEscalations();
    }

    public function createAlert($title, $description = '', $level = AlertEscalationService::LEVEL_INFO, $source = null, $category = null, $metadata = [])
    {
        return $this->escalationService->createAlert($title, $description, $level, $source, $category, $metadata);
    }

    public function getAlertsByStatus($status = 'active', $limit = 50, $offset = 0)
    {
        return $this->escalationService->getAlertsByStatus($status, $limit, $offset);
    }

    public function acknowledgeAlert($alertId, $userId)
    {
        return $this->escalationService->acknowledgeAlert($alertId, $userId);
    }

    public function resolveAlert($alertId, $userId, $resolution = '')
    {
        return $this->escalationService->resolveAlert($alertId, $userId, $resolution);
    }

    public function getAlertStats()
    {
        return $this->escalationService->getAlertStats();
    }
}

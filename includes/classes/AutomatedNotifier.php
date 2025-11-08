<?php
/**
 * Automated Notifier
 * Handles automated notifications based on system events and conditions
 */

class AutomatedNotifier {
    private $conn;
    private $templateManager;
    private $thresholds = [
        'response_time' => 5000,    // 5 seconds
        'error_rate' => 0.05,       // 5%
        'memory_usage' => 0.85,     // 85%
        'disk_usage' => 0.90,       // 90%
        'cpu_load' => 0.80         // 80%
    ];

    public function __construct($conn) {
        $this->conn = $conn;
        $this->templateManager = new NotificationTemplate($conn);
    }

    /**
     * Process automated notifications
     */
    public function processAutomatedNotifications() {
        $this->checkSystemPerformance();
        $this->checkErrorRates();
        $this->checkStaleAlerts();
        $this->sendDailyDigest();
        $this->checkResourceUsage();
    }

    /**
     * Check system performance
     */
    private function checkSystemPerformance() {
        $query = "SELECT 
                     system,
                     AVG(response_time) as avg_response
                 FROM system_logs
                 WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                 GROUP BY system
                 HAVING avg_response > ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('d', $this->thresholds['response_time']);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $this->notifyPerformanceIssue(
                $row['system'],
                'response_time',
                $row['avg_response'],
                $this->thresholds['response_time']
            );
        }
    }

    /**
     * Check error rates
     */
    private function checkErrorRates() {
        $query = "SELECT 
                     system,
                     COUNT(*) as total,
                     SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as errors
                 FROM system_logs
                 WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                 GROUP BY system
                 HAVING (errors / total) > ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('d', $this->thresholds['error_rate']);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $error_rate = ($row['errors'] / $row['total']);
            $this->notifyPerformanceIssue(
                $row['system'],
                'error_rate',
                $error_rate * 100 . '%',
                $this->thresholds['error_rate'] * 100 . '%'
            );
        }
    }

    /**
     * Check for stale alerts
     */
    private function checkStaleAlerts() {
        $query = "SELECT *
                 FROM system_alerts
                 WHERE resolved_at IS NULL
                   AND acknowledged_at IS NULL
                   AND created_at <= DATE_SUB(NOW(), INTERVAL 4 HOUR)";
        
        $result = $this->conn->query($query);
        while ($alert = $result->fetch_assoc()) {
            $this->notifyStaleAlert($alert);
        }
    }

    /**
     * Send daily digest
     */
    private function sendDailyDigest() {
        // Only send digest at 9 AM
        $hour = date('G');
        if ($hour != 9) {
            return;
        }

        // Check if digest already sent today
        $query = "SELECT COUNT(*) as sent
                 FROM notification_logs
                 WHERE type = 'daily_digest'
                   AND DATE(created_at) = CURDATE()";
        
        if ($this->conn->query($query)->fetch_assoc()['sent'] > 0) {
            return;
        }

        // Get daily statistics
        $stats = [
            'alerts' => $this->getAlertStats(),
            'performance' => $this->getPerformanceStats(),
            'systems' => $this->getSystemStats()
        ];

        $this->sendDailyDigestNotification($stats);
    }

    /**
     * Check resource usage
     */
    private function checkResourceUsage() {
        // Check memory usage
        if (function_exists('memory_get_usage')) {
            $memory_usage = memory_get_usage(true) / memory_get_peak_usage(true);
            if ($memory_usage > $this->thresholds['memory_usage']) {
                $this->notifyResourceUsage('memory', $memory_usage * 100 . '%');
            }
        }

        // Check disk usage
        $disk_free = disk_free_space('/');
        $disk_total = disk_total_space('/');
        $disk_usage = ($disk_total - $disk_free) / $disk_total;
        
        if ($disk_usage > $this->thresholds['disk_usage']) {
            $this->notifyResourceUsage('disk', $disk_usage * 100 . '%');
        }

        // Check CPU load
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            if ($load[0] > $this->thresholds['cpu_load']) {
                $this->notifyResourceUsage('cpu', $load[0] * 100 . '%');
            }
        }
    }

    /**
     * Notify performance issue
     */
    private function notifyPerformanceIssue($system, $metric, $value, $threshold) {
        $recipients = $this->getSystemSubscribers($system, 'warning');
        
        $variables = [
            'system' => $system,
            'metric' => $metric,
            'current_value' => $value,
            'threshold' => $threshold,
            'url' => SITE_URL . '/admin/system_monitor.php'
        ];

        $this->templateManager->sendNotification('performance_alert', $variables, $recipients);
    }

    /**
     * Notify stale alert
     */
    private function notifyStaleAlert($alert) {
        $recipients = $this->getSystemSubscribers($alert['system'], $alert['level']);
        
        $variables = [
            'system' => $alert['system'],
            'title' => $alert['title'],
            'message' => $alert['message'],
            'age' => floor((time() - strtotime($alert['created_at'])) / 3600),
            'url' => SITE_URL . '/admin/system_monitor.php?alert=' . $alert['id']
        ];

        $this->templateManager->sendNotification('alert_escalated', $variables, $recipients);
    }

    /**
     * Notify resource usage
     */
    private function notifyResourceUsage($resource, $usage) {
        $recipients = $this->getSystemSubscribers('system', 'warning');
        
        $variables = [
            'system' => 'System Resources',
            'metric' => $resource . ' usage',
            'current_value' => $usage,
            'threshold' => ($this->thresholds[$resource . '_usage'] * 100) . '%',
            'url' => SITE_URL . '/admin/system_monitor.php'
        ];

        $this->templateManager->sendNotification('performance_alert', $variables, $recipients);
    }

    /**
     * Send daily digest notification
     */
    private function sendDailyDigestNotification($stats) {
        $recipients = $this->getSystemSubscribers('system', 'info');
        
        $variables = [
            'status' => $this->getOverallStatus($stats),
            'critical_count' => $stats['alerts']['critical'],
            'warning_count' => $stats['alerts']['warning'],
            'avg_response_time' => $stats['performance']['avg_response_time'] . 'ms',
            'url' => SITE_URL . '/admin/system_monitor.php'
        ];

        $this->templateManager->sendNotification('system_status', $variables, $recipients);
    }

    /**
     * Get system subscribers
     */
    private function getSystemSubscribers($system, $level) {
        $query = "SELECT u.*
                 FROM users u
                 JOIN alert_subscriptions s ON u.id = s.user_id
                 WHERE s.system = ?
                   AND s.level = ?
                   AND s.email_enabled = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ss', $system, $level);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get alert statistics
     */
    private function getAlertStats() {
        $query = "SELECT 
                     level,
                     COUNT(*) as count
                 FROM system_alerts
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                 GROUP BY level";
        
        $result = $this->conn->query($query);
        $stats = ['critical' => 0, 'warning' => 0, 'info' => 0];
        
        while ($row = $result->fetch_assoc()) {
            $stats[$row['level']] = $row['count'];
        }

        return $stats;
    }

    /**
     * Get performance statistics
     */
    private function getPerformanceStats() {
        $query = "SELECT 
                     AVG(response_time) as avg_response_time,
                     COUNT(DISTINCT system) as active_systems
                 FROM system_logs
                 WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        
        return $this->conn->query($query)->fetch_assoc();
    }

    /**
     * Get system statistics
     */
    private function getSystemStats() {
        $query = "SELECT 
                     system,
                     COUNT(*) as total_logs,
                     AVG(response_time) as avg_response,
                     SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) / COUNT(*) as error_rate
                 FROM system_logs
                 WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                 GROUP BY system";
        
        return $this->conn->query($query)->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get overall system status
     */
    private function getOverallStatus($stats) {
        if ($stats['alerts']['critical'] > 0) {
            return 'Critical';
        } elseif ($stats['alerts']['warning'] > 0) {
            return 'Warning';
        } else {
            return 'Healthy';
        }
    }
}
?>

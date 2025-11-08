<?php
/**
 * Alert Manager Class
 * Handles system alerts and notifications
 */

class AlertManager {
    private $conn;
    private $alert_levels = ['info', 'warning', 'critical'];
    private $thresholds = [
        'response_time' => 5000, // 5 seconds
        'error_rate' => 0.05,    // 5%
        'system_load' => 0.8     // 80%
    ];

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Check system health and send alerts if needed
     */
    public function checkSystemHealth() {
        $this->checkResponseTimes();
        $this->checkErrorRates();
        $this->checkSystemLoad();
        $this->checkDatabaseHealth();
        $this->checkStorageSpace();
    }

    /**
     * Check response times for all systems
     */
    private function checkResponseTimes() {
        $query = "SELECT system, AVG(response_time) as avg_response
                 FROM system_logs
                 WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                 GROUP BY system
                 HAVING avg_response > ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('d', $this->thresholds['response_time']);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $this->createAlert(
                'warning',
                "{$row['system']} system response time is high",
                "Average response time: " . round($row['avg_response']) . "ms",
                $row['system']
            );
        }
    }

    /**
     * Check error rates for all systems
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
            $error_rate = ($row['errors'] / $row['total']) * 100;
            $this->createAlert(
                'critical',
                "High error rate in {$row['system']} system",
                "Error rate: " . round($error_rate, 2) . "%",
                $row['system']
            );
        }
    }

    /**
     * Check system load
     */
    private function checkSystemLoad() {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            if ($load[0] > $this->thresholds['system_load']) {
                $this->createAlert(
                    'warning',
                    'High system load detected',
                    "Current load average: " . round($load[0], 2),
                    'system'
                );
            }
        }
    }

    /**
     * Check database health
     */
    private function checkDatabaseHealth() {
        // Check connection time
        $start = microtime(true);
        $this->conn->query("SELECT 1");
        $time = (microtime(true) - $start) * 1000;

        if ($time > 100) { // Alert if query takes more than 100ms
            $this->createAlert(
                'warning',
                'Database performance issue',
                "Connection time: {$time}ms",
                'database'
            );
        }

        // Check table sizes
        $query = "SELECT 
                     table_name,
                     ROUND(((data_length + index_length) / 1024 / 1024), 2) as size_mb
                 FROM information_schema.tables
                 WHERE table_schema = DATABASE()
                   AND (data_length + index_length) / 1024 / 1024 > 100"; // Tables larger than 100MB

        $result = $this->conn->query($query);
        while ($row = $result->fetch_assoc()) {
            $this->createAlert(
                'warning',
                "Large table detected: {$row['table_name']}",
                "Size: {$row['size_mb']}MB",
                'database'
            );
        }
    }

    /**
     * Check storage space
     */
    private function checkStorageSpace() {
        $free_space = disk_free_space('/');
        $total_space = disk_total_space('/');
        $used_percentage = (($total_space - $free_space) / $total_space) * 100;

        if ($used_percentage > 90) {
            $this->createAlert(
                'critical',
                'Low disk space',
                "Used space: " . round($used_percentage, 2) . "%",
                'storage'
            );
        }
    }

    /**
     * Create a new alert
     */
    private function createAlert($level, $title, $message, $system) {
        $query = "INSERT INTO system_alerts (level, title, message, system, created_at)
                 VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ssss', $level, $title, $message, $system);
        $stmt->execute();

        // Send email for critical alerts
        if ($level === 'critical') {
            $this->sendAlertEmail($title, $message, $system);
        }
    }

    /**
     * Send alert email
     */
    private function sendAlertEmail($title, $message, $system) {
        // Get admin emails
        $query = "SELECT email FROM users WHERE role = 'admin'";
        $result = $this->conn->query($query);
        $admin_emails = [];
        
        while ($row = $result->fetch_assoc()) {
            $admin_emails[] = $row['email'];
        }

        if (!empty($admin_emails)) {
            $subject = "[ALERT] $title";
            $body = "System Alert\n\n";
            $body .= "System: $system\n";
            $body .= "Message: $message\n\n";
            $body .= "Time: " . date('Y-m-d H:i:s') . "\n";
            $body .= "Please check the system monitor dashboard for more details:\n";
            $body .= SITE_URL . "/admin/system_monitor.php";

            $headers = [
                'From: ' . SITE_NAME . ' <' . SUPPORT_EMAIL . '>',
                'Reply-To: ' . SUPPORT_EMAIL,
                'X-Priority: 1',
                'X-MSMail-Priority: High',
                'X-Mailer: PHP/' . phpversion()
            ];

            foreach ($admin_emails as $email) {
                mail($email, $subject, $body, implode("\r\n", $headers));
            }
        }
    }

    /**
     * Get active alerts
     */
    public function getActiveAlerts() {
        $query = "SELECT * FROM system_alerts
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                   AND (acknowledged_at IS NULL OR resolved_at IS NULL)
                 ORDER BY level DESC, created_at DESC";
        
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Acknowledge an alert
     */
    public function acknowledgeAlert($alert_id, $user_id) {
        $query = "UPDATE system_alerts 
                 SET acknowledged_at = NOW(),
                     acknowledged_by = ?
                 WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $user_id, $alert_id);
        return $stmt->execute();
    }

    /**
     * Resolve an alert
     */
    public function resolveAlert($alert_id, $user_id, $resolution_notes) {
        $query = "UPDATE system_alerts 
                 SET resolved_at = NOW(),
                     resolved_by = ?,
                     resolution_notes = ?
                 WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('isi', $user_id, $resolution_notes, $alert_id);
        return $stmt->execute();
    }
}
?>

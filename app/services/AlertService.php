<?php

namespace App\Services;

use App\Core\App;
use App\Models\Model;
use Exception;

class AlertService
{
    private $db;
    private $notificationService;
    private $alert_levels = ['info', 'warning', 'critical'];
    private $thresholds = [
        'response_time' => 5000, // 5 seconds
        'error_rate' => 0.05,    // 5%
        'system_load' => 0.8     // 80%
    ];

    private $escalation_levels = [
        1 => ['timeout' => 15, 'notify' => 'assigned_user'],     // Level 1: 15 minutes, notify assigned user
        2 => ['timeout' => 30, 'notify' => 'team_lead'],         // Level 2: 30 minutes, notify team lead
        3 => ['timeout' => 60, 'notify' => 'department_head'],   // Level 3: 60 minutes, notify department head
        4 => ['timeout' => 120, 'notify' => 'all_admins']        // Level 4: 120 minutes, notify all admins
    ];

    public function __construct(NotificationService $notificationService = null)
    {
        $this->db = Model::query()->getConnection();
        $this->notificationService = $notificationService ?: new NotificationService();
    }

    /**
     * Process all automated notifications and health checks
     */
    public function processAutomatedNotifications()
    {
        $this->checkSystemHealth();
        $this->checkStaleAlerts();
        $this->sendDailyDigest();
        $this->processEscalations();
    }

    /**
     * Check system health and send alerts if needed
     */
    public function checkSystemHealth()
    {
        $this->checkResponseTimes();
        $this->checkErrorRates();
        $this->checkSystemLoad();
        $this->checkDatabaseHealth();
        $this->checkStorageSpace();
    }

    /**
     * Check for stale alerts (unresolved and unacknowledged for > 4 hours)
     */
    private function checkStaleAlerts()
    {
        $query = "SELECT *
                 FROM system_alerts
                 WHERE resolved_at IS NULL
                   AND acknowledged_at IS NULL
                   AND created_at <= DATE_SUB(NOW(), INTERVAL 4 HOUR)";

        $results = $this->db->fetchAll($query);
        foreach ($results as $alert) {
            $recipients = $this->getSystemSubscribers($alert['system'], $alert['level']);

            $variables = [
                'system' => $alert['system'],
                'title' => $alert['title'],
                'message' => $alert['message'],
                'age' => floor((time() - strtotime($alert['created_at'])) / 3600),
                'url' => (defined('SITE_URL') ? SITE_URL : '') . '/admin/system_monitor.php?alert=' . $alert['id']
            ];

            $this->notificationService->sendTemplateNotification('alert_escalated', $variables, $recipients);
        }
    }

    /**
     * Send daily digest report
     */
    public function sendDailyDigest()
    {
        // Only send digest at 9 AM
        if (date('G') != 9) {
            return;
        }

        // Check if digest already sent today
        $query = "SELECT COUNT(*) as sent
                 FROM mlm_notification_log
                 WHERE type = 'system_status'
                   AND DATE(created_at) = CURDATE()";

        $row = $this->db->fetchOne($query);
        if (($row['sent'] ?? 0) > 0) {
            return;
        }

        $stats = [
            'alerts' => $this->getAlertStats(),
            'performance' => $this->getPerformanceStats()
        ];

        $recipients = $this->getSystemSubscribers('system', 'info');

        $variables = [
            'status' => $this->getOverallStatus($stats),
            'critical_count' => $stats['alerts']['critical'] ?? 0,
            'warning_count' => $stats['alerts']['warning'] ?? 0,
            'avg_response_time' => ($stats['performance']['avg_response_time'] ?? 0) . 'ms',
            'url' => (defined('SITE_URL') ? SITE_URL : '') . '/admin/system_monitor.php'
        ];

        $this->notificationService->sendTemplateNotification('system_status', $variables, $recipients);
    }

    private function getAlertStats()
    {
        $query = "SELECT level, COUNT(*) as count
                 FROM system_alerts
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                 GROUP BY level";

        $results = $this->db->fetchAll($query);
        $stats = ['critical' => 0, 'warning' => 0, 'info' => 0];

        foreach ($results as $row) {
            $stats[$row['level']] = $row['count'];
        }

        return $stats;
    }

    private function getPerformanceStats()
    {
        $query = "SELECT AVG(response_time) as avg_response_time
                 FROM system_logs
                 WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";

        return $this->db->fetchOne($query);
    }

    private function getOverallStatus($stats)
    {
        if (($stats['alerts']['critical'] ?? 0) > 0) {
            return 'Critical';
        } elseif (($stats['alerts']['warning'] ?? 0) > 0) {
            return 'Warning';
        }
        return 'Healthy';
    }

    private function getSystemSubscribers($system, $level)
    {
        $query = "SELECT u.*
                 FROM user u
                 JOIN alert_subscriptions s ON u.uid = s.user_id
                 WHERE s.system = ?
                   AND s.level = ?
                   AND s.email_enabled = 1";

        return $this->db->fetchAll($query, [$system, $level]);
    }

    /**
     * Check response times for all systems
     */
    private function checkResponseTimes()
    {
        $query = "SELECT system, AVG(response_time) as avg_response
                 FROM system_logs
                 WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                 GROUP BY system
                 HAVING avg_response > :threshold";

        $results = $this->db->fetchAll($query, ['threshold' => $this->thresholds['response_time']]);

        foreach ($results as $row) {
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
    private function checkErrorRates()
    {
        $query = "SELECT
                     system,
                     COUNT(*) as total,
                     SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as errors
                 FROM system_logs
                 WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                 GROUP BY system
                 HAVING (errors / total) > :threshold";

        $results = $this->db->fetchAll($query, ['threshold' => $this->thresholds['error_rate']]);

        foreach ($results as $row) {
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
    private function checkSystemLoad()
    {
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
    private function checkDatabaseHealth()
    {
        // Check connection time
        $start = microtime(true);
        $this->db->query("SELECT 1");
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

        $results = $this->db->fetchAll($query);
        foreach ($results as $row) {
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
    private function checkStorageSpace()
    {
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
    public function createAlert($level, $title, $message, $system)
    {
        $query = "INSERT INTO system_alerts (level, title, message, system, created_at)
                 VALUES (?, ?, ?, ?, NOW())";

        $this->db->execute($query, [$level, $title, $message, $system]);

        // Send email for critical alerts
        if ($level === 'critical') {
            $this->sendAlertEmail($title, $message, $system);
        }
    }

    /**
     * Send alert email
     */
    private function sendAlertEmail($title, $message, $system)
    {
        $variables = [
            'level' => 'critical',
            'title' => $title,
            'message' => $message,
            'system' => $system,
            'url' => (defined('SITE_URL') ? SITE_URL : '') . "/admin/system_monitor.php"
        ];

        // Get admin recipients
        $query = "SELECT uid, uemail, uphone FROM user WHERE utype = 1";
        $recipients = $this->db->fetchAll($query);

        $this->notificationService->sendTemplateNotification('alert_created', $variables, $recipients);
    }

    /**
     * Process alert escalations
     */
    public function processEscalations()
    {
        // Get unresolved alerts
        $query = "SELECT
                     a.*,
                     TIMESTAMPDIFF(MINUTE, created_at, NOW()) as age,
                     COALESCE(MAX(e.level), 0) as current_level
                 FROM system_alerts a
                 LEFT JOIN alert_escalations e ON a.id = e.alert_id
                 WHERE a.resolved_at IS NULL
                 GROUP BY a.id
                 HAVING age >= 15"; // Only process alerts older than 15 minutes

        $results = $this->db->fetchAll($query);
        foreach ($results as $alert) {
            $this->escalateAlert($alert);
        }
    }

    /**
     * Escalate an individual alert
     */
    private function escalateAlert($alert)
    {
        $next_level = $alert['current_level'] + 1;

        // Check if we should escalate
        if ($next_level <= 4 && $alert['age'] >= $this->escalation_levels[$next_level]['timeout']) {
            // Create escalation record
            $query = "INSERT INTO alert_escalations (alert_id, level, created_at)
                     VALUES (?, ?, NOW())";

            $this->db->execute($query, [$alert['id'], $next_level]);

            // Send notifications
            $this->sendEscalationNotifications($alert, $next_level);

            // Log escalation
            $this->logEscalation($alert, $next_level);
        }
    }

    /**
     * Send notifications for an escalated alert
     */
    private function sendEscalationNotifications($alert, $level)
    {
        $notify_type = $this->escalation_levels[$level]['notify'];
        $recipients = $this->getNotificationRecipients($notify_type, $alert);

        foreach ($recipients as $recipient) {
            // Send email notification
            $this->sendEmailNotification($recipient, $alert, $level);

            // Send SMS if enabled for critical alerts
            if ($alert['level'] === 'critical' && isset($recipient['sms_enabled']) && $recipient['sms_enabled']) {
                $this->sendSmsNotification($recipient, $alert, $level);
            }
        }
    }

    /**
     * Get notification recipients based on escalation level
     */
    private function getNotificationRecipients($notify_type, $alert)
    {
        $recipients = [];

        switch ($notify_type) {
            case 'assigned_user':
                $query = "SELECT u.*
                         FROM user u
                         JOIN alert_assignments aa ON u.uid = aa.user_id
                         WHERE aa.alert_id = ?";
                $params = [$alert['id']];
                break;

            case 'team_lead':
                $query = "SELECT u.*
                         FROM user u
                         JOIN teams t ON u.uid = t.lead_id
                         JOIN alert_assignments aa ON t.id = aa.team_id
                         WHERE aa.alert_id = ?";
                $params = [$alert['id']];
                break;

            case 'department_head':
                $query = "SELECT u.*
                         FROM user u
                         JOIN departments d ON u.uid = d.head_id
                         JOIN teams t ON d.id = t.department_id
                         JOIN alert_assignments aa ON t.id = aa.team_id
                         WHERE aa.alert_id = ?";
                $params = [$alert['id']];
                break;

            case 'all_admins':
                $query = "SELECT * FROM user WHERE utype = 1";
                $params = [];
                break;
        }

        $results = $this->db->fetchAll($query, $params);
        foreach ($results as $row) {
            $recipients[] = $row;
        }

        return $recipients;
    }

    /**
     * Send email notification
     */
    private function sendEmailNotification($recipient, $alert, $level)
    {
        $variables = [
            'level' => $level,
            'system' => $alert['system'],
            'title' => $alert['title'],
            'message' => $alert['message'],
            'age' => $alert['age'],
            'url' => (defined('SITE_URL') ? SITE_URL : '') . "/admin/system_monitor.php?alert=" . $alert['id']
        ];

        $this->notificationService->sendTemplateNotification('alert_escalated', $variables, [$recipient]);
    }

    /**
     * Send SMS notification
     */
    private function sendSmsNotification($recipient, $alert, $level)
    {
        // NotificationService handles SMS logic within sendTemplateNotification or notifyUser
        // But for direct SMS call:
        $message = sprintf(
            "[%s L%d] %s: %s",
            ucfirst($alert['level']),
            $level,
            $alert['system'],
            $alert['title']
        );

        $this->notificationService->sendSms($recipient['uphone'], $message, 'alert_escalated', $recipient['uid'] ?? null);
    }

    /**
     * Log escalation event
     */
    private function logEscalation($alert, $level)
    {
        $query = "INSERT INTO system_logs (system, event, status, details, timestamp)
                 VALUES (?, 'escalation', 'info', ?, NOW())";

        $details = json_encode([
            'alert_id' => $alert['id'],
            'level' => $level,
            'age' => $alert['age']
        ]);

        $this->db->execute($query, [$alert['system'], $details]);
    }
}

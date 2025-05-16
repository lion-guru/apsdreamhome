<?php
/**
 * Alert Escalation Manager
 * Handles alert escalation rules and notifications
 */

class AlertEscalation {
    private $conn;
    private $escalation_levels = [
        1 => ['timeout' => 15, 'notify' => 'assigned_user'],     // Level 1: 15 minutes, notify assigned user
        2 => ['timeout' => 30, 'notify' => 'team_lead'],         // Level 2: 30 minutes, notify team lead
        3 => ['timeout' => 60, 'notify' => 'department_head'],   // Level 3: 60 minutes, notify department head
        4 => ['timeout' => 120, 'notify' => 'all_admins']        // Level 4: 120 minutes, notify all admins
    ];

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Process alert escalations
     */
    public function processEscalations() {
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
        
        $result = $this->conn->query($query);
        while ($alert = $result->fetch_assoc()) {
            $this->escalateAlert($alert);
        }
    }

    /**
     * Escalate an individual alert
     */
    private function escalateAlert($alert) {
        $next_level = $alert['current_level'] + 1;
        
        // Check if we should escalate
        if ($next_level <= 4 && $alert['age'] >= $this->escalation_levels[$next_level]['timeout']) {
            // Create escalation record
            $query = "INSERT INTO alert_escalations (alert_id, level, created_at)
                     VALUES (?, ?, NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('ii', $alert['id'], $next_level);
            $stmt->execute();

            // Send notifications
            $this->sendEscalationNotifications($alert, $next_level);

            // Log escalation
            $this->logEscalation($alert, $next_level);
        }
    }

    /**
     * Send notifications for an escalated alert
     */
    private function sendEscalationNotifications($alert, $level) {
        $notify_type = $this->escalation_levels[$level]['notify'];
        $recipients = $this->getNotificationRecipients($notify_type, $alert);

        foreach ($recipients as $recipient) {
            // Send email notification
            $this->sendEmailNotification($recipient, $alert, $level);

            // Send SMS if enabled for critical alerts
            if ($alert['level'] === 'critical' && $recipient['sms_enabled']) {
                $this->sendSmsNotification($recipient, $alert, $level);
            }
        }
    }

    /**
     * Get notification recipients based on escalation level
     */
    private function getNotificationRecipients($notify_type, $alert) {
        $recipients = [];

        switch ($notify_type) {
            case 'assigned_user':
                $query = "SELECT u.* 
                         FROM users u
                         JOIN alert_assignments aa ON u.id = aa.user_id
                         WHERE aa.alert_id = ?";
                $params = [$alert['id']];
                break;

            case 'team_lead':
                $query = "SELECT u.* 
                         FROM users u
                         JOIN teams t ON u.id = t.lead_id
                         JOIN alert_assignments aa ON t.id = aa.team_id
                         WHERE aa.alert_id = ?";
                $params = [$alert['id']];
                break;

            case 'department_head':
                $query = "SELECT u.* 
                         FROM users u
                         JOIN departments d ON u.id = d.head_id
                         JOIN teams t ON d.id = t.department_id
                         JOIN alert_assignments aa ON t.id = aa.team_id
                         WHERE aa.alert_id = ?";
                $params = [$alert['id']];
                break;

            case 'all_admins':
                $query = "SELECT * FROM users WHERE role = 'admin'";
                $params = [];
                break;
        }

        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param(str_repeat('i', count($params)), ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $recipients[] = $row;
        }

        return $recipients;
    }

    /**
     * Send email notification
     */
    private function sendEmailNotification($recipient, $alert, $level) {
        $subject = sprintf(
            "[ESCALATED-%d] %s Alert: %s",
            $level,
            ucfirst($alert['level']),
            $alert['title']
        );

        $body = "Alert Escalation Notification\n\n";
        $body .= "Alert Details:\n";
        $body .= "System: {$alert['system']}\n";
        $body .= "Message: {$alert['message']}\n";
        $body .= "Created: {$alert['created_at']}\n";
        $body .= "Age: {$alert['age']} minutes\n\n";
        $body .= "This alert has been escalated to Level {$level}.\n";
        $body .= "Please take immediate action.\n\n";
        $body .= "View Alert: " . SITE_URL . "/admin/system_monitor.php?alert=" . $alert['id'];

        $headers = [
            'From: ' . SITE_NAME . ' <' . SUPPORT_EMAIL . '>',
            'Reply-To: ' . SUPPORT_EMAIL,
            'X-Priority: 1',
            'X-MSMail-Priority: High',
            'X-Mailer: PHP/' . phpversion()
        ];

        mail($recipient['email'], $subject, $body, implode("\r\n", $headers));
    }

    /**
     * Send SMS notification
     */
    private function sendSmsNotification($recipient, $alert, $level) {
        if (!empty($recipient['phone']) && defined('SMS_API_KEY')) {
            $message = sprintf(
                "[%s L%d] %s: %s",
                ucfirst($alert['level']),
                $level,
                $alert['system'],
                $alert['title']
            );

            // Implement SMS sending logic here using your preferred SMS gateway
            // This is a placeholder for SMS implementation
            error_log("SMS would be sent to {$recipient['phone']}: $message");
        }
    }

    /**
     * Log escalation event
     */
    private function logEscalation($alert, $level) {
        $query = "INSERT INTO system_logs (system, event, status, details, timestamp)
                 VALUES (?, 'escalation', 'info', ?, NOW())";
        
        $details = json_encode([
            'alert_id' => $alert['id'],
            'level' => $level,
            'age' => $alert['age']
        ]);

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ss', $alert['system'], $details);
        $stmt->execute();
    }
}
?>

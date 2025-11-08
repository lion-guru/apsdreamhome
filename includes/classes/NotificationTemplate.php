<?php
/**
 * Notification Template Manager
 * Handles notification templates and automated notifications
 */

class NotificationTemplate {
    private $conn;
    private $defaultTemplates = [
        'alert_created' => [
            'email_subject' => '[{level}] New Alert: {title}',
            'email_body' => "A new {level} alert has been created.\n\nSystem: {system}\nTitle: {title}\nMessage: {message}\n\nView Details: {url}",
            'sms_body' => "[{level}] {system}: {title}"
        ],
        'alert_escalated' => [
            'email_subject' => '[ESCALATED-{level}] Alert: {title}',
            'email_body' => "Alert has been escalated to Level {level}.\n\nSystem: {system}\nTitle: {title}\nMessage: {message}\nAge: {age} minutes\n\nImmediate action required.\n\nView Details: {url}",
            'sms_body' => "[ESC-L{level}] {system}: {title} requires immediate attention"
        ],
        'alert_resolved' => [
            'email_subject' => '[RESOLVED] Alert: {title}',
            'email_body' => "Alert has been resolved.\n\nSystem: {system}\nTitle: {title}\nResolution Time: {resolution_time}\nResolved By: {resolved_by}\n\nNotes: {resolution_notes}\n\nView Details: {url}",
            'sms_body' => "[RESOLVED] {system}: {title}"
        ],
        'system_status' => [
            'email_subject' => 'System Status Report: {status}',
            'email_body' => "System Status Report\n\nOverall Status: {status}\nCritical Alerts: {critical_count}\nWarning Alerts: {warning_count}\nAvg Response Time: {avg_response_time}\n\nDetails: {url}",
            'sms_body' => "Status: {status}, Critical: {critical_count}, Warning: {warning_count}"
        ],
        'performance_alert' => [
            'email_subject' => 'Performance Alert: {system}',
            'email_body' => "Performance issues detected.\n\nSystem: {system}\nMetric: {metric}\nCurrent Value: {current_value}\nThreshold: {threshold}\n\nView Details: {url}",
            'sms_body' => "Performance Alert: {system} {metric} at {current_value}"
        ]
    ];

    public function __construct($conn) {
        $this->conn = $conn;
        $this->initializeTemplates();
    }

    /**
     * Initialize default templates
     */
    private function initializeTemplates() {
        $query = "INSERT IGNORE INTO notification_templates 
                 (type, email_subject, email_body, sms_body, created_at)
                 VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($this->defaultTemplates as $type => $templates) {
            $stmt->bind_param('ssss', 
                $type,
                $templates['email_subject'],
                $templates['email_body'],
                $templates['sms_body']
            );
            $stmt->execute();
        }
    }

    /**
     * Get template by type
     */
    public function getTemplate($type) {
        $query = "SELECT * FROM notification_templates WHERE type = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $type);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Parse template with variables
     */
    public function parseTemplate($template, $variables) {
        foreach ($variables as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        return $template;
    }

    /**
     * Send notification using template
     */
    public function sendNotification($type, $variables, $recipients) {
        $template = $this->getTemplate($type);
        if (!$template) {
            throw new Exception("Template not found: $type");
        }

        // Parse templates
        $email_subject = $this->parseTemplate($template['email_subject'], $variables);
        $email_body = $this->parseTemplate($template['email_body'], $variables);
        $sms_body = $this->parseTemplate($template['sms_body'], $variables);

        $success = true;
        $errors = [];

        foreach ($recipients as $recipient) {
            try {
                // Send email
                $headers = [
                    'From: ' . SITE_NAME . ' <' . SUPPORT_EMAIL . '>',
                    'Reply-To: ' . SUPPORT_EMAIL,
                    'X-Mailer: PHP/' . phpversion()
                ];

                if (!mail($recipient['email'], $email_subject, $email_body, implode("\r\n", $headers))) {
                    throw new Exception("Failed to send email to {$recipient['email']}");
                }

                // Send SMS if enabled
                if ($recipient['sms_enabled'] && !empty($recipient['phone'])) {
                    $smsNotifier = new SmsNotifier();
                    if (!$smsNotifier->send($recipient['phone'], $sms_body)) {
                        throw new Exception("Failed to send SMS to {$recipient['phone']}");
                    }
                }

                // Log successful notification
                $this->logNotification($type, $recipient['id'], 'success');

            } catch (Exception $e) {
                $success = false;
                $errors[] = $e->getMessage();
                $this->logNotification($type, $recipient['id'], 'error', $e->getMessage());
            }
        }

        if (!$success) {
            throw new Exception("Some notifications failed: " . implode(", ", $errors));
        }

        return true;
    }

    /**
     * Log notification
     */
    private function logNotification($type, $user_id, $status, $error = null) {
        $query = "INSERT INTO notification_logs 
                 (type, user_id, status, error_message, created_at)
                 VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ssss', $type, $user_id, $status, $error);
        $stmt->execute();
    }

    /**
     * Get notification statistics
     */
    public function getStats($timeframe = '24h') {
        switch ($timeframe) {
            case '7d':
                $interval = '7 DAY';
                break;
            case '30d':
                $interval = '30 DAY';
                break;
            default:
                $interval = '24 HOUR';
        }

        $query = "SELECT 
                     type,
                     COUNT(*) as total,
                     SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful,
                     SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as failed
                 FROM notification_logs
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)
                 GROUP BY type";

        return $this->conn->query($query)->fetch_all(MYSQLI_ASSOC);
    }
}
?>

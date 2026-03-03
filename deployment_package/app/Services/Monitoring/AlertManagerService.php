<?php
namespace App\Services\Monitoring;

use App\Services\Notification\NotificationService;

class AlertManagerService
{
    private $notificationService;
    private $cache;
    private $config;
    private $alertRules = [];
    
    public function __construct()
    {
        $this->notificationService = new NotificationService();
        $this->cache = new \App\Services\Cache\RedisCacheService();
        $this->config = [
            'check_interval' => 60, // 1 minute
            'alert_cooldown' => 300, // 5 minutes
            'max_alerts_per_hour' => 100
        ];
        
        $this->initializeAlertRules();
    }
    
    /**
     * Initialize alert rules
     */
    private function initializeAlertRules()
    {
        $this->alertRules = [
            // System alerts
            'high_cpu_usage' => [
                'name' => 'High CPU Usage',
                'condition' => 'cpu_usage > 80',
                'severity' => 'warning',
                'cooldown' => 300,
                'enabled' => true,
                'channels' => ['email', 'slack']
            ],
            'critical_cpu_usage' => [
                'name' => 'Critical CPU Usage',
                'condition' => 'cpu_usage > 95',
                'severity' => 'critical',
                'cooldown' => 180,
                'enabled' => true,
                'channels' => ['email', 'slack', 'sms']
            ],
            'high_memory_usage' => [
                'name' => 'High Memory Usage',
                'condition' => 'memory_usage > 85',
                'severity' => 'warning',
                'cooldown' => 300,
                'enabled' => true,
                'channels' => ['email', 'slack']
            ],
            'critical_memory_usage' => [
                'name' => 'Critical Memory Usage',
                'condition' => 'memory_usage > 95',
                'severity' => 'critical',
                'cooldown' => 180,
                'enabled' => true,
                'channels' => ['email', 'slack', 'sms']
            ],
            'high_disk_usage' => [
                'name' => 'High Disk Usage',
                'condition' => 'disk_usage > 90',
                'severity' => 'warning',
                'cooldown' => 600,
                'enabled' => true,
                'channels' => ['email', 'slack']
            ],
            
            // Application alerts
            'high_response_time' => [
                'name' => 'High Response Time',
                'condition' => 'response_time > 1000',
                'severity' => 'warning',
                'cooldown' => 300,
                'enabled' => true,
                'channels' => ['email', 'slack']
            ],
            'critical_response_time' => [
                'name' => 'Critical Response Time',
                'condition' => 'response_time > 3000',
                'severity' => 'critical',
                'cooldown' => 180,
                'enabled' => true,
                'channels' => ['email', 'slack', 'sms']
            ],
            'high_error_rate' => [
                'name' => 'High Error Rate',
                'condition' => 'error_rate > 5',
                'severity' => 'warning',
                'cooldown' => 300,
                'enabled' => true,
                'channels' => ['email', 'slack']
            ],
            'critical_error_rate' => [
                'name' => 'Critical Error Rate',
                'condition' => 'error_rate > 10',
                'severity' => 'critical',
                'cooldown' => 180,
                'enabled' => true,
                'channels' => ['email', 'slack', 'sms']
            ],
            'service_down' => [
                'name' => 'Service Down',
                'condition' => 'service_status == "down"',
                'severity' => 'critical',
                'cooldown' => 60,
                'enabled' => true,
                'channels' => ['email', 'slack', 'sms', 'phone']
            ],
            
            // Business alerts
            'low_user_activity' => [
                'name' => 'Low User Activity',
                'condition' => 'active_users < 10',
                'severity' => 'info',
                'cooldown' => 1800,
                'enabled' => true,
                'channels' => ['email']
            ],
            'no_new_users' => [
                'name' => 'No New Users Today',
                'condition' => 'new_users_today == 0',
                'severity' => 'warning',
                'cooldown' => 3600,
                'enabled' => true,
                'channels' => ['email']
            ],
            'high_conversion_rate' => [
                'name' => 'High Conversion Rate',
                'condition' => 'conversion_rate > 20',
                'severity' => 'info',
                'cooldown' => 3600,
                'enabled' => true,
                'channels' => ['email', 'slack']
            ]
        ];
    }
    
    /**
     * Check all alert rules
     */
    public function checkAlertRules()
    {
        $metrics = $this->getCurrentMetrics();
        $alerts = [];
        
        foreach ($this->alertRules as $ruleId => $rule) {
            if (!$rule['enabled']) {
                continue;
            }
            
            if ($this->shouldCheckAlert($ruleId, $rule)) {
                $alert = $this->evaluateAlertRule($ruleId, $rule, $metrics);
                
                if ($alert) {
                    $alerts[] = $alert;
                    $this->triggerAlert($alert);
                }
            }
        }
        
        return $alerts;
    }
    
    /**
     * Check if alert should be evaluated
     */
    private function shouldCheckAlert($ruleId, $rule)
    {
        $cooldownKey = "alert_cooldown:{$ruleId}";
        $lastTriggered = $this->cache->get($cooldownKey);
        
        if ($lastTriggered && (time() - $lastTriggered) < $rule['cooldown']) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Evaluate alert rule
     */
    private function evaluateAlertRule($ruleId, $rule, $metrics)
    {
        $condition = $rule['condition'];
        
        // Replace variables in condition with actual values
        $condition = $this->replaceVariables($condition, $metrics);
        
        // Evaluate condition safely
        if ($this->evaluateCondition($condition)) {
            return [
                'id' => uniqid('alert_'),
                'rule_id' => $ruleId,
                'name' => $rule['name'],
                'severity' => $rule['severity'],
                'condition' => $rule['condition'],
                'evaluated_condition' => $condition,
                'metrics' => $metrics,
                'channels' => $rule['channels'],
                'created_at' => date('Y-m-d H:i:s'),
                'timestamp' => time()
            ];
        }
        
        return null;
    }
    
    /**
     * Replace variables in condition
     */
    private function replaceVariables($condition, $metrics)
    {
        $variables = [
            'cpu_usage' => $metrics['system']['cpu_usage'] ?? 0,
            'memory_usage' => $metrics['application']['memory_usage'] ?? 0,
            'disk_usage' => $metrics['system']['disk_usage'] ?? 0,
            'response_time' => $metrics['application']['response_time'] ?? 0,
            'error_rate' => $metrics['application']['error_rate'] ?? 0,
            'active_users' => $metrics['application']['active_users'] ?? 0,
            'service_status' => $metrics['system']['service_status'] ?? 'up',
            'new_users_today' => $metrics['business']['new_users'] ?? 0,
            'conversion_rate' => $metrics['business']['conversion_rate'] ?? 0
        ];
        
        foreach ($variables as $key => $value) {
            $condition = str_replace($key, $value, $condition);
        }
        
        return $condition;
    }
    
    /**
     * Evaluate condition safely
     */
    private function evaluateCondition($condition)
    {
        // Only allow safe operators
        $allowedOperators = ['>', '<', '>=', '<=', '==', '!=', '&&', '||'];
        
        // Check for any unsafe code
        $unsafePatterns = [
            '/eval/',
            '/exec/',
            '/system/',
            '/shell_exec/',
            '/passthru/',
            '/`.*`/',
            '/\$\{.*\}/'
        ];
        
        foreach ($unsafePatterns as $pattern) {
            if (preg_match($pattern, $condition)) {
                return false;
            }
        }
        
        try {
            // Use eval for simple arithmetic comparisons
            return eval("return {$condition};");
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Trigger alert
     */
    private function triggerAlert($alert)
    {
        // Store alert
        $this->storeAlert($alert);
        
        // Set cooldown
        $cooldownKey = "alert_cooldown:{$alert['rule_id']}";
        $this->cache->set($cooldownKey, time(), $this->alertRules[$alert['rule_id']]['cooldown']);
        
        // Send notifications
        foreach ($alert['channels'] as $channel) {
            $this->sendNotification($alert, $channel);
        }
        
        // Log alert
        $this->logAlert($alert);
    }
    
    /**
     * Store alert
     */
    private function storeAlert($alert)
    {
        $key = "alerts:recent";
        $recentAlerts = $this->cache->get($key) ?? [];
        
        array_unshift($recentAlerts, $alert);
        
        // Keep only last 100 alerts
        $recentAlerts = array_slice($recentAlerts, 0, 100);
        
        $this->cache->set($key, $recentAlerts, 3600);
    }
    
    /**
     * Send notification
     */
    private function sendNotification($alert, $channel)
    {
        $message = $this->formatAlertMessage($alert);
        
        switch ($channel) {
            case 'email':
                $this->sendEmailNotification($alert, $message);
                break;
            case 'slack':
                $this->sendSlackNotification($alert, $message);
                break;
            case 'sms':
                $this->sendSMSNotification($alert, $message);
                break;
            case 'phone':
                $this->sendPhoneNotification($alert, $message);
                break;
        }
    }
    
    /**
     * Format alert message
     */
    private function formatAlertMessage($alert)
    {
        $severityEmoji = [
            'info' => 'ℹ️',
            'warning' => '⚠️',
            'critical' => '🚨'
        ];
        
        $emoji = $severityEmoji[$alert['severity']] ?? '📊';
        
        $message = "{$emoji} **{$alert['name']}**\n\n";
        $message .= "Severity: {$alert['severity']}\n";
        $message .= "Condition: {$alert['condition']}\n";
        $message .= "Evaluated: {$alert['evaluated_condition']}\n";
        $message .= "Time: {$alert['created_at']}\n\n";
        
        // Add relevant metrics
        if (isset($alert['metrics']['system'])) {
            $message .= "System Metrics:\n";
            $message .= "- CPU: {$alert['metrics']['system']['cpu_usage']}%\n";
            $message .= "- Memory: {$alert['metrics']['system']['memory_usage']}%\n";
            $message .= "- Disk: {$alert['metrics']['system']['disk_usage']}%\n";
        }
        
        if (isset($alert['metrics']['application'])) {
            $message .= "Application Metrics:\n";
            $message .= "- Response Time: {$alert['metrics']['application']['response_time']}ms\n";
            $message .= "- Error Rate: {$alert['metrics']['application']['error_rate']}%\n";
            $message .= "- Active Users: {$alert['metrics']['application']['active_users']}\n";
        }
        
        return $message;
    }
    
    /**
     * Send email notification
     */
    private function sendEmailNotification($alert, $message)
    {
        $recipients = $this->getAlertRecipients($alert['severity'], 'email');
        
        foreach ($recipients as $recipient) {
            $this->notificationService->sendEmail([
                'to' => $recipient,
                'subject' => "[ALERT] {$alert['name']} - {$alert['severity']}",
                'body' => $message,
                'type' => 'alert'
            ]);
        }
    }
    
    /**
     * Send Slack notification
     */
    private function sendSlackNotification($alert, $message)
    {
        $webhookUrl = config('notifications.slack.webhook_url');
        
        if ($webhookUrl) {
            $payload = [
                'text' => "[ALERT] {$alert['name']}",
                'attachments' => [
                    [
                        'color' => $this->getSeverityColor($alert['severity']),
                        'text' => $message,
                        'timestamp' => $alert['timestamp']
                    ]
                ]
            ];
            
            $this->notificationService->sendSlack($webhookUrl, $payload);
        }
    }
    
    /**
     * Send SMS notification
     */
    private function sendSMSNotification($alert, $message)
    {
        $recipients = $this->getAlertRecipients($alert['severity'], 'sms');
        
        foreach ($recipients as $recipient) {
            $this->notificationService->sendSMS([
                'to' => $recipient,
                'message' => $message
            ]);
        }
    }
    
    /**
     * Send phone notification
     */
    private function sendPhoneNotification($alert, $message)
    {
        $recipients = $this->getAlertRecipients($alert['severity'], 'phone');
        
        foreach ($recipients as $recipient) {
            $this->notificationService->makePhoneCall([
                'to' => $recipient,
                'message' => $alert['name'] . '. ' . $alert['severity']
            ]);
        }
    }
    
    /**
     * Get alert recipients
     */
    private function getAlertRecipients($severity, $channel)
    {
        $configKey = "alerts.recipients.{$severity}.{$channel}";
        $recipients = config($configKey, []);
        
        return $recipients;
    }
    
    /**
     * Get severity color
     */
    private function getSeverityColor($severity)
    {
        $colors = [
            'info' => '#36a64f',
            'warning' => '#ff9500',
            'critical' => '#ff0000'
        ];
        
        return $colors[$severity] ?? '#808080';
    }
    
    /**
     * Log alert
     */
    private function logAlert($alert)
    {
        $logData = [
            'alert_id' => $alert['id'],
            'rule_id' => $alert['rule_id'],
            'name' => $alert['name'],
            'severity' => $alert['severity'],
            'condition' => $alert['condition'],
            'created_at' => $alert['created_at']
        ];
        
        file_put_contents(
            BASE_PATH . '/logs/alerts.log',
            json_encode($logData) . PHP_EOL,
            FILE_APPEND
        );
    }
    
    /**
     * Get current metrics
     */
    private function getCurrentMetrics()
    {
        $metricsCollector = new MetricsCollectorService();
        return $metricsCollector->getCurrentMetricsSummary();
    }
    
    /**
     * Get recent alerts
     */
    public function getRecentAlerts($limit = 50)
    {
        $key = "alerts:recent";
        $alerts = $this->cache->get($key) ?? [];
        
        return array_slice($alerts, 0, $limit);
    }
    
    /**
     * Get alert statistics
     */
    public function getAlertStatistics($timeRange = 3600)
    {
        $alerts = $this->getRecentAlerts(1000);
        
        $cutoffTime = time() - $timeRange;
        $filteredAlerts = array_filter($alerts, function($alert) use ($cutoffTime) {
            return $alert['timestamp'] >= $cutoffTime;
        });
        
        $stats = [
            'total' => count($filteredAlerts),
            'by_severity' => [],
            'by_rule' => [],
            'by_channel' => []
        ];
        
        foreach ($filteredAlerts as $alert) {
            // Count by severity
            $severity = $alert['severity'];
            $stats['by_severity'][$severity] = ($stats['by_severity'][$severity] ?? 0) + 1;
            
            // Count by rule
            $ruleId = $alert['rule_id'];
            $stats['by_rule'][$ruleId] = ($stats['by_rule'][$ruleId] ?? 0) + 1;
            
            // Count by channel
            foreach ($alert['channels'] as $channel) {
                $stats['by_channel'][$channel] = ($stats['by_channel'][$channel] ?? 0) + 1;
            }
        }
        
        return $stats;
    }
    
    /**
     * Acknowledge alert
     */
    public function acknowledgeAlert($alertId, $userId, $notes = '')
    {
        $key = "alert_acknowledgments:{$alertId}";
        $acknowledgment = [
            'alert_id' => $alertId,
            'user_id' => $userId,
            'notes' => $notes,
            'acknowledged_at' => date('Y-m-d H:i:s'),
            'timestamp' => time()
        ];
        
        $this->cache->set($key, $acknowledgment, 86400); // 24 hours
        
        return true;
    }
    
    /**
     * Get alert acknowledgment
     */
    public function getAlertAcknowledgment($alertId)
    {
        $key = "alert_acknowledgments:{$alertId}";
        return $this->cache->get($key);
    }
    
    /**
     * Enable/disable alert rule
     */
    public function toggleAlertRule($ruleId, $enabled)
    {
        if (isset($this->alertRules[$ruleId])) {
            $this->alertRules[$ruleId]['enabled'] = $enabled;
            return true;
        }
        
        return false;
    }
    
    /**
     * Get all alert rules
     */
    public function getAlertRules()
    {
        return $this->alertRules;
    }
    
    /**
     * Update alert rule
     */
    public function updateAlertRule($ruleId, $updates)
    {
        if (isset($this->alertRules[$ruleId])) {
            $this->alertRules[$ruleId] = array_merge($this->alertRules[$ruleId], $updates);
            return true;
        }
        
        return false;
    }
}

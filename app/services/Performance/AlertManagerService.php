<?php
namespace App\Services\Performance;

use App\Services\Notification\NotificationService;

class AlertManagerService
{
    private $notificationService;
    private $cache;
    private $config;
    
    public function __construct()
    {
        $this->notificationService = new NotificationService();
        $this->cache = new \App\Services\Cache\RedisCacheService();
        $this->config = [
            'thresholds' => [
                'response_time' => ['warning' => 1000, 'critical' => 2000],
                'error_rate' => ['warning' => 5, 'critical' => 10],
                'cpu_usage' => ['warning' => 80, 'critical' => 90],
                'memory_usage' => ['warning' => 85, 'critical' => 95],
                'disk_usage' => ['warning' => 90, 'critical' => 95],
                'database_connections' => ['warning' => 80, 'critical' => 100],
                'cache_hit_rate' => ['warning' => 70, 'critical' => 50],
                'queue_size' => ['warning' => 1000, 'critical' => 5000]
            ],
            'cooldown_period' => 300, // 5 minutes
            'escalation_rules' => [
                'critical' => ['email', 'sms', 'slack', 'phone'],
                'warning' => ['email', 'slack'],
                'info' => ['slack']
            ]
        ];
    }
    
    /**
     * Check and trigger alerts
     */
    public function checkAlerts($metrics)
    {
        $alerts = [];
        
        foreach ($this->config['thresholds'] as $metric => $thresholds) {
            if (isset($metrics[$metric])) {
                $value = $metrics[$metric];
                $severity = $this->getSeverity($metric, $value);
                
                if ($severity) {
                    $alert = [
                        'metric' => $metric,
                        'value' => $value,
                        'severity' => $severity,
                        'threshold' => $thresholds[$severity],
                        'timestamp' => time(),
                        'message' => $this->generateAlertMessage($metric, $value, $severity, $thresholds[$severity])
                    ];
                    
                    if ($this->shouldTriggerAlert($alert)) {
                        $alerts[] = $alert;
                        $this->triggerAlert($alert);
                    }
                }
            }
        }
        
        return $alerts;
    }
    
    /**
     * Get alert severity
     */
    private function getSeverity($metric, $value)
    {
        $thresholds = $this->config['thresholds'][$metric];
        
        if ($value >= $thresholds['critical']) {
            return 'critical';
        } elseif ($value >= $thresholds['warning']) {
            return 'warning';
        }
        
        return null;
    }
    
    /**
     * Generate alert message
     */
    private function generateAlertMessage($metric, $value, $severity, $threshold)
    {
        $metricNames = [
            'response_time' => 'Response Time',
            'error_rate' => 'Error Rate',
            'cpu_usage' => 'CPU Usage',
            'memory_usage' => 'Memory Usage',
            'disk_usage' => 'Disk Usage',
            'database_connections' => 'Database Connections',
            'cache_hit_rate' => 'Cache Hit Rate',
            'queue_size' => 'Queue Size'
        ];
        
        $metricName = $metricNames[$metric] ?? $metric;
        $unit = $this->getMetricUnit($metric);
        
        return "{$metricName} ({$value}{$unit}) is above {$severity} threshold ({$threshold}{$unit})";
    }
    
    /**
     * Get metric unit
     */
    private function getMetricUnit($metric)
    {
        $units = [
            'response_time' => 'ms',
            'error_rate' => '%',
            'cpu_usage' => '%',
            'memory_usage' => '%',
            'disk_usage' => '%',
            'database_connections' => '',
            'cache_hit_rate' => '%',
            'queue_size' => ''
        ];
        
        return $units[$metric] ?? '';
    }
    
    /**
     * Check if alert should be triggered
     */
    private function shouldTriggerAlert($alert)
    {
        $key = "alert:cooldown:{$alert['metric']}:{$alert['severity']}";
        $lastTriggered = $this->cache->get($key);
        
        if ($lastTriggered && (time() - $lastTriggered) < $this->config['cooldown_period']) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Trigger alert
     */
    private function triggerAlert($alert)
    {
        // Set cooldown
        $key = "alert:cooldown:{$alert['metric']}:{$alert['severity']}";
        $this->cache->set($key, time(), $this->config['cooldown_period']);
        
        // Store alert
        $this->storeAlert($alert);
        
        // Send notifications
        $this->sendNotifications($alert);
        
        // Log alert
        $this->logAlert($alert);
    }
    
    /**
     * Store alert
     */
    private function storeAlert($alert)
    {
        $alertData = [
            'id' => uniqid('alert_'),
            'metric' => $alert['metric'],
            'value' => $alert['value'],
            'severity' => $alert['severity'],
            'threshold' => $alert['threshold'],
            'message' => $alert['message'],
            'timestamp' => $alert['timestamp'],
            'created_at' => date('Y-m-d H:i:s', $alert['timestamp'])
        ];
        
        $this->cache->lpush('alerts:recent', json_encode($alertData));
        $this->cache->ltrim('alerts:recent', 0, 99); // Keep last 100 alerts
        
        // Store in database for long-term storage
        $this->storeAlertInDatabase($alertData);
    }
    
    /**
     * Store alert in database
     */
    private function storeAlertInDatabase($alert)
    {
        $sql = "INSERT INTO performance_alerts (metric, value, severity, threshold, message, created_at) VALUES (?, ?, ?, ?, ?, ?)";
        
        try {
            $this->cache->getDatabase()->prepare($sql)->execute([
                $alert['metric'],
                $alert['value'],
                $alert['severity'],
                $alert['threshold'],
                $alert['message'],
                $alert['created_at']
            ]);
        } catch (Exception $e) {
            error_log("Failed to store alert in database: " . $e->getMessage());
        }
    }
    
    /**
     * Send notifications
     */
    private function sendNotifications($alert)
    {
        $channels = $this->config['escalation_rules'][$alert['severity']] ?? ['slack'];
        
        foreach ($channels as $channel) {
            try {
                switch ($channel) {
                    case 'email':
                        $this->sendEmailNotification($alert);
                        break;
                    case 'sms':
                        $this->sendSMSNotification($alert);
                        break;
                    case 'slack':
                        $this->sendSlackNotification($alert);
                        break;
                    case 'phone':
                        $this->sendPhoneNotification($alert);
                        break;
                }
            } catch (Exception $e) {
                error_log("Failed to send {$channel} notification: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Send email notification
     */
    private function sendEmailNotification($alert)
    {
        $subject = "[{$alert['severity']}] Performance Alert: {$alert['metric']}";
        $message = $this->formatEmailMessage($alert);
        
        $this->notificationService->sendEmail([
            'to' => config('alerts.email.recipients', []),
            'subject' => $subject,
            'body' => $message,
            'type' => 'alert'
        ]);
    }
    
    /**
     * Send SMS notification
     */
    private function sendSMSNotification($alert)
    {
        $message = $this->formatSMSMessage($alert);
        
        $this->notificationService->sendSMS([
            'to' => config('alerts.sms.recipients', []),
            'message' => $message
        ]);
    }
    
    /**
     * Send Slack notification
     */
    private function sendSlackNotification($alert)
    {
        $webhookUrl = config('alerts.slack.webhook_url');
        
        if ($webhookUrl) {
            $payload = [
                'text' => "Performance Alert: {$alert['metric']}",
                'attachments' => [
                    [
                        'color' => $this->getSeverityColor($alert['severity']),
                        'fields' => [
                            [
                                'title' => 'Metric',
                                'value' => $alert['metric'],
                                'short' => true
                            ],
                            [
                                'title' => 'Value',
                                'value' => $alert['value'],
                                'short' => true
                            ],
                            [
                                'title' => 'Severity',
                                'value' => ucfirst($alert['severity']),
                                'short' => true
                            ],
                            [
                                'title' => 'Threshold',
                                'value' => $alert['threshold'],
                                'short' => true
                            ]
                        ],
                        'text' => $alert['message'],
                        'timestamp' => $alert['timestamp']
                    ]
                ]
            ];
            
            $this->notificationService->sendSlack($webhookUrl, $payload);
        }
    }
    
    /**
     * Send phone notification
     */
    private function sendPhoneNotification($alert)
    {
        $message = "Critical performance alert: {$alert['metric']} is {$alert['value']}";
        
        $this->notificationService->makePhoneCall([
            'to' => config('alerts.phone.recipients', []),
            'message' => $message
        ]);
    }
    
    /**
     * Format email message
     */
    private function formatEmailMessage($alert)
    {
        $severity = ucfirst($alert['severity']);
        $timestamp = date('Y-m-d H:i:s', $alert['timestamp']);
        
        return "
            <h2>Performance Alert</h2>
            <p><strong>Severity:</strong> {$severity}</p>
            <p><strong>Metric:</strong> {$alert['metric']}</p>
            <p><strong>Value:</strong> {$alert['value']}</p>
            <p><strong>Threshold:</strong> {$alert['threshold']}</p>
            <p><strong>Message:</strong> {$alert['message']}</p>
            <p><strong>Time:</strong> {$timestamp}</p>
            
            <p>Please check the performance dashboard for more details.</p>
        ";
    }
    
    /**
     * Format SMS message
     */
    private function formatSMSMessage($alert)
    {
        $severity = strtoupper($alert['severity']);
        return "[{$severity}] {$alert['message']} at " . date('H:i', $alert['timestamp']);
    }
    
    /**
     * Get severity color
     */
    private function getSeverityColor($severity)
    {
        $colors = [
            'critical' => 'danger',
            'warning' => 'warning',
            'info' => 'good'
        ];
        
        return $colors[$severity] ?? 'good';
    }
    
    /**
     * Log alert
     */
    private function logAlert($alert)
    {
        $logData = [
            'alert_id' => uniqid('alert_'),
            'metric' => $alert['metric'],
            'value' => $alert['value'],
            'severity' => $alert['severity'],
            'threshold' => $alert['threshold'],
            'message' => $alert['message'],
            'timestamp' => $alert['timestamp']
        ];
        
        file_put_contents(
            BASE_PATH . '/storage/logs/performance_alerts.log',
            json_encode($logData) . PHP_EOL,
            FILE_APPEND
        );
    }
    
    /**
     * Get recent alerts
     */
    public function getRecentAlerts($limit = 10)
    {
        $alerts = $this->cache->lrange('alerts:recent', 0, $limit - 1);
        
        return array_map(function($alert) {
            return json_decode($alert, true);
        }, $alerts);
    }
    
    /**
     * Get alert statistics
     */
    public function getAlertStatistics($timeRange = 3600)
    {
        $endTime = time();
        $startTime = $endTime - $timeRange;
        
        $sql = "SELECT severity, COUNT(*) as count FROM performance_alerts WHERE created_at >= ? AND created_at <= ? GROUP BY severity";
        
        try {
            $stmt = $this->cache->getDatabase()->prepare($sql);
            $stmt->execute([
                date('Y-m-d H:i:s', $startTime),
                date('Y-m-d H:i:s', $endTime)
            ]);
            
            $stats = [];
            while ($row = $stmt->fetch()) {
                $stats[$row['severity']] = (int) $row['count'];
            }
            
            return $stats;
        } catch (Exception $e) {
            error_log("Failed to get alert statistics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update alert thresholds
     */
    public function updateThresholds($thresholds)
    {
        $this->config['thresholds'] = array_merge($this->config['thresholds'], $thresholds);
        
        // Store in cache
        $this->cache->set('alert:thresholds', $this->config['thresholds'], 86400);
    }
    
    /**
     * Get alert thresholds
     */
    public function getThresholds()
    {
        return $this->config['thresholds'];
    }
}

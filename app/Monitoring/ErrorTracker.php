<?php
/**
 * APS Dream Home - Error Tracker
 */

namespace App\Monitoring;

class ErrorTracker
{
    private static $instance = null;
    private $config;
    private $errorCounts = [];
    private $alertThresholds;

    private function __construct()
    {
        $this->config = require CONFIG_PATH . '/monitoring.php';
        $this->alertThresholds = $this->config['alerts']['thresholds'];
        $this->initializeErrorCounts();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initializeErrorCounts()
    {
        $this->errorCounts = [
            'total' => 0,
            'by_type' => [],
            'by_severity' => [
                'critical' => 0,
                'high' => 0,
                'medium' => 0,
                'low' => 0
            ],
            'recent' => [],
            'trends' => []
        ];
    }

    public function trackError($error, $type = 'exception', $severity = 'medium', $context = [])
    {
        $errorData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'error' => $error,
            'type' => $type,
            'severity' => $severity,
            'context' => array_merge([
                'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'referer' => $_SERVER['HTTP_REFERER'] ?? 'unknown'
            ], $context),
            'stack_trace' => $this->getStackTrace(),
            'request_id' => uniqid('error_')
        ];

        // Update error counts
        $this->updateErrorCounts($errorData);

        // Log error
        $this->logError($errorData);

        // Check alert conditions
        $this->checkAlertConditions($errorData);

        return $errorData['request_id'];
    }

    private function updateErrorCounts($errorData)
    {
        $this->errorCounts['total']++;
        
        // Count by type
        $type = $errorData['type'];
        $this->errorCounts['by_type'][$type] = ($this->errorCounts['by_type'][$type] ?? 0) + 1;
        
        // Count by severity
        $severity = $errorData['severity'];
        if (isset($this->errorCounts['by_severity'][$severity])) {
            $this->errorCounts['by_severity'][$severity]++;
        }
        
        // Add to recent errors (keep last 100)
        array_unshift($this->errorCounts['recent'], $errorData);
        $this->errorCounts['recent'] = array_slice($this->errorCounts['recent'], 0, 100);
    }

    private function getStackTrace()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $formattedTrace = [];
        
        foreach ($trace as $i => $frame) {
            $formattedTrace[] = [
                'file' => $frame['file'] ?? 'unknown',
                'line' => $frame['line'] ?? 0,
                'function' => $frame['function'] ?? 'unknown',
                'class' => $frame['class'] ?? null
            ];
        }
        
        return $formattedTrace;
    }

    private function logError($errorData)
    {
        $logFile = BASE_PATH . '/logs/error_tracking.log';
        file_put_contents($logFile, json_encode($errorData) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    private function checkAlertConditions($errorData)
    {
        // Check severity-based alerts
        if ($errorData['severity'] === 'critical') {
            $this->sendAlert($errorData, 'critical_error');
        }

        // Check error rate alerts
        $errorRate = $this->getErrorRate();
        if ($errorRate > $this->alertThresholds['error_rate']) {
            $this->sendAlert($errorData, 'high_error_rate');
        }

        // Check error type spikes
        $this->checkErrorTypeSpikes($errorData);
    }

    private function getErrorRate()
    {
        $oneMinuteAgo = time() - 60;
        $recentErrors = 0;
        
        foreach ($this->errorCounts['recent'] as $error) {
            if (strtotime($error['timestamp']) >= $oneMinuteAgo) {
                $recentErrors++;
            }
        }
        
        return $recentErrors;
    }

    private function checkErrorTypeSpikes($errorData)
    {
        $type = $errorData['type'];
        $recentTypeErrors = 0;
        $fiveMinutesAgo = time() - 300;
        
        foreach ($this->errorCounts['recent'] as $error) {
            if ($error['type'] === $type && strtotime($error['timestamp']) >= $fiveMinutesAgo) {
                $recentTypeErrors++;
            }
        }
        
        // Alert if more than 10 errors of same type in 5 minutes
        if ($recentTypeErrors > 10) {
            $this->sendAlert($errorData, 'error_type_spike');
        }
    }

    private function sendAlert($errorData, $alertType)
    {
        if (!$this->config['alerts']['enabled']) {
            return;
        }

        $alert = [
            'id' => uniqid('alert_'),
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => $alertType,
            'severity' => $errorData['severity'],
            'message' => $this->getAlertMessage($alertType, $errorData),
            'error_data' => $errorData,
            'status' => 'active'
        ];

        // Log alert
        $this->logAlert($alert);

        // Send notifications
        $this->sendNotifications($alert);
    }

    private function getAlertMessage($alertType, $errorData)
    {
        $messages = [
            'critical_error' => 'Critical error detected: ' . $errorData['error'],
            'high_error_rate' => 'High error rate detected: ' . $this->getErrorRate() . ' errors per minute',
            'error_type_spike' => 'Error type spike detected: ' . $errorData['type'] . ' errors increasing rapidly'
        ];

        return $messages[$alertType] ?? 'Unknown alert type: ' . $alertType;
    }

    private function logAlert($alert)
    {
        $alertFile = BASE_PATH . '/logs/error_alerts.log';
        file_put_contents($alertFile, json_encode($alert) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    private function sendNotifications($alert)
    {
        $channels = $this->config['alerts']['channels'];
        
        foreach ($channels as $channel) {
            switch ($channel) {
                case 'email':
                    $this->sendEmailAlert($alert);
                    break;
                case 'log':
                    // Already logged in logAlert()
                    break;
                case 'webhook':
                    $this->sendWebhookAlert($alert);
                    break;
            }
        }
    }

    private function sendEmailAlert($alert)
    {
        $to = $this->config['alerts']['email_recipients'];
        $subject = 'APS Dream Home - ' . strtoupper($alert['severity']) . ' Alert';
        $message = $this->formatEmailMessage($alert);
        
        $headers = [
            'From: noreply@apsdreamhomes.com',
            'Content-Type: text/html; charset=UTF-8'
        ];

        foreach ($to as $recipient) {
            mail($recipient, $subject, $message, implode("\r\n", $headers));
        }
    }

    private function formatEmailMessage($alert)
    {
        $html = '<html><body>';
        $html .= '<h2>APS Dream Home - Error Alert</h2>';
        $html .= '<p><strong>Alert Type:</strong> ' . ucfirst($alert['type']) . '</p>';
        $html .= '<p><strong>Severity:</strong> ' . ucfirst($alert['severity']) . '</p>';
        $html .= '<p><strong>Time:</strong> ' . $alert['timestamp'] . '</p>';
        $html .= '<p><strong>Message:</strong> ' . $alert['message'] . '</p>';
        $html .= '<h3>Error Details:</h3>';
        $html .= '<p><strong>Error:</strong> ' . $alert['error_data']['error'] . '</p>';
        $html .= '<p><strong>Type:</strong> ' . $alert['error_data']['type'] . '</p>';
        $html .= '<p><strong>URI:</strong> ' . $alert['error_data']['context']['uri'] . '</p>';
        $html .= '<p><strong>IP:</strong> ' . $alert['error_data']['context']['ip'] . '</p>';
        $html .= '</body></html>';
        
        return $html;
    }

    private function sendWebhookAlert($alert)
    {
        // Placeholder for webhook implementation
        // Could integrate with Slack, Discord, or other webhook services
        $webhookUrl = $this->config['alerts']['webhook_url'] ?? null;
        
        if ($webhookUrl) {
            $payload = json_encode($alert);
            $ch = curl_init($webhookUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            curl_close($ch);
        }
    }

    public function getErrorStats()
    {
        return $this->errorCounts;
    }

    public function getRecentErrors($limit = 50)
    {
        return array_slice($this->errorCounts['recent'], 0, $limit);
    }

    public function getErrorTrends($hours = 24)
    {
        $trends = [];
        $startTime = time() - ($hours * 3600);
        
        foreach ($this->errorCounts['recent'] as $error) {
            if (strtotime($error['timestamp']) >= $startTime) {
                $hour = date('H', strtotime($error['timestamp']));
                if (!isset($trends[$hour])) {
                    $trends[$hour] = 0;
                }
                $trends[$hour]++;
            }
        }
        
        return $trends;
    }
}

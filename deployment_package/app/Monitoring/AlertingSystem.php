<?php
/**
 * APS Dream Home - Alerting System
 */

namespace App\Monitoring;

class AlertingSystem
{
    private static $instance = null;
    private $config;
    private $activeAlerts = [];

    private function __construct()
    {
        $this->config = require CONFIG_PATH . '/monitoring.php';
        $this->loadActiveAlerts();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function createAlert($type, $message, $severity = 'medium', $data = [])
    {
        $alert = [
            'id' => uniqid('alert_'),
            'type' => $type,
            'message' => $message,
            'severity' => $severity,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s'),
            'status' => 'active',
            'acknowledged' => false,
            'resolved' => false,
            'resolved_at' => null,
            'acknowledged_at' => null,
            'acknowledged_by' => null
        ];

        $this->activeAlerts[$alert['id']] = $alert;
        $this->saveAlert($alert);
        $this->sendNotification($alert);

        return $alert['id'];
    }

    public function acknowledgeAlert($alertId, $acknowledgedBy)
    {
        if (!isset($this->activeAlerts[$alertId])) {
            return false;
        }

        $this->activeAlerts[$alertId]['acknowledged'] = true;
        $this->activeAlerts[$alertId]['acknowledged_at'] = date('Y-m-d H:i:s');
        $this->activeAlerts[$alertId]['acknowledged_by'] = $acknowledgedBy;
        $this->activeAlerts[$alertId]['status'] = 'acknowledged';

        $this->saveAlert($this->activeAlerts[$alertId]);
        return true;
    }

    public function resolveAlert($alertId)
    {
        if (!isset($this->activeAlerts[$alertId])) {
            return false;
        }

        $this->activeAlerts[$alertId]['resolved'] = true;
        $this->activeAlerts[$alertId]['resolved_at'] = date('Y-m-d H:i:s');
        $this->activeAlerts[$alertId]['status'] = 'resolved';

        $this->saveAlert($this->activeAlerts[$alertId]);
        return true;
    }

    public function getActiveAlerts()
    {
        return array_filter($this->activeAlerts, function($alert) {
            return !$alert['resolved'];
        });
    }

    public function getAlertsBySeverity($severity)
    {
        return array_filter($this->activeAlerts, function($alert) use ($severity) {
            return $alert['severity'] === $severity && !$alert['resolved'];
        });
    }

    private function loadActiveAlerts()
    {
        $alertFile = BASE_PATH . '/logs/active_alerts.json';
        if (file_exists($alertFile)) {
            $alerts = json_decode(file_get_contents($alertFile), true);
            if (is_array($alerts)) {
                $this->activeAlerts = $alerts;
            }
        }
    }

    private function saveAlert($alert)
    {
        $this->activeAlerts[$alert['id']] = $alert;
        $alertFile = BASE_PATH . '/logs/active_alerts.json';
        file_put_contents($alertFile, json_encode($this->activeAlerts), LOCK_EX);
    }

    private function sendNotification($alert)
    {
        if (!$this->config['alerts']['enabled']) {
            return;
        }

        $channels = $this->config['alerts']['channels'];
        
        foreach ($channels as $channel) {
            switch ($channel) {
                case 'email':
                    $this->sendEmailNotification($alert);
                    break;
                case 'log':
                    $this->logAlert($alert);
                    break;
                case 'webhook':
                    $this->sendWebhookNotification($alert);
                    break;
            }
        }
    }

    private function sendEmailNotification($alert)
    {
        $to = $this->config['alerts']['email_recipients'];
        $subject = 'APS Dream Home Alert: ' . ucfirst($alert['type']);
        $message = $this->formatAlertMessage($alert);
        
        $headers = [
            'From: noreply@apsdreamhomes.com',
            'Content-Type: text/html; charset=UTF-8'
        ];

        foreach ($to as $recipient) {
            mail($recipient, $subject, $message, implode("\r\n", $headers));
        }
    }

    private function formatAlertMessage($alert)
    {
        $html = '<html><body>';
        $html .= '<h2>APS Dream Home - System Alert</h2>';
        $html .= '<p><strong>Alert Type:</strong> ' . ucfirst($alert['type']) . '</p>';
        $html .= '<p><strong>Severity:</strong> ' . ucfirst($alert['severity']) . '</p>';
        $html .= '<p><strong>Time:</strong> ' . $alert['timestamp'] . '</p>';
        $html .= '<p><strong>Message:</strong> ' . $alert['message'] . '</p>';
        $html .= '<p><strong>Status:</strong> ' . ucfirst($alert['status']) . '</p>';
        $html .= '</body></html>';
        
        return $html;
    }

    private function logAlert($alert)
    {
        $logFile = BASE_PATH . '/logs/alerts.log';
        file_put_contents($logFile, json_encode($alert) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    private function sendWebhookNotification($alert)
    {
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
}

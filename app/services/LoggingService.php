<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Core\Config\Config;
use App\Traits\ServiceTenantTrait;
use Exception;

/**
 * Custom Logging Service
 * Pure PHP implementation for APS Dream Home Custom MVC
 */
class LoggingService
{
    use ServiceTenantTrait;

    private static $instance = null;
    private $db;
    private $logFile;
    private $logLevel;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->logFile = 'logs/application.log';
        $this->logLevel = 'INFO';

        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * Log emergency message
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->log('EMERGENCY', $message, $context);
    }

    /**
     * Log alert message
     */
    public function alert(string $message, array $context = []): void
    {
        $this->log('ALERT', $message, $context);
    }

    /**
     * Log critical message
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log('CRITICAL', $message, $context);
    }

    /**
     * Log error message
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    /**
     * Log warning message
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }

    /**
     * Log notice message
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log('NOTICE', $message, $context);
    }

    /**
     * Log info message
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    /**
     * Log debug message
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context);
    }

    /**
     * Core logging method
     */
    private function log(string $level, string $message, array $context = []): void
    {
        // Check if we should log this level
        if (!$this->shouldLog($level)) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $contextStr = empty($context) ? '' : ' ' . json_encode($context);
        $logEntry = "[$timestamp] $level: $message$contextStr" . PHP_EOL;

        // Write to file
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);

        // Also store in database if enabled
        if (Config::get('log_to_database', false)) {
            $this->logToDatabase($level, $message, $context);
        }

        // Send to external monitoring if critical
        if (in_array($level, ['EMERGENCY', 'ALERT', 'CRITICAL'])) {
            $this->sendAlert($level, $message, $context);
        }
    }

    /**
     * Check if we should log this level
     */
    private function shouldLog(string $level): bool
    {
        $levels = [
            'DEBUG' => 0,
            'INFO' => 1,
            'NOTICE' => 2,
            'WARNING' => 3,
            'ERROR' => 4,
            'CRITICAL' => 5,
            'ALERT' => 6,
            'EMERGENCY' => 7
        ];

        return $levels[$level] >= $levels[$this->logLevel];
    }

    /**
     * Log to database
     */
    private function logToDatabase(string $level, string $message, array $context): void
    {
        try {
            $tid = $this->tenantId();
            $sql = "INSERT INTO system_logs (action, table_name, record_id, old_values, new_values, ip_address, user_agent, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $level,
                'application',
                0,
                '',
                json_encode(array_merge(['message' => $message], $context)),
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            // Fallback to file logging if database fails
            error_log("Failed to log to database: " . $e->getMessage());
        }
    }

    /**
     * Send alert for critical errors
     */
    private function sendAlert(string $level, string $message, array $context): void
    {
        try {
            // 1. Always log to alert file
            $alertFile = 'logs/alerts.log';
            $timestamp = date('Y-m-d H:i:s');
            $alertEntry = "[$timestamp] $level ALERT: $message " . json_encode($context) . PHP_EOL;
            file_put_contents($alertFile, $alertEntry, FILE_APPEND | LOCK_EX);

            // 2. Send email alert for critical levels
            $this->sendEmailAlert($level, $message, $context);

            // 3. Send Slack webhook alert
            $this->sendSlackAlert($level, $message, $context);

        } catch (Exception $e) {
            error_log("Failed to send alert: " . $e->getMessage());
        }
    }

    /**
     * Send email alert for critical errors
     */
    private function sendEmailAlert(string $level, string $message, array $context): void
    {
        $emailConfig = [
            'enabled' => env('ALERT_EMAIL_ENABLED', false),
            'smtp_host' => env('ALERT_EMAIL_SMTP_HOST', ''),
            'smtp_port' => env('ALERT_EMAIL_SMTP_PORT', 587),
            'smtp_user' => env('ALERT_EMAIL_SMTP_USER', ''),
            'smtp_pass' => env('ALERT_EMAIL_SMTP_PASS', ''),
            'from_email' => env('ALERT_EMAIL_FROM', 'alerts@apsdreamhome.com'),
            'from_name' => env('ALERT_EMAIL_FROM_NAME', 'APS Dream Home Alerts'),
            'to_emails' => array_filter(array_map('trim', explode(',', env('ALERT_EMAIL_TO', ''))))
        ];

        if (!$emailConfig['enabled'] || empty($emailConfig['to_emails'])) {
            return; // Email alerts not configured
        }

        $subject = "[APS Dream Home] $level Alert: " . substr($message, 0, 80);
        
        $body = $this->buildAlertEmailBody($level, $message, $context);

        $this->sendEmail($emailConfig, $emailConfig['to_emails'], $subject, $body);
    }

    /**
     * Send Slack webhook alert for critical errors
     */
    private function sendSlackAlert(string $level, string $message, array $context): void
    {
        $slackConfig = [
            'enabled' => env('ALERT_SLACK_ENABLED', false),
            'webhook_url' => env('ALERT_SLACK_WEBHOOK_URL', ''),
            'channel' => env('ALERT_SLACK_CHANNEL', '#alerts'),
            'username' => env('ALERT_SLACK_USERNAME', 'APS Dream Home Alerts'),
            'icon_emoji' => env('ALERT_SLACK_ICON', ':warning:')
        ];

        if (!$slackConfig['enabled'] || empty($slackConfig['webhook_url'])) {
            return; // Slack alerts not configured
        }

        $color = match ($level) {
            'EMERGENCY' => '#ff0000',
            'ALERT' => '#ff4500',
            'CRITICAL' => '#ff8c00',
            default => '#ffa500'
        };

        $fields = [
            [
                'title' => 'Level',
                'value' => $level,
                'short' => true
            ],
            [
                'title' => 'Time',
                'value' => date('Y-m-d H:i:s'),
                'short' => true
            ]
        ];

        if (!empty($context)) {
            foreach ($context as $key => $value) {
                $fields[] = [
                    'title' => ucfirst($key),
                    'value' => is_scalar($value) ? (string)$value : json_encode($value),
                    'short' => true
                ];
            }
        }

        $payload = [
            'channel' => $slackConfig['channel'],
            'username' => $slackConfig['username'],
            'icon_emoji' => $slackConfig['icon_emoji'],
            'attachments' => [[
                'color' => $color,
                'title' => "APS Dream Home - $level Alert",
                'text' => $message,
                'fields' => $fields,
                'footer' => 'APS Dream Home Monitoring',
                'ts' => time()
            ]]
        ];

        $this->sendWebhook($slackConfig['webhook_url'], $payload);
    }

    /**
     * Build HTML email body for alerts
     */
    private function buildAlertEmailBody(string $level, string $message, array $context): string
    {
        $levelColors = [
            'EMERGENCY' => '#dc3545',
            'ALERT' => '#fd7e14',
            'CRITICAL' => '#ffc107',
            'ERROR' => '#dc3545',
            'WARNING' => '#ffc107'
        ];

        $color = $levelColors[$level] ?? '#6c757d';

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: ' . $color . '; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border-radius: 0 0 8px 8px; }
        .level-badge { display: inline-block; background: ' . $color . '; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .field { margin-bottom: 12px; }
        .field-label { font-weight: bold; color: #666; font-size: 13px; }
        .field-value { font-family: monospace; background: #fff; padding: 8px; border-radius: 4px; border: 1px solid #dee2e6; }
        .footer { text-align: center; margin-top: 20px; color: #999; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">APS Dream Home - System Alert</h2>
        </div>
        <div class="content">
            <div class="field">
                <div class="field-label">Alert Level</div>
                <div class="field-value"><span class="level-badge">' . $level . '</span></div>
            </div>
            <div class="field">
                <div class="field-label">Message</div>
                <div class="field-value">' . htmlspecialchars($message) . '</div>
            </div>
            <div class="field">
                <div class="field-label">Timestamp</div>
                <div class="field-value">' . date('Y-m-d H:i:s') . '</div>
            </div>';

        if (!empty($context)) {
            $html .= '<div class="field">
                <div class="field-label">Context</div>
                <div class="field-value"><pre style="margin: 0;">' . htmlspecialchars(json_encode($context, JSON_PRETTY_PRINT)) . '</pre></div>
            </div>';
        }

        $html .= '<div class="field">
                <div class="field-label">Server</div>
                <div class="field-value">' . ($_SERVER['SERVER_NAME'] ?? 'unknown') . '</div>
            </div>
            <div class="field">
                <div class="field-label">URL</div>
                <div class="field-value">' . ($_SERVER['REQUEST_URI'] ?? 'CLI') . '</div>
            </div>
        </div>
        <div class="footer">
            This is an automated alert from APS Dream Home monitoring system.
        </div>
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Send email using SMTP
     */
    private function sendEmail(array $config, array $toEmails, string $subject, string $body): void
    {
        // Build SMTP headers
        $headers = [
            'From: ' . $config['from_name'] . ' <' . $config['from_email'] . '>',
            'Reply-To: ' . $config['from_email'],
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'X-Priority: 1 (Highest)',
            'X-MSMail-Priority: High',
            'Importance: High'
        ];

        $headerString = implode("\r\n", $headers);

        // Use PHPMailer if available, fallback to mail()
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = $config['smtp_host'];
                $mail->SMTPAuth = true;
                $mail->Username = $config['smtp_user'];
                $mail->Password = $config['smtp_pass'];
                $mail->SMTPSecure = 'tls';
                $mail->Port = $config['smtp_port'];

                $mail->setFrom($config['from_email'], $config['from_name']);
                
                foreach ($toEmails as $to) {
                    $mail->addAddress($to);
                }

                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $body;
                $mail->AltBody = strip_tags($body);

                $mail->send();
                return;
            } catch (Exception $e) {
                error_log("PHPMailer failed: " . $e->getMessage());
                // Fall through to mail()
            }
        }

        // Fallback to PHP mail()
        foreach ($toEmails as $to) {
            @mail($to, $subject, $body, $headerString);
        }
    }

    /**
     * Send webhook (for Slack, Discord, etc.)
     */
    private function sendWebhook(string $url, array $payload): void
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 10
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            error_log("Slack webhook failed: HTTP $httpCode - $response");
        }
    }

    /**
     * Log user activity
     */
    public function logUserActivity(int $userId, string $action, array $details = []): void
    {
        try {
            $tid = $this->tenantId();
            $col = "user_id, action, context, ip_address, user_agent, created_at";
            $placeholders = "?, ?, ?, ?, ?, NOW()";
            $params = [
                $userId,
                $action,
                json_encode($details),
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ];
            if ($tid > 1) {
                $col = "tenant_id, $col";
                $placeholders = ", $placeholders";
                $params = array_merge([$tid], $params);
            }
            $sql = "INSERT INTO user_activity_logs_unified ($col) VALUES ($placeholders)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            // Also log to main log
            $this->info("User activity: $action", array_merge(['user_id' => $userId], $details));
        } catch (Exception $e) {
            $this->error("Failed to log user activity: " . $e->getMessage());
        }
    }

    /**
     * Log security event
     */
    public function logSecurityEvent(string $event, array $details = []): void
    {
        try {
            $sql = "INSERT INTO security_log (event, details, ip_address, user_agent, created_at) 
                    VALUES (?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $event,
                json_encode($details),
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);

            // Also log to main log
            $this->warning("Security event: $event", $details);
        } catch (Exception $e) {
            $this->error("Failed to log security event: " . $e->getMessage());
        }
    }

    /**
     * Log performance metrics
     */
    public function logPerformance(string $action, float $duration, array $details = []): void
    {
        try {
            $sql = "INSERT INTO performance_log (action, duration, details, created_at) 
                    VALUES (?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $action,
                $duration,
                json_encode($details)
            ]);

            // Log slow queries
            if ($duration > 1.0) { // More than 1 second
                $this->warning("Slow operation detected: $action took {$duration}s", $details);
            }
        } catch (Exception $e) {
            $this->error("Failed to log performance: " . $e->getMessage());
        }
    }

    /**
     * Get recent logs
     */
    public function getRecentLogs(int $limit = 100, string $level = null): array
    {
        try {
            $sql = "SELECT * FROM system_logs";
            $params = [];

            if ($level) {
                $sql .= " WHERE level = ?";
                $params[] = $level;
            }

            $sql .= " ORDER BY created_at DESC LIMIT ?";
            $params[] = $limit;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->error("Failed to get recent logs: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get log statistics
     */
    public function getLogStats(string $startDate = null, string $endDate = null): array
    {
        try {
            $sql = "SELECT level, COUNT(*) as count FROM system_logs";
            $params = [];

            if ($startDate || $endDate) {
                $sql .= " WHERE";
                $conditions = [];

                if ($startDate) {
                    $conditions[] = " created_at >= ?";
                    $params[] = $startDate;
                }

                if ($endDate) {
                    $conditions[] = " created_at <= ?";
                    $params[] = $endDate;
                }

                $sql .= " " . implode(" AND", $conditions);
            }

            $sql .= " GROUP BY level ORDER BY count DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            $stats = [];
            foreach ($stmt->fetchAll() as $row) {
                $stats[$row['level']] = (int)$row['count'];
            }

            return $stats;
        } catch (Exception $e) {
            $this->error("Failed to get log stats: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Clean old logs
     */
    public function cleanOldLogs(int $daysToKeep = 30): bool
    {
        try {
            // Clean database logs
            $sql = "DELETE FROM system_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$daysToKeep]);

            // Clean log files
            $logFiles = ['logs/application.log', 'logs/alerts.log'];
            foreach ($logFiles as $file) {
                if (file_exists($file)) {
                    $lines = file($file);
                    $cutoffDate = date('Y-m-d H:i:s', strtotime("-$daysToKeep days"));

                    $newLines = [];
                    foreach ($lines as $line) {
                        if (preg_match('/\[([\d-]+ [\d:]+)\]/', $line, $matches)) {
                            if ($matches[1] >= $cutoffDate) {
                                $newLines[] = $line;
                            }
                        }
                    }

                    file_put_contents($file, implode('', $newLines));
                }
            }

            $this->info("Old logs cleaned successfully", ['days_kept' => $daysToKeep]);
            return true;
        } catch (Exception $e) {
            $this->error("Failed to clean old logs: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Set log level
     */
    public function setLogLevel(string $level): void
    {
        $this->logLevel = $level;
    }

    /**
     * Get log level
     */
    public function getLogLevel(): string
    {
        return $this->logLevel;
    }
}

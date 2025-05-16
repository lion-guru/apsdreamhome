<?php
/**
 * Advanced Event Monitoring and Logging System
 * Provides comprehensive tracking of system events, security incidents, and performance metrics
 */

// require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/config_manager.php';
require_once __DIR__ . '/security_middleware.php';

class EventMonitor {
    private $logger;
    private $config;
    private $securityMiddleware;
    private $eventLog = [];
    private $sensitiveEventTypes = [
        'LOGIN_ATTEMPT',
        'SECURITY_BREACH',
        'ADMIN_ACTION',
        'DATA_MODIFICATION'
    ];

    // Event severity levels
    const SEVERITY_LOW = 1;
    const SEVERITY_MEDIUM = 2;
    const SEVERITY_HIGH = 3;
    const SEVERITY_CRITICAL = 4;

    public function __construct() {
        $this->logger = new Logger();
        $this->config = ConfigManager::getInstance();
        $this->securityMiddleware = new SecurityMiddleware();
    }

    /**
     * Log a system event with comprehensive details
     * 
     * @param string $eventType Type of event
     * @param array $eventData Event details
     * @param int $severity Event severity
     * @return void
     */
    public function logEvent($eventType, $eventData = [], $severity = self::SEVERITY_LOW) {
        // Sanitize event data
        $sanitizedData = $this->securityMiddleware->sanitizeInput($eventData);

        // Create event record
        $eventRecord = [
            'timestamp' => time(),
            'type' => $eventType,
            'severity' => $severity,
            'data' => $sanitizedData,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];

        // Add to in-memory event log
        $this->eventLog[] = $eventRecord;

        // Log to file for sensitive events
        if (in_array($eventType, $this->sensitiveEventTypes)) {
            $this->logSensitiveEvent($eventRecord);
        }

        // Optional: Real-time alert for high-severity events
        if ($severity >= self::SEVERITY_HIGH) {
            $this->triggerHighSeverityAlert($eventRecord);
        }
    }

    /**
     * Log sensitive events with additional security measures
     * 
     * @param array $eventRecord Event details
     * @return void
     */
    private function logSensitiveEvent($eventRecord) {
        // Mask sensitive information
        $maskedEventRecord = $this->maskSensitiveData($eventRecord);

        // Write to secure log file
        $logPath = $this->config->get('SECURE_EVENT_LOG_PATH', __DIR__ . '/../logs/secure_events.log');
        $logEntry = json_encode($maskedEventRecord) . PHP_EOL;
        
        // Append to log with strict file permissions
        file_put_contents($logPath, $logEntry, FILE_APPEND | LOCK_EX);
        chmod($logPath, 0600); // Read/write for owner only
    }

    /**
     * Mask sensitive data in event records
     * 
     * @param array $eventRecord Event details
     * @return array Masked event record
     */
    private function maskSensitiveData($eventRecord) {
        $sensitiveKeys = [
            'password',
            'token',
            'secret',
            'credentials'
        ];

        foreach ($sensitiveKeys as $key) {
            if (isset($eventRecord['data'][$key])) {
                $eventRecord['data'][$key] = '***MASKED***';
            }
        }

        return $eventRecord;
    }

    /**
     * Trigger high-severity event alerts
     * 
     * @param array $eventRecord Event details
     * @return void
     */
    private function triggerHighSeverityAlert($eventRecord) {
        // Email alert configuration
        $alertEmail = $this->config->get('SECURITY_ALERT_EMAIL', 'admin@example.com');
        $emailSubject = "High Severity Event: {$eventRecord['type']}";
        
        $emailBody = "High Severity Security Event Detected:\n";
        $emailBody .= "Type: {$eventRecord['type']}\n";
        $emailBody .= "Timestamp: " . date('Y-m-d H:i:s', $eventRecord['timestamp']) . "\n";
        $emailBody .= "IP Address: {$eventRecord['ip_address']}\n";
        $emailBody .= "Severity: " . $this->getSeverityLabel($eventRecord['severity']);

        // Send email alert (implement secure email sending)
        $this->sendSecureAlert($alertEmail, $emailSubject, $emailBody);
    }

    /**
     * Get human-readable severity label
     * 
     * @param int $severity Severity level
     * @return string Severity label
     */
    private function getSeverityLabel($severity) {
        return match($severity) {
            self::SEVERITY_LOW => 'Low',
            self::SEVERITY_MEDIUM => 'Medium',
            self::SEVERITY_HIGH => 'High',
            self::SEVERITY_CRITICAL => 'Critical',
            default => 'Unknown'
        };
    }

    /**
     * Send secure email alert
     * 
     * @param string $email Recipient email
     * @param string $subject Email subject
     * @param string $body Email body
     * @return bool Success status
     */
    private function sendSecureAlert($email, $subject, $body) {
        // Implement secure email sending
        // Use a library like PHPMailer with SMTP encryption
        // This is a placeholder - replace with actual secure email implementation
        $headers = [
            'From' => 'security@apsdreamhomefinal.com',
            'X-Mailer' => 'PHP/' . phpversion(),
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/plain; charset=UTF-8'
        ];

        return mail($email, $subject, $body, $headers);
    }

    /**
     * Generate comprehensive event report
     * 
     * @param int $hours Hours of events to include
     * @return array Detailed event report
     */
    public function generateEventReport($hours = 24) {
        $cutoffTimestamp = time() - ($hours * 3600);
        
        $report = [
            'total_events' => 0,
            'events_by_severity' => [
                'low' => 0,
                'medium' => 0,
                'high' => 0,
                'critical' => 0
            ],
            'events_by_type' => [],
            'recent_events' => []
        ];

        foreach ($this->eventLog as $event) {
            if ($event['timestamp'] >= $cutoffTimestamp) {
                $report['total_events']++;
                
                // Count by severity
                $severityLabel = strtolower($this->getSeverityLabel($event['severity']));
                $report['events_by_severity'][$severityLabel]++;

                // Count by type
                $report['events_by_type'][$event['type']] = 
                    ($report['events_by_type'][$event['type']] ?? 0) + 1;

                // Store recent events
                $report['recent_events'][] = $event;
            }
        }

        return $report;
    }

    /**
     * Demonstrate event monitoring capabilities
     */
    public function demonstrateEventMonitoring() {
        // Simulate various events
        $this->logEvent('LOGIN_ATTEMPT', [
            'username' => 'testuser',
            'status' => 'success'
        ], self::SEVERITY_MEDIUM);

        $this->logEvent('SECURITY_BREACH', [
            'ip' => '192.168.1.100',
            'attempt_count' => 5
        ], self::SEVERITY_HIGH);

        $this->logEvent('DATA_MODIFICATION', [
            'table' => 'users',
            'action' => 'update',
            'user_id' => 123
        ], self::SEVERITY_CRITICAL);

        // Generate and display report
        $report = $this->generateEventReport();
        print_r($report);
    }
}

// Uncomment to demonstrate
// $eventMonitor = new EventMonitor();
// $eventMonitor->demonstrateEventMonitoring();

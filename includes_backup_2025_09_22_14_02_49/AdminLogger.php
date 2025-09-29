<?php
/**
 * Comprehensive Logging Utility for APS Dream Homes
 * Provides advanced logging mechanisms for admin actions
 */
class AdminLogger {
    // Log levels
    const LEVEL_INFO = 'INFO';
    const LEVEL_WARNING = 'WARNING';
    const LEVEL_ERROR = 'ERROR';
    const LEVEL_SECURITY = 'SECURITY';

    // Log file paths
    private static $logDir = '/var/log/apsdreamhomes/';
    private static $actionLogFile = '/var/log/apsdreamhomes/admin_actions.log';
    private static $errorLogFile = '/var/log/apsdreamhomes/system_errors.log';

    /**
     * Log admin actions
     * @param string $action
     * @param array $details
     * @param string $level
     */
    public static function log($action, $details = [], $level = self::LEVEL_INFO) {
        // Ensure log directory exists
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }

        // Prepare log entry
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'action' => $action,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            'user' => $_SESSION['admin_session']['username'] ?? 'Unauthenticated',
            'details' => json_encode($details)
        ];

        // Write to file
        error_log(json_encode($logEntry) . PHP_EOL, 3, self::$actionLogFile);

        // Optional: Log to database
        self::logToDatabase($logEntry);
    }

    /**
     * Log system errors
     * @param string $message
     * @param array $context
     */
    public static function logError($message, $context = []) {
        $errorEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'message' => $message,
            'context' => json_encode($context),
            'trace' => debug_backtrace()
        ];

        error_log(json_encode($errorEntry) . PHP_EOL, 3, self::$errorLogFile);
    }

    /**
     * Log to database (optional)
     * @param array $logEntry
     */
    private static function logToDatabase($logEntry) {
        try {
            global $pdo;
            $stmt = $pdo->prepare("INSERT INTO system_logs (level, action, ip_address, username, details, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $logEntry['level'],
                $logEntry['action'],
                $logEntry['ip'],
                $logEntry['user'],
                $logEntry['details']
            ]);
        } catch (PDOException $e) {
            // Fallback logging if database insert fails
            error_log('Database logging failed: ' . $e->getMessage(), 3, self::$errorLogFile);
        }
    }

    /**
     * Monitor and alert on critical security events
     * @param string $event
     * @param array $details
     */
    public static function securityAlert($event, $details = []) {
        // Send email or SMS for critical security events
        $alertDetails = array_merge([
            'event' => $event,
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
        ], $details);

        self::log($event, $alertDetails, self::LEVEL_SECURITY);
        
        // Optional: Implement email/SMS notification logic
        self::sendSecurityNotification($alertDetails);
    }

    /**
     * Send security notification (placeholder)
     * @param array $details
     */
    private static function sendSecurityNotification($details) {
        // Implement email or SMS notification
        // Use services like SendGrid, Twilio, etc.
    }
}

// Global helper function
function log_admin_action($action, $details = [], $level = AdminLogger::LEVEL_INFO) {
    AdminLogger::log($action, $details, $level);
}

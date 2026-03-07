<?php

namespace App\Services\Legacy;

use Exception;

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

    // Log file paths (initialized in constructor or via dynamic method)
    private static $logDir = __DIR__ . '/../logs/';
    private static $actionLogFile = __DIR__ . '/../logs/admin_actions.log';
    private static $errorLogFile = __DIR__ . '/../logs/system_errors.log';

    /**
     * Ensure log directory and files exist
     */
    private static function ensureLogSetup() {
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }

        if (!file_exists(self::$actionLogFile)) {
            touch(self::$actionLogFile);
        }

        if (!file_exists(self::$errorLogFile)) {
            touch(self::$errorLogFile);
        }
    }

    /**
     * Log admin actions
     * @param string $action
     * @param array $details
     * @param string $level
     */
    public static function log($action, $details = [], $level = self::LEVEL_INFO) {
        self::ensureLogSetup();

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
        self::ensureLogSetup();
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
            $db = \App\Core\App::database();
            $sql = "INSERT INTO system_logs (level, action, ip_address, username, details, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())";
            $db->execute($sql, [
                $logEntry['level'],
                $logEntry['action'],
                $logEntry['ip'],
                $logEntry['user'],
                $logEntry['details']
            ]);
        } catch (Exception $e) {
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

/**
 * Global helper function for namespaced admin activity logging
 */
if (!function_exists('App\Services\Legacy\log_admin_activity')) {
    function log_admin_activity($action, $details = '') {
        $detailsArray = is_array($details) ? $details : ['message' => $details];
        AdminLogger::log($action, $detailsArray);
    }
}

/**
 * Global helper function for easy access (legacy proxy support)
 */
if (!function_exists('log_admin_action')) {
    function log_admin_action($action, $details = [], $level = AdminLogger::LEVEL_INFO) {
        AdminLogger::log($action, $details, $level);
    }
}

<?php

namespace App\Services\Legacy;

/**
 * Security Events Logger
 * Logs security-related events for auditing and monitoring
 */
class SecurityLogger {
    private $logger;

    public function __construct($logger = null) {
        $this->logger = null;
    }

    public function info($message, $context = []) {
        $this->log('INFO', $message, $context);
    }

    public function warning($message, $context = []) {
        $this->log('WARNING', $message, $context);
    }

    public function error($message, $context = []) {
        $this->log('ERROR', $message, $context);
    }

    private function log($level, $message, $context = []) {
        $logEntry = sprintf(
            "[%s] [%s] %s %s\n",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            !empty($context) ? json_encode($context) : ''
        );
        $logDir = __DIR__ . '/../logs/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        error_log($logEntry, 3, $logDir . 'security.log');
    }

    /**
     * Log authentication attempts
     */
    public function logAuthAttempt($username, $success, $context = []) {
        $message = sprintf(
            'Authentication attempt for user "%s" %s',
            $username,
            $success ? 'succeeded' : 'failed'
        );

        $context = array_merge($context, [
            'event_type' => 'authentication',
            'username' => $username,
            'success' => $success,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'referer' => $_SERVER['HTTP_REFERER'] ?? 'direct'
        ]);

        $this->info($message, $context);
    }

    /**
     * Log password changes
     */
    public function logPasswordChange($userId, $success, $context = []) {
        $message = sprintf(
            'Password change for user ID %d %s',
            $userId,
            $success ? 'succeeded' : 'failed'
        );

        $context = array_merge($context, [
            'event_type' => 'password_change',
            'user_id' => $userId,
            'success' => $success
        ]);

        $this->info($message, $context);
    }

    /**
     * Log access control events
     */
    public function logAccessControl($userId, $resource, $action, $allowed) {
        $message = sprintf(
            'Access control: User ID %d %s to %s %s',
            $userId,
            $allowed ? 'granted access' : 'denied access',
            $action,
            $resource
        );

        $context = [
            'event_type' => 'access_control',
            'user_id' => $userId,
            'resource' => $resource,
            'action' => $action,
            'allowed' => $allowed
        ];

        $this->info($message, $context);
    }

    /**
     * Log rate limit violations
     */
    public function logRateLimitViolation($key, $type) {
        $message = sprintf(
            'Rate limit exceeded for %s (type: %s)',
            $key,
            $type
        );

        $context = [
            'event_type' => 'rate_limit',
            'key' => $key,
            'type' => $type,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];

        $this->warning($message, $context);
    }

    /**
     * Log file upload attempts
     */
    public function logFileUpload($userId, $filename, $success, $context = []) {
        $message = sprintf(
            'File upload by user ID %d: %s %s',
            $userId,
            $filename,
            $success ? 'succeeded' : 'failed'
        );

        $context = array_merge($context, [
            'event_type' => 'file_upload',
            'user_id' => $userId,
            'filename' => $filename,
            'success' => $success
        ]);

        $this->info($message, $context);
    }

    /**
     * Log configuration changes
     */
    public function logConfigChange($userId, $setting, $oldValue, $newValue) {
        $message = sprintf(
            'Configuration changed by user ID %d: %s from "%s" to "%s"',
            $userId,
            $setting,
            $oldValue,
            $newValue
        );

        $context = [
            'event_type' => 'config_change',
            'user_id' => $userId,
            'setting' => $setting,
            'old_value' => $oldValue,
            'new_value' => $newValue
        ];

        $this->info($message, $context);
    }

    /**
     * Log suspicious activities
     */
    public function logSuspiciousActivity($type, $details) {
        $message = sprintf(
            'Suspicious activity detected: %s',
            $type
        );

        $context = [
            'event_type' => 'suspicious_activity',
            'activity_type' => $type,
            'details' => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];

        $this->warning($message, $context);
    }
}

/**
 * Global helper function for namespaced security logging
 */
if (!function_exists('App\Services\Legacy\log_security_event')) {
    function log_security_event($event, $severity = 'INFO', $data = []) {
        $logger = new SecurityLogger();
        switch (strtoupper($severity)) {
            case 'ERROR':
            case 'CRITICAL':
                $logger->error($event, $data);
                break;
            case 'WARNING':
                $logger->warning($event, $data);
                break;
            default:
                $logger->info($event, $data);
                break;
        }
    }
}

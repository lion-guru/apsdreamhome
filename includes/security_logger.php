<?php
/**
 * Security Events Logger
 * Logs security-related events for auditing and monitoring
 */

require_once __DIR__ . '/logger.php';

class SecurityLogger {
    private $logger;
    
    public function __construct($logger = null) {
        $this->logger = $logger ?? new Logger();
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
        
        $this->logger->security($message, $context);
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
        
        $this->logger->security($message, $context);
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
        
        $this->logger->security($message, $context);
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
        
        $this->logger->security($message, $context);
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
        
        $this->logger->security($message, $context);
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
        
        $this->logger->security($message, $context);
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
        
        $this->logger->security($message, $context);
    }
}

// Create global security logger instance
$securityLogger = new SecurityLogger($logger ?? null);

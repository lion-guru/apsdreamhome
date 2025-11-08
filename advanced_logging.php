<?php
/**
 * Advanced Logging System for Secure File Management
 * Provides comprehensive, secure logging capabilities
 */
class AdvancedLogger {
    // Logging configuration
    private $log_config = [
        'log_levels' => [
            'emergency' => ['code' => 0, 'description' => 'System is unusable', 'notification_required' => true],
            'alert' => ['code' => 1, 'description' => 'Immediate action required', 'notification_required' => true],
            'critical' => ['code' => 2, 'description' => 'Critical conditions', 'notification_required' => true],
            'error' => ['code' => 3, 'description' => 'Error conditions', 'notification_required' => false],
            'warning' => ['code' => 4, 'description' => 'Warning conditions', 'notification_required' => false],
            'notice' => ['code' => 5, 'description' => 'Normal but significant condition', 'notification_required' => false],
            'info' => ['code' => 6, 'description' => 'Informational messages', 'notification_required' => false],
            'debug' => ['code' => 7, 'description' => 'Debug-level messages', 'notification_required' => false]
        ],
        'logging_targets' => [
            'file' => [
                'enabled' => true,
                'path' => __DIR__ . '/logs/',
                'max_file_size' => 10 * 1024 * 1024, // 10MB
                'retention_days' => 30
            ],
            'database' => [
                'enabled' => true,
                'connection' => 'audit_log_connection'
            ],
            'syslog' => [
                'enabled' => false
            ],
            'external_siem' => [
                'enabled' => false,
                'endpoint' => '',
                'api_key' => ''
            ]
        ]
    ];

    /**
     * Sanitize log messages to remove sensitive information
     * 
     * @param string $message Original log message
     * @return string Sanitized log message
     */
    private function sanitizeLogMessage($message) {
        // Remove sensitive information patterns
        $sanitized_message = preg_replace([
            '/password=[\'"]?[^&\'"]+/i',
            '/token=[\'"]?[^&\'"]+/i',
            '/secret=[\'"]?[^&\'"]+/i',
            '/api_key=[\'"]?[^&\'"]+/i'
        ], [
            'password=***', 
            'token=***', 
            'secret=***',
            'api_key=***'
        ], $message);
        
        // Truncate message length
        return substr($sanitized_message, 0, 1024);
    }

    /**
     * Sanitize log context to protect sensitive information
     * 
     * @param array $context Original log context
     * @return array Sanitized log context
     */
    private function sanitizeLogContext($context) {
        $sanitized_context = [];
        $sensitive_keys = ['password', 'token', 'secret', 'api_key', 'credentials'];
        
        foreach ($context as $key => $value) {
            // Mask sensitive keys
            $lower_key = strtolower($key);
            
            if (in_array($lower_key, $sensitive_keys)) {
                $sanitized_context[$key] = '***';
            } elseif (is_string($value)) {
                // Truncate long string values
                $sanitized_context[$key] = substr($value, 0, 256);
            } elseif (is_array($value)) {
                // Recursively sanitize nested arrays
                $sanitized_context[$key] = $this->sanitizeLogContext($value);
            } else {
                $sanitized_context[$key] = $value;
            }
        }
        
        return $sanitized_context;
    }

    /**
     * Generate a unique trace ID for log correlation
     * 
     * @return string Unique trace ID
     */
    private function generateTraceId() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Capture server context for logging
     * 
     * @return array Server context information
     */
    private function captureServerContext() {
        return [
            'timestamp' => time(),
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'memory_usage' => memory_get_usage(true),
            'peak_memory_usage' => memory_get_peak_usage(true)
        ];
    }

    /**
     * Generate a secure log entry with comprehensive sanitization
     * 
     * @param string $level Log level
     * @param string $message Log message
     * @param array $context Additional context
     * @return array Secure log entry
     */
    public function generateSecureLogEntry($level, $message, $context = []) {
        // Validate log level
        $valid_levels = array_keys($this->log_config['log_levels']);
        $level = in_array($level, $valid_levels) ? $level : 'info';
        
        // Prepare log entry
        $log_entry = [
            'timestamp' => time(),
            'level' => $level,
            'message' => $this->sanitizeLogMessage($message),
            'context' => $this->sanitizeLogContext($context),
            'trace_id' => $this->generateTraceId(),
            'server_info' => $this->captureServerContext()
        ];
        
        // Add level-specific configuration
        $log_entry['level_config'] = $this->log_config['log_levels'][$level];
        
        // Write to configured logging targets
        $this->writeLogToTargets($log_entry);
        
        return $log_entry;
    }

    /**
     * Write log entry to configured targets
     * 
     * @param array $log_entry Sanitized log entry
     */
    private function writeLogToTargets($log_entry) {
        $targets = $this->log_config['logging_targets'];
        
        // File logging
        if ($targets['file']['enabled']) {
            $this->writeLogToFile($log_entry);
        }
        
        // Database logging
        if ($targets['database']['enabled']) {
            $this->writeLogToDatabase($log_entry);
        }
        
        // Handle notifications for critical levels
        if ($log_entry['level_config']['notification_required']) {
            $this->sendLogNotification($log_entry);
        }
    }

    /**
     * Write log entry to file
     * 
     * @param array $log_entry Sanitized log entry
     */
    private function writeLogToFile($log_entry) {
        $config = $this->log_config['logging_targets']['file'];
        $log_dir = $config['path'];
        
        // Ensure log directory exists
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0700, true);
        }
        
        // Create log filename
        $filename = $log_dir . 'security_log_' . date('Y-m-d') . '.log';
        
        // Rotate log file if needed
        $this->rotateLogFile($filename, $config);
        
        // Write log entry
        $log_line = json_encode($log_entry, JSON_PRETTY_PRINT) . PHP_EOL;
        file_put_contents($filename, $log_line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Rotate log file if it exceeds maximum size
     * 
     * @param string $filename Log file path
     * @param array $config Log file configuration
     */
    private function rotateLogFile($filename, $config) {
        if (file_exists($filename) && filesize($filename) >= $config['max_file_size']) {
            $backup_filename = $filename . '.' . date('YmdHis') . '.bak';
            rename($filename, $backup_filename);
            
            // Optional: Compress backup
            if (extension_loaded('zip')) {
                $zip = new ZipArchive();
                $zip_filename = $backup_filename . '.zip';
                if ($zip->open($zip_filename, ZipArchive::CREATE) === TRUE) {
                    $zip->addFile($backup_filename, basename($backup_filename));
                    $zip->close();
                    unlink($backup_filename);
                }
            }
            
            // Clean up old log files
            $this->cleanupOldLogFiles($config);
        }
    }

    /**
     * Clean up log files older than retention period
     * 
     * @param array $config Log file configuration
     */
    private function cleanupOldLogFiles($config) {
        $log_dir = $config['path'];
        $retention_days = $config['retention_days'];
        
        $files = glob($log_dir . '*.log.bak*');
        foreach ($files as $file) {
            if (is_file($file) && (time() - filemtime($file) > $retention_days * 86400)) {
                unlink($file);
            }
        }
    }

    /**
     * Write log entry to database
     * 
     * @param array $log_entry Sanitized log entry
     */
    private function writeLogToDatabase($log_entry) {
        try {
            // Implement database logging logic
            // This is a placeholder and should be replaced with actual database connection
            $db = $this->getDatabaseConnection();
            $stmt = $db->prepare(
                'INSERT INTO system_audit_logs ' . 
                '(trace_id, level, message, context, server_info, created_at) ' . 
                'VALUES (:trace_id, :level, :message, :context, :server_info, NOW())'
            );
            
            $stmt->execute([
                ':trace_id' => $log_entry['trace_id'],
                ':level' => $log_entry['level'],
                ':message' => $log_entry['message'],
                ':context' => json_encode($log_entry['context']),
                ':server_info' => json_encode($log_entry['server_info'])
            ]);
        } catch (Exception $e) {
            // Fallback logging
            error_log('Database log write failed: ' . $e->getMessage());
        }
    }

    /**
     * Send notification for critical log entries
     * 
     * @param array $log_entry Sanitized log entry
     */
    private function sendLogNotification($log_entry) {
        // Implement notification mechanism (email, SMS, etc.)
        $notification_details = [
            'subject' => 'Critical System Log - ' . strtoupper($log_entry['level']),
            'body' => sprintf(
                "A critical log entry has been generated:\n" . 
                "Level: %s\n" . 
                "Message: %s\n" . 
                "Trace ID: %s\n" . 
                "Timestamp: %s",
                $log_entry['level'],
                $log_entry['message'],
                $log_entry['trace_id'],
                date('Y-m-d H:i:s', $log_entry['timestamp'])
            )
        ];
        
        // Placeholder for actual notification method
        $this->sendAdminNotification(
            $notification_details['subject'], 
            $notification_details['body']
        );
    }

    /**
     * Placeholder for database connection method
     * 
     * @return PDO Database connection
     */
    private function getDatabaseConnection() {
        // Implement actual database connection logic
        // This is a placeholder and should be replaced with secure connection method
        return new PDO('mysql:host=localhost;dbname=your_database', 'username', 'password');
    }

    /**
     * Placeholder for admin notification method
     * 
     * @param string $subject Notification subject
     * @param string $body Notification body
     */
    private function sendAdminNotification($subject, $body) {
        // Implement actual notification mechanism
        // This could be email, SMS, or other notification system
        error_log("ADMIN NOTIFICATION: $subject\n$body");
    }
}

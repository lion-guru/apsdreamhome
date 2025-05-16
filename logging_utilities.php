<?php
trait LoggingUtilities {
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
    private function generateSecureLogEntry($level, $message, $context = []) {
        $log_config = $this->system_readiness_report['advanced_logging'] ?? [];
        
        // Validate log level
        $valid_levels = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'];
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
        
        // Optional: Add level-specific configuration
        if (isset($log_config['log_levels'][$level])) {
            $log_entry['level_config'] = $log_config['log_levels'][$level];
        }
        
        return $log_entry;
    }
}

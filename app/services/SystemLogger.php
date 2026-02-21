<?php

namespace App\Services;

use App\Core\Database;
use Exception;
use JsonSerializable;

class SystemLogger
{
    // Log levels
    public const EMERGENCY = 'emergency';
    public const ALERT = 'alert';
    public const CRITICAL = 'critical';
    public const ERROR = 'error';
    public const WARNING = 'warning';
    public const NOTICE = 'notice';
    public const INFO = 'info';
    public const DEBUG = 'debug';

    // Database connection
    private $db;

    // Current user context
    private $user;

    // Configuration
    private $config;

    /**
     * Constructor
     * 
     * @param array $config Optional configuration
     */
    public function __construct(array $config = [])
    {
        // Initialize database connection
        $this->db = Database::getInstance();

        // Load configuration from ConfigurationManager
        $configManager = \App\Services\ConfigurationManager::getInstance();
        $loggingConfig = $configManager->get('logging');
        $this->config = array_merge($loggingConfig ?? [], $config);

        // Ensure default configuration values
        $this->config['log_to_database'] = $this->config['log_to_database'] ?? false;
        $this->config['log_to_file'] = $this->config['log_to_file'] ?? true;

        // Ensure log directory exists
        $filePath = $this->config['targets']['file']['path'] ?? (__DIR__ . '/../../logs/');
        if (!is_dir($filePath)) {
            mkdir($filePath, 0755, true);
        }
        $this->config['log_file_path'] = $filePath;
        $this->config['max_log_file_size'] = $this->config['targets']['file']['max_file_size'] ?? (10 * 1024 * 1024);

        // Set current user context
        $this->setUserContext();
    }

    /**
     * Set user context for logging
     */
    private function setUserContext()
    {
        // Implement user context retrieval
        // This could be from session, authentication service, etc.
        $this->user = [
            'id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? 'system',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
    }

    /**
     * Log an emergency message
     * 
     * @param string $message Log message
     * @param array $context Additional context
     * @return bool
     */
    public function emergency(string $message, array $context = []): bool
    {
        return $this->log(self::EMERGENCY, $message, $context);
    }

    /**
     * Log an alert message
     * 
     * @param string $message Log message
     * @param array $context Additional context
     * @return bool
     */
    public function alert(string $message, array $context = []): bool
    {
        return $this->log(self::ALERT, $message, $context);
    }

    /**
     * Log a critical message
     * 
     * @param string $message Log message
     * @param array $context Additional context
     * @return bool
     */
    public function critical(string $message, array $context = []): bool
    {
        return $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * Log an error message
     * 
     * @param string $message Log message
     * @param array $context Additional context
     * @return bool
     */
    public function error(string $message, array $context = []): bool
    {
        return $this->log(self::ERROR, $message, $context);
    }

    /**
     * Log a warning message
     * 
     * @param string $message Log message
     * @param array $context Additional context
     * @return bool
     */
    public function warning(string $message, array $context = []): bool
    {
        return $this->log(self::WARNING, $message, $context);
    }

    /**
     * Log a notice message
     * 
     * @param string $message Log message
     * @param array $context Additional context
     * @return bool
     */
    public function notice(string $message, array $context = []): bool
    {
        return $this->log(self::NOTICE, $message, $context);
    }

    /**
     * Log an info message
     * 
     * @param string $message Log message
     * @param array $context Additional context
     * @return bool
     */
    public function info(string $message, array $context = []): bool
    {
        return $this->log(self::INFO, $message, $context);
    }

    /**
     * Log a debug message
     * 
     * @param string $message Log message
     * @param array $context Additional context
     * @return bool
     */
    public function debug(string $message, array $context = []): bool
    {
        return $this->log(self::DEBUG, $message, $context);
    }

    /**
     * Core logging method
     * 
     * @param string $level Log level
     * @param string $message Log message
     * @param array $context Additional context
     * @return bool
     */
    private function log(string $level, string $message, array $context = []): bool
    {
        try {
            // Generate unique trace ID
            $traceId = $this->generateTraceId();

            // Prepare log entry
            $logEntry = [
                'trace_id' => $traceId,
                'level' => $level,
                'message' => $this->sanitizeMessage($message),
                'context' => $this->sanitizeContext($context),
                'user_id' => $this->user['id'],
                'username' => $this->user['username'],
                'ip_address' => $this->user['ip_address'],
                'timestamp' => time()
            ];

            // Log to database if enabled
            if ($this->config['log_to_database']) {
                $this->logToDatabase($logEntry);
            }

            // Log to file if enabled
            if ($this->config['log_to_file']) {
                $this->logToFile($logEntry);
            }

            return true;
        } catch (Exception $e) {
            // Fallback error logging
            error_log("SystemLogger Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log entry to database
     * 
     * @param array $logEntry Log entry details
     */
    private function logToDatabase(array $logEntry)
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO comprehensive_audit_log 
                (trace_id, user_id, username, action_type, severity_level, 
                ip_address, request_payload, created_at) 
                VALUES (:trace_id, :user_id, :username, :action_type, 
                :severity_level, :ip_address, :request_payload, NOW())"
            );

            $stmt->execute([
                ':trace_id' => $logEntry['trace_id'],
                ':user_id' => $logEntry['user_id'] ?? null,
                ':username' => $logEntry['username'],
                ':action_type' => 'system',
                ':severity_level' => $logEntry['level'],
                ':ip_address' => $logEntry['ip_address'],
                ':request_payload' => json_encode([
                    'message' => $logEntry['message'],
                    'context' => $logEntry['context']
                ])
            ]);
        } catch (Exception $e) {
            error_log("Database Logging Error: " . $e->getMessage());
        }
    }

    /**
     * Log entry to file
     * 
     * @param array $logEntry Log entry details
     */
    private function logToFile(array $logEntry)
    {
        try {
            // Create log filename with daily rotation
            $logFilename = $this->config['log_file_path'] . 'system_' . date('Y-m-d') . '.log';

            // Rotate log file if it exceeds max size
            $this->rotateLogFile($logFilename);

            // Prepare log line
            $logLine = sprintf(
                "[%s] %s.%s: %s\nContext: %s\nTrace ID: %s\n\n",
                date('Y-m-d H:i:s'),
                strtoupper($logEntry['level']),
                $logEntry['username'],
                $logEntry['message'],
                json_encode($logEntry['context'], JSON_PRETTY_PRINT),
                $logEntry['trace_id']
            );

            // Write to log file
            file_put_contents($logFilename, $logLine, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            error_log("File Logging Error: " . $e->getMessage());
        }
    }

    /**
     * Rotate log file if it exceeds maximum size
     * 
     * @param string $logFilename Log file path
     */
    private function rotateLogFile(string $logFilename)
    {
        if (file_exists($logFilename) && filesize($logFilename) >= $this->config['max_log_file_size']) {
            $backupFilename = $logFilename . '.' . date('YmdHis') . '.bak';
            rename($logFilename, $backupFilename);
        }
    }

    /**
     * Generate a unique trace ID
     * 
     * @return string
     */
    private function generateTraceId(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * Sanitize log message
     * 
     * @param string $message Original message
     * @return string Sanitized message
     */
    private function sanitizeMessage(string $message): string
    {
        // Remove sensitive information
        $sanitized = preg_replace([
            '/password=[\'"]?[^&\'"]+/i',
            '/token=[\'"]?[^&\'"]+/i',
            '/secret=[\'"]?[^&\'"]+/i'
        ], [
            'password=***',
            'token=***',
            'secret=***'
        ], $message);

        // Truncate message length
        return substr($sanitized, 0, 1024);
    }

    /**
     * Sanitize log context
     * 
     * @param array $context Original context
     * @return array Sanitized context
     */
    private function sanitizeContext(array $context): array
    {
        $sanitized = [];
        $sensitiveKeys = ['password', 'token', 'secret', 'api_key'];

        foreach ($context as $key => $value) {
            // Mask sensitive keys
            if (in_array(strtolower($key), $sensitiveKeys)) {
                $sanitized[$key] = '***';
            } elseif (is_string($value)) {
                // Truncate long string values
                $sanitized[$key] = substr($value, 0, 256);
            } elseif (is_array($value)) {
                // Recursively sanitize nested arrays
                $sanitized[$key] = $this->sanitizeContext($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}

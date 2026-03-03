<?php

namespace App\Services\Monitoring;

use PDO;

/**
 * Advanced Logging Service for APS Dream Home
 * Handles error logging, performance monitoring, and system health tracking
 */
class LoggingService
{
    private $db;
    private $logFile;
    private $logLevel;

    // Log levels
    const EMERGENCY = 'emergency';
    const ALERT = 'alert';
    const CRITICAL = 'critical';
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';

    public function __construct($db = null)
    {
        $this->db = $db;
        $this->logFile = __DIR__ . '/../../../logs/app.log';
        $this->logLevel = getenv('LOG_LEVEL') ?: self::INFO;

        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * Log emergency level message
     */
    public function emergency($message, array $context = [])
    {
        $this->log(self::EMERGENCY, $message, $context);
    }

    /**
     * Log alert level message
     */
    public function alert($message, array $context = [])
    {
        $this->log(self::ALERT, $message, $context);
    }

    /**
     * Log critical level message
     */
    public function critical($message, array $context = [])
    {
        $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * Log error level message
     */
    public function error($message, array $context = [])
    {
        $this->log(self::ERROR, $message, $context);
    }

    /**
     * Log warning level message
     */
    public function warning($message, array $context = [])
    {
        $this->log(self::WARNING, $message, $context);
    }

    /**
     * Log notice level message
     */
    public function notice($message, array $context = [])
    {
        $this->log(self::NOTICE, $message, $context);
    }

    /**
     * Log info level message
     */
    public function info($message, array $context = [])
    {
        $this->log(self::INFO, $message, $context);
    }

    /**
     * Log debug level message
     */
    public function debug($message, array $context = [])
    {
        $this->log(self::DEBUG, $message, $context);
    }

    /**
     * Log performance metrics
     */
    public function performance($operation, $duration, array $context = [])
    {
        $context['operation'] = $operation;
        $context['duration_ms'] = $duration;
        $context['memory_usage'] = memory_get_peak_usage(true);

        $this->info("Performance: {$operation} completed in {$duration}ms", $context);
    }

    /**
     * Log user activity
     */
    public function userActivity($userId, $action, array $context = [])
    {
        $context['user_id'] = $userId;
        $context['action'] = $action;
        $context['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $context['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        $this->info("User Activity: User {$userId} performed {$action}", $context);
    }

    /**
     * Log database query
     */
    public function databaseQuery($query, $duration, array $context = [])
    {
        $context['query'] = $query;
        $context['duration_ms'] = $duration;

        $this->debug("Database Query: {$query} ({$duration}ms)", $context);
    }

    /**
     * Log system health metrics
     */
    public function systemHealth(array $metrics)
    {
        $message = "System Health Check";
        $this->info($message, $metrics);
    }

    /**
     * Main logging method
     */
    private function log($level, $message, array $context = [])
    {
        // Check if this log level should be processed
        if (!$this->shouldLog($level)) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $logEntry = [
            'timestamp' => $timestamp,
            'level' => strtoupper($level),
            'message' => $message,
            'context' => json_encode($context),
            'file' => $context['file'] ?? basename(__FILE__),
            'line' => $context['line'] ?? __LINE__,
            'request_id' => $this->getRequestId()
        ];

        // Log to file
        $this->logToFile($logEntry);

        // Log to database if available
        if ($this->db) {
            $this->logToDatabase($logEntry);
        }
    }

    /**
     * Check if message should be logged based on level
     */
    private function shouldLog($level)
    {
        $levels = [
            self::DEBUG => 0,
            self::INFO => 1,
            self::NOTICE => 2,
            self::WARNING => 3,
            self::ERROR => 4,
            self::CRITICAL => 5,
            self::ALERT => 6,
            self::EMERGENCY => 7
        ];

        return isset($levels[$level]) && $levels[$level] >= $levels[$this->logLevel];
    }

    /**
     * Log to file
     */
    private function logToFile($logEntry)
    {
        $formattedMessage = sprintf(
            "[%s] %s: %s %s\n",
            $logEntry['timestamp'],
            $logEntry['level'],
            $logEntry['message'],
            !empty($logEntry['context']) && $logEntry['context'] !== '{}' ? "Context: {$logEntry['context']}" : ''
        );

        file_put_contents($this->logFile, $formattedMessage, FILE_APPEND | LOCK_EX);
    }

    /**
     * Log to database
     */
    private function logToDatabase($logEntry)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO system_logs
                (timestamp, level, message, context, file, line, request_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $logEntry['timestamp'],
                $logEntry['level'],
                $logEntry['message'],
                $logEntry['context'],
                $logEntry['file'],
                $logEntry['line'],
                $logEntry['request_id']
            ]);
        } catch (\Exception $e) {
            // If database logging fails, log to file only
            $this->logToFile([
                'timestamp' => date('Y-m-d H:i:s'),
                'level' => 'ERROR',
                'message' => 'Failed to log to database: ' . $e->getMessage(),
                'context' => '{}',
                'file' => __FILE__,
                'line' => __LINE__,
                'request_id' => $this->getRequestId()
            ]);
        }
    }

    /**
     * Get unique request ID for tracking
     */
    private function getRequestId()
    {
        static $requestId = null;

        if ($requestId === null) {
            $requestId = uniqid('req_', true);
        }

        return $requestId;
    }

    /**
     * Clean old log files (keep last 30 days)
     */
    public function cleanOldLogs($daysToKeep = 30)
    {
        $files = glob(dirname($this->logFile) . '/*.log');
        $cutoffTime = time() - ($daysToKeep * 24 * 60 * 60);

        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
            }
        }

        $this->info("Cleaned old log files older than {$daysToKeep} days");
    }

    /**
     * Get log statistics
     */
    public function getLogStats()
    {
        $stats = [
            'total_entries' => 0,
            'by_level' => [
                'EMERGENCY' => 0,
                'ALERT' => 0,
                'CRITICAL' => 0,
                'ERROR' => 0,
                'WARNING' => 0,
                'NOTICE' => 0,
                'INFO' => 0,
                'DEBUG' => 0
            ],
            'last_24h' => 0,
            'file_size' => filesize($this->logFile)
        ];

        if (file_exists($this->logFile)) {
            $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $yesterday = strtotime('-1 day');

            foreach ($lines as $line) {
                $stats['total_entries']++;

                // Parse log level
                if (preg_match('/\[([^\]]+)\]\s+([A-Z]+):/', $line, $matches)) {
                    $level = $matches[2];
                    if (isset($stats['by_level'][$level])) {
                        $stats['by_level'][$level]++;
                    }

                    // Check if from last 24 hours
                    $timestamp = strtotime($matches[1]);
                    if ($timestamp > $yesterday) {
                        $stats['last_24h']++;
                    }
                }
            }
        }

        return $stats;
    }
}

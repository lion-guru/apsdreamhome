<?php
/**
 * Logger Class
 * Handles logging of security events, errors, and debug information
 */

class Logger {
    private $logPath;
    private $logLevels = [
        'EMERGENCY' => 0,
        'ALERT'     => 1,
        'CRITICAL'  => 2,
        'ERROR'     => 3,
        'WARNING'   => 4,
        'NOTICE'    => 5,
        'INFO'      => 6,
        'DEBUG'     => 7
    ];
    private $currentLevel;
    private $maxFileSize = 10485760; // 10MB
    private $maxFiles = 5;

    public function __construct($basePath = null) {
        $this->logPath = $basePath ?? __DIR__ . '/../logs';
        $this->currentLevel = getenv('APP_DEBUG') ?: 'INFO';
        $this->initializeLogDirectory();
    }

    /**
     * Initialize log directory with proper permissions
     */
    private function initializeLogDirectory() {
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }

        // Create subdirectories for different log types
        $dirs = ['security', 'error', 'debug', 'access'];
        foreach ($dirs as $dir) {
            $path = $this->logPath . '/' . $dir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }

    /**
     * Log a security event
     */
    public function security($message, $context = []) {
        $this->log('ALERT', $message, $context, 'security');
    }

    /**
     * Log an error
     */
    public function error($message, $context = []) {
        $this->log('ERROR', $message, $context, 'error');
    }

    /**
     * Log debug information
     */
    public function debug($message, $context = []) {
        if ($this->shouldLog('DEBUG')) {
            $this->log('DEBUG', $message, $context, 'debug');
        }
    }

    /**
     * Log access information
     */
    public function access($message, $context = []) {
        $this->log('INFO', $message, $context, 'access');
    }

    /**
     * Main logging function
     */
    private function log($level, $message, array $context = [], $type = 'error') {
        if (!$this->shouldLog($level)) {
            return false;
        }

        $logFile = $this->logPath . '/' . $type . '/' . date('Y-m-d') . '.log';
        
        // Rotate log if needed
        $this->rotateLogIfNeeded($logFile);

        // Format the log entry
        $entry = $this->formatLogEntry($level, $message, $context);

        // Write to log file
        return file_put_contents(
            $logFile,
            $entry . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }

    /**
     * Format a log entry
     */
    private function formatLogEntry($level, $message, array $context = []) {
        $timestamp = date('Y-m-d H:i:s.v P');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userId = $_SESSION['user_id'] ?? 'guest';
        $requestId = $_SERVER['HTTP_X_REQUEST_ID'] ?? uniqid();

        $contextStr = !empty($context) ? json_encode($context) : '-';

        return sprintf(
            '[%s] [%s] [%s] [IP: %s] [User: %s] [ReqID: %s] %s %s',
            $timestamp,
            $level,
            php_sapi_name(),
            $ip,
            $userId,
            $requestId,
            $message,
            $contextStr
        );
    }

    /**
     * Check if we should log this level
     */
    private function shouldLog($level) {
        return $this->logLevels[$level] <= $this->logLevels[$this->currentLevel];
    }

    /**
     * Rotate log file if it exceeds max size
     */
    private function rotateLogIfNeeded($logFile) {
        if (!file_exists($logFile)) {
            return;
        }

        if (filesize($logFile) < $this->maxFileSize) {
            return;
        }

        $info = pathinfo($logFile);
        $prefix = $info['dirname'] . '/' . $info['filename'];

        // Shift existing rotated logs
        for ($i = $this->maxFiles - 1; $i >= 1; $i--) {
            $old = sprintf('%s.%d.log', $prefix, $i);
            $new = sprintf('%s.%d.log', $prefix, $i + 1);
            if (file_exists($old)) {
                rename($old, $new);
            }
        }

        // Rotate current log
        rename($logFile, sprintf('%s.1.log', $prefix));
    }

    /**
     * Set the current log level
     */
    public function setLevel($level) {
        if (array_key_exists($level, $this->logLevels)) {
            $this->currentLevel = $level;
        }
    }

    /**
     * Clean old log files
     */
    public function cleanOldLogs($days = 30) {
        $dirs = ['security', 'error', 'debug', 'access'];
        foreach ($dirs as $dir) {
            $path = $this->logPath . '/' . $dir;
            if (!is_dir($path)) {
                continue;
            }

            $files = glob($path . '/*.log*');
            foreach ($files as $file) {
                if (filemtime($file) < time() - ($days * 86400)) {
                    unlink($file);
                }
            }
        }
    }
}

// Create global logger instance
$logger = new Logger();

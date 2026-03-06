<?php

namespace App\Services\Legacy;
// Advanced Logging System

class Logger {
    // Log levels
    const EMERGENCY = 'emergency';
    const ALERT     = 'alert';
    const CRITICAL  = 'critical';
    const ERROR     = 'error';
    const WARNING   = 'warning';
    const NOTICE    = 'notice';
    const INFO      = 'info';
    const DEBUG     = 'debug';

    // Log channels
    private $channels = [
        'application',
        'security',
        'database',
        'performance',
        'email',
        'system'
    ];

    // Log configuration
    private $config = [
        'log_dir' => '',
        'max_log_files' => 10,
        'max_log_size' => 5 * 1024 * 1024, // 5MB
        'log_level' => self::INFO
    ];

    // Singleton instance
    private static $instance = null;

    private function __construct() {
        // Set default log directory
        $this->config['log_dir'] = __DIR__ . '/../logs';

        // Ensure log directory exists
        $this->createLogDirectory();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Create log directory with proper permissions
     */
    private function createLogDirectory() {
        if (!is_dir($this->config['log_dir'])) {
            mkdir($this->config['log_dir'], 0755, true);
        }

        // Create channel-specific log directories
        foreach ($this->channels as $channel) {
            $channel_dir = $this->config['log_dir'] . '/' . $channel;
            if (!is_dir($channel_dir)) {
                mkdir($channel_dir, 0755, true);
            }
        }
    }

    /**
     * Log a message
     * @param string $message Log message
     * @param string $level Log level
     * @param string $channel Log channel
     * @param array $context Additional context
     */
    public function log($message, $level = self::INFO, $channel = 'application', $context = []) {
        // Check if logging is enabled for this level
        if ($this->getLevelPriority($level) > $this->getLevelPriority($this->config['log_level'])) {
            return;
        }

        // Validate channel
        if (!in_array($channel, $this->channels)) {
            $channel = 'application';
        }

        // Prepare log entry
        $timestamp = date('Y-m-d H:i:s');
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $user_id = $_SESSION['user_id'] ?? 'guest';

        // Format log message
        $formatted_message = $this->formatLogMessage($message, $level, $context);

        // Write to log file
        $log_file = $this->getLogFilePath($channel);
        $this->writeToLogFile($log_file, $formatted_message);

        // Rotate logs if needed
        $this->rotateLogs($channel);
    }

    /**
     * Log a warning message.
     *
     * @param string $message The message to log.
     * @param array $context The context data.
     * @param string $channel The log channel.
     */
    public function warning($message, $context = [], $channel = 'application')
    {
        $this->log($message, self::WARNING, $channel, $context);
    }

    /**
     * Format log message
     * @param string $message Original message
     * @param string $level Log level
     * @param array $context Additional context
     * @return string Formatted log message
     */
    private function formatLogMessage($message, $level, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $user_id = $_SESSION['user_id'] ?? 'guest';

        // Add context information
        $context_str = $context ? ' | ' . json_encode($context) : '';

        return sprintf(
            "[%s] [%s] [%s] [%s] %s%s\n",
            $timestamp,
            strtoupper($level),
            $ip_address,
            $user_id,
            $message,
            $context_str
        );
    }

    /**
     * Get log file path for a specific channel
     * @param string $channel Log channel
     * @return string Log file path
     */
    private function getLogFilePath($channel) {
        $date = date('Y-m-d');
        return sprintf(
            '%s/%s/%s.log',
            $this->config['log_dir'],
            $channel,
            $date
        );
    }

    /**
     * Write message to log file
     * @param string $log_file Log file path
     * @param string $message Log message
     */
    private function writeToLogFile($log_file, $message) {
        // Ensure directory exists
        $dir = dirname($log_file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Append message to log file
        file_put_contents($log_file, $message, FILE_APPEND | LOCK_EX);
    }

    /**
     * Rotate log files to prevent them from growing too large
     * @param string $channel Log channel
     */
    private function rotateLogs($channel) {
        $log_dir = $this->config['log_dir'] . '/' . $channel;
        $log_files = glob($log_dir . '/*.log');

        // Sort log files by modification time (oldest first)
        usort($log_files, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });

        // Remove excess log files
        while (count($log_files) > $this->config['max_log_files']) {
            $oldest_log = array_shift($log_files);
            unlink($oldest_log);
        }

        // Check and truncate large log files
        foreach ($log_files as $log_file) {
            if (filesize($log_file) > $this->config['max_log_size']) {
                $this->truncateLogFile($log_file);
            }
        }
    }

    /**
     * Truncate log file to prevent it from growing too large
     * @param string $log_file Log file path
     */
    private function truncateLogFile($log_file) {
        $lines = file($log_file, FILE_IGNORE_NEW_LINES);
        $lines = array_slice($lines, -1000); // Keep last 1000 lines
        file_put_contents($log_file, implode("\n", $lines) . "\n");
    }

    /**
     * Get numeric priority for log level
     * @param string $level Log level
     * @return int Priority value
     */
    private function getLevelPriority($level) {
        $priorities = [
            self::EMERGENCY => 0,
            self::ALERT     => 1,
            self::CRITICAL  => 2,
            self::ERROR     => 3,
            self::WARNING   => 4,
            self::NOTICE    => 5,
            self::INFO      => 6,
            self::DEBUG     => 7
        ];

        return $priorities[$level] ?? $priorities[self::INFO];
    }

    /**
     * Log an exception
     * @param \Throwable $exception Exception to log
     * @param string $channel Log channel
     */
    public function logException(\Throwable $exception, $channel = 'application') {
        $message = sprintf(
            "Exception: %s\nFile: %s\nLine: %d\nTrace: %s",
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        $this->log($message, self::ERROR, $channel);
    }

    /**
     * Set log level
     * @param string $level Log level
     */
    public function setLogLevel($level) {
        if (defined('self::' . strtoupper($level))) {
            $this->config['log_level'] = $level;
        }
    }
}

// Global helper function
function logger() {
    return Logger::getInstance();
}

// Set global exception handler
set_exception_handler(function($exception) {
    logger()->logException($exception);
    
    // Display user-friendly error page in production
    if (getenv('APP_ENV') === 'production') {
        header('HTTP/1.1 500 Internal Server Error');
        include __DIR__ . '/../error_pages/500.php';
        exit;
    }
});

// Set global error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Only handle errors not suppressed by @
    if (!(error_reporting() & $errno)) {
        return false;
    }

    // Log the error
    $error_type = match($errno) {
        E_ERROR => 'Fatal Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        default => 'Unknown Error'
    };

    $message = sprintf(
        "%s: %s in %s on line %d",
        $error_type,
        $errstr,
        $errfile,
        $errline
    );

    logger()->log($message, Logger::ERROR, 'system');

    // Don't execute PHP's internal error handler
    return true;
}, E_ALL);

// Return logger instance for dependency injection
return logger();

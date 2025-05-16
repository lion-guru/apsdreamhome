<?php
/**
 * Advanced Error Tracking and Logging System
 * Provides comprehensive error handling, logging, and notification mechanisms
 */
class ErrorTrackingSystem {
    private $logDirectory;
    private $errorLogFile;
    private $exceptionLogFile;
    private $configFile;
    private $config;

    /**
     * Constructor initializes error tracking configuration
     */
    public function __construct() {
        $this->logDirectory = __DIR__ . '/logs/errors/';
        $this->configFile = __DIR__ . '/config/error_tracking_config.json';
        $this->errorLogFile = $this->logDirectory . 'error_log_' . date('Y-m-d') . '.log';
        $this->exceptionLogFile = $this->logDirectory . 'exception_log_' . date('Y-m-d') . '.log';

        $this->ensureLogDirectories();
        $this->loadConfiguration();
        $this->setupErrorHandlers();
    }

    /**
     * Ensure log directories exist
     */
    private function ensureLogDirectories() {
        if (!is_dir($this->logDirectory)) {
            mkdir($this->logDirectory, 0755, true);
        }
    }

    /**
     * Load error tracking configuration
     */
    private function loadConfiguration() {
        $defaultConfig = [
            'log_errors' => true,
            'log_exceptions' => true,
            'display_errors' => false,
            'error_reporting_level' => E_ALL,
            'notification_emails' => [],
            'max_log_files' => 30,
            'log_rotation_threshold' => 10 * 1024 * 1024, // 10 MB
            'error_types' => [
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
                E_STRICT => 'Strict Notice',
                E_RECOVERABLE_ERROR => 'Recoverable Error',
                E_DEPRECATED => 'Deprecated',
                E_USER_DEPRECATED => 'User Deprecated'
            ]
        ];

        // Load or create configuration file
        if (file_exists($this->configFile)) {
            $this->config = json_decode(file_get_contents($this->configFile), true);
            $this->config = array_merge($defaultConfig, $this->config);
        } else {
            $this->config = $defaultConfig;
            $this->saveConfiguration();
        }
    }

    /**
     * Save error tracking configuration
     */
    private function saveConfiguration() {
        $configDir = dirname($this->configFile);
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }
        file_put_contents($this->configFile, json_encode($this->config, JSON_PRETTY_PRINT));
    }

    /**
     * Setup custom error and exception handlers
     */
    private function setupErrorHandlers() {
        // Set error reporting level
        error_reporting($this->config['error_reporting_level']);

        // Configure error display
        ini_set('display_errors', $this->config['display_errors'] ? 1 : 0);

        // Set custom error handler
        set_error_handler([$this, 'customErrorHandler']);

        // Set custom exception handler
        set_exception_handler([$this, 'customExceptionHandler']);

        // Register shutdown function for fatal errors
        register_shutdown_function([$this, 'fatalErrorHandler']);
    }

    /**
     * Custom error handler
     * @param int $errno Error number
     * @param string $errstr Error message
     * @param string $errfile File where error occurred
     * @param int $errline Line number
     * @return bool
     */
    public function customErrorHandler($errno, $errstr, $errfile, $errline) {
        if (!($errno & $this->config['error_reporting_level'])) {
            return false;
        }

        $errorType = $this->config['error_types'][$errno] ?? 'Unknown Error';
        $errorDetails = [
            'type' => $errorType,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'timestamp' => date('Y-m-d H:i:s'),
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
        ];

        // Log the error
        if ($this->config['log_errors']) {
            $this->logError($errorDetails);
        }

        // Send notifications for critical errors
        if (in_array($errorType, ['Fatal Error', 'Core Error', 'Compile Error'])) {
            $this->sendErrorNotification($errorDetails);
        }

        // Allow default error handler for non-fatal errors
        return false;
    }

    /**
     * Custom exception handler
     * @param Throwable $exception Exception or Error object
     */
    public function customExceptionHandler($exception) {
        $exceptionDetails = [
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Log the exception
        if ($this->config['log_exceptions']) {
            $this->logException($exceptionDetails);
        }

        // Send exception notification
        $this->sendExceptionNotification($exceptionDetails);
    }

    /**
     * Handle fatal errors on script shutdown
     */
    public function fatalErrorHandler() {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->customErrorHandler(
                $error['type'], 
                $error['message'], 
                $error['file'], 
                $error['line']
            );
        }
    }

    /**
     * Log error details
     * @param array $errorDetails Error information
     */
    private function logError($errorDetails) {
        $this->rotateLogFiles($this->errorLogFile);

        $logEntry = sprintf(
            "[%s] %s in %s on line %d\n%s\nTrace:\n%s\n\n",
            $errorDetails['timestamp'],
            $errorDetails['type'],
            $errorDetails['file'],
            $errorDetails['line'],
            $errorDetails['message'],
            print_r($errorDetails['trace'], true)
        );

        file_put_contents($this->errorLogFile, $logEntry, FILE_APPEND);
    }

    /**
     * Log exception details
     * @param array $exceptionDetails Exception information
     */
    private function logException($exceptionDetails) {
        $this->rotateLogFiles($this->exceptionLogFile);

        $logEntry = sprintf(
            "[%s] %s: %s in %s on line %d\nTrace:\n%s\n\n",
            $exceptionDetails['timestamp'],
            $exceptionDetails['type'],
            $exceptionDetails['message'],
            $exceptionDetails['file'],
            $exceptionDetails['line'],
            $exceptionDetails['trace']
        );

        file_put_contents($this->exceptionLogFile, $logEntry, FILE_APPEND);
    }

    /**
     * Rotate log files to prevent excessive growth
     * @param string $logFile Path to log file
     */
    private function rotateLogFiles($logFile) {
        if (file_exists($logFile) && filesize($logFile) > $this->config['log_rotation_threshold']) {
            $archiveFile = $logFile . '.' . date('YmdHis') . '.bak';
            rename($logFile, $archiveFile);

            // Clean up old log files
            $this->cleanupOldLogFiles();
        }
    }

    /**
     * Clean up old log files
     */
    private function cleanupOldLogFiles() {
        $logFiles = glob($this->logDirectory . '*.log.*.bak');
        usort($logFiles, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        // Keep only the most recent log files
        $filesToKeep = array_slice($logFiles, 0, $this->config['max_log_files']);
        $filesToDelete = array_diff($logFiles, $filesToKeep);

        foreach ($filesToDelete as $file) {
            unlink($file);
        }
    }

    /**
     * Send error notification via email
     * @param array $errorDetails Error information
     */
    private function sendErrorNotification($errorDetails) {
        if (empty($this->config['notification_emails'])) {
            return;
        }

        $subject = "Critical Error: {$errorDetails['type']} in {$errorDetails['file']}";
        $message = sprintf(
            "A critical error occurred:\n\n" .
            "Type: %s\n" .
            "Message: %s\n" .
            "File: %s\n" .
            "Line: %d\n" .
            "Timestamp: %s\n",
            $errorDetails['type'],
            $errorDetails['message'],
            $errorDetails['file'],
            $errorDetails['line'],
            $errorDetails['timestamp']
        );

        $headers = 'From: error_tracking@yourdomain.com' . "\r\n" .
            'Reply-To: error_tracking@yourdomain.com' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        foreach ($this->config['notification_emails'] as $email) {
            mail($email, $subject, $message, $headers);
        }
    }

    /**
     * Send exception notification via email
     * @param array $exceptionDetails Exception information
     */
    private function sendExceptionNotification($exceptionDetails) {
        if (empty($this->config['notification_emails'])) {
            return;
        }

        $subject = "Unhandled Exception: {$exceptionDetails['type']} in {$exceptionDetails['file']}";
        $message = sprintf(
            "An unhandled exception occurred:\n\n" .
            "Type: %s\n" .
            "Message: %s\n" .
            "File: %s\n" .
            "Line: %d\n" .
            "Timestamp: %s\n\n" .
            "Trace:\n%s\n",
            $exceptionDetails['type'],
            $exceptionDetails['message'],
            $exceptionDetails['file'],
            $exceptionDetails['line'],
            $exceptionDetails['timestamp'],
            $exceptionDetails['trace']
        );

        $headers = 'From: error_tracking@yourdomain.com' . "\r\n" .
            'Reply-To: error_tracking@yourdomain.com' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        foreach ($this->config['notification_emails'] as $email) {
            mail($email, $subject, $message, $headers);
        }
    }

    /**
     * Generate error tracking report
     * @return array Error tracking report
     */
    public function generateReport() {
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'configuration' => $this->config,
            'log_files' => [
                'error_log' => $this->errorLogFile,
                'exception_log' => $this->exceptionLogFile
            ]
        ];

        return $report;
    }

    /**
     * Generate HTML report
     * @param array $report Error tracking report
     * @return string HTML report
     */
    public function generateHTMLReport($report) {
        $html = "<html><body>";
        $html .= "<h1>Error Tracking System Report</h1>";
        $html .= "<p>Timestamp: {$report['timestamp']}</p>";

        // Configuration Section
        $html .= "<h2>Configuration</h2>";
        $html .= "<table border='1'>";
        foreach ($report['configuration'] as $key => $value) {
            if (!is_array($value)) {
                $html .= "<tr><td>{$key}</td><td>" . htmlspecialchars(json_encode($value)) . "</td></tr>";
            }
        }
        $html .= "</table>";

        // Log Files Section
        $html .= "<h2>Log Files</h2>";
        $html .= "<ul>";
        foreach ($report['log_files'] as $type => $path) {
            $html .= "<li>{$type}: {$path}</li>";
        }
        $html .= "</ul>";

        $html .= "</body></html>";

        return $html;
    }
}

// Initialize error tracking system
$errorTracker = new ErrorTrackingSystem();

// Example usage and testing
if (php_sapi_name() === 'cli') {
    try {
        $report = $errorTracker->generateReport();
        
        // Generate and save HTML report
        $htmlReport = $errorTracker->generateHTMLReport($report);
        file_put_contents(__DIR__ . '/logs/error_tracking_report.html', $htmlReport);
        
        echo "Error Tracking System Report Generated.\n";
        echo "Report saved to: " . __DIR__ . "/logs/error_tracking_report.html\n";
    } catch (Exception $e) {
        echo "Error tracking report generation failed: " . $e->getMessage() . "\n";
    }
} else {
    // Web interface for report
    try {
        $report = $errorTracker->generateReport();
        echo $errorTracker->generateHTMLReport($report);
    } catch (Exception $e) {
        echo "Error tracking report generation failed: " . $e->getMessage();
    }
}


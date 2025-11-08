<?php
/**
 * Error Handler
 * Centralized error handling and logging
 */

namespace App\Core;

class ErrorHandler {
    private $logPath;
    private $isDevelopment;

    public function __construct() {
        $this->logPath = APP_ROOT . '/storage/logs/';
        $this->isDevelopment = ENVIRONMENT === 'development';

        $this->initialize();
    }

    /**
     * Initialize error handling
     */
    public function initialize() {
        // Set error reporting
        if ($this->isDevelopment) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(E_ERROR | E_PARSE);
            ini_set('display_errors', 0);
        }

        // Set error and exception handlers
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);

        // Create log directory if it doesn't exist
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }

        // Set error log file
        ini_set('error_log', $this->logPath . 'php_errors.log');
        ini_set('log_errors', 1);
    }

    /**
     * Handle PHP errors
     */
    public function handleError($errno, $errstr, $errfile, $errline) {
        $error = [
            'type' => $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'timestamp' => date('Y-m-d H:i:s'),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
        ];

        $this->logError($error);

        // Don't show errors in production unless fatal
        if (!$this->isDevelopment && !in_array($errno, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            return;
        }

        // Show error in development
        if ($this->isDevelopment) {
            echo '<div style="background: #f8d7da; color: #721c24; padding: 10px; margin: 10px; border: 1px solid #f5c6cb; border-radius: 4px;">';
            echo '<strong>Error:</strong> ' . htmlspecialchars($errstr) . '<br>';
            echo '<strong>File:</strong> ' . htmlspecialchars($errfile) . ':' . $errline;
            echo '</div>';
        }
    }

    /**
     * Handle exceptions
     */
    public function handleException($exception) {
        $error = [
            'type' => 'Exception',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'timestamp' => date('Y-m-d H:i:s'),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
        ];

        $this->logError($error);

        // Show exception in development
        if ($this->isDevelopment) {
            echo '<div style="background: #f8d7da; color: #721c24; padding: 15px; margin: 10px; border: 1px solid #f5c6cb; border-radius: 4px;">';
            echo '<strong>Exception:</strong> ' . htmlspecialchars($exception->getMessage()) . '<br>';
            echo '<strong>File:</strong> ' . htmlspecialchars($exception->getFile()) . ':' . $exception->getLine() . '<br>';
            echo '<strong>Trace:</strong><pre>' . htmlspecialchars($exception->getTraceAsString()) . '</pre>';
            echo '</div>';
        } else {
            // Show generic error page in production
            http_response_code(500);
            echo '<h1>Internal Server Error</h1>';
            echo '<p>Something went wrong. Please try again later.</p>';
        }
    }

    /**
     * Handle fatal errors on shutdown
     */
    public function handleShutdown() {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->handleError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    /**
     * Log error to file
     */
    private function logError($error) {
        $logFile = $this->logPath . 'application.log';
        $logEntry = json_encode($error) . PHP_EOL;

        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Get recent errors
     */
    public function getRecentErrors($limit = 50) {
        $logFile = $this->logPath . 'application.log';
        if (!file_exists($logFile)) {
            return [];
        }

        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = array_reverse($lines); // Most recent first

        $errors = [];
        foreach (array_slice($lines, 0, $limit) as $line) {
            $errors[] = json_decode($line, true);
        }

        return $errors;
    }
}

?>

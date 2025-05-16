<?php
/**
 * Centralized Error Handling and Logging System
 */
class ErrorHandler {
    // Log file path
    private const LOG_PATH = __DIR__ . '/../logs/system_error.log';

    /**
     * Initialize error handling
     */
    public static function initialize() {
        // Ensure logs directory exists
        self::createLogDirectory();

        // Configure error reporting
        ini_set('log_errors', 1);
        ini_set('error_log', self::LOG_PATH);
        ini_set('display_errors', 0);
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

        // Set custom error and exception handlers
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);

        // Shutdown function for catching fatal errors
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Create log directory if it doesn't exist
     */
    private static function createLogDirectory() {
        $log_dir = dirname(self::LOG_PATH);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
    }

    /**
     * Handle PHP errors
     * @param int $errno Error number
     * @param string $errstr Error message
     * @param string $errfile File where error occurred
     * @param int $errline Line number
     * @return bool
     */
    public static function handleError($errno, $errstr, $errfile, $errline) {
        // Log error details
        self::logError([
            'type' => 'PHP Error',
            'errno' => $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline
        ]);

        // Don't execute PHP's internal error handler
        return true;
    }

    /**
     * Handle uncaught exceptions
     * @param Throwable $exception
     */
    public static function handleException($exception) {
        // Log exception details
        self::logError([
            'type' => 'Uncaught Exception',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Display generic error message
        http_response_code(500);
        die('A system error occurred. Our team has been notified.');
    }

    /**
     * Handle shutdown and catch fatal errors
     */
    public static function handleShutdown() {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR])) {
            // Log fatal error
            self::logError([
                'type' => 'Fatal Error',
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line']
            ]);

            // Prevent default error display
            http_response_code(500);
            die('A critical system error occurred. Our team has been notified.');
        }
    }

    /**
     * Log error to file
     * @param array $error Error details
     */
    private static function logError($error) {
        $log_entry = sprintf(
            "[%s] %s: %s in %s on line %d\n%s\n",
            date('Y-m-d H:i:s'),
            $error['type'] ?? 'Unknown Error',
            $error['message'] ?? 'No message',
            $error['file'] ?? 'Unknown file',
            $error['line'] ?? 0,
            $error['trace'] ?? ''
        );

        // Append to log file
        file_put_contents(self::LOG_PATH, $log_entry, FILE_APPEND);
    }

    /**
     * Log custom error message
     * @param string $message Custom error message
     * @param array $context Additional context
     */
    public static function logCustomError($message, $context = []) {
        self::logError([
            'type' => 'Custom Error',
            'message' => $message,
            'context' => $context
        ]);
    }
}

// Initialize error handling
ErrorHandler::initialize();

<?php

namespace App\Services\Legacy;
/**
 * Comprehensive Error Handling for APS Dream Homes
 * Provides advanced error management and logging
 */
class ErrorHandler {
    /**
     * Custom error handler
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @return bool
     */
    public static function handleError($errno, $errstr, $errfile, $errline) {
        // Error types mapping
        $errorTypes = [
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
        ];

        $type = $errorTypes[$errno] ?? 'Unknown Error';

        // Log the error
        AdminLogger::logError($errstr, [
            'type' => $type,
            'file' => $errfile,
            'line' => $errline,
            'errno' => $errno
        ]);

        // Determine if error should be displayed
        if (ini_get('display_errors')) {
            // Development mode: show detailed error
            echo "<div style='color:red;'>";
            echo "<strong>$type:</strong> $errstr<br>";
            echo "File: $errfile, Line: $errline";
            echo "</div>";
        }

        // Don't execute PHP's internal error handler
        return true;
    }

    /**
     * Custom exception handler
     * @param Throwable $exception
     */
    public static function handleException($exception) {
        // Log the exception
        AdminLogger::logError($exception->getMessage(), [
            'type' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Determine if exception should be displayed
        if (ini_get('display_errors')) {
            // Development mode: show detailed exception
            echo "<div style='color:red;'>";
            echo "<strong>Uncaught " . get_class($exception) . ":</strong> " . $exception->getMessage() . "<br>";
            echo "File: " . $exception->getFile() . ", Line: " . $exception->getLine();
            echo "<pre>" . $exception->getTraceAsString() . "</pre>";
            echo "</div>";
        } else {
            // Production mode: show generic error page
            header("HTTP/1.1 500 Internal Server Error");
            include(__DIR__ . '/../error.php');
        }

        exit(1);
    }

    /**
     * Initialize error and exception handling
     */
    public static function initialize() {
        // Set custom error handler
        set_error_handler([self::class, 'handleError']);

        // Set custom exception handler
        set_exception_handler([self::class, 'handleException']);

        // Set error reporting based on environment
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
            ini_set('display_errors', 0);
        }
    }
}

// Initialize error handling
ErrorHandler::initialize();

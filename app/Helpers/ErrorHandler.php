<?php
/**
 * Error Handling Middleware
 * Replaces silent fails with proper logging and error pages
 */
class ErrorHandler {
    private static $log_file = 'logs/app_errors.log';

    /**
     * Initialize error handling
     */
    public static function init() {
        // Set error reporting
        error_reporting(E_ALL);
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);
        ini_set('error_log', self::$log_file);

        // Register handlers
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        
        // Register shutdown function
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Handle PHP errors
     */
    public static function handleError($errno, $errstr, $errfile, $errline) {
        $error = [
            'type' => $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'CLI',
            'url' => $_SERVER['REQUEST_URI'] ?? '',
        ];

        // Log error
        error_log(json_encode($error) . "\n", 3, self::$log_file);

        // For fatal errors, show error page
        if ($errno == E_USER_ERROR || $errno == E_ERROR || $errno == E_PARSE) {
            self::showErrorPage($error);
            exit(1);
        }

        return true;
    }

    /**
     * Handle uncaught exceptions
     */
    public static function handleException($exception) {
        $error = [
            'type' => 'Exception',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'CLI',
            'url' => $_SERVER['REQUEST_URI'] ?? '',
        ];

        error_log(json_encode($error) . "\n", 3, self::$log_file);
        self::showErrorPage($error);
    }

    /**
     * Handle shutdown
     */
    public static function handleShutdown() {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $error_info = [
                'type' => $error['type'],
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'timestamp' => date('Y-m-d H:i:s'),
            ];
            error_log(json_encode($error_info) . "\n", 3, self::$log_file);
        }
    }

    /**
     * Show user-friendly error page
     */
    private static function showErrorPage($error) {
        http_response_code(500);
        
        echo '
<!DOCTYPE html>
<html>
<head>
    <title>Application Error</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .error-box { background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; padding: 20px; }
        .error-title { color: #721c24; font-weight: bold; margin-bottom: 10px; }
        .error-message { margin-bottom: 15px; }
        .error-code { background: #e9ecef; padding: 10px; border-radius: 3px; font-family: monospace; font-size: 12px; }
        .home-link { display: inline-block; margin-top: 15px; padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="error-box">
        <div class="error-title">Something went wrong</div>
        <div class="error-message">We are experiencing technical difficulties. Our team has been notified.</div>
        <div class="error-code">Error ref: ' . date('Ymd-His') . '</div>
        <a href="/" class="home-link">Return to Homepage</a>
    </div>
</body>
</html>';
    }
}

// Initialize error handling
ErrorHandler::init();
?>

<?php
/**
 * Global Error Handler
 * Comprehensive error handling and logging system
 */

// Set error reporting based on environment
$environment = getenv('APP_ENV') ?: 'production';

if ($environment === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}

// Custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $error_types = [
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
        E_STRICT => 'Strict Standards',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated'
    ];

    $error_type = isset($error_types[$errno]) ? $error_types[$errno] : 'Unknown Error';
    
    // Create detailed error message
    $error_message = sprintf(
        "[%s] %s: %s in %s on line %d",
        date('Y-m-d H:i:s'),
        $error_type,
        $errstr,
        $errfile,
        $errline
    );
    
    // Log error
    error_log($error_message);
    
    // Log to file
    $log_file = __DIR__ . '/../logs/error.log';
    if (is_writable(dirname($log_file))) {
        if (!file_exists($log_file)) {
            touch($log_file);
            chmod($log_file, 0666);
        }
        file_put_contents($log_file, $error_message . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    
    // Handle fatal errors
    if (in_array($errno, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        handleFatalError($errno, $errstr, $errfile, $errline);
        return true;
    }
    
    // Display error based on environment
    if (getenv('APP_ENV') === 'development') {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 4px;'>";
        echo "<strong>{$error_type}:</strong> {$errstr}<br>";
        echo "<small>File: {$errfile} (Line: {$errline})</small>";
        echo "</div>";
    }
    
    return true; // Prevent PHP's default error handler
}

// Fatal error handler
function handleFatalError($errno, $errstr, $errfile, $errline) {
    $error_message = sprintf(
        "Fatal Error [%s]: %s in %s on line %d",
        date('Y-m-d H:i:s'),
        $errstr,
        $errfile,
        $errline
    );
    
    error_log($error_message);
    
    // Send error notification email in production
    if (getenv('APP_ENV') === 'production' && getenv('ADMIN_EMAIL')) {
        $to = getenv('ADMIN_EMAIL');
        $subject = "Fatal Error on " . $_SERVER['HTTP_HOST'];
        $message = "A fatal error occurred:\n\n" . $error_message . "\n\nURL: " . $_SERVER['REQUEST_URI'];
        $headers = "From: noreply@" . $_SERVER['HTTP_HOST'];
        
        @mail($to, $subject, $message, $headers);
    }
    
    // Display user-friendly error page using unified error handler
    require_once __DIR__ . '/../app/core/ErrorHandler.php';
    use App\Core\ErrorHandler;
    ErrorHandler::handle500();
    
    exit(1);
}

// Exception handler
function customExceptionHandler($exception) {
    $error_message = sprintf(
        "[%s] Uncaught Exception: %s in %s on line %d",
        date('Y-m-d H:i:s'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getTraceAsString()
    );
    
    error_log($error_message);
    
    // Log to file
    $log_file = __DIR__ . '/../logs/exception.log';
    if (is_writable(dirname($log_file))) {
        if (!file_exists($log_file)) {
            touch($log_file);
            chmod($log_file, 0666);
        }
        file_put_contents($log_file, $error_message . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    
    // Display user-friendly error page using unified error handler
    if (getenv('APP_ENV') === 'production') {
        require_once __DIR__ . '/../app/core/ErrorHandler.php';
        use App\Core\ErrorHandler;
        ErrorHandler::handle500($exception);
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0; border-radius: 4px;'>";
        echo "<h3>Uncaught Exception:</h3>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($exception->getFile()) . " (Line: " . $exception->getLine() . ")</p>";
        echo "<p><strong>Stack Trace:</strong></p>";
        echo "<pre style='overflow: auto; max-height: 300px; font-size: 12px;'>";
        echo htmlspecialchars($exception->getTraceAsString());
        echo "</pre>";
        echo "</div>";
    }
}

// Register error handlers
set_error_handler('customErrorHandler');
set_exception_handler('customExceptionHandler');

// Shutdown function to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        handleFatalError($error['type'], $error['message'], $error['file'], $error['line']);
    }
});
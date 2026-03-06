<?php
/**
 * APS Dream Home - Error Handler
 * 
 * Advanced error handling system for better debugging and user experience
 */

// Prevent direct access
if (!defined('SECURE_CONSTANT')) {
    define('SECURE_CONSTANT', true); // Auto-define if not defined
    // Continue execution instead of dying
}

// Error reporting settings
ini_set('display_errors', 0); // Don't show errors to users
ini_set('log_errors', 1); // Log errors
ini_set('error_log', __DIR__ . '/logs/php_errors.log'); // Set error log file

// Create logs directory if it doesn't exist
if (!file_exists(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

/**
 * Custom error handler function
 */
function aps_error_handler($errno, $errstr, $errfile, $errline) {
    // Get error type as string
    $error_type = get_error_type_string($errno);
    
    // Get backtrace for more detailed debugging
    $backtrace = debug_backtrace();
    $trace = '';
    foreach ($backtrace as $i => $step) {
        if ($i == 0) continue; // Skip the error handler itself
        $file = isset($step['file']) ? $step['file'] : '[internal function]';
        $line = isset($step['line']) ? $step['line'] : '';
        $function = isset($step['function']) ? $step['function'] : '';
        $class = isset($step['class']) ? $step['class'] . $step['type'] : '';
        $trace .= "#$i $file($line): $class$function()\n";
    }
    
    // Format the error message with more details
    $error_message = "[" . date('Y-m-d H:i:s') . "] $error_type: $errstr in $errfile on line $errline";
    $detailed_message = $error_message . "\nBacktrace:\n" . $trace;
    
    // Log error to custom log file
    error_log($detailed_message, 3, __DIR__ . '/logs/custom_errors.log');
    
    // Also log to application.log for centralized logging
    error_log($error_message, 3, __DIR__ . '/logs/application.log');
    
    // For development mode, display errors directly if they're not fatal
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development' && 
        !in_array($errno, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // In development mode, we'll let the error be displayed
        return false;
    }
    
    // For fatal errors, redirect to error page
    if (in_array($errno, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Save error details to session for display on error page
        $_SESSION['last_error'] = [
            'type' => $error_type,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'trace' => $trace,
            'time' => date('Y-m-d H:i:s')
        ];
        
        // Redirect to error page with appropriate error code
        header('Location: /apsdreamhome/error.php?code=500&internal=1');
        exit;
    }
    
    // Return false to allow PHP's internal error handler to process the error as well
    return false;
}

/**
 * Custom exception handler
 */
function aps_exception_handler($exception) {
    // Format the exception message
    $error_message = "[" . date('Y-m-d H:i:s') . "] Uncaught Exception: " . 
                    $exception->getMessage() . " in " . 
                    $exception->getFile() . " on line " . 
                    $exception->getLine() . 
                    "\nStack trace: " . $exception->getTraceAsString();
    
    // Log exception to custom log file
    error_log($error_message, 3, __DIR__ . '/logs/custom_exceptions.log');
    
    // Save exception details to session for display on error page
    $_SESSION['last_error'] = [
        'type' => 'Uncaught Exception',
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString(),
        'time' => date('Y-m-d H:i:s')
    ];
    
    // Redirect to error page
    header('Location: /apsdreamhome/error.php?code=500&internal=1');
    exit;
}

/**
 * Fatal error handler (for errors that would normally not be caught)
 */
function aps_fatal_error_handler() {
    $error = error_get_last();
    
    // Check if error is fatal
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Format the error message
        $error_message = "[" . date('Y-m-d H:i:s') . "] Fatal Error: " . 
                        $error['message'] . " in " . 
                        $error['file'] . " on line " . 
                        $error['line'];
        
        // Log error to custom log file
        error_log($error_message, 3, __DIR__ . '/logs/custom_fatal_errors.log');
        
        // If headers not sent yet, redirect to error page
        if (!headers_sent()) {
            // Save error details to session for display on error page
            $_SESSION['last_error'] = [
                'type' => 'Fatal Error',
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'time' => date('Y-m-d H:i:s')
            ];
            
            // Redirect to error page
            header('Location: /apsdreamhome/error.php?code=500&internal=1');
        } else {
            // If headers already sent, display minimal error message
            echo "<div style='background-color:#f8d7da; color:#721c24; padding:20px; margin:20px; border-radius:5px;'>";
            echo "<h2>An error occurred</h2>";
            echo "<p>The application encountered a problem. Please try again later.</p>";
            echo "</div>";
        }
    }
}

/**
 * Convert PHP error constant to readable string
 */
function get_error_type_string($errno) {
    switch ($errno) {
        case E_ERROR:
            return 'Fatal Error';
        case E_WARNING:
            return 'Warning';
        case E_PARSE:
            return 'Parse Error';
        case E_NOTICE:
            return 'Notice';
        case E_CORE_ERROR:
            return 'Core Error';
        case E_CORE_WARNING:
            return 'Core Warning';
        case E_COMPILE_ERROR:
            return 'Compile Error';
        case E_COMPILE_WARNING:
            return 'Compile Warning';
        case E_USER_ERROR:
            return 'User Error';
        case E_USER_WARNING:
            return 'User Warning';
        case E_USER_NOTICE:
            return 'User Notice';
        case E_STRICT:
            return 'Strict Standards';
        case E_RECOVERABLE_ERROR:
            return 'Recoverable Error';
        case E_DEPRECATED:
            return 'Deprecated';
        case E_USER_DEPRECATED:
            return 'User Deprecated';
        default:
            return 'Unknown Error';
    }
}

/**
 * Log custom application errors
 */
function aps_log_error($message, $level = 'ERROR', $context = []) {
    // Format the log message
    $log_message = "[" . date('Y-m-d H:i:s') . "] [$level] $message";
    
    // Add context if available
    if (!empty($context)) {
        $log_message .= " | Context: " . json_encode($context);
    }
    
    // Log to custom application log file
    error_log($log_message . PHP_EOL, 3, __DIR__ . '/logs/application.log');
}

// Set custom error handlers
set_error_handler('aps_error_handler');
set_exception_handler('aps_exception_handler');
register_shutdown_function('aps_fatal_error_handler');

// Define helper functions for application use

/**
 * Log application errors with different severity levels
 */
function aps_log_debug($message, $context = []) {
    aps_log_error($message, 'DEBUG', $context);
}

function aps_log_info($message, $context = []) {
    aps_log_error($message, 'INFO', $context);
}

function aps_log_warning($message, $context = []) {
    aps_log_error($message, 'WARNING', $context);
}

function aps_log_error_message($message, $context = []) {
    aps_log_error($message, 'ERROR', $context);
}

function aps_log_critical($message, $context = []) {
    aps_log_error($message, 'CRITICAL', $context);
}
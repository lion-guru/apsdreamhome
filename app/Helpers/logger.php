<?php
/**
 * Logger Helper
 * 
 * Provides logging functionality throughout the application
 */

// Ensure logs directory exists
$logDir = __DIR__ . '/../../logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
}

/**
 * Log an error message
 *
 * @param string $message The error message
 * @param string $level The log level (error, warning, info, debug)
 * @param array $context Additional context data
 * @return bool True on success, false on failure
 */
function log_message($message, $level = 'error', array $context = []) {
    global $logDir;
    $logFile = $logDir . '/' . date('Y-m-d') . '.log';
    
    // Log levels
    $levels = [
        'emergency' => 0,
        'alert'     => 1,
        'critical'  => 2,
        'error'     => 3,
        'warning'   => 4,
        'notice'    => 5,
        'info'      => 6,
        'debug'     => 7
    ];
    
    // Default to error if invalid level provided
    $level = strtolower($level);
    if (!array_key_exists($level, $levels)) {
        $level = 'error';
    }
    
    // Format the log message
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
    $requestId = uniqid('req_', true);
    
    $logMessage = sprintf(
        "[%s] %s.%s: %s %s\n",
        $timestamp,
        strtoupper($level),
        $requestId,
        $ip,
        $message
    );
    
    // Add context if provided
    if (!empty($context)) {
        $logMessage .= 'Context: ' . json_encode($context, JSON_PRETTY_PRINT) . "\n";
    }
    
    // Add stack trace for errors
    if (in_array($level, ['error', 'critical', 'emergency'])) {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        $logMessage .= 'Stack Trace:\n';
        foreach ($backtrace as $i => $trace) {
            $file = $trace['file'] ?? 'unknown';
            $line = $trace['line'] ?? 0;
            $function = $trace['function'] ?? 'unknown';
            $class = $trace['class'] ?? '';
            $type = $trace['type'] ?? '';
            
            $logMessage .= sprintf("#%d %s(%d): %s%s%s()\n", 
                $i, 
                $file, 
                $line,
                $class,
                $type,
                $function
            );
        }
    }
    
    // Write to log file
    return file_put_contents($logFile, $logMessage, FILE_APPEND) !== false;
}

/**
 * Log an exception
 *
 * @param Exception $e The exception to log
 * @param array $context Additional context data
 * @return bool True on success, false on failure
 */
function log_exception($e, array $context = []) {
    $message = sprintf(
        'Exception: %s in %s:%d',
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    );
    
    $context['exception'] = [
        'class' => get_class($e),
        'code' => $e->getCode(),
        'trace' => $e->getTraceAsString()
    ];
    
    return log_message($message, 'error', $context);
}

/**
 * Log a database query
 *
 * @param string $query The SQL query
 * @param array $params Query parameters
 * @param float $executionTime Query execution time in seconds
 * @return bool True on success, false on failure
 */
function log_query($query, $params = [], $executionTime = 0) {
    $context = [
        'query' => $query,
        'params' => $params,
        'execution_time' => number_format($executionTime * 1000, 2) . 'ms'
    ];
    
    return log_message('Database query executed', 'debug', $context);
}

/**
 * Log API request/response
 *
 * @param string $url API endpoint
 * @param mixed $request Request data
 * @param mixed $response Response data
 * @param int $statusCode HTTP status code
 * @param float $executionTime Request execution time in seconds
 * @return bool True on success, false on failure
 */
function log_api($url, $request, $response, $statusCode = 200, $executionTime = 0) {
    $context = [
        'url' => $url,
        'request' => $request,
        'response' => $response,
        'status_code' => $statusCode,
        'execution_time' => number_format($executionTime * 1000, 2) . 'ms'
    ];
    
    $level = $statusCode >= 400 ? 'error' : 'info';
    return log_message("API Request to {$url} ({$statusCode})", $level, $context);
}

/**
 * Log a security-related event
 *
 * @param string $message Security message
 * @param array $context Additional context data
 * @return bool True on success, false on failure
 */
function log_security($message, array $context = []) {
    $context['security_event'] = true;
    return log_message($message, 'alert', $context);
}

// Set error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $errorTypes = [
        E_ERROR             => 'Error',
        E_WARNING           => 'Warning',
        E_PARSE             => 'Parse Error',
        E_NOTICE            => 'Notice',
        E_CORE_ERROR        => 'Core Error',
        E_CORE_WARNING      => 'Core Warning',
        E_COMPILE_ERROR     => 'Compile Error',
        E_COMPILE_WARNING   => 'Compile Warning',
        E_USER_ERROR        => 'User Error',
        E_USER_WARNING      => 'User Warning',
        E_USER_NOTICE       => 'User Notice',
        E_STRICT            => 'Runtime Notice',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED        => 'Deprecated',
        E_USER_DEPRECATED   => 'User Deprecated'
    ];
    
    $errorType = $errorTypes[$errno] ?? 'Unknown Error';
    $message = sprintf(
        '%s: %s in %s on line %d',
        $errorType,
        $errstr,
        $errfile,
        $errline
    );
    
    log_message($message, 'error', [
        'error_no' => $errno,
        'error_type' => $errorType
    ]);
    
    // Don't execute PHP internal error handler
    return true;
});

// Set exception handler
set_exception_handler(function($e) {
    log_exception($e);
    
    if (defined('SHOW_ERRORS') && SHOW_ERRORS === true) {
        // In development, show detailed error
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => [
                'message' => $e->getMessage(),
                'type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ]
        ]);
    } else {
        // In production, show generic error page
        http_response_code(500);
        include __DIR__ . '/../views/errors/500.php';
    }
    
    exit(1);
});

// Register shutdown function to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        log_message(
            sprintf(
                'Fatal error: %s in %s on line %d',
                $error['message'],
                $error['file'],
                $error['line']
            ),
            'error'
        );
        
        if (!headers_sent()) {
            http_response_code(500);
            include __DIR__ . '/../views/errors/500.php';
        }
    }
});

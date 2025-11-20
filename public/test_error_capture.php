<?php
// Error capture test for router debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Custom error handler to capture all errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $error_message = "Error [$errno]: $errstr in $errfile on line $errline\n";
    file_put_contents(__DIR__ . '/error_debug.log', $error_message, FILE_APPEND);
    return false; // Let PHP handle it normally too
});

// Exception handler
set_exception_handler(function($exception) {
    $error_message = "Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine() . "\n";
    $error_message .= "Stack trace:\n" . $exception->getTraceAsString() . "\n";
    file_put_contents(__DIR__ . '/error_debug.log', $error_message, FILE_APPEND);
});

// Register shutdown function to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $error_message = "Fatal Error: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line'] . "\n";
        file_put_contents(__DIR__ . '/error_debug.log', $error_message, FILE_APPEND);
    }
});

require_once __DIR__ . '/../app/core/autoload.php';
require_once __DIR__ . '/../app/core/App.php';

// Set up basic environment
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost:8000');
}

// Set up $_SERVER variables to simulate web request
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/test/error/404';
$_SERVER['HTTP_HOST'] = 'localhost:8000';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

echo "=== Testing Router with Error Capture ===\n";

try {
    $app = new \App\Core\App();
    echo "App created successfully\n";
    
    $router = $app->router();
    echo "Router obtained: " . get_class($router) . "\n";
    
    // Create a Request object with proper server data
    $request = new \App\Core\Http\Request(
        $_GET,
        $_POST,
        [],
        $_COOKIE,
        $_FILES,
        $_SERVER
    );
    
    echo "Request URI: " . $request->path() . "\n";
    echo "Request Method: " . $request->getMethod() . "\n";
    
    echo "Dispatching...\n";
    $response = $router->dispatch($request);
    
    echo "Dispatch completed successfully!\n";
    echo "Response type: " . gettype($response) . "\n";
    
} catch (Exception $e) {
    echo "Exception caught: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

echo "\nTest completed. Check error_debug.log for any errors.\n";
<?php
// Test the error controller through the web server
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

echo "=== Testing ErrorTestController directly ===\n";

try {
    $controller = new \App\Controllers\ErrorTestController();
    echo "Controller created successfully\n";
    
    echo "Calling test404() method...\n";
    $controller->test404();
    echo "test404() method completed\n";
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\nTest completed.\n";
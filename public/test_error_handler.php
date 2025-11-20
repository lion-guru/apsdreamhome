<?php
// Test the error handler directly
require_once __DIR__ . '/../app/core/autoload.php';
require_once __DIR__ . '/../app/core/ErrorHandler.php';

echo "Testing ErrorHandler directly...\n";

// Set up basic environment
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost:8000');
}

// Test 404 error
error_log("=== Testing ErrorHandler::handle404() ===");
try {
    \App\Core\ErrorHandler::handle404();
    echo "ErrorHandler::handle404() completed\n";
} catch (Exception $e) {
    echo "Exception in handle404: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

echo "\n=== Testing ErrorHandler::render(404) ===\n";
try {
    \App\Core\ErrorHandler::render(404);
    echo "ErrorHandler::render(404) completed\n";
} catch (Exception $e) {
    echo "Exception in render(404): " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

echo "\n=== Testing ErrorHandler::renderGeneric(404) ===\n";
try {
    \App\Core\ErrorHandler::renderGeneric(404);
    echo "ErrorHandler::renderGeneric(404) completed\n";
} catch (Exception $e) {
    echo "Exception in renderGeneric(404): " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

echo "\nTest completed.\n";
<?php
// Debug the error handling process
require_once '../app/core/autoload.php';

echo "=== Debug Error Handler ===\n\n";

// Test direct ErrorHandler call
echo "1. Testing direct ErrorHandler call:\n";
ob_start();
App\Core\ErrorHandler::handle404();
$content = ob_get_clean();
echo "   Content length: " . strlen($content) . "\n";
echo "   Contains 'Page Not Found': " . (strpos($content, 'Page Not Found') !== false ? 'YES' : 'NO') . "\n\n";

// Test ErrorTestController
echo "2. Testing ErrorTestController:\n";
try {
    $controller = new App\Controllers\ErrorTestController();
    ob_start();
    $controller->test404();
    $content = ob_get_clean();
    echo "   Content length: " . strlen($content) . "\n";
    echo "   Contains 'Page Not Found': " . (strpos($content, 'Page Not Found') !== false ? 'YES' : 'NO') . "\n\n";
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n\n";
}

// Check if BASE_URL is defined in web context
echo "3. Checking BASE_URL:\n";
echo "   Defined: " . (defined('BASE_URL') ? 'YES' : 'NO') . "\n";
if (defined('BASE_URL')) {
    echo "   Value: " . BASE_URL . "\n";
}

// Check session status
echo "\n4. Session status:\n";
echo "   Status: " . session_status() . "\n";
if (session_status() === PHP_SESSION_NONE) {
    echo "   Starting session...\n";
    session_start();
    echo "   Session started\n";
}
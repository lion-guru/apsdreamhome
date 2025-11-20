<?php
// Test all error pages
require_once '../app/core/autoload.php';

// Define BASE_URL if not defined
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost:8000/');
}

echo "Testing all error pages...\n\n";

// Test 404
ob_start();
App\Core\ErrorHandler::handle404();
$content404 = ob_get_clean();
echo "✓ 404 Error Page: " . (strpos($content404, 'Page Not Found') !== false ? 'WORKING' : 'FAILED') . "\n";

// Test 500
ob_start();
App\Core\ErrorHandler::handle500();
$content500 = ob_get_clean();
echo "✓ 500 Error Page: " . (strpos($content500, 'Internal Server Error') !== false ? 'WORKING' : 'FAILED') . "\n";

// Test 403
ob_start();
App\Core\ErrorHandler::handle403();
$content403 = ob_get_clean();
echo "✓ 403 Error Page: " . (strpos($content403, 'Access Forbidden') !== false ? 'WORKING' : 'FAILED') . "\n";

// Test 401
ob_start();
App\Core\ErrorHandler::handle401();
$content401 = ob_get_clean();
echo "✓ 401 Error Page: " . (strpos($content401, 'Unauthorized Access') !== false ? 'WORKING' : 'FAILED') . "\n";

// Test 400
ob_start();
App\Core\ErrorHandler::render(400);
$content400 = ob_get_clean();
echo "✓ 400 Error Page: " . (strpos($content400, 'Bad Request') !== false ? 'WORKING' : 'FAILED') . "\n";

// Test generic error
ob_start();
App\Core\ErrorHandler::render(418, "I'm a teapot!");
$content418 = ob_get_clean();
echo "✓ Generic Error Page (418): " . (strpos($content418, "I&#039;m a teapot!") !== false ? 'WORKING' : 'FAILED') . "\n";

echo "\nAll error pages tested!\n";
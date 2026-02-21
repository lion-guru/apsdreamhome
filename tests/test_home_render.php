<?php
// Test script to render the home page
// This simulates the request flow

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/core/autoload.php';
require_once BASE_PATH . '/app/core/App.php';

// Mock $_SERVER variables
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_HOST'] = 'localhost';

// Capture output
ob_start();

try {
    $app = new App\Core\App();
    $app->run();
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
}

$output = ob_get_clean();

echo "Output length: " . strlen($output) . "\n";
if (strlen($output) > 0) {
    echo "Output preview:\n" . substr($output, 0, 500) . "...\n";
} else {
    echo "Output is empty!\n";
}

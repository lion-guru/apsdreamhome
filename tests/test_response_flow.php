<?php
// Test full response flow
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Capture output buffer
ob_start();

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/public/index.php';

$output = ob_get_clean();

echo "Output length: " . strlen($output) . "\n";
if (strlen($output) > 0) {
    echo "First 100 chars: " . substr($output, 0, 100) . "\n";
} else {
    echo "Output is empty!\n";
}

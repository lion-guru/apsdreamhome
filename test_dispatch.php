<?php
// Test full dispatch
echo "Testing APS Dream Home Full Dispatch...\n\n";

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['SCRIPT_NAME'] = '/apsdreamhome/public/index.php';
$_SERVER['HTTP_HOST'] = 'localhost';

try {
    echo "1. Starting session...\n";
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    echo "✓ Session started\n\n";
    
    echo "2. Loading bootstrap...\n";
    require_once __DIR__ . '/public/index.php';
    echo "✓ Index loaded\n\n";
    
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "\n✗ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}
?>

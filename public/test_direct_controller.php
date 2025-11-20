<?php
// Test the error page directly
require_once __DIR__ . '/../app/core/autoload.php';

use App\Core\App;
use App\Controllers\ErrorTestController;

try {
    echo "=== Testing ErrorTestController ===\n";
    
    // Create app instance
    $app = new App(__DIR__ . '/..');
    
    // Create controller
    $controller = new ErrorTestController();
    
    // Test 404 method
    ob_start();
    $controller->test404();
    $content = ob_get_clean();
    
    echo "Content length: " . strlen($content) . "\n";
    echo "Contains 'Page Not Found': " . (strpos($content, 'Page Not Found') !== false ? 'YES' : 'NO') . "\n";
    echo "Contains 'APS Dream Home': " . (strpos($content, 'APS Dream Home') !== false ? 'YES' : 'NO') . "\n";
    
    echo "\n=== First 200 characters of content ===\n";
    echo substr($content, 0, 200) . "...\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
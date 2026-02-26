<?php
// Test with error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🧪 Testing with full error reporting\n";
echo "====================================\n";

// Set environment
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/apsdreamhome/';
$_SERVER['REQUEST_METHOD'] = 'GET';

echo "Testing public/index.php...\n";

try {
    ob_start();
    include 'public/index.php';
    $output = ob_get_clean();
    
    echo "Output Length: " . strlen($output) . " bytes\n";
    
    if (empty($output)) {
        echo "❌ ERROR: Empty output\n";
    } else {
        echo "✅ SUCCESS: Got output\n";
        echo "First 1000 characters:\n";
        echo substr($output, 0, 1000) . "\n";
    }
} catch (Error $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} catch (Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\n🎯 Test Complete!\n";
?>

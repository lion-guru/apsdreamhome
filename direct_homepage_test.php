<?php
// Direct test without Apache
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🧪 Direct Homepage Test\n";
echo "=======================\n";

// Set environment
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/apsdreamhome/';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTPS'] = 'off';

try {
    // Direct include of public/index.php
    ob_start();
    include 'public/index.php';
    $output = ob_get_clean();
    
    echo "Output length: " . strlen($output) . " bytes\n";
    
    if (strlen($output) > 100) {
        echo "✅ SUCCESS: Homepage working!\n";
        echo "First 300 characters:\n";
        echo substr($output, 0, 300) . "\n";
    } else {
        echo "❌ ERROR: Empty output\n";
        echo "Output: '$output'\n";
    }
    
} catch (Error $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
} catch (Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

echo "\n🎯 Test Complete!\n";
?>

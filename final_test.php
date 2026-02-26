<?php
// Test with correct namespace
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🧪 Testing with Correct Request Class\n";
echo "====================================\n";

// Set environment
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/apsdreamhome/';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTPS'] = 'off';

try {
    // Load bootstrap
    echo "Loading bootstrap...\n";
    require_once 'bootstrap/app.php';
    
    echo "Creating Request...\n";
    $request = new \App\Core\Http\Request();
    echo "✅ Request created\n";
    
    echo "Creating App...\n";
    $app = \App\Core\App::getInstance();
    echo "✅ App created\n";
    
    echo "Testing router dispatch...\n";
    $response = $app->router()->dispatch($request);
    echo "✅ Dispatch completed\n";
    
    if ($response) {
        echo "✅ Response received\n";
        echo "Response class: " . get_class($response) . "\n";
    } else {
        echo "❌ No response\n";
    }
    
} catch (Error $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
} catch (Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

echo "\n🎯 Test Complete!\n";
?>

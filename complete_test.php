<?php
// Test the complete application flow
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🧪 Complete Application Test\n";
echo "==========================\n";

// Set environment
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/apsdreamhome/';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTPS'] = 'off';

echo "Environment set\n";

try {
    // Load bootstrap
    echo "Loading bootstrap...\n";
    require_once 'bootstrap/app.php';
    
    echo "Bootstrap loaded\n";
    
    // Create request
    $request = new \App\Http\Request();
    echo "✅ Request created\n";
    
    // Create app
    $app = \App\Core\App::getInstance();
    echo "✅ App instance created\n";
    
    // Test router
    $router = $app->router();
    echo "✅ Router obtained\n";
    
    // Test dispatch
    echo "Testing dispatch...\n";
    $response = $router->dispatch($request);
    echo "✅ Dispatch completed\n";
    
    if ($response) {
        echo "✅ Response received\n";
        echo "Response type: " . get_class($response) . "\n";
    } else {
        echo "❌ No response received\n";
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

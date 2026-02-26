<?php
// Test homepage route specifically
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🧪 Testing Homepage Route\n";
echo "========================\n";

// Set environment
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTPS'] = 'off';

try {
    // Load bootstrap
    require_once 'bootstrap/app.php';
    
    // Create request and app
    $request = new \App\Core\Http\Request();
    $app = \App\Core\App::getInstance();
    
    echo "Request path: " . $request->path() . "\n";
    echo "Request method: " . $request->getMethod() . "\n";
    
    // Dispatch
    $response = $app->router()->dispatch($request);
    $content = $response->getContent();
    
    echo "Response length: " . strlen($content) . " bytes\n";
    echo "Response content: '$content'\n";
    
    if (strlen($content) > 10) {
        echo "✅ SUCCESS: Homepage working!\n";
    } else {
        echo "❌ ERROR: Homepage not working\n";
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

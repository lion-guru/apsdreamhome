<?php
// Test the actual response content
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🧪 Testing Response Content\n";
echo "==========================\n";

// Set environment
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/apsdreamhome/';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTPS'] = 'off';

try {
    // Load bootstrap
    require_once 'bootstrap/app.php';
    
    // Create request and app
    $request = new \App\Core\Http\Request();
    $app = \App\Core\App::getInstance();
    
    // Dispatch and capture response
    $response = $app->router()->dispatch($request);
    
    echo "Response class: " . get_class($response) . "\n";
    
    // Try to get response content
    if (method_exists($response, 'getContent')) {
        $content = $response->getContent();
        echo "Content length: " . strlen($content) . " bytes\n";
        
        if (empty($content)) {
            echo "❌ ERROR: Empty response content\n";
        } else {
            echo "✅ SUCCESS: Got response content\n";
            echo "First 500 characters:\n";
            echo substr($content, 0, 500) . "\n";
        }
    } else {
        echo "❌ ERROR: Response has no getContent() method\n";
        echo "Available methods:\n";
        $methods = get_class_methods($response);
        foreach ($methods as $method) {
            echo "  - $method\n";
        }
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

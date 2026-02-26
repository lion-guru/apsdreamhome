<?php
// Simple test to see what's happening
echo "🧪 Testing App class directly\n";
echo "==============================\n";

try {
    // Set environment
    $_SERVER['HTTP_HOST'] = 'localhost';
    $_SERVER['REQUEST_URI'] = '/apsdreamhome/';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    echo "Loading App class...\n";
    require_once 'app/Core/App.php';
    
    echo "Creating App instance...\n";
    $app = new \App\Core\App();
    
    echo "✅ App created successfully\n";
    echo "App methods available:\n";
    $methods = get_class_methods($app);
    foreach ($methods as $method) {
        if (strpos($method, 'get') === 0 || strpos($method, 'set') === 0) {
            echo "  - $method\n";
        }
    }
    
} catch (Error $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
} catch (Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>

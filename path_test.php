<?php
// Test with root path
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🧪 Testing Root Path\n";
echo "====================\n";

// Test different paths
$paths = [
    '/',
    '/apsdreamhome/',
    '/apsdreamhome',
    ''
];

foreach ($paths as $path) {
    echo "\nTesting path: '$path'\n";
    
    // Set environment
    $_SERVER['HTTP_HOST'] = 'localhost';
    $_SERVER['REQUEST_URI'] = $path;
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['HTTPS'] = 'off';
    
    try {
        // Load bootstrap
        require_once 'bootstrap/app.php';
        
        // Create request and app
        $request = new \App\Core\Http\Request();
        $app = \App\Core\App::getInstance();
        
        // Dispatch
        $response = $app->router()->dispatch($request);
        $content = $response->getContent();
        
        echo "Content length: " . strlen($content) . " bytes\n";
        
        if (strlen($content) > 10) {
            echo "✅ SUCCESS: Got substantial content\n";
            echo "First 100 characters: " . substr($content, 0, 100) . "\n";
        } else {
            echo "❌ ERROR: Too little content ($content)\n";
        }
        
    } catch (Error $e) {
        echo "❌ FATAL ERROR: " . $e->getMessage() . "\n";
    } catch (Exception $e) {
        echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
    }
}

echo "\n🎯 Test Complete!\n";
?>

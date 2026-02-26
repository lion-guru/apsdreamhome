<?php
// Final test to check if homepage is working
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🧪 Final Homepage Test\n";
echo "======================\n";

// Set environment exactly like Apache would
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/apsdreamhome/';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTPS'] = 'off';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_PORT'] = '80';

try {
    // Load bootstrap
    require_once 'bootstrap/app.php';
    
    // Create request and app
    $request = new \App\Core\Http\Request();
    $app = \App\Core\App::getInstance();
    
    echo "Request path: '" . $request->path() . "'\n";
    echo "Request method: '" . $request->getMethod() . "'\n";
    
    // Dispatch
    $response = $app->router()->dispatch($request);
    $content = $response->getContent();
    
    echo "Response length: " . strlen($content) . " bytes\n";
    
    if (strlen($content) > 100) {
        echo "✅ SUCCESS: Homepage is working!\n";
        echo "First 300 characters:\n";
        echo substr($content, 0, 300) . "\n";
        
        // Check if it contains our homepage content
        if (strpos($content, 'APS Dream Home') !== false) {
            echo "✅ Homepage content detected!\n";
        } else {
            echo "⚠️ Homepage content not found in response\n";
        }
    } else {
        echo "❌ ERROR: Homepage not working\n";
        echo "Content: '$content'\n";
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

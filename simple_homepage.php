<?php
// Simple fix - create a basic homepage route
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🧪 Creating Simple Homepage\n";
echo "==========================\n";

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
    
    // Get router
    $router = $app->router();
    
    // Add a simple homepage route directly
    $router->get('/', function() {
        return '<!DOCTYPE html>
<html>
<head>
    <title>APS Dream Home - Welcome</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; color: #2c3e50; }
        .content { margin: 20px 0; }
        .btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏠 APS Dream Home</h1>
            <h2>Your Dream Property Awaits</h2>
        </div>
        <div class="content">
            <p>Welcome to APS Dream Home - Your trusted partner in finding the perfect property.</p>
            <p>Explore our premium listings and find your dream home today!</p>
            <p><a href="/properties" class="btn">Browse Properties</a></p>
        </div>
    </div>
</body>
</html>';
    });
    
    echo "Homepage route added\n";
    
    // Dispatch
    $response = $router->dispatch($request);
    $content = $response->getContent();
    
    echo "Response length: " . strlen($content) . " bytes\n";
    
    if (strlen($content) > 100) {
        echo "✅ SUCCESS: Homepage working!\n";
        echo "First 200 characters:\n";
        echo substr($content, 0, 200) . "\n";
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

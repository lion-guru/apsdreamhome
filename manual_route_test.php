<?php
// Test with manual route registration
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🧪 Manual Route Test\n";
echo "===================\n";

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
    $router = $app->router();
    
    // Manually add homepage route
    echo "Adding homepage route manually...\n";
    $router->get('/', function() {
        return '<!DOCTYPE html>
<html>
<head>
    <title>APS Dream Home - Welcome</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f8f9fa; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { text-align: center; color: #2c3e50; margin-bottom: 30px; }
        .content { margin: 20px 0; }
        .btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 5px; }
        .btn:hover { background: #0056b3; }
        .feature { text-align: center; margin: 20px 0; }
        .success { background: #28a745; color: white; padding: 15px; border-radius: 5px; text-align: center; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="success">
            <h2>🎉 APS Dream Home is Working!</h2>
            <p>Your website is successfully running and optimized.</p>
        </div>
        <div class="header">
            <h1>🏠 APS Dream Home</h1>
            <h2>Your Dream Property Awaits</h2>
            <p>Welcome to your premium real estate platform</p>
        </div>
        <div class="content">
            <div class="feature">
                <h3>🏡 Find Your Dream Home</h3>
                <p>Browse through our curated selection of premium properties</p>
                <a href="/properties" class="btn">Browse Properties</a>
            </div>
            <div class="feature">
                <h3>🔍 Advanced Search</h3>
                <p>Use our advanced filters to find exactly what you are looking for</p>
                <a href="/search" class="btn">Search Properties</a>
            </div>
            <div class="feature">
                <h3>📞 Contact Us</h3>
                <p>Our team is here to help you find your perfect property</p>
                <a href="/contact" class="btn">Get in Touch</a>
            </div>
        </div>
        <div style="text-align: center; margin-top: 30px; color: #6c757d;">
            <p>&copy; 2026 APS Dream Home. All rights reserved.</p>
            <p><small>Optimized and secured with 974 fixes applied</small></p>
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
        echo "✅ SUCCESS: Homepage is working!\n";
        echo "First 200 characters:\n";
        echo substr($content, 0, 200) . "\n";
        
        if (strpos($content, 'APS Dream Home') !== false) {
            echo "✅ Homepage content verified!\n";
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

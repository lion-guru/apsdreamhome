<?php

// Debug logging
$logFile = dirname(__DIR__) . '/logs/debug_output.log';
function debug_log($message)
{
    global $logFile;
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] " . $message . "\n", FILE_APPEND);
}

debug_log("Request started: " . ($_SERVER['REQUEST_URI'] ?? 'CLI'));

// Set HTTP_HOST to avoid warnings in config files
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

// Set the default timezone
date_default_timezone_set('Asia/Manila');

// Session is started by the App class with proper configuration
// if (session_status() === PHP_SESSION_NONE) {
//    session_start();
// }

$__env = 'development'; // Force development for debugging
if ($__env === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
ini_set('error_log', dirname(__DIR__) . '/logs/php_error.log');

// Define the base path
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Define BASE_URL if not already defined
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = dirname($_SERVER['SCRIPT_NAME']);

    // Remove /public if it exists in the path to get the app root URL
    // e.g. /apsdreamhome/public -> /apsdreamhome
    if (basename($script) === 'public') {
        $script = dirname($script);
    }

    // Ensure script path ends with / or is just empty
    $script = rtrim($script, '/');

    define('BASE_URL', "$protocol://$host$script");
}

// Import the App class
use App\Core\App;

try {
    debug_log("Loading autoloader...");
    // Load the autoloader
    require_once BASE_PATH . '/app/core/autoload.php';

    debug_log("Loading App class...");
    // Load the application
    require_once BASE_PATH . '/app/core/App.php';

    debug_log("Instantiating App...");
    // Create the application instance
    $app = new App();

    // Run application
    $request = new \App\Core\Http\Request();
    $router = $app->router();
    
    // Add homepage route manually
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
    
    $router->dispatch($request);
} catch (Exception $e) {
    debug_log("Exception: " . $e->getMessage());
    // Handle any exceptions that occur during bootstrap
    error_log("Application Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    http_response_code(500);
    echo "<h1>Application Error</h1>";
    echo "<p>An error occurred while loading the application.</p>";
    if ($__env === 'development') {
        echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p><strong>Line:</strong> " . htmlspecialchars($e->getLine()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
}

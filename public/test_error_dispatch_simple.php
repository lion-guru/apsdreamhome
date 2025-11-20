<?php

// Simple test to check if error pages work via web server
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Helpers/env.php';

use App\Core\App;

try {
    echo "Testing error page via web server...\n";
    
    // Set HTTP_HOST to avoid warnings
    $_SERVER['HTTP_HOST'] = 'localhost';
    
    // Create app instance
    $app = App::getInstance();
    echo "App created successfully\n";
    
    // Test the route directly
    echo "\nTesting route dispatch for /test/error/404...\n";
    
    // Set up the server variables properly
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/test/error/404';
    $_SERVER['HTTP_HOST'] = 'localhost';
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/index.php';
    $_SERVER['PHP_SELF'] = '/index.php';
    
    // Create request and dispatch
    $request = App\Core\Http\Request::createFromGlobals();
    $router = $app->router();
    
    echo "Request URI: " . $request->getUri() . "\n";
    echo "Request path: " . $request->path() . "\n";
    echo "Request method: " . $request->getMethod() . "\n";
    
    // Try to dispatch the request
    try {
        $response = $router->dispatch($request);
        echo "✅ Route dispatched successfully!\n";
        echo "Response status: " . $response->getStatusCode() . "\n";
        echo "Response content length: " . strlen($response->getContent()) . " bytes\n";
        echo "Response content preview: " . substr($response->getContent(), 0, 200) . "...\n";
    } catch (Exception $e) {
        echo "❌ Route dispatch failed!\n";
        echo "Error: " . $e->getMessage() . "\n";
        echo "Error class: " . get_class($e) . "\n";
        
        // Check if it's a controller not found error
        if (strpos($e->getMessage(), 'HomeController') !== false) {
            echo "\n🚨 Found the issue! Router is looking for HomeController instead of ErrorTestController\n";
            echo "This suggests the route action is being misconfigured or overridden.\n";
        }
    }
    
    echo "\nTest complete.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
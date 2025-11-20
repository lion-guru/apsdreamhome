<?php

// Simple test to check if error pages work
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/bootstrap.php';

// Set up basic server variables
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_METHOD'] = 'GET';

try {
    // Test each error page
    $errorPages = [
        '/test/error/404',
        '/test/error/500',
        '/test/error/403',
        '/test/error/401',
        '/test/error/400'
    ];
    
    foreach ($errorPages as $uri) {
        echo "\n=== Testing $uri ===\n";
        $_SERVER['REQUEST_URI'] = $uri;
        
        // Create request
        $request = \App\Core\Http\Request::createFromGlobals();
        
        // Get router and dispatch
        $app = \App\Core\App::getInstance();
        $router = $app->router();
        
        try {
            $response = $router->dispatch($request);
            echo "✅ Route dispatched successfully!\n";
            echo "Status: " . $response->getStatusCode() . "\n";
            echo "Content length: " . strlen($response->getContent()) . " bytes\n";
            
            // Show a snippet of the content
            $content = $response->getContent();
            if (strlen($content) > 0) {
                echo "Content preview: " . substr(strip_tags($content), 0, 100) . "...\n";
            } else {
                echo "Warning: Empty response content\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Dispatch failed: " . $e->getMessage() . "\n";
            echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
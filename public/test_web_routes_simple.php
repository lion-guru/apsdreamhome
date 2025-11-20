<?php

// Simple test to check if web.php routes are loaded
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Helpers/env.php';

use App\Core\App;

try {
    echo "Testing web.php route loading...\n";
    
    // Set HTTP_HOST to avoid warnings
    $_SERVER['HTTP_HOST'] = 'localhost';
    
    // Create app instance
    $app = App::getInstance();
    echo "App instance created\n";
    
    // Get router
    $router = $app->router();
    echo "Router obtained\n";
    
    // Get route collection
    $routeCollection = $router->getRoutes();
    echo "Route collection obtained\n";
    
    // Check for error test routes
    echo "\nChecking for error test routes...\n";
    $getRoutes = $routeCollection->get('GET');
    
    $errorRoutes = [
        '/test/error/404',
        '/test/error/500', 
        '/test/error/403',
        '/test/error/401',
        '/test/error/400'
    ];
    
    $foundRoutes = 0;
    foreach ($errorRoutes as $routeUri) {
        if (isset($getRoutes[$routeUri])) {
            echo "✅ Found route: $routeUri => " . json_encode($getRoutes[$routeUri]->getAction()) . "\n";
            $foundRoutes++;
        } else {
            echo "❌ Missing route: $routeUri\n";
        }
    }
    
    echo "\nFound $foundRoutes out of " . count($errorRoutes) . " error test routes\n";
    
    // Show total routes count
    echo "Total GET routes: " . count($getRoutes) . "\n";
    
    // Test one specific route
    if (isset($getRoutes['/test/error/404'])) {
        echo "\nTesting /test/error/404 route dispatch...\n";
        
        // Create request
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test/error/404';
        $request = \App\Core\Http\Request::createFromGlobals();
        
        try {
            $response = $router->dispatch($request);
            echo "✅ Route dispatched successfully!\n";
            echo "Response status: " . $response->getStatusCode() . "\n";
            echo "Response content: " . substr($response->getContent(), 0, 200) . "...\n";
        } catch (Exception $e) {
            echo "❌ Route dispatch failed: " . $e->getMessage() . "\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
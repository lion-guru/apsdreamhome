<?php

// Test script to debug route dispatch
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Helpers/env.php';

use App\Core\App;
use App\Core\Http\Request;

try {
    echo "Testing route dispatch...\n";
    
    // Set HTTP_HOST to avoid warnings
    $_SERVER['HTTP_HOST'] = 'localhost';
    
    // Create app instance
    $app = App::getInstance();
    echo "App created successfully\n";
    
    // Get router
    $router = $app->router();
    echo "Router obtained\n";
    
    // Create a request for the error test route
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/test/error/404';
    $_SERVER['HTTP_HOST'] = 'localhost';
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $request = Request::createFromGlobals();
    echo "Request URI: " . $request->getUri() . "\n";
    echo "Request path: " . $request->path() . "\n";
    echo "Request method: " . $request->getMethod() . "\n";
    echo "Server REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
    echo "Server SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
    
    // Debug the path parsing
    echo "\nDebugging path parsing:\n";
    
    // Create reflection object to access protected methods
    $reflection = new ReflectionClass($request);
    
    // Get script name
    $getScriptNameMethod = $reflection->getMethod('getScriptName');
    $getScriptNameMethod->setAccessible(true);
    $scriptName = $getScriptNameMethod->invoke($request);
    echo "Script name: '$scriptName'\n";
    
    // Get base URL
    $getBaseUrlMethod = $reflection->getMethod('getBaseUrl');
    $getBaseUrlMethod->setAccessible(true);
    $baseUrl = $getBaseUrlMethod->invoke($request);
    echo "Base URL: '$baseUrl'\n";
    
    $preparePathInfoMethod = $reflection->getMethod('preparePathInfo');
    $preparePathInfoMethod->setAccessible(true);
    $pathInfo = $preparePathInfoMethod->invoke($request);
    echo "preparePathInfo result: '$pathInfo'\n";
    
    // Try to find the route
    echo "\nFinding route...\n";
    $method = $request->getMethod();
    $uri = $request->path();
    echo "Looking for method: $method, URI: $uri\n";
    $route = $router->findRoute($method, $uri);
    
    if ($route) {
        echo "✅ Route found!\n";
        echo "Route URI: " . $route->uri() . "\n";
        echo "Route action: " . json_encode($route->getAction()) . "\n";
        
        // Try to run the route
        echo "\nRunning route...\n";
        try {
            $response = $router->dispatch($request);
            echo "✅ Route dispatched successfully!\n";
            echo "Response status: " . $response->getStatusCode() . "\n";
            echo "Response content: " . substr($response->getContent(), 0, 200) . "...\n";
        } catch (Exception $e) {
            echo "❌ Route dispatch failed!\n";
            echo "Error: " . $e->getMessage() . "\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
        }
    } else {
        echo "❌ Route NOT found!\n";
        
        // Check what routes are available
        echo "\nAvailable GET routes:\n";
        $routeCollection = $router->getRoutes();
        $getRoutes = $routeCollection->get('GET');
        
        // Look for similar routes
        foreach ($getRoutes as $uri => $route) {
            if (strpos($uri, 'error') !== false || strpos($uri, 'test') !== false) {
                echo "  Found: $uri => " . json_encode($route->getAction()) . "\n";
            }
        }
    }
    
    echo "\nDispatch test complete.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
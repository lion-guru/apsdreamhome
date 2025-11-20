<?php

// Test script to check route registration
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Helpers/env.php';

use App\Core\App;

try {
    echo "Testing route registration...\n";
    
    // Set HTTP_HOST to avoid warnings
    $_SERVER['HTTP_HOST'] = 'localhost';
    
    // Create app instance
    $app = App::getInstance();
    echo "App created successfully\n";
    
    // Get router
    $router = $app->router();
    echo "Router obtained\n";
    
    // Get route collection
    $routeCollection = $router->getRoutes();
    echo "Route collection obtained\n";
    
    // Check if web.php routes are loaded
    echo "\nChecking web.php routes...\n";
    
    // Try to include web.php manually to see what happens
    $webRoutesFile = __DIR__ . '/../routes/web.php';
    if (file_exists($webRoutesFile)) {
        echo "web.php file exists\n";
        
        // Check if we can access the $app variable in web.php context
        echo "Checking $app variable scope...\n";
        
        // Create a simple test closure that captures $app
        $testClosure = function() use ($app) {
            return isset($app);
        };
        
        if ($testClosure()) {
            echo "✅ $app variable is accessible in closure context\n";
        } else {
            echo "❌ $app variable is NOT accessible\n";
        }
        
        // Try to manually include and execute web.php
        echo "Attempting to manually include web.php...\n";
        
        // Save current routes state
        $getRoutes = $routeCollection->get('GET');
        $beforeCount = count($getRoutes);
        echo "GET routes before manual include: $beforeCount\n";
        
        // Include web.php in a controlled context
        $includeResult = include $webRoutesFile;
        echo "web.php include result: " . var_export($includeResult, true) . "\n";
        
        // Check routes after manual include
        $getRoutes = $routeCollection->get('GET');
        $afterCount = count($getRoutes);
        echo "GET routes after manual include: $afterCount\n";
        
        if ($afterCount > $beforeCount) {
            echo "✅ Routes were added by web.php\n";
            
            // Show the new routes
            echo "New routes added:\n";
            foreach ($getRoutes as $uri => $route) {
                if (!isset($getRoutes[$uri]) || $getRoutes[$uri] !== $route) {
                    echo "  - $uri => " . json_encode($route->getAction()) . "\n";
                }
            }
        } else {
            echo "❌ No routes were added by web.php\n";
        }
        
    } else {
        echo "❌ web.php file does not exist\n";
    }
    
    // Show all available GET routes
    echo "\nAll available GET routes:\n";
    $getRoutes = $routeCollection->get('GET');
    foreach ($getRoutes as $uri => $route) {
        if (strpos($uri, 'error') !== false || strpos($uri, 'test') !== false) {
            echo "  - $uri => " . json_encode($route->getAction()) . "\n";
        }
    }
    
    echo "\nRoute registration test complete.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
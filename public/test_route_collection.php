<?php
/**
 * Test route collection contents
 * This script checks what's actually in the route collection
 */

// Set HTTP_HOST to avoid warnings
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/test/error/404';

// Include necessary files
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/core/App.php';
require_once __DIR__ . '/../app/core/Http/Request.php';

use App\Core\App;
use App\Core\Http\Request;

try {
    // Create app instance
    $app = new App();
    
    echo "App created successfully\n";
    echo "Checking route collection...\n\n";
    
    // Get router instance
    $router = $app->router();
    echo "Router instance obtained\n";
    
    // Use reflection to access protected properties
    $reflection = new ReflectionClass($router);
    $routesProperty = $reflection->getProperty('routes');
    $routesProperty->setAccessible(true);
    $routes = $routesProperty->getValue($router);
    
    echo "Route collection type: " . get_class($routes) . "\n";
    
    // Get GET routes
    $getRoutes = $routes->get('GET');
    echo "GET routes count: " . count($getRoutes) . "\n";
    
    if (count($getRoutes) > 0) {
        echo "First few GET routes:\n";
        $count = 0;
        foreach ($getRoutes as $uri => $route) {
            echo "  $uri => " . get_class($route) . "\n";
            $count++;
            if ($count >= 5) break;
        }
    }
    
    // Check for error test routes specifically
    echo "\nLooking for error test routes:\n";
    $errorRoutes = [];
    foreach ($getRoutes as $uri => $route) {
        if (strpos($uri, 'test/error') !== false) {
            $errorRoutes[$uri] = $route;
        }
    }
    
    echo "Found " . count($errorRoutes) . " error test routes:\n";
    foreach ($errorRoutes as $uri => $route) {
        echo "  $uri\n";
    }
    
    // Test specific route lookup
    echo "\nTesting route lookup for /test/error/404:\n";
    if (isset($getRoutes['/test/error/404'])) {
        echo "  ✅ Route found in collection\n";
        $route = $getRoutes['/test/error/404'];
        echo "  Route class: " . get_class($route) . "\n";
        echo "  Route URI: " . $route->uri() . "\n";
        
        // Get route action
        $action = $route->getAction();
        echo "  Route action: " . json_encode($action) . "\n";
    } else {
        echo "  ❌ Route not found in collection\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nRoute collection check complete.\n";
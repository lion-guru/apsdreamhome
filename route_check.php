<?php
// Check what routes are actually registered
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🧪 Checking Registered Routes\n";
echo "===========================\n";

// Load bootstrap
require_once 'bootstrap/app.php';

// Get app instance
$app = \App\Core\App::getInstance();

// Get router
$router = $app->router();

echo "Router class: " . get_class($router) . "\n";

// Check if routes are loaded
if (method_exists($router, 'getRoutes')) {
    $routes = $router->getRoutes();
    echo "Total routes registered: " . count($routes) . "\n\n";
    
    foreach ($routes as $route) {
        echo "Route: " . $route->uri() . " -> " . $route->getAction() . "\n";
    }
} else {
    echo "❌ Router has no getRoutes method\n";
    echo "Available methods:\n";
    $methods = get_class_methods($router);
    foreach ($methods as $method) {
        if (strpos($method, 'get') === 0 || strpos($method, 'route') === 0) {
            echo "  - $method\n";
        }
    }
}

echo "\n🎯 Check Complete!\n";
?>

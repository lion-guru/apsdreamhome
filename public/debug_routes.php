<?php

// Debug script to check route loading
require_once __DIR__ . '/../app/core/autoload.php';
require_once __DIR__ . '/../config/config.php';

echo "=== Debug Route Loading ===\n\n";

// Create app instance
$app = new \App\Core\App();

// Get router
$router = $app->router();
echo "Router instance: " . get_class($router) . "\n";

// Check if routes are loaded
$routeCollection = $router->getRoutes();
echo "Route collection: " . get_class($routeCollection) . "\n";

// Use reflection to access protected properties
$reflection = new ReflectionClass($routeCollection);
$routesProperty = $reflection->getProperty('routes');
$routesProperty->setAccessible(true);
$routes = $routesProperty->getValue($routeCollection);

echo "Total routes loaded: " . count($routes, COUNT_RECURSIVE) . "\n";
echo "GET routes: " . count($routes['GET'] ?? []) . "\n";

// Check for error test routes
$errorRoutes = [
    '/test/error/404',
    '/test/error/500',
    '/test/error/403',
    '/test/error/401',
    '/test/error/400'
];

echo "\nChecking for error test routes:\n";
foreach ($errorRoutes as $route) {
    if (isset($routes['GET'][$route])) {
        echo "✅ Found: $route => " . json_encode($routes['GET'][$route]->getAction()) . "\n";
    } else {
        echo "❌ Missing: $route\n";
    }
}

// Check if web.php was loaded
echo "\nChecking if web.php was loaded...\n";
$webPhpPath = dirname(__DIR__) . '/routes/web.php';
echo "web.php path: $webPhpPath\n";
echo "web.php exists: " . (file_exists($webPhpPath) ? 'Yes' : 'No') . "\n";

// Try to manually load web.php
echo "\nManually loading web.php...\n";
if (file_exists($webPhpPath)) {
    $app = $app; // Make $app available in web.php scope
    ob_start();
    require $webPhpPath;
    $output = ob_get_clean();
    echo "web.php loaded successfully\n";
    
    // Check routes again
    $routes = $routesProperty->getValue($routeCollection);
    echo "GET routes after loading web.php: " . count($routes['GET'] ?? []) . "\n";
    
    foreach ($errorRoutes as $route) {
        if (isset($routes['GET'][$route])) {
            echo "✅ Found: $route => " . json_encode($routes['GET'][$route]->getAction()) . "\n";
        }
    }
}
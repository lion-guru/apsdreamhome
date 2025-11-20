<?php
require_once 'app/core/Router.php';
require_once 'app/core/Route.php';
require_once 'app/core/Request.php';
require_once 'app/core/Response.php';

use App\Core\Router;

$router = new Router();

// Include the routes file
$routes = require 'app/core/routes.php';
$routes($router);

// Use reflection to access the private routes property
$reflection = new ReflectionClass($router);
$routesProperty = $reflection->getProperty('routes');
$routesProperty->setAccessible(true);
$allRoutes = $routesProperty->getValue($router);

echo "=== ALL REGISTERED ROUTES ===\n";
foreach ($allRoutes as $method => $routes) {
    echo "\n{$method} routes:\n";
    foreach ($routes as $route) {
        echo "  Path: '" . $route->getPath() . "' Handler: " . $route->getHandler() . "\n";
    }
}

echo "\n\n=== TESTING ROUTE MATCHING ===\n";

// Test the exact route we expect to exist
$testPaths = ['/api/test/', '/api/test', '/api/test/users'];

foreach ($testPaths as $path) {
    echo "\nTesting path: {$path}\n";
    try {
        $result = $router->dispatch('GET', $path);
        echo "SUCCESS: Route found and dispatched\n";
    } catch (Exception $e) {
        echo "FAILED: " . $e->getMessage() . "\n";
    }
}

echo "\n\n=== CHECKING ROUTE CLASS ===\n";
// Let's also check what the Route class looks like
$routeReflection = new ReflectionClass('App\Core\Route');
echo "Route class methods:\n";
foreach ($routeReflection->getMethods() as $method) {
    echo "  - " . $method->getName() . "\n";
}
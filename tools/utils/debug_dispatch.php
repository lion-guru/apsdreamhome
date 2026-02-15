<?php
require_once 'app/core/Router.php';
require_once 'app/core/Route.php';
require_once 'app/core/Request.php';
require_once 'app/core/Response.php';

use App\Core\Router;

$router = new Router();

// Test the exact same route structure as in routes.php
$router->group(['prefix' => 'api'], function($router) {
    $router->group(['prefix' => 'test'], function($router) {
        $router->get('/', 'TestApiController@index', ['name' => 'api.test.index']);
    });
});

// Check the registered routes
echo "=== REGISTERED ROUTES ===\n";
$reflection = new ReflectionClass($router);
$routesProperty = $reflection->getProperty('routes');
$routesProperty->setAccessible(true);
$allRoutes = $routesProperty->getValue($router);

foreach ($allRoutes as $method => $routes) {
    echo "\n{$method} routes:\n";
    foreach ($routes as $route) {
        echo "  Path: '" . $route->getPath() . "' Handler: " . $route->getHandler() . "\n";
    }
}

// Test dispatch with detailed debugging
echo "\n=== TESTING DISPATCH ===\n";

$testPaths = ['/api/test/', '/api/test'];

foreach ($testPaths as $path) {
    echo "\nTesting path: {$path}\n";
    
    // Check what normalizePath does
    $normalizeMethod = $reflection->getMethod('normalizePath');
    $normalizeMethod->setAccessible(true);
    $normalizedPath = $normalizeMethod->invoke($router, $path);
    echo "Normalized path: {$normalizedPath}\n";
    
    // Check each registered route
    $getRoutes = $allRoutes['GET'] ?? [];
    foreach ($getRoutes as $route) {
        $routePath = $route->getPath();
        echo "  Checking against route path: '{$routePath}'\n";
        
        // Check if the route matches
        if ($route->matches($normalizedPath, 'GET')) {
            echo "  ✓ MATCH FOUND!\n";
            break;
        } else {
            echo "  ✗ No match\n";
        }
    }
    
    // Try actual dispatch
    try {
        $result = $router->dispatch('GET', $path);
        echo "Dispatch result: " . json_encode($result) . "\n";
    } catch (Exception $e) {
        echo "Dispatch error: " . $e->getMessage() . "\n";
    }
}
<?php

require_once 'app/core/Router.php';

use App\Core\Router;

$router = new Router();

// Test with a simple callback instead of controller
$router->group(['prefix' => 'api'], function($router) {
    $router->group(['prefix' => 'test'], function($router) {
        $router->get('/', function() {
            return 'API Test Root';
        });
        $router->get('/users', function() {
            return 'API Test Users';
        });
    });
});

echo "=== REGISTERED ROUTES ===\n";
$reflection = new ReflectionClass($router);
$routesProperty = $reflection->getProperty('routes');
$routesProperty->setAccessible(true);
$routes = $routesProperty->getValue($router);

foreach ($routes as $method => $methodRoutes) {
    echo "\n$method routes:\n";
    foreach ($methodRoutes as $route) {
        $routeReflection = new ReflectionClass($route);
        $pathProperty = $routeReflection->getProperty('path');
        $pathProperty->setAccessible(true);
        $handlerProperty = $routeReflection->getProperty('handler');
        $handlerProperty->setAccessible(true);
        
        echo "  Path: '" . $pathProperty->getValue($route) . "' Handler: ";
        $handler = $handlerProperty->getValue($route);
        if (is_callable($handler)) {
            echo "Closure\n";
        } else {
            echo $handler . "\n";
        }
    }
}

echo "\n=== TESTING DISPATCH ===\n";

$testPaths = ['/api/test', '/api/test/', '/api/test/users'];

foreach ($testPaths as $path) {
    echo "\nTesting path: $path\n";
    try {
        $result = $router->dispatch('GET', $path);
        echo "✓ SUCCESS: " . $result . "\n";
    } catch (Exception $e) {
        echo "✗ ERROR: " . $e->getMessage() . "\n";
    }
}
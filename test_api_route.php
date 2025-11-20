<?php
require_once 'app/core/Router.php';
require_once 'app/core/Route.php';

$router = new App\Core\Router();
$routeConfig = require 'app/core/routes.php';
$routeConfig($router);

// Use reflection to access private properties
$reflection = new ReflectionClass($router);
$routesProperty = $reflection->getProperty('routes');
$routesProperty->setAccessible(true);
$routes = $routesProperty->getValue($router);

echo "Looking for GET routes that contain 'test':" . PHP_EOL;
if (isset($routes['GET'])) {
    foreach ($routes['GET'] as $route) {
        $path = $route->getPath();
        if (strpos($path, 'test') !== false) {
            echo "  Path: '$path' Handler: " . $route->getHandler() . PHP_EOL;
        }
    }
}

echo PHP_EOL . "Testing /api/test/ (with trailing slash):" . PHP_EOL;
try {
    $result = $router->dispatch('GET', '/api/test/');
    echo 'Route found and dispatched successfully!' . PHP_EOL;
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "Testing /api/test (without trailing slash):" . PHP_EOL;
try {
    $result = $router->dispatch('GET', '/api/test');
    echo 'Route found and dispatched successfully!' . PHP_EOL;
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
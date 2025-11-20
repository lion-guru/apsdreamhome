<?php
require_once 'app/core/Router.php';
require_once 'app/core/Route.php';
require_once 'app/core/Request.php';
require_once 'app/core/Response.php';

use App\Core\Router;

$router = new Router();

// Test nested groups with more detailed debugging
echo "=== TESTING NESTED GROUP PREFIXES (DETAILED) ===\n";

$router->group(['prefix' => 'api'], function($router) {
    // Use reflection to access the private property
    $reflection = new ReflectionClass($router);
    $currentGroupProperty = $reflection->getProperty('currentGroup');
    $currentGroupProperty->setAccessible(true);
    
    echo "Level 1 - After 'api' group: currentGroup = " . json_encode($currentGroupProperty->getValue($router)) . "\n";
    
    $router->group(['prefix' => 'test'], function($router) {
        $reflection = new ReflectionClass($router);
        $currentGroupProperty = $reflection->getProperty('currentGroup');
        $currentGroupProperty->setAccessible(true);
        
        echo "Level 2 - After 'test' group: currentGroup = " . json_encode($currentGroupProperty->getValue($router)) . "\n";
        
        // Let's manually test the applyGroupPrefix method
        echo "Testing applyGroupPrefix('/') with currentGroup: " . json_encode($currentGroupProperty->getValue($router)) . "\n";
        
        $method = $reflection->getMethod('applyGroupPrefix');
        $method->setAccessible(true);
        
        $result = $method->invoke($router, '/');
        echo "Result of applyGroupPrefix('/'): " . $result . "\n";
        
        $router->get('/', 'TestApiController@index', ['name' => 'api.test.index']);
    });
});

// Check the final routes
$reflection = new ReflectionClass($router);
$routesProperty = $reflection->getProperty('routes');
$routesProperty->setAccessible(true);
$allRoutes = $routesProperty->getValue($router);

echo "\n=== REGISTERED ROUTES ===\n";
foreach ($allRoutes as $method => $routes) {
    echo "\n{$method} routes:\n";
    foreach ($routes as $route) {
        echo "  Path: '" . $route->getPath() . "' Handler: " . $route->getHandler() . "\n";
    }
}
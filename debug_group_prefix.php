<?php
require_once 'app/core/Router.php';
require_once 'app/core/Route.php';
require_once 'app/core/Request.php';
require_once 'app/core/Response.php';

use App\Core\Router;

$router = new Router();

// Test nested groups
echo "=== TESTING NESTED GROUP PREFIXES ===\n";

$router->group(['prefix' => 'api'], function($router) {
    echo "Inside 'api' group - currentGroup prefix: " . ($router->currentGroup['prefix'] ?? 'none') . "\n";
    
    $router->group(['prefix' => 'test'], function($router) {
        echo "Inside 'test' nested group - currentGroup prefix: " . ($router->currentGroup['prefix'] ?? 'none') . "\n";
        
        $router->get('/', 'TestApiController@index', ['name' => 'api.test.index']);
        echo "Registered route '/'\n";
    });
});

// Use reflection to access the private routes property
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
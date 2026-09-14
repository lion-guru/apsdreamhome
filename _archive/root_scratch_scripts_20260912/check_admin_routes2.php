<?php
// Properly initialize the app like public/index.php does
require_once 'c:/xampp/htdocs/apsdreamhome/config/bootstrap.php';
require_once 'c:/xampp/htdocs/apsdreamhome/routes/router.php';

$router = new Router();
require_once 'c:/xampp/htdocs/apsdreamhome/routes/web.php';
require_once 'c:/xampp/htdocs/apsdreamhome/routes/api.php';

$routerInstance = $GLOBALS['router'] ?? null;
if (!$routerInstance) {
    // Try to get from global
    $routerInstance = $GLOBALS['router'] ?? null;
}

if ($routerInstance) {
    $routes = $routerInstance->getRoutes();
    foreach($routes as $method => $routesArr) {
        foreach($routesArr as $path => $route) {
            if (stripos($path, '/admin/users') !== false || stripos($path, '/admin/properties') !== false || stripos($path, '/admin/bookings') !== false || stripos($path, '/admin/leads') !== false || stripos($path, '/admin/reports') !== false || stripos($path, '/admin/settings') !== false) {
                echo "[$method] $path -> " . $route['handler'] . "\n";
            }
        }
    }
} else {
    echo "Router not found in globals\n";
    // Try to get from the router variable
    if (isset($router)) {
        $routes = $router->getRoutes();
        foreach($routes as $method => $routesArr) {
            foreach($routesArr as $path => $route) {
                if (stripos($path, '/admin/users') !== false || stripos($path, '/admin/properties') !== false || stripos($path, '/admin/bookings') !== false || stripos($path, '/admin/leads') !== false || stripos($path, '/admin/reports') !== false || stripos($path, '/admin/settings') !== false) {
                    echo "[$method] $path -> " . $route['handler'] . "\n";
                }
            }
        }
    }
}
<?php
require_once 'c:/xampp/htdocs/apsdreamhome/vendor/autoload.php';
require_once 'c:/xampp/htdocs/apsdreamhome/routes/router.php';
require_once 'c:/xampp/htdocs/apsdreamhome/routes/web.php';

$router = new \Router();
$routes = $router->getRoutes();
foreach($routes as $method => $routesArr) {
    foreach($routesArr as $path => $route) {
        if (stripos($path, '/admin/users') !== false || stripos($path, '/admin/properties') !== false || stripos($path, '/admin/bookings') !== false || stripos($path, '/admin/leads') !== false || stripos($path, '/admin/reports') !== false || stripos($path, '/admin/settings') !== false) {
            echo "[$method] $path -> " . $route['handler'] . "\n";
        }
    }
}
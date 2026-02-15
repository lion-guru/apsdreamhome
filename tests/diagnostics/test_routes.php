<?php
require_once 'app/core/Router.php';
require_once 'app/core/Route.php';

$router = new App\Core\Router();
$routeConfig = require 'app/core/routes.php';
$routeConfig($router);

echo 'Routes loaded: ' . count($router->getRoutes()) . PHP_EOL;
$routes = $router->getRoutes();
print_r($routes);
<?php
require_once 'app/core/Router.php';
require_once 'app/core/Route.php';

$router = new App\Core\Router();
$routeConfig = require 'app/core/routes.php';
$routeConfig($router);

echo 'Routes loaded: ' . count($router->getRoutes()) . PHP_EOL;
$routes = $router->getRoutes();
// DEBUG CODE REMOVED: 2026-02-22 19:56:19 CODE REMOVED: 2026-02-22 19:56:19
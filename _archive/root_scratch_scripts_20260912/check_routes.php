<?php
require_once 'vendor/autoload.php';

$router = new \App\Core\Router\Router();
$routes = $router->getRoutes();
foreach($routes as $r) {
    if (stripos($r[0], 'admin') !== false) {
        echo $r[0] . ' -> ' . $r[1] . "\n";
    }
}
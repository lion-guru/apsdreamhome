<?php
require_once 'c:/xampp/htdocs/apsdreamhome/vendor/autoload.php';

use App\Core\Routing\Router;

$router = new \App\Core\Routing\Router();
$routes = $router->getRoutes();
foreach($routes as $r) {
    if (stripos($r[0], '/admin/users') !== false || stripos($r[0], '/admin/properties') !== false || stripos($r[0], '/admin/bookings') !== false || stripos($r[0], '/admin/leads') !== false || stripos($r[0], '/admin/reports') !== false || stripos($r[0], '/admin/settings') !== false) {
        echo $r[0] . ' -> ' . $r[1] . "\n";
    }
}
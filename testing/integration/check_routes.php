<?php
// Define constants first
define('APS_ROOT', __DIR__);
define('APS_APP', APS_ROOT . '/app');
define('APS_CONFIG', APS_ROOT . '/config');
define('APS_STORAGE', APS_ROOT . '/storage');

// Include bootstrap
require_once 'config/bootstrap.php';

// Include router
require_once 'routes/router.php';

$router = new Router();
require __DIR__ . '/routes/web.php';

$getRoutes = array_keys($router->routes['GET'] ?? []);
echo "Total GET routes: " . count($getRoutes) . "\n";
echo "\nAdmin routes:\n";
foreach ($getRoutes as $route) {
    if (strpos($route, 'admin') === 0) {
        echo "  - " . $route . "\n";
    }
}

echo "\nLooking for exact 'admin' route:\n";
if (isset($router->routes['GET']['admin'])) {
    echo "  Found: admin\n";
} else {
    echo "  NOT found: admin\n";
}

echo "\nAll routes containing 'admin':\n";
foreach ($getRoutes as $route) {
    if (strpos($route, 'admin') !== false) {
        echo "  - " . $route . "\n";
    }
}
<?php
// Check routes
require_once __DIR__ . '/vendor/autoload.php';

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

echo "\nLooking for 'admin':\n";
if (isset($router->routes['GET']['admin'])) {
    echo "  Found: admin\n";
} else {
    echo "  NOT found: admin\n";
}

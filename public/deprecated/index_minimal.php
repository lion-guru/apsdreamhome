<?php
// APS Dream Home - Minimal Entry Point
echo "INDEX LOADED<br>";

// Define constants
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('BASE_URL', 'http://localhost:8000/apsdreamhome');

echo "CONSTANTS SET<br>";

// Load router
require_once ROOT_PATH . '/routes/index.php';
echo "ROUTER CLASS LOADED<br>";

// Create router
$router = new Router();
echo "ROUTER INSTANCE CREATED<br>";

// Add route
$router->get('/properties', function() {
    echo "PROPERTIES PAGE WORKS!";
    exit;
});

$router->get('/test', function() {
    echo "TEST ROUTE WORKS!";
    exit;
});

echo "ROUTES ADDED<br>";

// Dispatch
$router->dispatch();
echo "ROUTER DISPATCHED<br>";
?>

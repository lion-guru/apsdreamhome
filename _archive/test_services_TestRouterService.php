<?php
// Debug router test
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define constants
define('APS_ROOT', dirname(__DIR__));
define('APS_APP', APS_ROOT . '/app');
define('BASE_URL', 'http://localhost:8000');

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = 'app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

require_once 'routes/router.php';
require_once 'routes/web.php';

// Mock server variables
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['SCRIPT_NAME'] = '/index.php';

echo "Testing router dispatch...\n";
error_log("ROUTER TEST: Starting dispatch");

try {
    $router->dispatch();
    echo "✅ Router dispatch completed successfully\n";
} catch (Exception $e) {
    echo "❌ Router error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

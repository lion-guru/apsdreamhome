<?php
// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define BASE_URL
define('BASE_URL', '/apsdreamhome');

// Start session
session_start();

// Set up autoloading
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Simulate POST data
$_POST['identity'] = 'test@aps.com';
$_POST['password'] = 'test123';

// Create controller instance
$controller = new App\Http\Controllers\Auth\CustomerAuthController();

// Call authenticate method
ob_start();
$result = $controller->authenticate();
$output = ob_get_clean();

// Output results
echo "<pre>";
echo "Result: ";
var_dump($result);
echo "<br>";
echo "Session: ";
var_dump($_SESSION);
echo "<br>";
echo "Output: ";
echo htmlspecialchars($output);
echo "</pre>";
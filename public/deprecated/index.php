<?php

/**
 * APS Dream Home - Public Index (Main Entry Point)
 * This is the main entry point for the application
 */

// Debug: Create a test file to verify this script is running
file_put_contents(__DIR__ . '/../logs/debug_test.txt', "Index.php executed at: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

// Define constants
define('APS_ROOT', dirname(__DIR__));
define('APS_APP', APS_ROOT . '/app');
// Define APP_PATH for legacy compatibility
if (!defined('APP_PATH')) define('APP_PATH', APS_APP);

define('APS_PUBLIC', __DIR__);
define('APS_CONFIG', APS_ROOT . '/config');
define('APS_STORAGE', APS_ROOT . '/storage');
define('APS_LOGS', APS_ROOT . '/logs');

// Define BASE_URL dynamically for XAMPP
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'] ?? 'localhost';
// Check if accessing via XAMPP localhost/apsdreamhome
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
if (str_contains($requestUri, '/apsdreamhome')) {
    define('BASE_URL', $protocol . $domainName . '/apsdreamhome');
} else {
    define('BASE_URL', $protocol . $domainName);
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', APS_LOGS . '/php_error.log');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = APS_APP . '/';

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

// Include required files
require_once APS_ROOT . '/app/helpers.php';

// Create router instance
error_log("=== INDEX: APS_ROOT = " . APS_ROOT . " ===");
error_log("=== INDEX: Router path = " . APS_ROOT . '/routes/router.php' . " ===");
require_once APS_ROOT . '/routes/router.php';
error_log("=== INDEX: Creating router instance ===");
$router = new Router();
error_log("=== INDEX: Router created successfully ===");

// Include routes
error_log("=== INDEX: Including routes/web.php ===");
try {
    require_once APS_ROOT . '/routes/web.php';
    error_log("=== INDEX: Routes included successfully ===");
} catch (Exception $e) {
    error_log("=== INDEX: Error including routes: " . $e->getMessage() . " ===");
    error_log("=== INDEX: Error in file: " . $e->getFile() . " line " . $e->getLine() . " ===");
}

// Dispatch router
try {
    // Use the actual REQUEST_URI for routing
    $router->dispatch();
} catch (Exception $e) {
    // Log error
    error_log("Router Error: " . $e->getMessage());

    // Show error page
    http_response_code(404);
    echo "<!DOCTYPE html>
<html>
<head>
    <title>404 - Page Not Found</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        h1 { color: #e74c3c; }
        a { color: #3498db; text-decoration: none; }
    </style>
</head>
<body>
    <h1>404 - Page Not Found</h1>
    <p>The page you requested could not be found.</p>
    <p>Debug Info: URI = " . htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'not set') . "</p>
    <p><a href='" . BASE_URL . "'>Go to Home</a></p>
</body>
</html>";
    exit;
}

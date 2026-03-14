<?php

/**
 * APS Dream Home - Public Index (Main Entry Point)
 * This is the main entry point for the application
 */

// Define constants
define('APS_ROOT', dirname(__DIR__));
define('APS_APP', APS_ROOT . '/app');
// Define APP_PATH for legacy compatibility
if (!defined('APP_PATH')) define('APP_PATH', APS_APP);

define('APS_PUBLIC', __DIR__);
define('APS_CONFIG', APS_ROOT . '/config');
define('APS_STORAGE', APS_ROOT . '/storage');
define('APS_LOGS', APS_ROOT . '/logs');

// Define BASE_URL dynamically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'] ?? 'localhost';
$projectName = '/apsdreamhome';
define('BASE_URL', $protocol . $domainName . $projectName);

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
require_once APS_ROOT . '/routes/web.php';

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

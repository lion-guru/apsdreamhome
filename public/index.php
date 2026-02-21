<?php

// Debug logging
$logFile = dirname(__DIR__) . '/logs/debug_output.log';
function debug_log($message)
{
    global $logFile;
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] " . $message . "\n", FILE_APPEND);
}

debug_log("Request started: " . ($_SERVER['REQUEST_URI'] ?? 'CLI'));

// Set HTTP_HOST to avoid warnings in config files
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

// Set the default timezone
date_default_timezone_set('Asia/Manila');

// Session is started by the App class with proper configuration
// if (session_status() === PHP_SESSION_NONE) {
//    session_start();
// }

$__env = getenv('APP_ENV') ?: 'production';
if ($__env === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
ini_set('error_log', dirname(__DIR__) . '/logs/php_error.log');

// Define the base path
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Define BASE_URL if not already defined
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = dirname($_SERVER['SCRIPT_NAME']);

    // Remove /public if it exists in the path to get the app root URL
    // e.g. /apsdreamhome/public -> /apsdreamhome
    if (basename($script) === 'public') {
        $script = dirname($script);
    }

    // Ensure script path ends with / or is just empty
    $script = rtrim($script, '/');

    define('BASE_URL', "$protocol://$host$script/");
}

// Import the App class
use App\Core\App;

try {
    debug_log("Loading autoloader...");
    // Load the autoloader
    require_once BASE_PATH . '/app/core/autoload.php';

    debug_log("Loading App class...");
    // Load the application
    require_once BASE_PATH . '/app/core/App.php';

    debug_log("Instantiating App...");
    // Create the application instance
    $app = new App();

    // Run the application
    $app->run();
} catch (Exception $e) {
    debug_log("Exception: " . $e->getMessage());
    // Handle any exceptions that occur during bootstrap
    error_log("Application Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    http_response_code(500);
    echo "<h1>Application Error</h1>";
    echo "<p>An error occurred while loading the application.</p>";
    if (getenv('APP_ENV') === 'development') {
        echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p><strong>Line:</strong> " . htmlspecialchars($e->getLine()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
}

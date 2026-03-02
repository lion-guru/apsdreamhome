<?php
/**
 * APS Dream Home - Fixed MVC Router
 * Using existing HomeController and MVC structure properly
 */

// Define essential constants and functions first
define('BASE_URL', '/apsdreamhome/');
define('APP_ROOT', __DIR__);

// Define essential functions that MVC controllers need
if (!function_exists('logger')) {
    function logger() {
        return new class {
            public function info($message) {
                error_log("[INFO] $message");
            }
            public function error($message) {
                error_log("[ERROR] $message");
            }
        };
    }
}

// Debug logging
$logFile = __DIR__ . '/logs/debug_output.log';
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
//     session_start();
// }

// Load the application
require_once __DIR__ . '/config/bootstrap.php';

// Initialize the application
$app = \App\Core\App::getInstance(__DIR__);

// Handle the request
debug_log("About to call app->run()");
$response = $app->run();
debug_log("Response from app->run(): " . substr($response, 0, 200) . "...");

// Send the response
echo $response;

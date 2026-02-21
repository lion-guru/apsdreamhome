<?php
// Define minimal environment constants
define('APP_ROOT', dirname(__DIR__));
define('BASE_PATH', APP_ROOT . '/public');
define('BASE_URL', 'http://localhost/apsdreamhome/');

// Mock $_SERVER
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['HTTP_HOST'] = 'localhost';

// Setup logging
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('error_log', APP_ROOT . '/logs/debug_output.log');

error_log("----------------------------------------------------------------");
error_log("STARTING FULL REQUEST DEBUG: " . date('Y-m-d H:i:s'));

// Load Autoloader directly
require_once APP_ROOT . '/app/Core/Autoloader.php';
$loader = \App\Core\Autoloader::getInstance();
$loader->addNamespace('App', APP_ROOT . '/app');
$loader->register();

error_log("Autoloader registered.");

// Load App
require_once APP_ROOT . '/app/Core/App.php';
// require_once APP_ROOT . '/app/Core/Container.php'; // Removed as it doesn't exist

try {
    error_log("Initializing App...");
    $app = new \App\Core\App();

    // We need to simulate the bootstrap process slightly
    // Manually register some bindings if needed, but App constructor should handle container

    error_log("Running App...");
    error_log("Output buffer level before run: " . ob_get_level());
    $app->run();
    error_log("Output buffer level after run: " . ob_get_level());
    
    // Force flush if output buffering is stuck
    while (ob_get_level() > 0) {
        ob_end_flush();
    }
} catch (Throwable $e) {
    error_log("EXCEPTION CAUGHT: " . $e->getMessage());
    error_log($e->getTraceAsString());
}

error_log("Request Debug Finished.");

<?php

// Initialize error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define application root path
define('APP_ROOT', dirname(__DIR__));

// Load configuration
$config = require_once APP_ROOT . '/src/config/app.php';

// Set timezone
date_default_timezone_set($config['app']['timezone']);

// Start session
session_start();

// Autoloader function
spl_autoload_register(function ($class) {
    // Convert namespace to file path
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    $file = APP_ROOT . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $file;
    
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
});

// Load database configuration
require_once APP_ROOT . '/src/config/database.php';

// Load helper functions
require_once APP_ROOT . '/src/utils/helpers.php';

// Set error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    switch ($errno) {
        case E_USER_ERROR:
            error_log("Fatal Error: [$errno] $errstr\n");
            exit(1);
            break;
        
        case E_USER_WARNING:
            error_log("Warning: [$errno] $errstr\n");
            break;
        
        case E_USER_NOTICE:
            error_log("Notice: [$errno] $errstr\n");
            break;
        
        default:
            error_log("Unknown error type: [$errno] $errstr\n");
            break;
    }
    
    return true;
});
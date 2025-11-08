<?php
/**
 * Front Controller
 * 
 * This is the main entry point for the APS Dream Home MVC application.
 * All requests are routed through this file.
 */

// Define application path constants
define('ROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('APP', ROOT . 'app' . DIRECTORY_SEPARATOR);
define('PUBLIC_PATH', __DIR__ . DIRECTORY_SEPARATOR);

// Autoload classes
require_once APP . 'core/autoload.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(ROOT);
$dotenv->load();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create new App instance
$app = new App\Core\App();

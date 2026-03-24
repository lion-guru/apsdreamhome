<?php
/**
 * APS Dream Home - Working Index File
 * This bypasses routing and directly loads the homepage
 */

// Define constants
define('APS_ROOT', dirname(__DIR__));
define('APS_APP', APS_ROOT . '/app');
define('APP_PATH', APS_APP);
define('APS_PUBLIC', __DIR__);
define('APS_CONFIG', APS_ROOT . '/config');
define('APS_STORAGE', APS_ROOT . '/storage');
define('APS_LOGS', APS_ROOT . '/logs');
define('BASE_URL', 'http://localhost:8000');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', APS_LOGS . '/php_error.log');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once APS_ROOT . '/app/Core/Controller.php';
require_once APS_ROOT . '/app/Http/Controllers/BaseController.php';
require_once APS_ROOT . '/app/Http/Controllers/HomeController.php';

// Load the homepage directly
try {
    $homeController = new \App\Http\Controllers\HomeController();
    
    // Capture the output
    ob_start();
    $homeController->index();
    $content = ob_get_clean();
    
    // Output the content
    echo $content;
    
} catch (Exception $e) {
    echo "<h1>Error Loading Homepage</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
}
?>

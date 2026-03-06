<?php
/**
 * APS Dream Home - Public Entry Point
 * Main application entry point
 */

// Define constants
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('BASE_URL', 'http://localhost/apsdreamhome/public');

// Autoload
require_once ROOT_PATH . '/vendor/autoload.php';

// Load configuration
require_once CONFIG_PATH . '/app.php';

// Start session
session_start();

// Load custom router (working system)
require_once ROOT_PATH . '/routes/router.php';

// Create router instance
$router = new Router();

// Load web routes (contains all routes)
require_once ROOT_PATH . '/routes/web.php';

// Load API routes
require_once ROOT_PATH . '/routes/api.php';

// Dispatch router
$uri = $_GET['url'] ?? $_SERVER['REQUEST_URI'] ?? '/';
$router->dispatch($uri);
?>
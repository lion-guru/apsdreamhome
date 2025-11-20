<?php
/**
 * APS Dream Home - Main Entry Point
 * Organized and modernized main router with proper MVC structure
 */

// Define security constant for database connection
define('INCLUDED_FROM_MAIN', true);

// Load the bootstrap configuration
require_once __DIR__ . '/config/bootstrap.php';

// Initialize the application
$app = app();

// Handle routing based on URL parameter
$route = $_GET['route'] ?? 'home';

// Route to appropriate controller and action
try {
    $router = new App\Core\Router();
    $router->dispatch($route);
} catch (Exception $e) {
    // Log error and show 404
    error_log('Routing error: ' . $e->getMessage());

    if ($app->isDebug()) {
        echo 'Routing Error: ' . $e->getMessage();
    } else {
        header('HTTP/1.0 404 Not Found');
        require_once __DIR__ . '/app/views/layouts/404.php';
    }
}

?>

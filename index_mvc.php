<?php
/**
 * APS Dream Home - Proper MVC Router
 * Using the existing HomeController and MVC structure
 */

// Load the application bootstrap
require_once __DIR__ . '/bootstrap/app.php';

try {
    // Create application instance
    $app = \App\Core\App::getInstance();

    // Create request
    $request = new \App\Core\Http\Request();

    // Get the requested path
    $request_uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($request_uri, PHP_URL_PATH);

    // Remove /apsdreamhome prefix if present
    $path = str_replace('/apsdreamhome', '', $path);

    // Define BASE_URL
    define('BASE_URL', '/apsdreamhome/');

    // Route to controllers (proper MVC)
    switch ($path) {
        case '/':
        case '':
            // Use the existing HomeController
            $controller = new \App\Http\Controllers\HomeController();
            echo $controller->index();
            break;

        case '/properties':
        case '/properties/':
            // Use HomeController for properties
            $controller = new \App\Http\Controllers\HomeController();
            echo $controller->properties();
            break;

        case '/about':
            // About page using controller method
            $controller = new \App\Http\Controllers\HomeController();
            echo $controller->about();
            break;

        case '/contact':
            // Contact page using controller method
            $controller = new \App\Http\Controllers\HomeController();
            echo $controller->contact();
            break;

        case '/projects':
            // Use HomeController for projects
            $controller = new \App\Http\Controllers\HomeController();
            echo $controller->projects();
            break;

        case '/admin':
        case '/admin/':
            // Use AdminController
            $controller = new \App\Http\Controllers\Admin\AdminController();
            echo $controller->dashboard();
            break;

        default:
            // Check for property details
            if (preg_match('/^\/properties\/(\d+)$/', $path, $matches)) {
                $controller = new \App\Http\Controllers\Property\PropertyController();
                echo $controller->show($matches[1]);
            } else {
                // 404 page
                http_response_code(404);
                echo '<h1>Page Not Found</h1><p><a href="' . BASE_URL . '">Go Home</a></p>';
            }
            break;
    }

} catch (Exception $e) {
    // Handle errors gracefully
    http_response_code(500);
    echo '<h1>Application Error</h1>';
    echo '<p>An error occurred: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><a href="' . BASE_URL . '">Go Home</a></p>';
}
?>

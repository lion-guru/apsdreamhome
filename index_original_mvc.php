<?php
/**
 * APS Dream Home - Proper MVC Router
 * Using the existing HomeController
 */

// Load the application bootstrap
require_once __DIR__ . '/bootstrap/app.php';

try {
    // Get the requested path
    $request_uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($request_uri, PHP_URL_PATH);
    $path = str_replace('/apsdreamhome', '', $path);

    define('BASE_URL', '/apsdreamhome/');

    // Use existing HomeController (proper MVC)
    switch ($path) {
        case '/':
        case '':
            $controller = new \App\Http\Controllers\HomeController();
            $controller->index();
            break;

        case '/properties':
        case '/properties/':
            $controller = new \App\Http\Controllers\HomeController();
            $controller->properties();
            break;

        case '/about':
            $controller = new \App\Http\Controllers\HomeController();
            $controller->about();
            break;

        case '/contact':
            $controller = new \App\Http\Controllers\HomeController();
            $controller->contact();
            break;

        case '/projects':
            $controller = new \App\Http\Controllers\HomeController();
            $controller->projects();
            break;

        case '/admin':
        case '/admin/':
            $controller = new \App\Http\Controllers\Admin\AdminController();
            $controller->dashboard();
            break;

        default:
            if (preg_match('/^\/properties\/(\d+)$/', $path, $matches)) {
                $controller = new \App\Http\Controllers\Property\PropertyController();
                $controller->show($matches[1]);
            } else {
                http_response_code(404);
                echo '<h1>Page Not Found</h1><p><a href="' . BASE_URL . '">Go Home</a></p>';
            }
            break;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo '<h1>Application Error</h1>';
    echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><a href="' . BASE_URL . '">Go Home</a></p>';
}
?>

<?php
/**
 * APS Dream Home - Hybrid Router
 * Uses MVC controllers when possible, falls back to simple views
 */

// Define BASE_URL
define('BASE_URL', '/apsdreamhome/');

// Get the requested path
$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($request_uri, PHP_URL_PATH);
$path = str_replace('/apsdreamhome', '', $path);

// Try MVC first, fallback to simple views
switch ($path) {
    case '/':
    case '':
        // Try MVC HomeController first
        try {
            require_once __DIR__ . '/bootstrap/app.php';
            $controller = new \App\Http\Controllers\HomeController();
            $controller->index();
            echo "<!-- MVC HomeController used -->";
        } catch (Exception $e) {
            // Fallback to simple view
            include 'views/home.php';
            echo "<!-- Fallback view used: " . htmlspecialchars($e->getMessage()) . " -->";
        }
        break;

    case '/properties':
    case '/properties/':
        try {
            require_once __DIR__ . '/bootstrap/app.php';
            $controller = new \App\Http\Controllers\HomeController();
            $controller->properties();
        } catch (Exception $e) {
            include 'views/properties.php';
        }
        break;

    case '/about':
        try {
            require_once __DIR__ . '/bootstrap/app.php';
            $controller = new \App\Http\Controllers\HomeController();
            $controller->about();
        } catch (Exception $e) {
            include 'views/about.php';
        }
        break;

    case '/contact':
        try {
            require_once __DIR__ . '/bootstrap/app.php';
            $controller = new \App\Http\Controllers\HomeController();
            $controller->contact();
        } catch (Exception $e) {
            include 'views/contact.php';
        }
        break;

    case '/projects':
        try {
            require_once __DIR__ . '/bootstrap/app.php';
            $controller = new \App\Http\Controllers\HomeController();
            $controller->projects();
        } catch (Exception $e) {
            include 'views/projects.php';
        }
        break;

    case '/admin':
    case '/admin/':
        try {
            require_once __DIR__ . '/bootstrap/app.php';
            $controller = new \App\Http\Controllers\Admin\AdminController();
            $controller->dashboard();
        } catch (Exception $e) {
            include 'views/admin.php';
        }
        break;

    default:
        if (preg_match('/^\/properties\/(\d+)$/', $path, $matches)) {
            try {
                require_once __DIR__ . '/bootstrap/app.php';
                $controller = new \App\Http\Controllers\Property\PropertyController();
                $controller->show($matches[1]);
            } catch (Exception $e) {
                include 'views/property_details.php';
            }
        } else {
            http_response_code(404);
            echo '<h1>Page Not Found</h1><p><a href="' . BASE_URL . '">Go Home</a></p>';
        }
        break;
}
?>

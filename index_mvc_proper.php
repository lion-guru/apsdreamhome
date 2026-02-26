<?php
/**
 * APS Dream Home - Working MVC Router
 * Using the existing HomeController properly
 */

// Define BASE_URL first
define('BASE_URL', '/apsdreamhome/');

// Get the requested path
$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($request_uri, PHP_URL_PATH);
$path = str_replace('/apsdreamhome', '', $path);

// Simple MVC routing using existing controllers
switch ($path) {
    case '/':
    case '':
        // Use existing HomeController - but with error handling
        try {
            // Load bootstrap manually to avoid complex dependencies
            require_once __DIR__ . '/bootstrap/app.php';

            $controller = new \App\Http\Controllers\HomeController();
            $controller->index();
        } catch (Exception $e) {
            // Fallback to simple page if MVC fails
            echo '<!DOCTYPE html>
<html>
<head><title>APS Dream Home</title></head>
<body>
    <h1>APS Dream Home</h1>
    <p>Welcome to our real estate platform</p>
    <p>MVC system loading... Error: ' . htmlspecialchars($e->getMessage()) . '</p>
    <a href="' . BASE_URL . 'simple_homepage_direct.php">Use Simple Version</a>
</body>
</html>';
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
        // Check for property details
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

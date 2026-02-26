<?php
/**
 * APS Dream Home - Fix Original MVC
 * Let's fix the original HomeController to work
 */

// Define BASE_URL first
define('BASE_URL', '/apsdreamhome/');

// Load essential files manually to avoid complex bootstrap issues
require_once __DIR__ . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

// Define essential functions that HomeController needs
if (!function_exists('logger')) {
    function logger() {
        return new class {
            public function info($message) {
                error_log("[INFO] $message");
            }
            public function error($message) {
                error_log("[ERROR] $message");
            }
        };
    }
}

// Get the requested path
$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($request_uri, PHP_URL_PATH);
$path = str_replace('/apsdreamhome', '', $path);

// Now use the original HomeController
switch ($path) {
    case '/':
    case '':
        try {
            $controller = new \App\Http\Controllers\HomeController();
            $controller->index();
            echo "<!-- Original MVC HomeController working! -->";
        } catch (Exception $e) {
            echo "<!-- MVC Error: " . htmlspecialchars($e->getMessage()) . " -->";
            // Fallback to working version
            include 'views/home.php';
        }
        break;

    case '/properties':
    case '/properties/':
        try {
            $controller = new \App\Http\Controllers\HomeController();
            $controller->properties();
        } catch (Exception $e) {
            include 'views/properties.php';
        }
        break;

    case '/about':
        try {
            $controller = new \App\Http\Controllers\HomeController();
            $controller->about();
        } catch (Exception $e) {
            include 'views/about.php';
        }
        break;

    case '/contact':
        try {
            $controller = new \App\Http\Controllers\HomeController();
            $controller->contact();
        } catch (Exception $e) {
            include 'views/contact.php';
        }
        break;

    case '/projects':
        try {
            $controller = new \App\Http\Controllers\HomeController();
            $controller->projects();
        } catch (Exception $e) {
            include 'views/projects.php';
        }
        break;

    case '/admin':
    case '/admin/':
        try {
            $controller = new \App\Http\Controllers\Admin\AdminController();
            $controller->dashboard();
        } catch (Exception $e) {
            include 'views/admin.php';
        }
        break;

    default:
        if (preg_match('/^\/properties\/(\d+)$/', $path, $matches)) {
            try {
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

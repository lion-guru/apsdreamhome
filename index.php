7<?php
/**
 * APS Dream Home - Fixed MVC Router
 * Using the existing HomeController and MVC structure properly
 */

// Define essential constants and functions first
define('BASE_URL', '/apsdreamhome/');
define('APP_ROOT', __DIR__);

// Define essential functions that MVC controllers need
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

// Define view() function that controllers use
if (!function_exists('view')) {
    function view($template, $data = [], $layout = 'layouts/base') {
        // Extract data to make variables available in templates
        extract($data, EXTR_SKIP);
        
        // For now, just include the view directly
        $viewFile = __DIR__ . '/app/views/' . $template . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            // Fallback to our simple views
            $simpleViewFile = __DIR__ . '/views/' . basename($template) . '.php';
            if (file_exists($simpleViewFile)) {
                include $simpleViewFile;
            } else {
                echo "<h1>View not found: $template</h1>";
            }
        }
    }
}

// Simple autoloader function instead of complex Autoloader class
spl_autoload_register(function ($class) {
    // Convert namespace to file path
    $file = str_replace('\\', '/', $class) . '.php';
    
    // Check in app directory
    $appFile = __DIR__ . '/app/' . $file;
    if (file_exists($appFile)) {
        require_once $appFile;
        return;
    }
    
    // Check in root directory
    $rootFile = __DIR__ . '/' . $file;
    if (file_exists($rootFile)) {
        require_once $rootFile;
        return;
    }
});

// Get the requested path
$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($request_uri, PHP_URL_PATH);
$path = str_replace('/apsdreamhome', '', $path);

// Route to proper MVC controllers
switch ($path) {
    case '/':
    case '':
        try {
            $controller = new \App\Http\Controllers\HomeController();
            $controller->index();
            echo "<!-- ✅ MVC HomeController working! -->";
        } catch (Exception $e) {
            echo "<!-- ❌ MVC Error: " . htmlspecialchars($e->getMessage()) . " -->";
            // Fallback to simple view
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
        
    case '/admin/login':
        include 'views/admin_login.php';
        break;
        
    case '/admin/dashboard':
        include 'views/admin_dashboard.php';
        break;
        
    case '/admin/logout':
        include 'views/admin_logout.php';
        break;
        
    default:
        // Check for property details
        if (preg_match('/^\/properties\/(\d+)$/', $path, $matches)) {
            try {
                $controller = new \App\Http\Controllers\Property\PropertyController();
                $controller->show($matches[1]);
            } catch (Exception $e) {
                include 'views/property_details.php';
            }
        } else {
            http_response_code(404);
            include 'views/404.php';
        }
        break;
}
?>

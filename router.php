<?php
/**
 * APS Dream Home - Main Router
 * Handles all incoming requests and routes them to the appropriate controller
 */

// Define security constant for database connection
define('INCLUDED_FROM_MAIN', true);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_name('APS_DREAM_HOME_SESSID');
    session_set_cookie_params([
        'lifetime' => 86400, // 24 hours
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Set default timezone
date_default_timezone_set('Asia/Kolkata');

// Define base paths
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH);
define('UPLOAD_PATH', ROOT_PATH . '/uploads');

// Define base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'];
$base_url = $protocol . $domainName . str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

define('BASE_URL', rtrim($base_url, '/'));

// Include configuration
require_once 'includes/config/config.php';
require_once 'includes/functions.php';

// Define routes
$routes = [
    // Public routes
    '' => 'home.php',
    'home' => 'home.php',
    'about' => 'about.php',
    'contact' => 'contact.php',
    'properties' => 'properties.php',
    'property/([0-9]+)' => 'property_details.php?id=$1',
    
    // Authentication routes
    'login' => 'auth/login.php',
    'register' => 'auth/register.php',
    'logout' => 'auth/logout.php',
    'forgot-password' => 'auth/forgot_password.php',
    'reset-password' => 'auth/reset_password.php',
    
    // User dashboard routes
    'dashboard' => 'user/dashboard.php',
    'profile' => 'user/profile.php',
    'properties/my' => 'user/my_properties.php',
    'properties/add' => 'user/add_property.php',
    'properties/edit/([0-9]+)' => 'user/edit_property.php?id=$1',
    'bookings' => 'user/bookings.php',
    'favorites' => 'user/favorites.php',
    'messages' => 'user/messages.php',
    'notifications' => 'user/notifications.php',
    'settings' => 'user/settings.php',
    
    // API routes
    'api/properties' => 'api/properties.php',
    'api/properties/([0-9]+)' => 'api/property_details.php',
    'api/bookings' => 'api/bookings.php',
    'api/messages' => 'api/messages.php',
    
    // Admin routes - Using MVC Controllers
    'admin' => 'admin/dashboard',
    'admin/dashboard' => 'admin/dashboard',
    'admin/properties' => 'admin/properties',
    'admin/users' => 'admin/users',
    'admin/leads' => 'admin/leads',
    'admin/reports' => 'admin/reports',
    'admin/settings' => 'admin/settings',
    'admin/database' => 'admin/database',
    'admin/logs' => 'admin/logs',
    'admin/bookings' => 'admin/bookings',
    
    // Support routes
    'support' => 'support.php',
    'support/tickets' => 'user/support_tickets.php',
    'support/ticket/([0-9]+)' => 'user/view_ticket.php?id=$1',
    
    // 404 page (catch-all route)
    '404' => 'errors/404.php'
];

// Get the requested URL
$request_uri = str_replace('/apsdreamhomefinal', '', $_SERVER['REQUEST_URI']);
$request_uri = strtok($request_uri, '?');
$request_uri = trim($request_uri, '/');

// Default to home if no URI
if (empty($request_uri)) {
    $request_uri = 'home';
}

// Route the request
$found = false;
$file_path = '';

// Simple exact match first
if (isset($routes[$request_uri])) {
    $route = $routes[$request_uri];

    // Check if this is an MVC controller route (admin/*)
    if (strpos($route, '/') !== false && strpos($route, 'admin/') === 0) {
        $found = true;
        handleMVCRequest($route);
        exit();
    } elseif (file_exists($route)) {
        $file_path = $route;
        $found = true;
    }
} else {
    // Try pattern matching for dynamic routes
    foreach ($routes as $route => $file) {
        // Skip if it's a simple route (already checked)
        if (strpos($route, '(') === false) {
            continue;
        }

        // Convert route to regex pattern
        $pattern = '#^' . $route . '$#';

        // Check if the current request matches the route
        if (preg_match($pattern, $request_uri, $matches)) {
            // Remove the full match from matches
            array_shift($matches);

            // Replace placeholders in the file path
            $file_path = str_replace('$1', $matches[0] ?? '', $file);
            $file_path = ltrim($file_path, '/');

            // Check if the file exists
            if (file_exists($file_path)) {
                $found = true;
                require_once $file_path;
                exit();
            }
            break;
        }
    }
}

/**
 * Handle MVC controller requests
 */
function handleMVCRequest($route) {
    // Parse controller and method from route
    $parts = explode('/', $route);
    $controllerName = ucfirst($parts[0]) . 'Controller';
    $methodName = $parts[1] ?? 'index';

    $controllerPath = APP_PATH . "/controllers/{$controllerName}.php";

    if (file_exists($controllerPath)) {
        require_once $controllerPath;

        $fullControllerName = "App\\Controllers\\{$controllerName}";

        if (class_exists($fullControllerName)) {
            $controller = new $fullControllerName();

            if (method_exists($controller, $methodName)) {
                $controller->$methodName();
                return;
            }
        }
    }

    // Fallback to 404 if MVC route fails
    show404();
}

/**
 * Show 404 page
 */
function show404() {
    header("HTTP/1.0 404 Not Found");
    if (file_exists('errors/404.php')) {
        require_once 'errors/404.php';
    } else {
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>404 - Page Not Found</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="bg-light">
            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-md-8 text-center">
                        <h1 class="display-1 text-danger">404</h1>
                        <h2>Page Not Found</h2>
                        <p class="lead">The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
                        <a href="' . BASE_URL . '" class="btn btn-primary">Go to Homepage</a>
                    </div>
                </div>
            </div>
        </body>
        </html>';
    }
    exit();
}

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
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
$base_url = $protocol . $domainName . $scriptPath;

if (!defined('BASE_URL')) {
    define('BASE_URL', rtrim($base_url, '/'));
}

// Include configuration
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/hybrid_template_system.php';

// Define routes
$routes = [
    // Public routes
    '' => 'index.php',
    'home' => 'index.php',
    
    // Main navigation routes
    'about' => 'about.php',
    'contact' => 'contact.php',
    'properties' => 'properties.php',
    'projects' => 'projects.php',
    'blog' => 'blog.php',
    'gallery' => 'gallery.php',
    'team' => 'team.php',
    'testimonials' => 'testimonials.php',
    'career' => 'career.php',
    'faq' => 'faq.php',
    'news' => 'news.php',
    'downloads' => 'downloads.php',
    
    // Property related routes
    'property/([0-9]+)' => 'property_details.php?id=$1',
    'property/category/([^/]+)' => 'properties.php?category=$1',
    'property-management' => 'property_management.php',
    'resell' => 'resell_properties.php',
    'resell/([0-9]+)' => 'resell_property_details.php?id=$1',
    
    // Project related routes
    'project/([0-9]+)' => 'project_details.php?id=$1',
    'project/location/([^/]+)' => 'projects.php?location=$1',
    
    // Service routes
    'services' => 'services.php',
    'legal-services' => 'legal_services.php',
    'financial-services' => 'financial_services.php',
    'interior-design' => 'interior_design.php',
    
    // Blog related routes
    'blog/([^/]+)' => 'blog_post.php?slug=$1',
    'blog/category/([^/]+)' => 'blog.php?category=$1',
    'blog/author/([^/]+)' => 'blog.php?author=$1',
    
    // Search routes
    'search' => 'search.php',
    'search/properties' => 'search.php?type=properties',
    'search/projects' => 'search.php?type=projects',
    'search/blog' => 'search.php?type=blog',
    
    // Additional pages
    'privacy-policy' => 'privacy_policy.php',
    'terms-of-service' => 'terms_of_service.php',
    'sitemap' => 'sitemap.php',
    'colonies' => 'colonies.php',
    
    // Utility pages
    'coming-soon' => 'coming_soon.php',
    'maintenance' => 'maintenance.php',
    'thank-you' => 'thank_you.php',
    
    // Form handlers
    'submit/contact' => 'contact_handler.php',
    'submit/property-inquiry' => 'property_inquiry_handler.php',
    'submit/job-application' => 'job_application_handler.php',

    // Authentication routes
    'login' => 'auth/login.php',
    'register' => 'auth/register.php',
    'logout' => 'logout.php',
    'forgot-password' => 'forgot_password.php',
    'reset-password' => 'reset_password.php',

    // User dashboard routes - Map to existing files
    'dashboard' => 'dash.php',
    'customer-dashboard' => 'customer_dashboard.php',
    'associate-dashboard' => 'dashasso.php',
    'profile' => 'profile.php',
    'properties/my' => 'properties.php', // Will need parameter handling
    'properties/add' => 'properties.php', // Will need parameter handling
    'properties/edit/([0-9]+)' => 'property_details.php?id=$1',
    'bookings' => 'bookings.php',
    'favorites' => 'favorites.php',
    'messages' => 'messages.php',
    'notifications' => 'notifications.php',
    'settings' => 'settings.php',

    // Admin routes - Direct file access for now
    'admin' => 'admin.php',
    'admin/dashboard' => 'admin/admin_dashboard.php',
    'admin/properties' => 'admin/properties.php',
    'admin/users' => 'admin/manage_users.php',
    'admin/leads' => 'admin/leads.php',
    'admin/reports' => 'admin/reports.php',
    'admin/settings' => 'admin/manage_site_settings.php',
    'admin/database' => 'admin/db_health_check_and_fix.php',
    'admin/logs' => 'admin/log_viewer.php',
    'admin/bookings' => 'admin/bookings.php',

    // API routes
    'api/properties' => 'api/properties.php',
    'api/properties/([0-9]+)' => 'api/property_details.php',
    'api/bookings' => 'api/bookings.php',
    'api/messages' => 'api/messages.php',

    // Support routes
    'support' => 'support.php',
    'support/tickets' => 'admin/support_tickets.php',

    // 404 page (catch-all route)
    '404' => 'errors/404.php'
];

// Get the requested URL
$request_uri = $_SERVER['REQUEST_URI'];

// Remove query string
$query_string_pos = strpos($request_uri, '?');
if ($query_string_pos !== false) {
    $request_uri = substr($request_uri, 0, $query_string_pos);
}

// Remove leading slash and any path issues
$request_uri = trim($request_uri, '/');

// Handle specific path adjustments if needed
if (strpos($request_uri, 'apsdreamhomefinal/') === 0) {
    $request_uri = str_replace('apsdreamhomefinal/', '', $request_uri);
}

// Default to home if no URI
if (empty($request_uri)) {
    $request_uri = 'home';
}

/**
 * Enhanced Auto-Routing System
 * Automatically handles page requests based on file structure
 */

// Enhanced routing with auto-discovery
function enhancedAutoRouting($request_uri, $routes) {
    // First check explicit routes
    if (isset($routes[$request_uri])) {
        $route = $routes[$request_uri];
        if (file_exists($route)) {
            return $route;
        }
    }

    // Auto-discover pages in root directory
    $possibleFiles = [
        $request_uri . '.php',
        strtolower($request_uri) . '.php',
        str_replace('-', '_', $request_uri) . '.php'
    ];

    foreach ($possibleFiles as $file) {
        if (file_exists($file)) {
            return $file;
        }
    }

    // Check in subdirectories that are likely to contain pages
    $subdirectories = [
        'associate_dir',
        'customer',
        'admin',
        'api',
        'auth'
    ];

    foreach ($subdirectories as $subdir) {
        $filePath = $subdir . '/' . $request_uri . '.php';
        if (file_exists($filePath)) {
            return $filePath;
        }
    }

    return false;
}

// Route the request
$found = false;
$file_path = '';

// Use enhanced auto-routing
$file_path = enhancedAutoRouting($request_uri, $routes);
if ($file_path) {
    $found = true;
    require_once $file_path;
    exit();
}

// Simple exact match first (fallback)
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

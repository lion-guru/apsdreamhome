<?php
/**
 * APS Dream Home - Main Router
 * Handles all incoming requests and routes them to the appropriate controller
 */

// Define security constant for database connection
if (!defined('INCLUDED_FROM_MAIN')) {
    define('INCLUDED_FROM_MAIN', true);
}

// Set error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Disable direct display for better error handling
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

// Define environment (development/production)
define('ENVIRONMENT', 'development'); // Change to 'production' for live site

// Set error display based on environment
if (ENVIRONMENT === 'development') {
    // For development, we'll log errors but handle them through our custom handler
    error_reporting(E_ALL);
    ini_set('display_errors', '1'); // Show errors directly in development mode
} else {
    // For production, we'll only log errors and use our custom error pages
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
}

// Start session with enhanced security
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
        'sid_length' => 48,
        'sid_bits_per_character' => 6
    ]);
}

// Set default timezone

// ==========================================
// ROUTING SYSTEM INTEGRATION
// ==========================================

// Get the route from URL parameter or default to empty for homepage
$route = $_GET['route'] ?? '';

// If no route is specified, default to homepage
if (empty($route)) {
    $route = 'home';
}

// Handle error routes
if (isset($_GET['error'])) {
    $error_code = $_GET['error'];
    header("HTTP/1.0 $error_code");
    if (file_exists('error.php')) {
        require_once 'error.php';
        exit();
    }
}

// Define routes array (from router.php)
$routes = [
    // Public routes
    '' => 'homepage.php',
    'home' => 'homepage.php',
    'homepage' => 'homepage.php',

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
    'property' => 'property_details.php',
    'property-management' => 'property_management.php',
    'resell' => 'resell_properties.php',
    'featured-properties' => 'featured_properties.php',

    // Service routes
    'services' => 'services.php',
    'legal-services' => 'legal_services.php',
    'financial-services' => 'financial_services.php',
    'interior-design' => 'interior_design.php',

    // Additional pages
    'privacy-policy' => 'privacy_policy.php',
    'sitemap' => 'sitemap.php',
    'colonies' => 'colonies.php',

    // Utility pages
    'coming-soon' => 'coming_soon.php',
    'maintenance' => 'maintenance.php',
    'thank-you' => 'thank_you.php',

    // Authentication routes
    'login' => 'login.php',
    'register' => 'registration.php',
    'logout' => 'logout.php',

    // Dashboard routes
    'dashboard' => 'dash.php',
    'customer-dashboard' => 'customer_dashboard.php',
    'associate-dashboard' => 'dashasso.php',

    // Admin routes
    'admin' => 'admin.php',

    // API routes
    'api/properties' => 'api/properties.php',

    // Support routes
    'support' => 'support.php',
];

// Check if this is a direct route match
if (isset($routes[$route])) {
    $file_path = $routes[$route];

    // Handle special cases for routes that need parameters
    if ($route === 'property' && isset($_GET['id'])) {
        $file_path = 'property_details.php';
    }

    if (file_exists($file_path)) {
        require_once $file_path;
        exit();
    }
}

// Auto-discover pages if exact match not found
$possible_files = [
    $route . '.php',
    strtolower($route) . '.php',
    str_replace('-', '_', $route) . '.php'
];

foreach ($possible_files as $file) {
    if (file_exists($file)) {
        require_once $file;
        exit();
    }
}

// If no route matches, show 404
header("HTTP/1.0 404 Not Found");
if (file_exists('error.php')) {
    require_once 'error.php';
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
                    <a href="http://localhost/apsdreamhomefinal/" class="btn btn-primary">Go to Homepage</a>
                </div>
            </div>
        </div>
    </body>
    </html>';
}
exit();

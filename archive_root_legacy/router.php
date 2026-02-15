<?php
/**
 * APS Dream Home - Main Router
 * Handles all incoming requests and routes them to the appropriate controller
 */

// Define security constant for database connection
if (!defined('INCLUDED_FROM_MAIN')) {
    define('INCLUDED_FROM_MAIN', true);
}

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
    define('BASE_URL', rtrim($base_url, '/') . '/');
}

// Serve static files directly when using PHP built-in server
if (php_sapi_name() === 'cli-server') {
    $requestedPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $staticFile = PUBLIC_PATH . $requestedPath;
    
    // Only serve as static file if it's NOT a PHP file
    if ($requestedPath && is_file($staticFile) && !preg_match('/\.php$/i', $requestedPath)) {
        return false; // Let built-in server serve assets (css/js/images/fonts)
    }
}

// Include configuration
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/hybrid_template_system.php';

// Map to enhanced unified templates where applicable
// Existing pages have been migrated to EnhancedUniversalTemplate variants

// Define routes
$routes = [
    // Public routes
    '' => 'homepage.php',
    'home' => 'homepage.php',
    
    // Main navigation routes
    'about' => 'about_template_new.php',
    'contact' => 'contact_template_new.php',
    'properties' => 'properties_template_new.php',
    'projects' => 'projects.php',
    'blog' => 'blog.php',
    'gallery' => 'gallery.php',
    'team' => 'team.php', // keep legacy if exists; update when enhanced version available
    'testimonials' => 'testimonials.php',
    'career' => 'career.php',
    'faq' => 'faq.php',
    'news' => 'news.php',
    'downloads' => 'downloads.php',
    
    // Property related routes
    'property/([0-9]+)' => 'project-details.php?id=$1',
    'property/category/([^/]+)' => 'properties.php?category=$1',
    'property-management' => 'property_management.php',
    'resell' => 'resell_properties.php',
    'resell/([0-9]+)' => 'resell_property_details.php?id=$1',
    
    // Project related routes
    'project/([0-9]+)' => 'project-details.php?id=$1',
    'project/location/([^/]+)' => 'projects.php?location=$1',
    
    // Service routes
    'services' => 'services.php',
    'legal-services' => 'legal_services.php',
    'financial-services' => 'financial_services.php',
    'interior-design' => 'interior_design.php',
    
    // Blog related routes
    'blog/([0-9]+)' => 'blog-details.php?id=$1',
    'blog/category/([^/]+)' => 'blog.php?category=$1',
    'blog/tag/([^/]+)' => 'blog.php?tag=$1',
    
    // User authentication routes
    'login' => 'login.php',
    'register' => 'registration.php',
    'logout' => 'logout.php',
    'forgot-password' => 'forgot_password.php',
    'reset-password' => 'reset_password.php',
    'profile' => 'profile.php',
    'dashboard' => 'dashboard.php',
    
    // Admin routes
    'admin' => 'admin/dashboard.php',
    'admin/login' => 'admin/login.php',
    'admin/logout' => 'admin/logout.php',
    'admin/dashboard' => 'admin/dashboard.php',
    'admin/properties' => 'admin/properties.php',
    'admin/projects' => 'admin/projects.php',
    'admin/users' => 'admin/users.php',
    'admin/blog' => 'admin/blog.php',
    'admin/settings' => 'admin/settings.php',
    'admin/reports' => 'admin/reports.php',
    
    // Error pages
    'error/404' => 'error_pages/404.php',
    'error/500' => 'error_pages/500.php',
    'error/403' => 'error_pages/403.php',
    
    // API routes
    'api/properties' => 'api/properties.php',
    'api/projects' => 'api/projects.php',
    'api/contact' => 'api/contact.php',
    'api/newsletter' => 'api/newsletter.php',
    'api/booking' => 'api/booking.php',
    
    // Payment routes
    'payment/process' => 'payment/process.php',
    'payment/success' => 'payment/success.php',
    'payment/failed' => 'payment/failed.php',
    'payment/webhook' => 'payment/webhook.php',
    
    // WhatsApp routes
    'whatsapp/send' => 'whatsapp/send.php',
    'whatsapp/webhook' => 'whatsapp/webhook.php',
    
    // Test routes
    'test/error/404' => 'test_error.php',
    'test/error/500' => 'test_error_500.php',
    'test/performance' => 'test_performance.php',
    'test/database' => 'test_database.php',
    'test/email' => 'test_email.php',
    'test/whatsapp' => 'test_whatsapp.php',
    'test/payment' => 'test_payment.php',
    
    // Legal pages
    'privacy-policy' => 'privacy_policy.php',
    'terms-of-service' => 'terms_of_service.php',
    'cookie-policy' => 'cookie_policy.php',
    'disclaimer' => 'disclaimer.php',
    
    // Sitemap and feeds
    'sitemap.xml' => 'sitemap_xml.php',
    'sitemap' => 'sitemap.php',
    'rss' => 'rss.php',
    'atom' => 'atom.php',
    
    // AJAX endpoints
    'ajax/search' => 'ajax/search.php',
    'ajax/contact' => 'ajax/contact.php',
    'ajax/newsletter' => 'ajax/newsletter.php',
    'ajax/property-inquiry' => 'ajax/property_inquiry.php',
    'ajax/project-inquiry' => 'ajax/project_inquiry.php',
    'ajax/booking' => 'ajax/booking.php',
    'ajax/calculator' => 'ajax/calculator.php',
    
    // Calculator
    'calculator' => 'calculator.php',
    'emi-calculator' => 'emi_calculator.php',
    'loan-calculator' => 'loan_calculator.php',
    
    // Documents
    'documents' => 'documents.php',
    'downloads' => 'downloads.php',
    
    // Landing pages
    'landing/([a-zA-Z0-9-]+)' => 'landing.php?page=$1',
    
    // Default catch-all route for enhanced pages
    '([a-zA-Z0-9-]+)' => 'enhanced_router.php?page=$1',
];

/**
 * Enhanced Auto-Routing System
 * Automatically discovers and routes to the appropriate file
 */
function enhancedAutoRouting($requestedPath) {
    // Remove leading/trailing slashes
    $requestedPath = trim($requestedPath, '/');
    
    // If empty, route to homepage
    if (empty($requestedPath)) {
        return PUBLIC_PATH . '/homepage.php';
    }
    
    // Convert path to filename format
    $fileName = str_replace('-', '_', $requestedPath) . '.php';
    $fileName2 = str_replace('-', '', $requestedPath) . '.php';
    $fileName3 = str_replace('-', '-', $requestedPath) . '.php';
    
    // Check for template variants
    $possibleFiles = [
        APP_PATH . '/views/home/' . $requestedPath . '.php',
        APP_PATH . '/views/home/' . $fileName,
        $fileName2,
        $fileName3,
        APP_PATH . '/views/templates/' . $requestedPath . '.php',
        APP_PATH . '/views/templates/' . $fileName,
        APP_PATH . '/views/pages/' . $requestedPath . '.php',
        APP_PATH . '/views/pages/' . $fileName,
        APP_PATH . '/views/' . $requestedPath . '.php',
        APP_PATH . '/views/' . $fileName,
        APP_PATH . '/views/components/' . $requestedPath . '.php',
        APP_PATH . '/views/components/' . $fileName,
        APP_PATH . '/views/modules/' . $requestedPath . '.php',
        APP_PATH . '/views/modules/' . $fileName,
        APP_PATH . '/views/sections/' . $requestedPath . '.php',
        APP_PATH . '/views/sections/' . $fileName,
        APP_PATH . '/views/features/' . $requestedPath . '.php',
        APP_PATH . '/views/features/' . $fileName,
        APP_PATH . '/views/includes/' . $requestedPath . '.php',
        APP_PATH . '/views/includes/' . $fileName,
        APP_PATH . '/views/inc/' . $requestedPath . '.php',
        APP_PATH . '/views/inc/' . $fileName,
        APP_PATH . '/views/template_' . $requestedPath . '.php',
        APP_PATH . '/views/template_' . $fileName,
        APP_PATH . '/views/page_' . $requestedPath . '.php',
        APP_PATH . '/views/page_' . $fileName,
        APP_PATH . '/views/component_' . $requestedPath . '.php',
        APP_PATH . '/views/component_' . $fileName,
        APP_PATH . '/views/section_' . $requestedPath . '.php',
        APP_PATH . '/views/section_' . $fileName,
        APP_PATH . '/views/feature_' . $requestedPath . '.php',
        APP_PATH . '/views/feature_' . $fileName,
        APP_PATH . '/views/' . $requestedPath . '_template.php',
        APP_PATH . '/views/' . $fileName . '_template.php',
        APP_PATH . '/views/' . $requestedPath . '_page.php',
        APP_PATH . '/views/' . $fileName . '_page.php',
        APP_PATH . '/views/' . $requestedPath . '_component.php',
        APP_PATH . '/views/' . $fileName . '_component.php',
        APP_PATH . '/views/' . $requestedPath . '_section.php',
        APP_PATH . '/views/' . $fileName . '_section.php',
        APP_PATH . '/views/' . $requestedPath . '_feature.php',
        APP_PATH . '/views/' . $fileName . '_feature.php',
        APP_PATH . '/views/' . $requestedPath . '_view.php',
        APP_PATH . '/views/' . $fileName . '_view.php',
        APP_PATH . '/views/' . $requestedPath . '_module.php',
        APP_PATH . '/views/' . $fileName . '_module.php',
    ];
    
    // Check each possible file
    foreach ($possibleFiles as $file) {
        if (file_exists($file)) {
            return $file;
        }
    }
    
    // Check for directory index
    if (is_dir(APP_PATH . '/views/' . $requestedPath)) {
        $indexFiles = ['index.php', 'home.php', 'default.php', 'main.php'];
        foreach ($indexFiles as $indexFile) {
            if (file_exists(APP_PATH . '/views/' . $requestedPath . '/' . $indexFile)) {
                return APP_PATH . '/views/' . $requestedPath . '/' . $indexFile;
            }
        }
    }
    
    // Check for category-based routing
    $pathParts = explode('/', $requestedPath);
    if (count($pathParts) > 1) {
        $category = $pathParts[0];
        $item = $pathParts[1];
        
        $categoryFiles = [
            APP_PATH . '/views/' . $category . '/' . $item . '.php',
        APP_PATH . '/views/' . $category . '/details_' . $item . '.php',
        APP_PATH . '/views/' . $category . '/view_' . $item . '.php',
        APP_PATH . '/views/' . $category . '/show_' . $item . '.php',
        APP_PATH . '/views/' . $category . '/item_' . $item . '.php',
        APP_PATH . '/views/category_' . $category . '.php',
        APP_PATH . '/views/category_' . $category . '_details.php',
        ];
        
        foreach ($categoryFiles as $file) {
            if (file_exists($file)) {
                return $file;
            }
        }
    }
    
    return false;
}

// Get the requested path
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestedPath = parse_url($requestUri, PHP_URL_PATH);
$requestedPath = trim($requestedPath, '/');

// Check if the request matches any defined route
foreach ($routes as $pattern => $target) {
    // Convert route pattern to regex
    $regex = '#^' . $pattern . '$#';
    
    if (preg_match($regex, $requestedPath, $matches)) {
        // Remove the full match from the beginning
        array_shift($matches);
        
        // Replace placeholders in the target
        $targetFile = $target;
        foreach ($matches as $index => $value) {
            $targetFile = str_replace('$' . ($index + 1), $value, $targetFile);
        }
        
        // Check if the target file exists
        $targetPath = PUBLIC_PATH . '/' . $targetFile;
        if (file_exists($targetPath)) {
            // Route to the target file
            require_once $targetPath;
            exit;
        }
    }
}

// Try enhanced auto-routing
$autoRoutedFile = enhancedAutoRouting($requestedPath);
if ($autoRoutedFile && file_exists($autoRoutedFile)) {
    require_once $autoRoutedFile;
    exit;
}

// Check for error pages
$errorPages = [
    '404' => 'error_pages/404.php',
    '403' => 'error_pages/403.php',
    '500' => 'error_pages/500.php',
    '401' => 'error_pages/401.php',
    '400' => 'error_pages/400.php',
];

// Try to find a suitable error page
foreach ($errorPages as $code => $errorPage) {
    $errorPagePath = PUBLIC_PATH . '/' . $errorPage;
    if (file_exists($errorPagePath)) {
        http_response_code((int)$code);
        require_once $errorPagePath;
        exit;
    }
}

// If no route or error page is found, show a generic error
http_response_code(404);
echo '<!DOCTYPE html>';
echo '<html>';
echo '<head>';
echo '<title>404 - Page Not Found</title>';
echo '<style>';
echo 'body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }';
echo 'h1 { color: #e74c3c; }';
echo 'p { color: #666; }';
echo '</style>';
echo '</head>';
echo '<body>';
echo '<h1>404 - Page Not Found</h1>';
echo '<p>The page you are looking for could not be found.</p>';
echo '<p>Requested URL: ' . htmlspecialchars($requestedPath) . '</p>';
echo '</body>';
echo '</html>';
exit;
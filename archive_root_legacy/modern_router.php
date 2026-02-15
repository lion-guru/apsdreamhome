<?php
/**
 * Modern Router Integration
 * APS Dream Home - Enhanced Routing System
 * 
 * This file integrates the modern MVC routing system with backward compatibility
 */

// Define security constant
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
    define('BASE_URL', rtrim($base_url, '/'));
}

// Load configuration
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Load modern application bootstrap
require_once APP_PATH . '/bootstrap.php';

/**
 * Enhanced Route Loader
 * Loads and registers all routing systems
 */
class EnhancedRouteLoader {
    private $app;
    private $legacyRoutes = [];
    private $modernRoutes = [];
    private $webRoutes = [];
    
    public function __construct($app) {
        $this->app = $app;
        $this->loadAllRoutes();
    }
    
    /**
     * Load all routing systems
     */
    private function loadAllRoutes() {
        // Load legacy routes from router.php
        $this->loadLegacyRoutes();
        
        // Load modern MVC routes
        $this->loadModernRoutes();
        
        // Load web routes
        $this->loadWebRoutes();
    }
    
    /**
     * Load legacy routes from router.php
     */
    private function loadLegacyRoutes() {
        $legacyRouterFile = ROOT_PATH . '/router.php';
        if (file_exists($legacyRouterFile)) {
            // Extract routes array from legacy router
            ob_start();
            $routes = [];
            
            // Define the routes array that router.php expects
            $this->legacyRoutes = [
                // Public routes
                '' => 'homepage.php',
                'home' => 'homepage.php',
                'about' => 'about_template_new.php',
                'contact' => 'contact_template_new.php',
                'properties' => 'properties_template_new.php',
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
                'blog/([^/]+)' => 'blog_post.php?slug=$1',
                'blog/category/([^/]+)' => 'blog.php?category=$1',
                'blog/author/([^/]+)' => 'blog.php?author=$1',
                
                // Search routes
                'search' => 'search.php',
                'search/properties' => 'search.php?type=properties',
                'search/projects' => 'search.php?type=projects',
                'search/blog' => 'search.php?type=blog',
                
                // Additional pages
                'privacy-policy' => 'privacy-policy.php',
                'terms-of-service' => 'terms-of-service.php',
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

                // User dashboard routes
                'dashboard' => 'dash.php',
                'customer-dashboard' => 'customer_dashboard.php',
                'associate-dashboard' => 'dashasso.php',
                'profile' => 'profile.php',
                'properties/my' => 'properties.php',
                'properties/add' => 'properties.php',
                'properties/edit/([0-9]+)' => 'property_details.php?id=$1',
                'bookings' => 'bookings.php',
                'favorites' => 'favorites.php',
                'messages' => 'messages.php',
                'notifications' => 'notifications.php',
                'settings' => 'settings.php',

                // Admin routes
                'admin' => 'admin.php',
                'admin/properties' => 'admin/properties.php',
                'admin/users' => 'admin/manage_users.php',
                'admin/leads' => 'admin/leads.php',
                'admin/reports' => 'admin/reports.php',
                'admin/settings' => 'admin/manage_site_settings.php',
                'admin/database' => 'admin/db_health_check_and_fix.php',
                'admin/logs' => 'admin/log_viewer.php',
                'admin/bookings' => 'admin/bookings.php',

                // API routes
                'api/test' => 'api/test.php',
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
            
            ob_end_clean();
        }
    }
    
    /**
     * Load modern MVC routes
     */
    private function loadModernRoutes() {
        $modernRoutesFile = APP_PATH . '/core/routes.php';
        if (file_exists($modernRoutesFile)) {
            // The modern routes file returns a router instance
            $this->modernRoutes = require $modernRoutesFile;
        }
    }
    
    /**
     * Load web routes
     */
    private function loadWebRoutes() {
        $webRoutesFile = ROOT_PATH . '/routes/web.php';
        if (file_exists($webRoutesFile)) {
            $routes = require $webRoutesFile;
            if (is_array($routes) && isset($routes['webRoutes'])) {
                $this->webRoutes = $routes['webRoutes'];
            }
        }
    }
    
    /**
     * Dispatch request using modern routing first, fallback to legacy
     */
    public function dispatch($requestUri) {
        // Remove query string
        $queryPos = strpos($requestUri, '?');
        if ($queryPos !== false) {
            $requestUri = substr($requestUri, 0, $queryPos);
        }
        
        // Normalize request URI
        $requestUri = trim($requestUri, '/');
        
        // Try modern MVC routing first
        if ($this->tryModernRouting($requestUri)) {
            return true;
        }
        
        // Try web routes
        if ($this->tryWebRoutes($requestUri)) {
            return true;
        }
        
        // Fallback to legacy routing
        if ($this->tryLegacyRouting($requestUri)) {
            return true;
        }
        
        // 404 Not Found
        $this->handle404();
        return false;
    }
    
    /**
     * Try modern MVC routing
     */
    private function tryModernRouting($requestUri) {
        if (!$this->modernRoutes) {
            return false;
        }
        
        try {
            // Create a new request instance
            $request = new \App\Core\Request();
            
            // Set the request URI
            $request->setUri('/' . $requestUri);
            
            // Dispatch through the modern router
            $response = $this->app->router->dispatch($request);
            
            if ($response) {
                $response->send();
                return true;
            }
        } catch (Exception $e) {
            error_log("Modern routing failed: " . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Try web routes
     */
    private function tryWebRoutes($requestUri) {
        if (empty($this->webRoutes)) {
            return false;
        }
        
        // Get request method
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Check public routes
        foreach ($this->webRoutes as $routeType => $routesByMethod) {
            if (isset($routesByMethod[$method])) {
                foreach ($routesByMethod[$method] as $route => $handler) {
                    if ($this->matchRoute($requestUri, $route)) {
                        return $this->handleWebRoute($handler);
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Try legacy routing
     */
    private function tryLegacyRouting($requestUri) {
        if (empty($this->legacyRoutes)) {
            return false;
        }
        
        // Check exact matches first
        if (isset($this->legacyRoutes[$requestUri])) {
            return $this->handleLegacyRoute($this->legacyRoutes[$requestUri]);
        }
        
        // Check pattern matches
        foreach ($this->legacyRoutes as $pattern => $target) {
            if (strpos($pattern, '(') !== false) {
                // This is a regex pattern
                if (preg_match('#^' . $pattern . '$#', $requestUri, $matches)) {
                    return $this->handleLegacyPatternRoute($pattern, $target, $matches);
                }
            }
        }
        
        return false;
    }
    
    /**
     * Handle web route
     */
    private function handleWebRoute($handler) {
        // Parse controller@action format
        if (strpos($handler, '@') !== false) {
            list($controller, $action) = explode('@', $handler);
            
            // Try to load and call the controller
            try {
                $controllerClass = "App\\Controllers\\$controller";
                if (class_exists($controllerClass)) {
                    $controllerInstance = new $controllerClass($this->app);
                    if (method_exists($controllerInstance, $action)) {
                        $controllerInstance->$action();
                        return true;
                    }
                }
            } catch (Exception $e) {
                error_log("Web route handler failed: " . $e->getMessage());
            }
        }
        
        return false;
    }
    
    /**
     * Handle legacy route
     */
    private function handleLegacyRoute($target) {
        $targetFile = ROOT_PATH . '/' . $target;
        
        if (file_exists($targetFile)) {
            require_once $targetFile;
            return true;
        }
        
        return false;
    }
    
    /**
     * Handle legacy pattern route with parameters
     */
    private function handleLegacyPatternRoute($pattern, $target, $matches) {
        // Replace parameters in target
        $targetFile = ROOT_PATH . '/' . $target;
        
        // Set GET parameters from matches
        if (count($matches) > 1) {
            for ($i = 1; $i < count($matches); $i++) {
                $_GET['param_' . $i] = $matches[$i];
            }
        }
        
        if (file_exists($targetFile)) {
            require_once $targetFile;
            return true;
        }
        
        return false;
    }
    
    /**
     * Match route pattern
     */
    private function matchRoute($requestUri, $route) {
        // Remove leading slash from route
        $route = trim($route, '/');
        
        // Exact match
        if ($requestUri === $route) {
            return true;
        }
        
        // Parameter matching (simple implementation)
        if (strpos($route, '{') !== false) {
            // Convert route to regex
            $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
            return preg_match('#^' . $pattern . '$#', $requestUri);
        }
        
        return false;
    }
    
    /**
     * Handle 404 error
     */
    private function handle404() {
        http_response_code(404);
        
        $error404File = ROOT_PATH . '/errors/404.php';
        if (file_exists($error404File)) {
            require_once $error404File;
        } else {
            echo '<!DOCTYPE html>
<html>
<head>
    <title>404 Not Found</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        h1 { color: #e74c3c; }
        .back-link { color: #3498db; text-decoration: none; }
    </style>
</head>
<body>
    <h1>404 - Page Not Found</h1>
    <p>The page you are looking for could not be found.</p>
    <p><a href="' . BASE_URL . '" class="back-link">Go back to homepage</a></p>
</body>
</html>';
        }
    }
}

// Initialize the enhanced route loader
$routeLoader = new EnhancedRouteLoader($app);

// Get the request URI
$requestUri = $_SERVER['REQUEST_URI'];

// Remove base path if present
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath && $basePath !== '/' && $basePath !== '\\') {
    $requestUri = str_replace($basePath, '', $requestUri);
}

// Dispatch the request
$routeLoader->dispatch($requestUri);
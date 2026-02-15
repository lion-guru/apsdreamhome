<?php
/**
 * Route Loader Integration System
 * APS Dream Home - Unified Routing
 * 
 * This file integrates all routing systems into a unified interface
 */

class RouteLoader {
    private $app;
    private $routes = [];
    private $middleware = [];
    private $currentGroup = '';
    
    public function __construct($app) {
        $this->app = $app;
        $this->initializeRoutes();
    }
    
    /**
     * Initialize all routing systems
     */
    private function initializeRoutes() {
        // Load legacy routes
        $this->loadLegacyRoutes();
        
        // Load modern MVC routes
        $this->loadModernRoutes();
        
        // Load web routes
        $this->loadWebRoutes();
        
        // Load API routes
        $this->loadApiRoutes();
        
        // Load admin routes
        $this->loadAdminRoutes();
    }
    
    /**
     * Load legacy routes from router.php
     */
    private function loadLegacyRoutes() {
        $legacyRouterFile = ROOT_PATH . '/router.php';
        if (file_exists($legacyRouterFile)) {
            // Extract routes from legacy router
            $legacyRoutes = $this->extractLegacyRoutes($legacyRouterFile);
            
            foreach ($legacyRoutes as $pattern => $target) {
                $this->routes[] = [
                    'type' => 'legacy',
                    'pattern' => $pattern,
                    'target' => $target,
                    'method' => 'GET',
                    'middleware' => []
                ];
            }
        }
    }
    
    /**
     * Extract routes from legacy router.php file
     */
    private function extractLegacyRoutes($filePath) {
        $content = file_get_contents($filePath);
        $routes = [];
        
        // Extract routes array using regex
        if (preg_match('/\$routes\s*=\s*array\s*\((.*?)\);/s', $content, $matches)) {
            $routesArray = $matches[1];
            
            // Parse individual routes
            preg_match_all('/[\'"](.*?)[\'"]\s*=>\s*[\'"](.*?)[\'"]/s', $routesArray, $routeMatches, PREG_SET_ORDER);
            
            foreach ($routeMatches as $match) {
                $pattern = trim($match[1]);
                $target = trim($match[2]);
                $routes[$pattern] = $target;
            }
        }
        
        return $routes;
    }
    
    /**
     * Load modern MVC routes from app/core/routes.php
     */
    private function loadModernRoutes() {
        $modernRoutesFile = APP_PATH . '/core/routes.php';
        if (file_exists($modernRoutesFile)) {
            // Include the modern routes file
            $router = require $modernRoutesFile;
            
            // Extract routes from the router instance
            if (method_exists($router, 'getRoutes')) {
                $modernRoutes = $router->getRoutes();
                
                foreach ($modernRoutes as $route) {
                    $this->routes[] = [
                        'type' => 'modern',
                        'pattern' => $route['pattern'],
                        'target' => $route['handler'],
                        'method' => $route['method'] ?? 'GET',
                        'middleware' => $route['middleware'] ?? [],
                        'router' => $router
                    ];
                }
            }
        }
    }
    
    /**
     * Load web routes from routes/web.php
     */
    private function loadWebRoutes() {
        $webRoutesFile = ROOT_PATH . '/routes/web.php';
        if (file_exists($webRoutesFile)) {
            $webRoutes = require $webRoutesFile;
            
            if (is_array($webRoutes)) {
                foreach ($webRoutes as $method => $routes) {
                    if (is_array($routes)) {
                        foreach ($routes as $pattern => $handler) {
                            $this->routes[] = [
                                'type' => 'web',
                                'pattern' => $pattern,
                                'target' => $handler,
                                'method' => $method,
                                'middleware' => []
                            ];
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Load API routes
     */
    private function loadApiRoutes() {
        $apiRoutesFile = ROOT_PATH . '/routes/api.php';
        if (file_exists($apiRoutesFile)) {
            $apiRoutes = require $apiRoutesFile;
            
            if (is_array($apiRoutes)) {
                foreach ($apiRoutes as $method => $routes) {
                    if (is_array($routes)) {
                        foreach ($routes as $pattern => $handler) {
                            $this->routes[] = [
                                'type' => 'api',
                                'pattern' => 'api/' . $pattern,
                                'target' => $handler,
                                'method' => $method,
                                'middleware' => ['api']
                            ];
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Load admin routes
     */
    private function loadAdminRoutes() {
        $adminRoutes = [
            // Admin dashboard
            ['GET', 'admin', 'admin/dashboard.php'],
            ['GET', 'admin/dashboard', 'admin/dashboard.php'],
            
            // User management
            ['GET', 'admin/users', 'admin/users.php'],
            ['GET', 'admin/users/create', 'admin/users_create.php'],
            ['POST', 'admin/users/store', 'admin/users_store.php'],
            ['GET', 'admin/users/edit/([0-9]+)', 'admin/users_edit.php'],
            ['POST', 'admin/users/update/([0-9]+)', 'admin/users_update.php'],
            ['DELETE', 'admin/users/delete/([0-9]+)', 'admin/users_delete.php'],
            
            // Property management
            ['GET', 'admin/properties', 'admin/properties.php'],
            ['GET', 'admin/properties/create', 'admin/properties_create.php'],
            ['POST', 'admin/properties/store', 'admin/properties_store.php'],
            ['GET', 'admin/properties/edit/([0-9]+)', 'admin/properties_edit.php'],
            ['POST', 'admin/properties/update/([0-9]+)', 'admin/properties_update.php'],
            ['DELETE', 'admin/properties/delete/([0-9]+)', 'admin/properties_delete.php'],
            
            // Booking management
            ['GET', 'admin/bookings', 'admin/bookings.php'],
            ['GET', 'admin/bookings/([0-9]+)', 'admin/booking_details.php'],
            ['POST', 'admin/bookings/update/([0-9]+)', 'admin/bookings_update.php'],
            
            // Category management
            ['GET', 'admin/categories', 'admin/categories.php'],
            ['POST', 'admin/categories/store', 'admin/categories_store.php'],
            ['DELETE', 'admin/categories/delete/([0-9]+)', 'admin/categories_delete.php'],
            
            // Location management
            ['GET', 'admin/locations', 'admin/locations.php'],
            ['POST', 'admin/locations/store', 'admin/locations_store.php'],
            ['DELETE', 'admin/locations/delete/([0-9]+)', 'admin/locations_delete.php'],
            
            // Reports
            ['GET', 'admin/reports', 'admin/reports.php'],
            ['GET', 'admin/reports/sales', 'admin/reports_sales.php'],
            ['GET', 'admin/reports/users', 'admin/reports_users.php'],
            ['GET', 'admin/reports/properties', 'admin/reports_properties.php'],
            
            // Settings
            ['GET', 'admin/settings', 'admin/settings.php'],
            ['POST', 'admin/settings/update', 'admin/settings_update.php'],
            
            // System tools
            ['GET', 'admin/system-status', 'admin/system_status.php'],
            ['GET', 'admin/logs', 'admin/logs.php'],
            ['GET', 'admin/database', 'admin/database.php']
        ];
        
        foreach ($adminRoutes as $route) {
            $this->routes[] = [
                'type' => 'admin',
                'pattern' => $route[1],
                'target' => $route[2],
                'method' => $route[0],
                'middleware' => ['auth', 'admin']
            ];
        }
    }
    
    /**
     * Dispatch the current request
     */
    public function dispatch() {
        $requestUri = $_SERVER['REQUEST_URI'];
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        
        // Remove query string
        $queryPos = strpos($requestUri, '?');
        if ($queryPos !== false) {
            $requestUri = substr($requestUri, 0, $queryPos);
        }
        
        // Remove base path
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath && $basePath !== '/' && $basePath !== '\\') {
            $requestUri = str_replace($basePath, '', $requestUri);
        }
        
        // Normalize request URI
        $requestUri = trim($requestUri, '/');
        
        // Find matching route
        $matchedRoute = $this->findMatchingRoute($requestUri, $requestMethod);
        
        if ($matchedRoute) {
            return $this->handleRoute($matchedRoute);
        }
        
        // Check for special cases
        if ($this->handleSpecialCases($requestUri)) {
            return true;
        }
        
        // 404 Not Found
        $this->handle404();
        return false;
    }
    
    /**
     * Find matching route
     */
    private function findMatchingRoute($requestUri, $requestMethod) {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }
            
            if ($this->matchPattern($requestUri, $route['pattern'])) {
                return $route;
            }
        }
        
        return null;
    }
    
    /**
     * Match route pattern
     */
    private function matchPattern($requestUri, $pattern) {
        // Exact match
        if ($requestUri === $pattern) {
            return true;
        }
        
        // Pattern matching
        if (strpos($pattern, '(') !== false) {
            // This is a regex pattern
            return preg_match('#^' . $pattern . '$#', $requestUri);
        }
        
        // Parameter matching (simple implementation)
        if (strpos($pattern, '{') !== false) {
            // Convert route to regex
            $regex = preg_replace('/\{[^}]+\}/', '([^/]+)', $pattern);
            return preg_match('#^' . $regex . '$#', $requestUri);
        }
        
        return false;
    }
    
    /**
     * Handle matched route
     */
    private function handleRoute($route) {
        // Apply middleware
        if (!$this->applyMiddleware($route['middleware'])) {
            return false;
        }
        
        // Handle different route types
        switch ($route['type']) {
            case 'legacy':
                return $this->handleLegacyRoute($route['target']);
                
            case 'modern':
                return $this->handleModernRoute($route);
                
            case 'web':
                return $this->handleWebRoute($route['target']);
                
            case 'api':
                return $this->handleApiRoute($route['target']);
                
            case 'admin':
                return $this->handleAdminRoute($route['target']);
                
            default:
                return $this->handleDefaultRoute($route['target']);
        }
    }
    
    /**
     * Apply middleware
     */
    private function applyMiddleware($middleware) {
        foreach ($middleware as $mw) {
            if (!$this->applySingleMiddleware($mw)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Apply single middleware
     */
    private function applySingleMiddleware($middleware) {
        switch ($middleware) {
            case 'auth':
                return $this->checkAuthentication();
                
            case 'admin':
                return $this->checkAdminAccess();
                
            case 'api':
                return $this->checkApiAccess();
                
            default:
                return true;
        }
    }
    
    /**
     * Check authentication
     */
    private function checkAuthentication() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirectToLogin();
            return false;
        }
        
        return true;
    }
    
    /**
     * Check admin access
     */
    private function checkAdminAccess() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->handleUnauthorized();
            return false;
        }
        
        return true;
    }
    
    /**
     * Check API access
     */
    private function checkApiAccess() {
        // Check for API key or token
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? null;
        
        if (!$apiKey) {
            $this->handleApiUnauthorized();
            return false;
        }
        
        // Validate API key (implement your validation logic)
        if (!$this->validateApiKey($apiKey)) {
            $this->handleApiUnauthorized();
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate API key
     */
    private function validateApiKey($apiKey) {
        // Implement your API key validation logic
        // This is a placeholder implementation
        return !empty($apiKey) && strlen($apiKey) >= 32;
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
     * Handle modern route
     */
    private function handleModernRoute($route) {
        if (isset($route['router']) && method_exists($route['router'], 'dispatch')) {
            return $route['router']->dispatch();
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
     * Handle API route
     */
    private function handleApiRoute($handler) {
        header('Content-Type: application/json');
        
        try {
            // Parse controller@action format
            if (strpos($handler, '@') !== false) {
                list($controller, $action) = explode('@', $handler);
                
                $controllerClass = "App\\Controllers\\Api\\$controller";
                if (class_exists($controllerClass)) {
                    $controllerInstance = new $controllerClass($this->app);
                    if (method_exists($controllerInstance, $action)) {
                        $response = $controllerInstance->$action();
                        echo json_encode($response);
                        return true;
                    }
                }
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
            error_log("API route handler failed: " . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Handle admin route
     */
    private function handleAdminRoute($target) {
        $targetFile = ROOT_PATH . '/' . $target;
        
        if (file_exists($targetFile)) {
            require_once $targetFile;
            return true;
        }
        
        return false;
    }
    
    /**
     * Handle default route
     */
    private function handleDefaultRoute($target) {
        $targetFile = ROOT_PATH . '/' . $target;
        
        if (file_exists($targetFile)) {
            require_once $targetFile;
            return true;
        }
        
        return false;
    }
    
    /**
     * Handle special cases
     */
    private function handleSpecialCases($requestUri) {
        // Homepage
        if (empty($requestUri)) {
            require_once ROOT_PATH . '/index_modern.php';
            return true;
        }
        
        // Modern homepage
        if ($requestUri === 'home' || $requestUri === 'index') {
            require_once ROOT_PATH . '/index_modern.php';
            return true;
        }
        
        return false;
    }
    
    /**
     * Redirect to login
     */
    private function redirectToLogin() {
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
    
    /**
     * Handle unauthorized access
     */
    private function handleUnauthorized() {
        http_response_code(403);
        echo '<!DOCTYPE html>
<html>
<head>
    <title>Access Denied</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        h1 { color: #e74c3c; }
        .back-link { color: #3498db; text-decoration: none; }
    </style>
</head>
<body>
    <h1>Access Denied</h1>
    <p>You do not have permission to access this page.</p>
    <p><a href="' . BASE_URL . '" class="back-link">Go back to homepage</a></p>
</body>
</html>';
        exit;
    }
    
    /**
     * Handle API unauthorized access
     */
    private function handleApiUnauthorized() {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unauthorized', 'message' => 'Invalid or missing API key']);
        exit;
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

// Create and use the route loader
$app = new stdClass(); // Create a basic app object
$app->config = new stdClass();
$app->config->base_url = BASE_URL ?? '/';
$app->config->debug = getenv('APP_DEBUG') === 'true';

$routeLoader = new RouteLoader($app);
$routeLoader->dispatch();
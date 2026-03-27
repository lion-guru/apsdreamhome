<?php

error_log("ROUTER FILE: Loading router.php - " . __FILE__);

/**
 * APS Dream Home - Router Class
 * Handle all routing logic
 */
class Router
{
    public $routes = [];
    private $lastMethod;
    private $lastPath;

    public function __construct()
    {
        // Router initialized
    }

    public function get($path, $handler)
    {
        return $this->addRoute('GET', $path, $handler);
    }

    public function post($path, $handler)
    {
        return $this->addRoute('POST', $path, $handler);
    }

    public function put($path, $handler)
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    public function delete($path, $handler)
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute($method, $path, $handler)
    {
        // Normalize route path by removing leading slash, but preserve root
        if ($path !== '/') {
            $path = ltrim($path, '/');
        }

        $this->routes[$method][$path] = [
            'handler' => $handler,
            'middleware' => []
        ];

        $this->lastMethod = $method;
        $this->lastPath = $path;

        return $this;
    }

    public function middleware($middleware)
    {
        if ($this->lastMethod && $this->lastPath) {
            $this->routes[$this->lastMethod][$this->lastPath]['middleware'][] = $middleware;
        }
        return $this;
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function dispatch($uri = null)
    {
        $method = $_SERVER['REQUEST_METHOD'];
        
        // HEAD requests should use GET routes
        if ($method === 'HEAD') {
            $method = 'GET';
        }

        if ($uri === null) {
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        }

        error_log("ROUTER DEBUG: Method: $method, URI: $uri");

        // For XAMPP localhost, handle /apsdreamhome/public base path
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $port = $_SERVER['SERVER_PORT'] ?? '';
        error_log("ROUTER DEBUG: Host: $host, Port: $port");

        // Check if we're running directly on localhost without subdirectory
        if (str_contains($host, 'localhost') && !str_contains($uri, '/apsdreamhome')) {
            // Running directly on localhost:port - don't modify URI
            // Keep URI as is: /terms, /privacy, /admin, etc.
            error_log("ROUTER DEBUG: Direct localhost access - keeping URI as: $uri");
        } elseif (str_contains($host, 'localhost')) {
            $basePath = '/apsdreamhome';
            $publicPath = '/apsdreamhome/public';

            // Check if URI starts with public path
            if (strpos($uri, $publicPath) === 0) {
                // Remove /apsdreamhome/public from URI
                $uri = substr($uri, strlen($publicPath));
                // If URI is empty after removal, set to root
                if (empty($uri)) {
                    $uri = '/';
                }
                error_log("ROUTER DEBUG: Removed public path - URI now: $uri");
            } elseif (strpos($uri, $basePath) === 0) {
                // Remove /apsdreamhome from URI
                $uri = substr($uri, strlen($basePath));
                // If URI is empty after removal, set to root
                if (empty($uri)) {
                    $uri = '/';
                }
                error_log("ROUTER DEBUG: Removed base path - URI now: $uri");
            }
        }

        // Remove script filename if present (for direct file access like index_minimal.php)
        $scriptName = basename($_SERVER['SCRIPT_NAME']);
        $scriptPath = '/' . $scriptName;
        error_log("ROUTER DEBUG: Script name: $scriptName, Script path: $scriptPath");
        // Only remove script path if it's actually a PHP file, not a route
        if ($scriptName === 'index.php' || str_ends_with($scriptName, '.php')) {
            if (strpos($uri, $scriptPath) === 0) {
                $uri = substr($uri, strlen($scriptPath));
                error_log("ROUTER DEBUG: Removed script path - URI now: $uri");
            }
        } else {
            error_log("ROUTER DEBUG: Script name is not PHP, treating as route - keeping URI: $uri");
        }

        // For XAMPP localhost, keep leading slash for route matching consistency
        $host = $_SERVER['HTTP_HOST'] ?? '';
        error_log("ROUTER DEBUG: Before slash processing - URI: $uri");
        if (str_contains($host, 'localhost')) {
            // Keep leading slash for route matching (routes defined with /prefix)
            // Don't strip leading slash - keep as /terms, /privacy, etc.
            error_log("ROUTER DEBUG: Keeping leading slash for localhost");
        } else {
            // For production, keep leading slash
            if ($uri !== '/' && !str_starts_with($uri, '/')) {
                $uri = '/' . $uri;
            }
        }

        // If URI is empty, set to root
        if (empty($uri)) {
            $uri = '/';
            error_log("ROUTER DEBUG: URI was empty, set to root: $uri");
        }

        // Normalize URI - strip leading slash for consistent route matching
        $uriForMatching = ($uri === '/') ? '/' : ltrim($uri, '/');
        
        // Remove .php extension if present (for direct file access)
        $uri = preg_replace('/\.php$/', '', $uri);
        error_log("ROUTER DEBUG: After all processing - Final URI: $uri");
        error_log("FINAL ROUTER URI: $uri");

        // Debug: Check if route exists
        error_log("ROUTER DEBUG: Looking for route: $method $uriForMatching");

        // Debug output - remove in production
        file_put_contents(__DIR__ . '/../storage/logs/router_debug.log', 
            "Looking for: $method $uriForMatching\n", FILE_APPEND);
        file_put_contents(__DIR__ . '/../storage/logs/router_debug.log', 
            "Is set? " . (isset($this->routes[$method][$uriForMatching]) ? 'YES' : 'NO') . "\n", FILE_APPEND);
        if (isset($this->routes[$method][$uriForMatching])) {
            file_put_contents(__DIR__ . '/../storage/logs/router_debug.log', 
                "Value: " . print_r($this->routes[$method][$uriForMatching], true), FILE_APPEND);
        }

        if (!isset($this->routes[$method][$uriForMatching])) {
            error_log("ROUTER DEBUG: Route not found in routes array");
            // Try to match dynamic routes with parameters
            $matchedRoute = $this->matchDynamicRoute($method, $uriForMatching);
            if ($matchedRoute) {
                $routeData = $matchedRoute['route_data'];
                $params = $matchedRoute['params'];
                error_log("ROUTER DEBUG: Found dynamic route");
            } else {
                error_log("ROUTER DEBUG: No dynamic route found, returning 404");
                http_response_code(404);
                echo '<h1>404 - Page Not Found</h1>';
                echo '<p>The page you requested could not be found.</p>';
                echo '<p>Route: ' . htmlspecialchars($method . ' ' . $uriForMatching) . '</p>';
                return;
            }
        } else {
            error_log("ROUTER DEBUG: Found exact route match");
            $routeData = $this->routes[$method][$uriForMatching];
        }

        // Support both old structure (string handler) and new (array with middleware)
        $handler = is_array($routeData) ? $routeData['handler'] : $routeData;
        $middlewareList = is_array($routeData) ? ($routeData['middleware'] ?? []) : [];

        // Execute Middleware
        foreach ($middlewareList as $middlewareClass) {
            if (class_exists($middlewareClass)) {
                $middleware = new $middlewareClass();
                // We need to pass Request object, but for simplicity in this custom router
                // we'll use a basic handle call. 
                // Actual implementation would involve a proper request/response stack.
                if (method_exists($middleware, 'handle')) {
                    // Create a mock request/response if needed, or just let middleware handle globals
                    $request = new \App\Core\Http\Request();
                    $middleware->handle($request, function ($req) {
                        return $req;
                    });
                }
            }
        }

        if (is_callable($handler)) {
            call_user_func($handler);
        } elseif (is_string($handler)) {
            // Parse "Controller@method" format
            if (strpos($handler, '@') !== false) {
                list($controller, $method) = explode('@', $handler);

                // Handle different controller formats
                if (strpos($controller, 'App\\') === 0) {
                    // Full namespace format: "App\Http\Controllers\Auth\AdminAuthController"
                    $controllerClass = $controller;
                    // Remove 'App\' prefix to get relative path from app/ directory
                    $relativePath = substr($controller, 4);
                    $controllerFile = __DIR__ . '/../app/' . str_replace('\\', '/', $relativePath) . '.php';
                } elseif (strpos($controller, 'Admin\\') === 0) {
                    // Admin namespace: "Admin\AdminDashboardController"
                    $controllerClass = "App\\Http\\Controllers\\" . $controller;
                    $relativePath = substr($controller, 6); // Remove 'Admin' prefix (6 chars)
                    $relativePath = ltrim($relativePath, '\\'); // Remove leading backslash if any
                    $controllerFile = __DIR__ . '/../app/Http/Controllers/Admin/' . str_replace('\\', '/', $relativePath) . '.php';
                } else {
                    // Simple or relative format: "HomeController" or "Front\PageController"
                    $controllerClass = "App\\Http\\Controllers\\" . $controller;
                    $controllerFile = __DIR__ . '/../app/Http/Controllers/' . str_replace('\\', '/', $controller) . '.php';
                }

                // Debug logging
                error_log("Loading controller: $controllerClass from $controllerFile");

                if (file_exists($controllerFile)) {
                    require_once $controllerFile;
                    
                    // Check if class exists
                    if (!class_exists($controllerClass)) {
                        error_log("Router Error: Controller class $controllerClass does not exist");
                        echo "Controller class not found: $controllerClass";
                        return;
                    }
                    
                    // Check if method exists
                    if (!method_exists($controllerClass, $method)) {
                        error_log("Router Error: Method $controllerClass::$method does not exist");
                        echo "Method not found: $controllerClass::$method";
                        return;
                    }
                    
                    $controllerInstance = new $controllerClass();

                    // Call method with parameters
                    if (!empty($params)) {
                        call_user_func_array([$controllerInstance, $method], $params);
                    } else {
                        $controllerInstance->$method();
                    }
                } else {
                    echo "Controller not found: $controller (File: $controllerFile)";
                }
            } else {
                // Direct function call
                call_user_func($handler);
            }
        }
    }

    /**
     * Match dynamic routes with parameters
     */
    private function matchDynamicRoute($method, $uri)
    {
        if (!isset($this->routes[$method])) {
            return null;
        }

        foreach ($this->routes[$method] as $route => $handler) {
            // Convert route pattern to regex
            $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                // Remove full match, keep only parameters
                array_shift($matches);
                return [
                    'route_data' => $handler,
                    'params' => $matches
                ];
            }
        }

        return null;
    }
}

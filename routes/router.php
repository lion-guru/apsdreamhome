<?php

/**
 * APS Dream Home - Router
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

        if ($uri === null) {
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        }

        error_log("ROUTER DEBUG: Method: $method, URI: $uri");

        // For XAMPP localhost, handle /apsdreamhome/public base path
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (str_contains($host, 'localhost')) {
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
            } elseif (strpos($uri, $basePath) === 0) {
                // Remove /apsdreamhome from URI
                $uri = substr($uri, strlen($basePath));
                // If URI is empty after removal, set to root
                if (empty($uri)) {
                    $uri = '/';
                }
            }
        }

        // Remove script filename if present (for direct file access like index_minimal.php)
        $scriptName = basename($_SERVER['SCRIPT_NAME']);
        $scriptPath = '/' . $scriptName;
        if (strpos($uri, $scriptPath) === 0) {
            $uri = substr($uri, strlen($scriptPath));
        }

        // For XAMPP localhost, remove leading slash for proper routing
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (str_contains($host, 'localhost')) {
            // Remove leading slash if present (but keep it for root)
            if ($uri !== '/') {
                $uri = ltrim($uri, '/');
            }
        } else {
            // For production, keep leading slash
            if ($uri !== '/' && !str_starts_with($uri, '/')) {
                $uri = '/' . $uri;
            }
        }

        // If URI is empty, set to root
        if (empty($uri)) {
            $uri = '/';
        }

        // Remove .php extension if present (for direct file access)
        $uri = preg_replace('/\.php$/', '', $uri);

        error_log("FINAL ROUTER URI: $uri");

        if (!isset($this->routes[$method][$uri])) {
            // Try to match dynamic routes with parameters
            $matchedRoute = $this->matchDynamicRoute($method, $uri);
            if ($matchedRoute) {
                $routeData = $matchedRoute['route_data'];
                $params = $matchedRoute['params'];
            } else {
                http_response_code(404);
                echo "Page not found: " . htmlspecialchars($uri);
                return;
            }
        } else {
            $routeData = $this->routes[$method][$uri];
            $params = [];
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
                    $middleware->handle($request, function($req) { return $req; });
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
                } else {
                    // Simple or relative format: "HomeController" or "Front\PageController"
                    $controllerClass = "App\\Http\\Controllers\\" . $controller;
                    $controllerFile = __DIR__ . '/../app/Http/Controllers/' . str_replace('\\', '/', $controller) . '.php';
                }

                // Debug logging
                error_log("Loading controller: $controllerClass from $controllerFile");

                if (file_exists($controllerFile)) {
                    require_once $controllerFile;
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

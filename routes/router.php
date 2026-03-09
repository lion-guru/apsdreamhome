<?php

/**
 * APS Dream Home - Router
 * Handle all routing logic
 */
class Router
{
    private $routes = [];

    public function get($path, $handler)
    {
        // Normalize route path by removing leading slash, but preserve root
        if ($path !== '/') {
            $path = ltrim($path, '/');
        }
        $this->routes['GET'][$path] = $handler;
    }

    public function post($path, $handler)
    {
        // Normalize route path by removing leading slash, but preserve root
        if ($path !== '/') {
            $path = ltrim($path, '/');
        }
        $this->routes['POST'][$path] = $handler;
    }

    public function put($path, $handler)
    {
        $this->routes['PUT'][$path] = $handler;
    }

    public function delete($path, $handler)
    {
        $this->routes['DELETE'][$path] = $handler;
    }

    public function getRoutesCount()
    {
        return count($this->routes['GET'] ?? []);
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
        }

        // If URI is empty, set to root
        if (empty($uri)) {
            $uri = '/';
        }

        // Remove .php extension if present (for direct file access)
        $uri = preg_replace('/\.php$/', '', $uri);

        if (!isset($this->routes[$method][$uri])) {
            // Try to match dynamic routes with parameters
            $matchedRoute = $this->matchDynamicRoute($method, $uri);
            if ($matchedRoute) {
                $handler = $matchedRoute['handler'];
                $params = $matchedRoute['params'];
            } else {
                http_response_code(404);
                echo "Page not found: " . htmlspecialchars($uri);
                return;
            }
        } else {
            $handler = $this->routes[$method][$uri];
            $params = [];
        }

        if (is_callable($handler)) {
            call_user_func($handler);
        } elseif (is_string($handler)) {
            // Parse "Controller@method" format
            list($controller, $method) = explode('@', $handler);

            // Handle different controller formats
            if (strpos($controller, '\\') !== false) {
                // Full namespace format: "Property\PropertyController"
                $controllerClass = "App\\Http\\Controllers\\" . $controller;
                $controllerFile = __DIR__ . '/../app/Http/Controllers/' . str_replace('\\', '/', $controller) . '.php';
            } else {
                // Simple format: "HomeController"
                $controllerClass = "App\\Http\\Controllers\\" . $controller;
                $controllerFile = __DIR__ . '/../app/Http/Controllers/' . $controller . '.php';
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
                    'handler' => $handler,
                    'params' => $matches
                ];
            }
        }

        return null;
    }
}

// Router class definition only - instance created in public/index.php

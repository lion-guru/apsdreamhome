<?php

namespace App\Core;

use App\Core\Route;

/**
 * Enhanced Router Class
 * Handles routing with middleware support, parameter binding, and caching
 */
class Router
{
    private array $routes = [];
    private array $namedRoutes = [];
    private array $middleware = [];
    private array $currentGroup = [];
    private bool $cacheEnabled = true;
    private string $cacheFile;
    private array $patterns = [
        ':any' => '([^/]+)',
        ':num' => '([0-9]+)',
        ':all' => '(.*)',
        ':string' => '([a-zA-Z]+)',
        ':slug' => '([a-z0-9-]+)',
        ':uuid' => '([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})',
    ];
    
    /**
     * Router constructor
     */
    public function __construct()
    {
        $this->cacheFile = __DIR__ . '/../../cache/routes.php';
        $this->loadCachedRoutes();
    }
    
    /**
     * Register a GET route
     */
    public function get(string $path, $handler, array $options = []): Route
    {
        return $this->addRoute(['GET'], $path, $handler, $options);
    }
    
    /**
     * Register a POST route
     */
    public function post(string $path, $handler, array $options = []): Route
    {
        return $this->addRoute(['POST'], $path, $handler, $options);
    }
    
    /**
     * Register a PUT route
     */
    public function put(string $path, $handler, array $options = []): Route
    {
        return $this->addRoute(['PUT'], $path, $handler, $options);
    }
    
    /**
     * Register a DELETE route
     */
    public function delete(string $path, $handler, array $options = []): Route
    {
        return $this->addRoute(['DELETE'], $path, $handler, $options);
    }
    
    /**
     * Register a PATCH route
     */
    public function patch(string $path, $handler, array $options = []): Route
    {
        return $this->addRoute(['PATCH'], $path, $handler, $options);
    }
    
    /**
     * Register a route for multiple methods
     */
    public function match(array $methods, string $path, $handler, array $options = []): Route
    {
        return $this->addRoute($methods, $path, $handler, $options);
    }
    
    /**
     * Register a route for all methods
     */
    public function any(string $path, $handler, array $options = []): Route
    {
        return $this->addRoute(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], $path, $handler, $options);
    }
    
    /**
     * Create a route group with shared attributes
     */
    public function group(array $attributes, callable $callback): void
    {
        $previousGroup = $this->currentGroup;
        
        // Accumulate prefixes from parent groups
        if (isset($previousGroup['prefix']) && isset($attributes['prefix'])) {
            $attributes['prefix'] = rtrim($previousGroup['prefix'], '/') . '/' . ltrim($attributes['prefix'], '/');
        }
        
        $this->currentGroup = array_merge($previousGroup, $attributes);
        
        $callback($this);
        
        $this->currentGroup = $previousGroup;
    }
    
    /**
     * Add a route
     */
    private function addRoute(array $methods, string $path, $handler, array $options): Route
    {
        // Apply group attributes
        if (!empty($this->currentGroup)) {
            $options = $this->applyGroupAttributes($options);
            $path = $this->applyGroupPrefix($path);
        }
        
        // Create route object
        $route = new Route($path, $handler, $methods, $options);
        
        // Add to routes collection
        foreach ($methods as $method) {
            $this->routes[strtoupper($method)][] = $route;
        }
        
        // Add to named routes if it has a name
        if ($route->getName()) {
            $this->namedRoutes[$route->getName()] = $route;
        }
        
        return $route;
    }
    
    /**
     * Apply group attributes to route options
     */
    private function applyGroupAttributes(array $options): array
    {
        // Apply middleware
        if (isset($this->currentGroup['middleware'])) {
            $groupMiddleware = is_array($this->currentGroup['middleware']) 
                ? $this->currentGroup['middleware'] 
                : [$this->currentGroup['middleware']];
            
            $routeMiddleware = $options['middleware'] ?? [];
            $routeMiddleware = is_array($routeMiddleware) ? $routeMiddleware : [$routeMiddleware];
            
            $options['middleware'] = array_merge($groupMiddleware, $routeMiddleware);
        }
        
        // Apply other group attributes
        foreach (['prefix', 'namespace', 'host', 'scheme'] as $key) {
            if (isset($this->currentGroup[$key]) && !isset($options[$key])) {
                $options[$key] = $this->currentGroup[$key];
            }
        }
        
        return $options;
    }
    
    /**
     * Apply group prefix to path
     */
    private function applyGroupPrefix(string $path): string
    {
        if (isset($this->currentGroup['prefix'])) {
            $prefix = rtrim($this->currentGroup['prefix'], '/');
            
            // Handle root path
            if ($path === '/') {
                return '/' . $prefix;
            }
            
            $path = '/' . ltrim($path, '/');
            return '/' . ltrim($prefix . $path, '/');
        }
        
        return $path;
    }
    
    /**
     * Dispatch the request
     */
    public function dispatch(string $method, string $path): mixed
    {
        $method = strtoupper($method);
        $path = $this->normalizePath($path);
        
        // Find matching route
        $route = $this->findRoute($method, $path);
        
        if (!$route) {
            throw new \App\Core\Exceptions\RouteNotFoundException("No route found for {$method} {$path}");
        }
        
        // Extract parameters
        $parameters = $this->extractParameters($route, $path);
        
        // Prepare request data
        $request = [
            'method' => $method,
            'path' => $path,
            'parameters' => $parameters,
            'data' => $_REQUEST,
            'headers' => function_exists('getallheaders') ? getallheaders() : $this->getHeadersFallback(),
            'files' => $_FILES,
            'server' => $_SERVER,
        ];
        
        // Execute middleware pipeline
        return $this->executeMiddlewarePipeline($route, $request);
    }
    
    /**
     * Find matching route
     */
    private function findRoute(string $method, string $path): ?Route
    {
        $routes = $this->routes[$method] ?? [];
        
        foreach ($routes as $route) {
            if ($route->matches($path, $method)) {
                return $route;
            }
        }
        
        return null;
    }
    
    /**
     * Extract parameters from path
     */
    private function extractParameters(Route $route, string $path): array
    {
        $parameters = [];
        $routePath = $route->getPath();
        
        // Convert route path to regex
        $pattern = $this->convertToRegex($routePath);
        
        // Extract parameters
        if (preg_match($pattern, $path, $matches)) {
            $paramNames = $this->extractParameterNames($routePath);
            
            foreach ($paramNames as $index => $name) {
                if (isset($matches[$index + 1])) {
                    $parameters[$name] = $matches[$index + 1];
                }
            }
        }
        
        return $parameters;
    }
    
    /**
     * Convert route path to regex
     */
    private function convertToRegex(string $path): string
    {
        // Replace patterns
        $path = str_replace(array_keys($this->patterns), array_values($this->patterns), $path);
        
        // Replace named parameters
        $path = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '([^/]+)', $path);
        $path = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\?\}/', '([^/]*)', $path);
        
        return '#^' . $path . '$#';
    }
    
    /**
     * Extract parameter names from route path
     */
    private function extractParameterNames(string $path): array
    {
        preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_]*)\??\}/', $path, $matches);
        return $matches[1] ?? [];
    }
    
    /**
     * Execute middleware pipeline
     */
    private function executeMiddlewarePipeline(Route $route, array $request): mixed
    {
        $middleware = $route->getMiddleware();
        
        // Build middleware pipeline
        $pipeline = array_reduce(
            array_reverse($middleware),
            function ($next, $middleware) {
                return function ($request) use ($middleware, $next) {
                    return $this->executeMiddleware($middleware, $request, $next);
                };
            },
            function ($request) use ($route) {
                return $this->executeHandler($route, $request);
            }
        );
        
        return $pipeline($request);
    }
    
    /**
     * Execute individual middleware
     */
    private function executeMiddleware(string $middleware, array $request, callable $next): mixed
    {
        // Create middleware instance
        $middlewareClass = $this->resolveMiddleware($middleware);
        
        if (!$middlewareClass) {
            throw new \Exception("Middleware {$middleware} not found");
        }
        
        return $middlewareClass->handle($request, $next);
    }
    
    /**
     * Resolve middleware class
     */
    private function resolveMiddleware(string $middleware): ?object
    {
        // Map middleware aliases to full class names
        $middlewareMap = [
            'auth' => 'App\Core\Middleware\AuthMiddleware',
            'csrf' => 'App\Core\Middleware\CsrfMiddleware',
            'admin' => 'App\Core\Middleware\RoleMiddleware',
            'error' => 'App\Core\Middleware\ErrorMiddleware',
        ];
        
        $className = $middlewareMap[$middleware] ?? $middleware;
        
        if (!class_exists($className)) {
            return null;
        }
        
        return new $className();
    }
    
    /**
     * Execute route handler
     */
    private function executeHandler(Route $route, array $request): mixed
    {
        $handler = $route->getHandler();
        
        if (is_callable($handler)) {
            return call_user_func_array($handler, [$request, $route->getParameters()]);
        }
        
        if (is_string($handler) && strpos($handler, '@') !== false) {
            list($controller, $method) = explode('@', $handler);
            $controller = $this->resolveController($controller, $route);
            
            if (!method_exists($controller, $method)) {
                throw new \Exception("Method {$method} not found in controller " . get_class($controller));
            }
            
            return call_user_func_array([$controller, $method], [$request, $route->getParameters()]);
        }
        
        throw new \Exception("Invalid route handler");
    }
    
    /**
     * Resolve controller class
     */
    private function resolveController(string $controller, Route $route): object
    {
        // Get namespace from route (set by group attributes)
        $namespace = $route->getNamespace();
        
        // Build full class name
        if ($namespace) {
            $className = "App\\Controllers\\{$namespace}\\{$controller}";
        } else {
            $className = "App\\Controllers\\{$controller}";
        }
        
        if (!class_exists($className)) {
            throw new \Exception("Controller {$className} not found");
        }
        
        return new $className();
    }
    
    /**
     * Normalize path
     */
    private function normalizePath(string $path): string
    {
        $path = trim($path, '/');
        return $path === '' ? '/' : '/' . $path;
    }
    
    /**
     * Fallback for getallheaders() function
     */
    private function getHeadersFallback(): array
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$headerName] = $value;
            }
        }
        return $headers;
    }
    
    /**
     * Generate URL for named route
     */
    public function url(string $name, array $parameters = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \Exception("Route {$name} not found");
        }
        
        $route = $this->namedRoutes[$name];
        $path = $route->getPath();
        
        // Replace parameters
        foreach ($parameters as $key => $value) {
            $path = str_replace('{' . $key . '}', $value, $path);
            $path = str_replace('{' . $key . '?}', $value, $path);
        }
        
        // Remove optional parameters that weren't provided
        $path = preg_replace('/\{[a-zA-Z_][a-zA-Z0-9_]*\?\}/', '', $path);
        
        return $path;
    }
    
    /**
     * Load cached routes
     */
    private function loadCachedRoutes(): void
    {
        if (!$this->cacheEnabled || !file_exists($this->cacheFile)) {
            return;
        }
        
        $cached = include $this->cacheFile;
        if ($cached) {
            $this->routes = $cached['routes'] ?? [];
            $this->namedRoutes = $cached['named_routes'] ?? [];
        }
    }
    
    /**
     * Cache routes
     */
    public function cacheRoutes(): void
    {
        if (!$this->cacheEnabled) {
            return;
        }
        
        $cacheDir = dirname($this->cacheFile);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        $data = [
            'routes' => $this->routes,
            'named_routes' => $this->namedRoutes,
            'generated_at' => date('Y-m-d H:i:s'),
        ];
        
        file_put_contents(
            $this->cacheFile,
            '<?php return ' . var_export($data, true) . ';'
        );
    }
    
    /**
     * Get all routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
    
    /**
     * Get named routes
     */
    public function getNamedRoutes(): array
    {
        return $this->namedRoutes;
    }
    
    /**
     * Enable/disable caching
     */
    public function setCacheEnabled(bool $enabled): void
    {
        $this->cacheEnabled = $enabled;
    }
}
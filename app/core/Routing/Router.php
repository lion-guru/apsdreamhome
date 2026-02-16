<?php

namespace App\Core\Routing;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\App;
use Closure;
use Exception;

class Router
{
    /**
     * The application instance
     */
    protected $app;

    /**
     * The route collection instance.
     *
     * @var \App\Core\Routing\RouteCollection
     */
    protected $routes;

    /**
     * The current route being dispatched.
     *
     * @var \App\Core\Routing\Route|null
     */
    protected $currentRoute;

    /**
     * The current request.
     *
     * @var \App\Core\Http\Request
     */
    protected $request;

    /**
     * The route group attributes stack.
     *
     * @var array
     */
    protected $groupStack = [];

    /**
     * The route patterns.
     *
     * @var array
     */
    protected $patterns = [
        'id' => '([0-9]+)',
        'slug' => '([a-z0-9-]+)',
        'hash' => '([a-f0-9]+)',
        'hex' => '([a-f0-9]+)',
        'uuid' => '([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})',
    ];

    /**
     * The HTTP methods that can be used in routes.
     *
     * @var array
     */
    public static $verbs = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    /**
     * Create a new Router instance.
     *
     * @param  \App\Core\App  $app
     * @return void
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->routes = new RouteCollection();
    }

    /**
     * Register a GET route
     */
    public function get($uri, $action)
    {
        return $this->addRoute('GET', $uri, $action);
    }

    /**
     * Register a POST route
     */
    public function post($uri, $action)
    {
        return $this->addRoute('POST', $uri, $action);
    }

    /**
     * Register a PUT route
     */
    public function put($uri, $action)
    {
        return $this->addRoute('PUT', $uri, $action);
    }

    /**
     * Register a PATCH route
     */
    public function patch($uri, $action)
    {
        return $this->addRoute('PATCH', $uri, $action);
    }

    /**
     * Register a DELETE route
     */
    public function delete($uri, $action)
    {
        return $this->addRoute('DELETE', $uri, $action);
    }

    /**
     * Register an OPTIONS route
     */
    public function options($uri, $action)
    {
        return $this->addRoute('OPTIONS', $uri, $action);
    }

    /**
     * Register a route that responds to any HTTP method
     */
    public function any($uri, $action)
    {
        return $this->addRoute(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], $uri, $action);
    }

    /**
     * Register a route with the given methods
     */
    public function match($methods, $uri, $action)
    {
        return $this->addRoute(array_map('strtoupper', (array) $methods), $uri, $action);
    }

    /**
     * Create a route group with shared attributes.
     *
     * @param  array  $attributes
     * @param  \Closure  $callback
     * @return void
     */
    public function group(array $attributes, Closure $callback)
    {
        $this->updateGroupStack($attributes);

        // Once we have updated the group stack, we'll load the provided routes and
        // merge in the group's attributes when the routes are created. After we
        // have created the routes, we will pop the attributes off the stack.
        $this->loadRoutes($callback);

        array_pop($this->groupStack);
    }

    /**
     * Update the group stack with the given attributes.
     *
     * @param  array  $attributes
     * @return void
     */
    protected function updateGroupStack(array $attributes)
    {
        if (!empty($this->groupStack)) {
            $attributes = $this->mergeWithLastGroup($attributes);
        }

        $this->groupStack[] = $attributes;
    }

    /**
     * Merge the given group attributes with the last added group.
     *
     * @param  array  $new
     * @return array
     */
    protected function mergeWithLastGroup($new)
    {
        // Use array_slice to get the last element without modifying the array pointer
        $lastGroup = array_slice($this->groupStack, -1, 1)[0];
        return $this->mergeGroup($new, $lastGroup);
    }

    /**
     * Merge the given group attributes.
     *
     * @param  array  $new
     * @param  array  $old
     * @return array
     */
    protected function mergeGroup($new, $old)
    {
        $new['namespace'] = $this->formatUsesPrefix($new, $old);

        $new['prefix'] = $this->formatGroupPrefix($new, $old);

        if (isset($new['domain'])) {
            unset($old['domain']);
        }

        if (isset($old['as'])) {
            $new['as'] = $old['as'] . ($new['as'] ?? '');
        }

        if (isset($old['suffix'])) {
            $new['suffix'] = $old['suffix'] . ($new['suffix'] ?? '');
        }

        $new['middleware'] = $this->mergeMiddleware($new, $old);

        // Remove the keys we don't want to merge
        $old = array_diff_key($old, array_flip(['namespace', 'prefix', 'as', 'suffix', 'middleware']));

        // Merge the old and new arrays
        return array_merge_recursive($old, $new);
    }

    /**
     * Format the uses prefix for the new group attributes.
     *
     * @param  array  $new
     * @param  array  $old
     * @return string|null
     */
    protected function formatUsesPrefix($new, $old)
    {
        if (isset($new['namespace'])) {
            return isset($old['namespace'])
                ? trim((string)$old['namespace'], '\\\\') . '\\\\' . trim((string)$new['namespace'], '\\\\')
                : trim((string)$new['namespace'], '\\\\');
        }

        return $old['namespace'] ?? null;
    }

    /**
     * Format the prefix for the new group attributes.
     *
     * @param  array  $new
     * @param  array  $old
     * @return string|null
     */
    protected function formatGroupPrefix($new, $old)
    {
        $oldPrefix = $old['prefix'] ?? null;

        if (isset($new['prefix'])) {
            $prefix = trim((string)$new['prefix'], '/');
            return $oldPrefix ? (trim((string)$oldPrefix, '/') . '/' . $prefix) : $prefix;
        }

        return $oldPrefix;
    }

    /**
     * Merge the middleware for the group.
     *
     * @param  array  $new
     * @param  array  $old
     * @return array
     */
    protected function mergeMiddleware($new, $old)
    {
        $middleware = (array) ($old['middleware'] ?? []);

        if (isset($new['middleware'])) {
            $middleware = array_merge($middleware, (array) $new['middleware']);
        }

        return $middleware;
    }

    /**
     * Load the provided routes.
     *
     * @param  \Closure|string  $routes
     * @return void
     */
    protected function loadRoutes($routes)
    {
        if ($routes instanceof Closure) {
            $routes($this);
        } else {
            require $routes;
        }
    }

    /**
     * Add a route to the collection.
     *
     * @param  string|array  $methods
     * @param  string  $uri
     * @param  mixed  $action
     * @return \App\Core\Routing\Route
     */
    protected function addRoute($methods, $uri, $action)
    {
        $methods = (array) $methods;
        $uri = $this->prefixUri($uri);

        $route = $this->createRoute($methods, $uri, $action);

        // If we have groups, merge the group attributes with the route
        if (!empty($this->groupStack)) {
            $this->mergeGroupAttributesIntoRoute($route);
        }

        $this->routes->add($route);

        return $route;
    }

    /**
     * Merge the group attributes into the route.
     *
     * @param  \App\Core\Routing\Route  $route
     * @return void
     */
    protected function mergeGroupAttributesIntoRoute(Route $route)
    {
        $route->setPrefix($this->getGroupPrefix());

        $lastGroup = end($this->groupStack);

        if (isset($lastGroup['middleware'])) {
            $route->middleware($lastGroup['middleware']);
        }

        if (isset($lastGroup['namespace'])) {
            $route->namespace($lastGroup['namespace']);
        }
    }

    /**
     * Get the prefix from the last group on the stack.
     *
     * @return string
     */
    protected function getGroupPrefix()
    {
        if (empty($this->groupStack)) {
            return '';
        }

        // Use array_slice to get the last element without modifying the array pointer
        $lastGroup = array_slice($this->groupStack, -1, 1)[0];

        return $lastGroup['prefix'] ?? '';
    }

    /**
     * Create a new route instance.
     *
     * @param  array|string  $methods
     * @param  string  $uri
     * @param  mixed  $action
     * @return \App\Core\Routing\Route
     */
    protected function createRoute($methods, $uri, $action)
    {
        // If the route is routing to a controller we will parse the route action into
        // an array that can be passed to the Route constructor.
        if ($this->actionReferencesController($action)) {
            $action = $this->convertToControllerAction($action);
        }

        return new Route($methods, $uri, $action);
    }

    /**
     * Determine if the action is a controller action.
     *
     * @param  mixed  $action
     * @return bool
     */
    protected function actionReferencesController($action)
    {
        if ($action instanceof Closure) {
            return false;
        }

        return is_string($action) || (is_array($action) && isset($action['uses']));
    }

    /**
     * Add a controller based route action to the action array.
     *
     * @param  string  $action
     * @return array
     */
    protected function convertToControllerAction($action)
    {
        if (is_string($action)) {
            $action = ['uses' => $action];
        }

        return $action;
    }

    /**
     * Parse the route action.
     *
     * @param  mixed  $action
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function parseAction($action)
    {
        if (is_string($action)) {
            return ['uses' => $action];
        } elseif ($action instanceof Closure) {
            return ['uses' => $action];
        } elseif (is_array($action)) {
            return $action;
        }

        throw new \InvalidArgumentException('Invalid route action');
    }

    /**
     * Prefix the given URI with the last group prefix.
     *
     * @param  string  $uri
     * @return string
     */
    protected function prefixUri($uri)
    {
        $prefix = $this->getGroupPrefix();

        if (empty($prefix)) {
            return $uri;
        }

        return trim((string)$prefix, '/') . '/' . ltrim((string)$uri, '/');
    }

    /**
     * Dispatch the request to the application
     */
    public function dispatch(Request $request)
    {
        $this->request = $request;

        $method = $request->getMethod();
        $uri = $request->path();

        error_log("Router::dispatch() - Method: {$method}, URI: {$uri}");

        // Find the matching route
        $route = $this->findRoute($method, $uri);

        error_log("Router::dispatch() - Route found: " . ($route ? 'yes' : 'no'));

        if (!$route) {
            error_log("Router::dispatch() - No route found, trying legacy fallback");
            // Try legacy fallback if modern route not found
            return $this->handleLegacyFallback($method, $uri);
        }

        $this->currentRoute = $route;

        // Apply route middleware
        error_log("Router::dispatch() - Running route middleware");
        $response = $this->runRouteMiddleware($route);

        if ($response instanceof Response) {
            error_log("Router::dispatch() - Middleware returned response");
            return $response;
        }

        error_log("Router::dispatch() - Executing route action");
        // Execute the route action
        $response = $this->runRoute($route);

        error_log("Router::dispatch() - Preparing response");
        return $this->prepareResponse($response);
    }

    /**
     * Find the route matching the request
     */
    protected function findRoute($method, $uri)
    {
        // Get routes for this method
        $methodRoutes = $this->routes->get($method);

        // First, try to find an exact match
        if (isset($methodRoutes[$uri])) {
            return $methodRoutes[$uri];
        }

        // If no exact match, try to find a parameterized route
        foreach ($methodRoutes as $route) {
            if ($this->matchesRoute($route, $uri)) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Handle legacy route fallback
     */
    protected function handleLegacyFallback($method, $uri)
    {
        // Check if legacy routes file exists and try to find a match
        $legacyRoutesFile = $this->app->basePath('routes/web.php');

        if (file_exists($legacyRoutesFile)) {
            // Include legacy routes configuration
            $webRoutes = [];
            require $legacyRoutesFile;

            // Try to find a legacy route match in public routes (for error testing)
            if (isset($webRoutes['public'][$method][$uri])) {
                return $this->handleLegacyRoute($webRoutes['public'][$method][$uri], $uri);
            }

            // Try to match legacy routes with parameters in public routes
            if (isset($webRoutes['public'][$method])) {
                foreach ($webRoutes['public'][$method] as $routeUri => $handler) {
                    if ($this->matchesLegacyRoute($routeUri, $uri)) {
                        return $this->handleLegacyRoute($handler, $uri);
                    }
                }
            }
        }

        // If no legacy route found, try enhancedAutoRouting from router.php
        if (file_exists($this->app->basePath('router.php'))) {
            require_once $this->app->basePath('router.php');
            if (function_exists('enhancedAutoRouting')) {
                $legacyRouteFile = enhancedAutoRouting($uri);
                if ($legacyRouteFile) {
                    // If enhancedAutoRouting finds a file, include it and return a success response
                    require_once $legacyRouteFile;
                    return new Response('Legacy route handled', 200); // Or a more appropriate response
                }
            }
        }

        // If no legacy route found, return 404
        return $this->handleNotFound();
    }

    /**
     * Handle legacy route execution
     */
    protected function handleLegacyRoute($handler, $uri)
    {
        // Convert legacy route handler to modern format
        if (is_string($handler) && strpos($handler, '@') !== false) {
            // Execute the controller method directly
            list($controller, $method) = explode('@', $handler);

            // Add default controller namespace if not present
            if (!str_contains($controller, '\\')) {
                $controller = 'App\\Http\\Controllers\\' . $controller;
            }

            // Create controller instance
            $controller = $this->app->make($controller);

            if (!method_exists($controller, $method)) {
                return $this->handleNotFound();
            }

            // Execute the method
            $response = $controller->$method();

            return $this->prepareResponse($response);
        }

        // If it's a closure or other format, handle accordingly
        return $this->handleNotFound();
    }

    /**
     * Check if a legacy route matches the URI
     */
    protected function matchesLegacyRoute($routeUri, $uri)
    {
        // Convert legacy route patterns to regex
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routeUri);
        $pattern = '#^' . $pattern . '$#';

        return preg_match($pattern, $uri);
    }

    /**
     * Check if a route matches the given URI
     */
    protected function matchesRoute($route, $uri)
    {
        $pattern = $this->compileRoute($route->uri(), []);

        return (bool) preg_match('#^' . $pattern . '$#', $uri);
    }

    /**
     * Compile the route URI into a regex pattern
     */
    protected function compileRoute($uri, array $wheres = [])
    {
        // Replace route parameters with regex patterns
        $pattern = preg_replace_callback('/\{([^\}]+)\}/', function ($matches) use ($wheres) {
            $param = $matches[1];
            $pattern = $wheres[$param] ?? '[^/]+';

            // Check if the parameter is optional
            if (str_ends_with($param, '?')) {
                $param = rtrim($param, '?');
                $pattern = '(?:' . $pattern . ')?';
            }

            return '(?P<' . $param . '>' . $pattern . ')';
        }, $uri);

        return $pattern;
    }

    /**
     * Run the route middleware
     */
    protected function runRouteMiddleware($route)
    {
        $middleware = $route->getMiddleware();

        foreach ($middleware as $middlewareName) {
            $middlewareInstance = $this->app->make($middlewareName);

            $response = $middlewareInstance->handle($this->request, function ($request) {
                return $this->runRoute($this->currentRoute);
            });

            if ($response instanceof Response) {
                return $response;
            }
        }

        return null;
    }

    /**
     * Run the route action
     */
    protected function runRoute($route)
    {
        $action = $route->getAction();

        if (isset($action['uses'])) {
            return $this->runController($action['uses']);
        }

        if (is_callable($action['uses'])) {
            return $this->runCallable($action['uses']);
        }

        throw new Exception('Invalid route action');
    }

    /**
     * Run a controller action
     */
    protected function runController($controller)
    {
        list($class, $method) = explode('@', $controller);

        // Add default controller namespace if not present
        if (!str_contains($class, '\\')) {
            $class = 'App\\Http\\Controllers\\' . $class;
        } elseif (str_starts_with($class, 'Admin\\') || str_starts_with($class, 'Associate\\') || str_starts_with($class, 'Api\\') || str_starts_with($class, 'Public\\')) {
            $class = 'App\\Http\\Controllers\\' . $class;
        }

        // Try to get controller from container first
        try {
            $controller = $this->app->make($class);
        } catch (Exception $e) {
            // If not found in container, try to instantiate directly
            if (class_exists($class)) {
                $controller = new $class();
            } else {
                throw new Exception("Controller class {$class} not found");
            }
        }

        if (!method_exists($controller, $method)) {
            throw new Exception("Method {$method} does not exist on controller {$class}");
        }

        $parameters = $this->resolveMethodDependencies(
            [$controller, $method],
            $this->request->route()
        );

        return $controller->$method(...$parameters);
    }

    /**
     * Run a callable action
     */
    protected function runCallable(callable $callback)
    {
        $parameters = $this->resolveMethodDependencies(
            $callback,
            $this->request->route()
        );

        return $callback(...$parameters);
    }

    /**
     * Resolve the method dependencies
     */
    protected function resolveMethodDependencies($callback, array $parameters = [])
    {
        if (is_array($callback)) {
            $reflection = new \ReflectionMethod($callback[0], $callback[1]);
        } else {
            $reflection = new \ReflectionFunction($callback);
        }

        $dependencies = [];

        foreach ($reflection->getParameters() as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();

            // If the parameter is in the route parameters, use it
            if (array_key_exists($name, $parameters)) {
                $dependencies[] = $parameters[$name];
                continue;
            }

            // If the parameter is type-hinted, resolve it from the container
            if ($type && !$type->isBuiltin()) {
                $dependencies[] = $this->app->make($type->getName());
                continue;
            }

            // If the parameter is optional, use its default value
            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

            // If we get here, the parameter is required but not provided
            throw new Exception("Unresolvable dependency resolving [{$parameter}] in class {$parameter->getDeclaringClass()->getName()}");
        }

        return $dependencies;
    }

    /**
     * Handle a route not found
     */
    protected function handleNotFound()
    {
        return new Response('Not Found', 404);
    }

    /**
     * Prepare the response for sending.
     *
     * @param  mixed  $response
     * @return \App\Core\Http\Response
     */
    protected function prepareResponse($response)
    {
        if ($response instanceof Response) {
            return $response;
        }

        if (is_array($response) || is_object($response)) {
            return new Response(json_encode($response), 200, ['Content-Type' => 'application/json']);
        }

        if (is_string($response) || is_numeric($response)) {
            return new Response((string) $response);
        }

        return new Response('', 204); // No Content
    }

    /**
     * Get the route collection.
     *
     * @return \App\Core\Routing\RouteCollection
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Get the current request.
     *
     * @return \App\Core\Http\Request|null
     */
    public function getCurrentRequest()
    {
        return $this->request;
    }

    /**
     * Get the current route.
     *
     * @return \App\Core\Routing\Route|null
     */
    public function getCurrentRoute()
    {
        return $this->currentRoute;
    }
}

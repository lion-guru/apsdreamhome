<?php

namespace App\Core\Routing;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\App;
use Closure;
use Exception;

class Router {
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
    public function __construct(App $app) {
        $this->app = $app;
        $this->routes = new RouteCollection();
    }
    
    /**
     * Register a GET route
     */
    public function get($uri, $action) {
        return $this->addRoute('GET', $uri, $action);
    }
    
    /**
     * Register a POST route
     */
    public function post($uri, $action) {
        return $this->addRoute('POST', $uri, $action);
    }
    
    /**
     * Register a PUT route
     */
    public function put($uri, $action) {
        return $this->addRoute('PUT', $uri, $action);
    }
    
    /**
     * Register a PATCH route
     */
    public function patch($uri, $action) {
        return $this->addRoute('PATCH', $uri, $action);
    }
    
    /**
     * Register a DELETE route
     */
    public function delete($uri, $action) {
        return $this->addRoute('DELETE', $uri, $action);
    }
    
    /**
     * Register an OPTIONS route
     */
    public function options($uri, $action) {
        return $this->addRoute('OPTIONS', $uri, $action);
    }
    
    /**
     * Register a route that responds to any HTTP method
     */
    public function any($uri, $action) {
        return $this->addRoute(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], $uri, $action);
    }
    
    /**
     * Register a route with the given methods
     */
    public function match($methods, $uri, $action) {
        return $this->addRoute(array_map('strtoupper', (array) $methods), $uri, $action);
    }
    
    /**
     * Create a route group with shared attributes.
     *
     * @param  array  $attributes
     * @param  \Closure  $callback
     * @return void
     */
    public function group(array $attributes, Closure $callback) {
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
        return $this->mergeGroup($new, end($this->groupStack));
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
                ? trim($old['namespace'], '\\\\') . '\\\\' . trim($new['namespace'], '\\\\') 
                : trim($new['namespace'], '\\\\');
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
            return trim($oldPrefix, '/') . '/' . trim($new['prefix'], '/');
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
    protected function addRoute($methods, $uri, $action) {
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
        
        if (isset($this->groupStack['middleware'])) {
            $route->middleware($this->groupStack['middleware']);
        }
        
        if (isset($this->groupStack['namespace'])) {
            $route->namespace($this->groupStack['namespace']);
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
        
        $lastGroup = end($this->groupStack);
        
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
    protected function createRoute($methods, $uri, $action) {
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
    protected function parseAction($action) {
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
    protected function prefixUri($uri) {
        $prefix = $this->getGroupPrefix();
        
        if (empty($prefix)) {
            return $uri;
        }
        
        return trim($prefix, '/') . '/' . ltrim($uri, '/');
    }
    
    /**
     * Dispatch the request to the application
     */
    public function dispatch(Request $request) {
        $this->request = $request;
        
        $method = $request->method();
        $uri = $request->path();
        
        // Find the matching route
        $route = $this->findRoute($method, $uri);
        
        if (!$route) {
            return $this->handleNotFound();
        }
        
        $this->currentRoute = $route;
        
        // Apply route middleware
        $response = $this->runRouteMiddleware($route);
        
        if ($response instanceof Response) {
            return $response;
        }
        
        // Execute the route action
        $response = $this->runRoute($route);
        
        return $this->prepareResponse($response);
    }
    
    /**
     * Find the route matching the request
     */
    protected function findRoute($method, $uri) {
        // First, try to find an exact match
        if (isset($this->routes[$method][$uri])) {
            return $this->routes[$method][$uri];
        }
        
        // If no exact match, try to find a parameterized route
        foreach ($this->routes[$method] as $route) {
            if ($this->matchesRoute($route, $uri)) {
                return $route;
            }
        }
        
        return null;
    }
    
    /**
     * Check if a route matches the given URI
     */
    protected function matchesRoute($route, $uri) {
        $pattern = $this->compileRoute($route['uri'], $route['where'] ?? []);
        
        return (bool) preg_match('#^' . $pattern . '$#', $uri);
    }
    
    /**
     * Compile the route URI into a regex pattern
     */
    protected function compileRoute($uri, array $wheres = []) {
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
    protected function runRouteMiddleware($route) {
        $middleware = $route['middleware'] ?? [];
        
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
    protected function runRoute($route) {
        $action = $route['action'];
        
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
    protected function runController($controller) {
        list($class, $method) = explode('@', $controller);
        
        $controller = $this->app->make($class);
        
        if (!method_exists($controller, $method)) {
            throw new Exception("Method {$method} does not exist on controller {$class}");
        }
        
        $parameters = $this->resolveMethodDependencies(
            $controller, $method, $this->request->route()
        );
        
        return $controller->$method(...$parameters);
    }
    
    /**
     * Run a callable action
     */
    protected function runCallable(callable $callback) {
        $parameters = $this->resolveMethodDependencies(
            $callback, $this->request->route()
        );
        
        return $callback(...$parameters);
    }
    
    /**
     * Resolve the method dependencies
     */
    protected function resolveMethodDependencies($callback, array $parameters = []) {
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
    protected function handleNotFound() {
        return new Response('Not Found', 404);
    }
    
    /**
     * Prepare the response for sending.
     *
     * @param  mixed  $response
     * @return \App\Core\Http\Response
     */
    protected function prepareResponse($response) {
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


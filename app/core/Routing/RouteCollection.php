<?php

namespace App\Core\Routing;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Exceptions\RouteNotFoundException;
use App\Core\Exceptions\MethodNotAllowedException;
use Closure;

class RouteCollection
{
    /**
     * All of the routes.
     *
     * @var array
     */
    protected $routes = [];

    /**
     * All of the named routes.
     *
     * @var array
     */
    protected $namedRoutes = [];

    /**
     * The current route being dispatched.
     *
     * @var \App\Core\Routing\Route|null
     */
    protected $current;

    /**
     * The current request.
     *
     * @var \App\Core\Http\Request
     */
    protected $request;
    
    /**
     * The HTTP methods that can be used in routes.
     *
     * @var array
     */
    public static $verbs = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    /**
     * Add a route to the collection.
     *
     * @param  \App\Core\Routing\Route  $route
     * @return \App\Core\Routing\Route
     */
    public function add(Route $route)
    {
        $this->addToCollections($route);
        $this->addLookups($route);
        
        return $route;
    }

    /**
     * Add the given route to the arrays of routes.
     *
     * @param  \App\Core\Routing\Route  $route
     * @return void
     */
    protected function addToCollections(Route $route)
    {
        foreach ($route->methods() as $method) {
            $this->routes[$method][$route->uri()] = $route;
        }
    }

    /**
     * Add the route to any route collection lookups.
     *
     * @param  \App\Core\Routing\Route  $route
     * @return void
     */
    protected function addLookups(Route $route)
    {
        if ($name = $route->getName()) {
            $this->namedRoutes[$name] = $route;
        }
    }

    /**
     * Find the first route matching a given request.
     *
     * @param  \App\Core\Http\Request  $request
     * @return \App\Core\Routing\Route
     *
     * @throws \App\Core\Exceptions\RouteNotFoundException
     */
    public function match(Request $request)
    {
        $this->request = $request;
        
        $routes = $this->get($request->getMethod());
        
        $route = $this->matchAgainstRoutes($routes, $request);
        
        if (! is_null($route)) {
            return $route->bind($request);
        }
        
        // If no route was found, check for other methods
        $others = $this->checkForAlternateVerbs($request);
        
        if (count($others) > 0) {
            return $this->getRouteForMethods($request, $others);
        }
        
        throw new RouteNotFoundException(
            "No route found for [{$request->getMethod()}] " . $request->getPathInfo()
        );
    }
    
    /**
     * Determine if a route in the array matches the request.
     *
     * @param  array  $routes
     * @param  \App\Core\Http\Request  $request
     * @param  bool  $includingMethod
     * @return \App\Core\Routing\Route|null
     */
    protected function matchAgainstRoutes(array $routes, $request, $includingMethod = true)
    {
        foreach ($routes as $route) {
            if ($route->matches($request, $includingMethod)) {
                return $route;
            }
        }
        
        return null;
    }
    
    /**
     * Determine if any routes match on another HTTP verb.
     *
     * @param  \App\Core\Http\Request  $request
     * @return array
     */
    protected function checkForAlternateVerbs($request)
    {
        $methods = array_diff(Router::$verbs, [$request->getMethod()]);
        
        $others = [];
        
        foreach ($methods as $method) {
            if (! is_null($this->matchAgainstRoutes($this->get($method), $request, false))) {
                $others[] = $method;
            }
        }
        
        return $others;
    }
    
    /**
     * Get a route (if necessary) that responds when other available methods are present.
     *
     * @param  \App\Core\Http\Request  $request
     * @param  array  $methods
     * @return \App\Core\Routing\Route
     *
     * @throws \App\Core\Exceptions\MethodNotAllowedException
     */
    protected function getRouteForMethods($request, array $methods)
    {
        if ($request->getMethod() === 'OPTIONS') {
            return (new Route('OPTIONS', $request->getPathInfo(), function () use ($methods) {
                return new Response('', 200, ['Allow' => implode(',', $methods)]);
            }))->bind($request);
        }
        
        $this->methodNotAllowed($methods);
        
        // This line is unreachable but required for static analysis
        throw new MethodNotAllowedException($methods);
    }
    
    /**
     * Throw a method not allowed HTTP exception.
     *
     * @param  array  $others
     * @return void
     *
     * @throws \App\Core\Exceptions\MethodNotAllowedException
     */
    protected function methodNotAllowed(array $others)
    {
        throw new MethodNotAllowedException($others);
    }
    
    /**
     * Get all of the routes in the collection.
     *
     * @param  string|null  $method
     * @return array
     */
    public function get($method = null)
    {
        if (is_null($method)) {
            return $this->getRoutes();
        }
        
        return $this->getRoutes()[$method] ?? [];
    }
    
    /**
     * Get all of the routes in the collection.
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }
    
    /**
     * Get all of the routes keyed by their HTTP verb / method.
     *
     * @return array
     */
    public function getRoutesByMethod()
    {
        return $this->routes;
    }
    
    /**
     * Get all of the routes keyed by their name.
     *
     * @return array
     */
    public function getRoutesByName()
    {
        return $this->namedRoutes;
    }
    
    /**
     * Get a route by its name.
     *
     * @param  string  $name
     * @return \App\Core\Routing\Route|null
     */
    public function getByName($name)
    {
        return $this->namedRoutes[$name] ?? null;
    }
    
    /**
     * Get the current route being dispatched.
     *
     * @return \App\Core\Routing\Route|null
     */
    public function getCurrent()
    {
        return $this->current;
    }
    
    /**
     * Set the current route.
     *
     * @param  \App\Core\Routing\Route  $route
     * @return void
     */
    public function setCurrent(Route $route)
    {
        $this->current = $route;
    }
    
    /**
     * Get the current request.
     *
     * @return \App\Core\Http\Request
     */
    public function getRequest()
    {
        return $this->request;
    }
    
    /**
     * Set the current request.
     *
     * @param  \App\Core\Http\Request  $request
     * @return void
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }
}

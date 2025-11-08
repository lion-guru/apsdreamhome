<?php

namespace App\Core\Routing;

use App\Core\Http\Request;
use Closure;

class Route
{
    /**
     * The HTTP methods the route responds to.
     *
     * @var array
     */
    protected $methods = [];

    /**
     * The route URI pattern.
     *
     * @var string
     */
    protected $uri;

    /**
     * The route action.
     *
     * @var array
     */
    protected $action;

    /**
     * The route parameters.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * The parameter names for the route.
     *
     * @var array
     */
    protected $parameterNames = [];

    /**
     * The middleware attached to the route.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * The route prefix.
     *
     * @var string
     */
    protected $prefix = '';
    
    /**
     * The route action namespace.
     *
     * @var string
     */
    protected $namespace = '';

    /**
     * The compiled version of the route.
     *
     * @var string|null
     */
    protected $compiled;
    
    /**
     * The route name.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Create a new Route instance.
     *
     * @param  array|string  $methods
     * @param  string  $uri
     * @param  mixed  $action
     */
    public function __construct($methods, $uri, $action)
    {
        $this->methods = (array) $methods;
        $this->uri = $uri;
        $this->action = $this->parseAction($action);
    }
    
    /**
     * Parse the route action into a standard array.
     *
     * @param  mixed  $action
     * @return array
     */
    protected function parseAction($action)
    {
        if (is_string($action)) {
            return ['uses' => $action];
        } elseif ($action instanceof Closure) {
            return ['uses' => $action];
        }
        
        return $action;
    }
    
    /**
     * Set the route prefix.
     *
     * @param  string  $prefix
     * @return $this
     */
    public function prefix($prefix)
    {
        $this->prefix = trim($prefix, '/') . '/';
        return $this;
    }
    
    /**
     * Get the route prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }
    
    /**
     * Set the route namespace.
     *
     * @param  string  $namespace
     * @return $this
     */
    public function namespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }
    
    /**
     * Get the route namespace.
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }
    
    /**
     * Set the route name.
     *
     * @param  string  $name
     * @return $this
     */
    public function name($name)
    {
        $this->name = $name;
        return $this;
    }
    
    /**
     * Get the route name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Add middleware to the route.
     *
     * @param  string|array  $middleware
     * @return $this
     */
    public function middleware($middleware)
    {
        $this->middleware = array_merge(
            $this->middleware,
            is_array($middleware) ? $middleware : func_get_args()
        );
        
        return $this;
    }
    
    /**
     * Get the route middleware.
     *
     * @return array
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }
    
    /**
     * Get the route's HTTP methods.
     *
     * @return array
     */
    public function methods()
    {
        return $this->methods;
    }
    
    /**
     * Get the route URI.
     *
     * @return string
     */
    public function uri()
    {
        return $this->uri;
    }
    
    /**
     * Get the route's action.
     *
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }
    
    /**
     * Set the route's action.
     *
     * @param  array  $action
     * @return $this
     */
    public function setAction(array $action)
    {
        $this->action = $action;
        return $this;
    }
    
    /**
     * Determine if the route matches a given request.
     *
     * @param  \App\Core\Http\Request  $request
     * @param  bool  $includingMethod
     * @return bool
     */
    public function matches(Request $request, $includingMethod = true)
    {
        $this->compileRoute();
        
        $path = '/' . trim($request->getPathInfo(), '/');
        
        if ($includingMethod && !in_array($request->getMethod(), $this->methods)) {
            return false;
        }

        return (bool) preg_match($this->compiled, $path, $this->parameters);
    }
    
    /**
     * Compile the route into a regular expression.
     *
     * @return void
     */
    protected function compileRoute()
    {
        if (!is_null($this->compiled)) {
            return;
        }

        // Reset parameter names
        $this->parameterNames = [];
        
        // Escape forward slashes in the URI
        $pattern = preg_quote($this->prefix . $this->uri, '#');
        
        // Replace parameter placeholders with regex patterns
        $pattern = preg_replace_callback('/\{(\w+)(<[^>]+>)?(\?)?\}/', function ($matches) {
            $name = $matches[1];
            $pattern = isset($matches[2]) ? trim($matches[2], '<>') : '[^/]+';
            $optional = isset($matches[3]);
            
            $this->parameterNames[] = $name;
            
            return $optional ? "(?:/($pattern))?" : "($pattern)";
        }, $pattern);

        $this->compiled = '#^' . $pattern . '$#s';
    }
    
    /**
     * Get the route parameters.
     *
     * @return array
     */
    public function parameters()
    {
        return $this->parameters;
    }
    
    /**
     * Get a parameter from the route.
     *
     * @param  string  $name
     * @param  mixed  $default
     * @return mixed
     */
    public function parameter($name, $default = null)
    {
        return $this->parameters[$name] ?? $default;
    }
    
    /**
     * Set a parameter on the route.
     *
     * @param  string  $name
     * @param  mixed  $value
     * @return void
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }
    
    /**
     * Get the parameter names for the route.
     *
     * @return array
     */
    public function parameterNames()
    {
        return $this->parameterNames;
    }
    
    /**
     * Bind the route to a given request for execution.
     *
     * @param  \App\Core\Http\Request  $request
     * @return $this
     */
    public function bind(Request $request)
    {
        $this->compileRoute();
        
        $path = '/' . trim($request->getPathInfo(), '/');
        
        if (preg_match($this->compiled, $path, $matches)) {
            $this->parameters = array_intersect_key(
                $matches, 
                array_flip($this->parameterNames)
            );
        }
        
        return $this;
    }
    
    /**
     * Run the route action and return the response.
     *
     * @param  \App\Core\Http\Request  $request
     * @return mixed
     */
    public function run(Request $request)
    {
        $this->bind($request);
        
        $action = $this->getAction();
        
        if (isset($action['uses'])) {
            return $this->runCallable($action['uses'], $this->parameters());
        }
        
        if (is_string($action)) {
            return $this->runController($action, $this->parameters());
        }
        
        throw new \RuntimeException('Invalid route action.');
    }
    
    /**
     * Run a controller based action.
     *
     * @param  string  $action
     * @param  array  $parameters
     * @return mixed
     */
    protected function runController($action, $parameters)
    {
        list($controller, $method) = explode('@', $action);
        
        if (!empty($this->namespace)) {
            $controller = $this->namespace . '\\' . $controller;
        }
        
        $controllerInstance = new $controller();
        
        return call_user_func_array([$controllerInstance, $method], $parameters);
    }
    
    /**
     * Run a callable based action.
     *
     * @param  \Closure  $callback
     * @param  array  $parameters
     * @return mixed
     */
    protected function runCallable(Closure $callback, $parameters)
    {
        return call_user_func_array($callback, $parameters);
    }
}

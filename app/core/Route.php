<?php

namespace App\Core;

/**
 * Route Class
 * Represents a single route with its handler, middleware, and metadata
 */
class Route
{
    private string $path;
    private $handler;
    private array $methods;
    private array $middleware = [];
    private array $parameters = [];
    private ?string $name = null;
    private array $requirements = [];
    private array $defaults = [];
    private ?string $host = null;
    private ?string $scheme = null;
    private int $priority = 0;
    private ?string $namespace = null;

    /**
     * Route constructor
     * 
     * @param string $path Route path pattern
     * @param mixed $handler Route handler (callable or controller action)
     * @param array $methods HTTP methods (GET, POST, etc.)
     * @param array $options Additional route options
     */
    public function __construct(string $path, $handler, array $methods = ['GET'], array $options = [])
    {
        $this->path = $path;
        $this->handler = $handler;
        $this->methods = array_map('strtoupper', $methods);
        
        // Set optional properties
        if (isset($options['middleware'])) {
            $this->middleware = (array) $options['middleware'];
        }
        if (isset($options['name'])) {
            $this->name = $options['name'];
        }
        if (isset($options['requirements'])) {
            $this->requirements = $options['requirements'];
        }
        if (isset($options['defaults'])) {
            $this->defaults = $options['defaults'];
        }
        if (isset($options['host'])) {
            $this->host = $options['host'];
        }
        if (isset($options['scheme'])) {
            $this->scheme = $options['scheme'];
        }
        if (isset($options['priority'])) {
            $this->priority = (int) $options['priority'];
        }
        if (isset($options['namespace'])) {
            $this->namespace = $options['namespace'];
        }
    }

    /**
     * Get route path
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get route handler
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Get HTTP methods
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * Get middleware
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Add middleware
     */
    public function middleware($middleware): self
    {
        if (is_array($middleware)) {
            $this->middleware = array_merge($this->middleware, $middleware);
        } else {
            $this->middleware[] = $middleware;
        }
        return $this;
    }

    /**
     * Get route name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set route name
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get requirements
     */
    public function getRequirements(): array
    {
        return $this->requirements;
    }

    /**
     * Set requirements
     */
    public function where(string $parameter, string $regex): self
    {
        $this->requirements[$parameter] = $regex;
        return $this;
    }

    /**
     * Get defaults
     */
    public function getDefaults(): array
    {
        return $this->defaults;
    }

    /**
     * Set default value
     */
    public function default(string $parameter, $value): self
    {
        $this->defaults[$parameter] = $value;
        return $this;
    }

    /**
     * Get host
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * Set host
     */
    public function host(string $host): self
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Get scheme
     */
    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    /**
     * Set scheme
     */
    public function scheme(string $scheme): self
    {
        $this->scheme = strtolower($scheme);
        return $this;
    }

    /**
     * Get priority
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Set priority
     */
    public function priority(int $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * Get namespace
     */
    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    /**
     * Set namespace
     */
    public function namespace(string $namespace): self
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * Check if route matches given path and method
     */
    public function matches(string $path, string $method): bool
    {
        // Check HTTP method
        if (!in_array(strtoupper($method), $this->methods)) {
            return false;
        }

        // Convert route pattern to regex
        $pattern = $this->path;
        
        // Replace parameter placeholders with regex
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
        
        // Apply requirements
        foreach ($this->requirements as $param => $regex) {
            $pattern = str_replace("(?P<{$param}>[^/]+)", "(?P<{$param}>{$regex})", $pattern);
        }
        
        // Add start/end anchors
        $pattern = '#^' . $pattern . '$#';
        
        // Check if path matches
        if (!preg_match($pattern, $path, $matches)) {
            return false;
        }
        
        // Extract parameters
        $this->parameters = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        
        return true;
    }

    /**
     * Get extracted parameters
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Generate URL for this route with given parameters
     */
    public function generateUrl(array $parameters = []): string
    {
        $url = $this->path;
        
        // Replace parameters
        foreach ($parameters as $key => $value) {
            $url = str_replace('{' . $key . '}', $value, $url);
        }
        
        // Apply defaults for missing parameters
        foreach ($this->defaults as $key => $value) {
            if (!isset($parameters[$key]) && strpos($url, '{' . $key . '}') !== false) {
                $url = str_replace('{' . $key . '}', $value, $url);
            }
        }
        
        // Remove optional parameters that weren't provided
        $url = preg_replace('/\{\w+\}\/?/', '', $url);
        
        return $url;
    }

    /**
     * Check if this route requires HTTPS
     */
    public function requiresHttps(): bool
    {
        return $this->scheme === 'https';
    }

    /**
     * Convert route to array for caching
     */
    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'handler' => $this->handler,
            'methods' => $this->methods,
            'middleware' => $this->middleware,
            'name' => $this->name,
            'requirements' => $this->requirements,
            'defaults' => $this->defaults,
            'host' => $this->host,
            'scheme' => $this->scheme,
            'priority' => $this->priority,
            'namespace' => $this->namespace,
        ];
    }

    /**
     * Create route from array (for caching)
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['path'],
            $data['handler'],
            $data['methods'],
            [
                'middleware' => $data['middleware'] ?? [],
                'name' => $data['name'] ?? null,
                'requirements' => $data['requirements'] ?? [],
                'defaults' => $data['defaults'] ?? [],
                'host' => $data['host'] ?? null,
                'scheme' => $data['scheme'] ?? null,
                'priority' => $data['priority'] ?? 0,
                'namespace' => $data['namespace'] ?? null,
            ]
        );
    }
}
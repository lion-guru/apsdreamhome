<?php
namespace App\Microservices;

use Exception;

class ApiGateway
{
    private $serviceRegistry;
    private $routes = [];
    private $middleware = [];
    private $rateLimiters = [];
    
    public function __construct()
    {
        $this->serviceRegistry = new ServiceRegistry();
        $this->initializeRoutes();
        $this->initializeMiddleware();
        $this->initializeRateLimiters();
    }
    
    /**
     * Handle incoming request
     */
    public function handleRequest($method, $path, $data = null, $headers = [])
    {
        // Apply middleware
        $request = [
            'method' => $method,
            'path' => $path,
            'data' => $data,
            'headers' => $headers,
            'timestamp' => time()
        ];
        
        $request = $this->applyMiddleware($request);
        
        // Rate limiting
        $this->checkRateLimit($request);
        
        // Route request
        $route = $this->findRoute($method, $path);
        
        if (!$route) {
            throw new Exception('Route not found', 404);
        }
        
        // Call service
        return $this->callService($route, $request);
    }
    
    /**
     * Initialize routes
     */
    private function initializeRoutes()
    {
        $this->routes = [
            // User service routes
            'GET:/api/users' => [
                'service' => 'user_service',
                'endpoint' => '/users',
                'method' => 'GET'
            ],
            'POST:/api/users' => [
                'service' => 'user_service',
                'endpoint' => '/users',
                'method' => 'POST'
            ],
            'GET:/api/users/{id}' => [
                'service' => 'user_service',
                'endpoint' => '/users/{id}',
                'method' => 'GET'
            ],
            
            // Property service routes
            'GET:/api/properties' => [
                'service' => 'property_service',
                'endpoint' => '/properties',
                'method' => 'GET'
            ],
            'POST:/api/properties' => [
                'service' => 'property_service',
                'endpoint' => '/properties',
                'method' => 'POST'
            ],
            'GET:/api/properties/{id}' => [
                'service' => 'property_service',
                'endpoint' => '/properties/{id}',
                'method' => 'GET'
            ],
            
            // Analytics service routes
            'GET:/api/analytics/dashboard' => [
                'service' => 'analytics_service',
                'endpoint' => '/dashboard',
                'method' => 'GET'
            ],
            'GET:/api/analytics/reports' => [
                'service' => 'analytics_service',
                'endpoint' => '/reports',
                'method' => 'GET'
            ],
            
            // Notification service routes
            'POST:/api/notifications' => [
                'service' => 'notification_service',
                'endpoint' => '/notifications',
                'method' => 'POST'
            ],
            'GET:/api/notifications/{user_id}' => [
                'service' => 'notification_service',
                'endpoint' => '/notifications/{user_id}',
                'method' => 'GET'
            ],
            
            // Payment service routes
            'POST:/api/payments' => [
                'service' => 'payment_service',
                'endpoint' => '/payments',
                'method' => 'POST'
            ],
            'GET:/api/payments/{id}' => [
                'service' => 'payment_service',
                'endpoint' => '/payments/{id}',
                'method' => 'GET'
            ]
        ];
    }
    
    /**
     * Initialize middleware
     */
    private function initializeMiddleware()
    {
        $this->middleware = [
            'authentication' => new AuthenticationMiddleware(),
            'authorization' => new AuthorizationMiddleware(),
            'logging' => new LoggingMiddleware(),
            'validation' => new ValidationMiddleware(),
            'caching' => new CachingMiddleware()
        ];
    }
    
    /**
     * Initialize rate limiters
     */
    private function initializeRateLimiters()
    {
        $this->rateLimiters = [
            'default' => new RateLimiter(100, 3600), // 100 requests per hour
            'api' => new RateLimiter(1000, 3600), // 1000 requests per hour
            'upload' => new RateLimiter(10, 3600) // 10 uploads per hour
        ];
    }
    
    /**
     * Apply middleware
     */
    private function applyMiddleware($request)
    {
        foreach ($this->middleware as $name => $middleware) {
            $request = $middleware->handle($request);
        }
        
        return $request;
    }
    
    /**
     * Check rate limit
     */
    private function checkRateLimit($request)
    {
        $key = $this->getRateLimitKey($request);
        $rateLimiter = $this->getRateLimiter($request);
        
        if (!$rateLimiter->check($key)) {
            throw new Exception('Rate limit exceeded', 429);
        }
    }
    
    /**
     * Get rate limit key
     */
    private function getRateLimitKey($request)
    {
        $clientId = $request['headers']['X-Client-ID'] ?? $request['headers']['User-Agent'] ?? 'anonymous';
        $path = $request['path'];
        
        return md5($clientId . ':' . $path);
    }
    
    /**
     * Get rate limiter
     */
    private function getRateLimiter($request)
    {
        $path = $request['path'];
        
        if (strpos($path, '/api/') === 0) {
            return $this->rateLimiters['api'];
        }
        
        if (strpos($path, '/upload') !== false) {
            return $this->rateLimiters['upload'];
        }
        
        return $this->rateLimiters['default'];
    }
    
    /**
     * Find route
     */
    private function findRoute($method, $path)
    {
        $routeKey = $method . ':' . $path;
        
        // Exact match
        if (isset($this->routes[$routeKey])) {
            return $this->routes[$routeKey];
        }
        
        // Pattern match
        foreach ($this->routes as $routePattern => $route) {
            if ($this->matchesPattern($routePattern, $method, $path)) {
                return $route;
            }
        }
        
        return null;
    }
    
    /**
     * Check if path matches pattern
     */
    private function matchesPattern($pattern, $method, $path)
    {
        $parts = explode(':', $pattern);
        $patternMethod = $parts[0];
        $patternPath = $parts[1];
        
        if ($patternMethod !== $method) {
            return false;
        }
        
        // Convert pattern to regex
        $regex = preg_replace('/\{[^}]+\}/', '([^/]+)', $patternPath);
        $regex = '/^' . str_replace('/', '\/', $regex) . '$/';
        
        return preg_match($regex, $path);
    }
    
    /**
     * Call service
     */
    private function callService($route, $request)
    {
        $serviceName = $route['service'];
        $endpoint = $route['endpoint'];
        $method = $route['method'];
        
        // Extract path parameters
        $endpoint = $this->extractPathParameters($endpoint, $request['path']);
        
        // Call service
        $response = $this->serviceRegistry->call(
            $serviceName,
            $endpoint,
            $method,
            $request['data'],
            $request['headers']
        );
        
        return $response;
    }
    
    /**
     * Extract path parameters
     */
    private function extractPathParameters($endpoint, $path)
    {
        // This would extract parameters like {id} from the path
        // For now, return the endpoint as is
        return $endpoint;
    }
    
    /**
     * Register service
     */
    public function registerService($serviceName, $config)
    {
        return $this->serviceRegistry->register($serviceName, $config);
    }
    
    /**
     * Get gateway metrics
     */
    public function getMetrics()
    {
        $metrics = [
            'total_requests' => 0,
            'successful_requests' => 0,
            'failed_requests' => 0,
            'avg_response_time' => 0,
            'services' => []
        ];
        
        foreach ($this->serviceRegistry->getServices() as $serviceName => $service) {
            $serviceMetrics = $this->serviceRegistry->getServiceMetrics($serviceName);
            if ($serviceMetrics) {
                $metrics['services'][$serviceName] = $serviceMetrics;
                $metrics['total_requests'] += $serviceMetrics['requests'];
                $metrics['successful_requests'] += $serviceMetrics['requests'] - $serviceMetrics['failures'];
                $metrics['failed_requests'] += $serviceMetrics['failures'];
            }
        }
        
        if ($metrics['total_requests'] > 0) {
            $metrics['success_rate'] = ($metrics['successful_requests'] / $metrics['total_requests']) * 100;
        } else {
            $metrics['success_rate'] = 0;
        }
        
        return $metrics;
    }
}

/**
 * Authentication Middleware
 */
class AuthenticationMiddleware
{
    public function handle($request)
    {
        $token = $request['headers']['Authorization'] ?? $request['headers']['X-API-Key'] ?? null;
        
        if (!$token) {
            throw new Exception('Authentication required', 401);
        }
        
        // Validate token (placeholder)
        $request['user'] = $this->validateToken($token);
        
        return $request;
    }
    
    private function validateToken($token)
    {
        // This would validate JWT token or API key
        return ['id' => 1, 'name' => 'Test User'];
    }
}

/**
 * Authorization Middleware
 */
class AuthorizationMiddleware
{
    public function handle($request)
    {
        $user = $request['user'] ?? null;
        $path = $request['path'];
        
        if (!$this->isAuthorized($user, $path)) {
            throw new Exception('Access denied', 403);
        }
        
        return $request;
    }
    
    private function isAuthorized($user, $path)
    {
        // This would check user permissions
        return true;
    }
}

/**
 * Logging Middleware
 */
class LoggingMiddleware
{
    public function handle($request)
    {
        $startTime = microtime(true);
        
        // Log request
        $this->logRequest($request);
        
        return $request;
    }
    
    private function logRequest($request)
    {
        $logData = [
            'method' => $request['method'],
            'path' => $request['path'],
            'timestamp' => $request['timestamp'],
            'user_agent' => $request['headers']['User-Agent'] ?? null
        ];
        
        file_put_contents(
            BASE_PATH . '/logs/api_gateway.log',
            json_encode($logData) . PHP_EOL,
            FILE_APPEND
        );
    }
}

/**
 * Validation Middleware
 */
class ValidationMiddleware
{
    public function handle($request)
    {
        // Validate request data
        if ($request['data']) {
            $this->validateData($request['data']);
        }
        
        return $request;
    }
    
    private function validateData($data)
    {
        // This would validate request data
        return true;
    }
}

/**
 * Caching Middleware
 */
class CachingMiddleware
{
    private $cache;
    
    public function __construct()
    {
        $this->cache = new \App\Services\Cache\RedisCacheService();
    }
    
    public function handle($request)
    {
        // Check cache for GET requests
        if ($request['method'] === 'GET') {
            $cacheKey = $this->getCacheKey($request);
            $cachedResponse = $this->cache->get($cacheKey);
            
            if ($cachedResponse) {
                return $request;
            }
        }
        
        return $request;
    }
    
    private function getCacheKey($request)
    {
        return 'api_gateway:' . md5($request['method'] . ':' . $request['path']);
    }
}

/**
 * Rate Limiter
 */
class RateLimiter
{
    private $requests;
    private $window;
    private $storage = [];
    
    public function __construct($requests, $window)
    {
        $this->requests = $requests;
        $this->window = $window;
    }
    
    public function check($key)
    {
        $currentTime = time();
        $windowStart = $currentTime - $this->window;
        
        // Clean old requests
        if (!isset($this->storage[$key])) {
            $this->storage[$key] = [];
        }
        
        $this->storage[$key] = array_filter($this->storage[$key], function($timestamp) use ($windowStart) {
            return $timestamp > $windowStart;
        });
        
        // Check if under limit
        if (count($this->storage[$key]) >= $this->requests) {
            return false;
        }
        
        // Add current request
        $this->storage[$key][] = $currentTime;
        
        return true;
    }
}

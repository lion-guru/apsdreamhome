<?php

namespace App\Services\Custom;

/**
 * Custom Request Service - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 * Following APS Dream Home custom architecture patterns
 */
class RequestService
{
    private $database;
    private $logger;
    private $config;
    private $session;
    
    // Request Types
    const TYPE_HTTP = 'http';
    const TYPE_CLI = 'cli';
    const TYPE_WEBSOCKET = 'websocket';
    
    // HTTP Methods
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    const METHOD_PATCH = 'PATCH';
    
    // Middleware Stages
    const STAGE_PRE_PROCESS = 'pre_process';
    const STAGE_AUTH = 'authentication';
    const STAGE_VALIDATION = 'validation';
    const STAGE_PROCESSING = 'processing';
    const STAGE_POST_PROCESS = 'post_process';
    
    // Request and Response Data
    private $request = [];
    private $response = [];
    private $middlewareStack = [];
    private $routeRegistry = [];
    
    // Configuration
    private $corsEnabled = true;
    private $allowedOrigins = ['*'];
    private $maxRequestSize = 10 * 1024 * 1024; // 10MB
    
    public function __construct()
    {
        $this->database = \App\Core\Database::getInstance();
        $this->logger = new \App\Core\Logger();
        $this->config = \App\Core\Config::getInstance();
        $this->session = new \App\Core\Session();
        
        $this->loadConfiguration();
        $this->initializeRequest();
    }
    
    /**
     * Load configuration settings
     */
    private function loadConfiguration()
    {
        $this->corsEnabled = $this->config->get('cors.enabled', true);
        $this->allowedOrigins = $this->config->get('cors.allowed_origins', ['*']);
        $this->maxRequestSize = $this->config->get('request.max_size', 10 * 1024 * 1024);
    }
    
    /**
     * Initialize request data
     */
    private function initializeRequest()
    {
        $this->request = [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
            'uri' => $_SERVER['REQUEST_URI'] ?? '/',
            'protocol' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http',
            'host' => $_SERVER['HTTP_HOST'] ?? 'localhost',
            'headers' => $this->getAllHeaders(),
            'get' => $_GET,
            'post' => $_POST,
            'files' => $_FILES,
            'body' => file_get_contents('php://input'),
            'ip' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'timestamp' => time()
        ];
        
        // Parse JSON body if applicable
        if ($this->isJsonRequest()) {
            $this->request['json'] = json_decode($this->request['body'], true) ?? [];
        }
    }
    
    /**
     * Register middleware handler
     */
    public function registerMiddleware($stage, callable $handler)
    {
        if (!isset($this->middlewareStack[$stage])) {
            $this->middlewareStack[$stage] = [];
        }
        
        $this->middlewareStack[$stage][] = $handler;
        
        $this->logger->info('Middleware registered', [
            'stage' => $stage,
            'handler' => get_class($handler[0]) ?? 'closure'
        ]);
    }
    
    /**
     * Register route with optional middleware
     */
    public function registerRoute($method, $path, callable $handler, array $middleware = [])
    {
        $routeKey = strtoupper($method) . ':' . $path;
        
        $this->routeRegistry[$routeKey] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware,
            'registered_at' => time()
        ];
        
        $this->logger->info('Route registered', [
            'method' => strtoupper($method),
            'path' => $path,
            'middleware' => $middleware
        ]);
    }
    
    /**
     * Process incoming request
     */
    public function processRequest($type = self::TYPE_HTTP)
    {
        try {
            $this->logger->info('Request processing started', [
                'type' => $type,
                'method' => $this->request['method'],
                'uri' => $this->request['uri'],
                'ip' => $this->request['ip']
            ]);
            
            // Validate request
            if (!$this->validateRequest()) {
                return $this->sendErrorResponse(400, 'Invalid request');
            }
            
            // Run pre-process middleware
            if (!$this->runMiddleware(self::STAGE_PRE_PROCESS)) {
                return $this->sendErrorResponse(500, 'Pre-processing failed');
            }
            
            // Find matching route
            $route = $this->matchRoute();
            if (!$route) {
                return $this->sendErrorResponse(404, 'Route not found');
            }
            
            // Run authentication middleware
            if (!$this->runMiddleware(self::STAGE_AUTH, $route['middleware'])) {
                return $this->sendErrorResponse(401, 'Authentication failed');
            }
            
            // Run validation middleware
            if (!$this->runMiddleware(self::STAGE_VALIDATION, $route['middleware'])) {
                return $this->sendErrorResponse(422, 'Validation failed');
            }
            
            // Execute route handler
            $this->runMiddleware(self::STAGE_PROCESSING, $route['middleware']);
            $result = $this->executeRouteHandler($route);
            
            // Run post-process middleware
            $this->runMiddleware(self::STAGE_POST_PROCESS, $route['middleware']);
            
            // Send response
            return $this->sendResponse($result);
            
        } catch (Exception $e) {
            $this->logger->error('Request processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $this->request
            ]);
            
            return $this->sendErrorResponse(500, 'Internal server error');
        }
    }
    
    /**
     * Validate request
     */
    private function validateRequest()
    {
        // Check request size
        if (isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > $this->maxRequestSize) {
            $this->logger->warning('Request size exceeded', [
                'size' => $_SERVER['CONTENT_LENGTH'],
                'max_allowed' => $this->maxRequestSize
            ]);
            return false;
        }
        
        // Check required headers
        if ($this->request['method'] === 'POST' && empty($this->request['headers']['Content-Type'])) {
            $this->logger->warning('Missing Content-Type header for POST request');
            return false;
        }
        
        // Check User-Agent
        if (empty($this->request['user_agent'])) {
            $this->logger->warning('Missing User-Agent header');
            return false;
        }
        
        return true;
    }
    
    /**
     * Run middleware for specific stage
     */
    private function runMiddleware($stage, array $routeMiddleware = [])
    {
        // Run global middleware for stage
        if (isset($this->middlewareStack[$stage])) {
            foreach ($this->middlewareStack[$stage] as $middleware) {
                $result = call_user_func($middleware, $this->request, $this->response);
                if ($result === false) {
                    $this->logger->warning('Middleware execution failed', [
                        'stage' => $stage,
                        'middleware' => get_class($middleware[0]) ?? 'closure'
                    ]);
                    return false;
                }
            }
        }
        
        // Run route-specific middleware
        foreach ($routeMiddleware as $middlewareName) {
            if (isset($this->middlewareStack[$middlewareName])) {
                $result = call_user_func($this->middlewareStack[$middlewareName], $this->request, $this->response);
                if ($result === false) {
                    $this->logger->warning('Route middleware failed', [
                        'middleware' => $middlewareName
                    ]);
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Match request to registered route
     */
    private function matchRoute()
    {
        $method = $this->request['method'];
        $uri = parse_url($this->request['uri'], PHP_URL_PATH);
        
        // Try exact match first
        $routeKey = $method . ':' . $uri;
        if (isset($this->routeRegistry[$routeKey])) {
            return $this->routeRegistry[$routeKey];
        }
        
        // Try pattern matching
        foreach ($this->routeRegistry as $key => $route) {
            if ($route['method'] === $method && $this->matchRoutePattern($route['path'], $uri)) {
                // Extract route parameters
                $this->request['params'] = $this->extractRouteParams($route['path'], $uri);
                return $route;
            }
        }
        
        return null;
    }
    
    /**
     * Check if route path matches URI pattern
     */
    private function matchRoutePattern($routePath, $uri)
    {
        // Convert route pattern to regex
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
        $pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';
        
        return preg_match($pattern, $uri);
    }
    
    /**
     * Extract route parameters from URI
     */
    private function extractRouteParams($routePath, $uri)
    {
        $params = [];
        
        // Get parameter names from route
        preg_match_all('/\{([^}]+)\}/', $routePath, $paramNames);
        
        // Get parameter values from URI
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
        $pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';
        
        if (preg_match($pattern, $uri, $matches)) {
            // Remove full match, keep only parameter values
            array_shift($matches);
            
            foreach ($paramNames[1] as $index => $name) {
                $params[$name] = $matches[$index] ?? null;
            }
        }
        
        return $params;
    }
    
    /**
     * Execute route handler
     */
    private function executeRouteHandler($route)
    {
        try {
            $this->logger->info('Executing route handler', [
                'method' => $route['method'],
                'path' => $route['path'],
                'handler' => get_class($route['handler'][0]) ?? 'closure'
            ]);
            
            // Call handler with request data
            $result = call_user_func($route['handler'], $this->request);
            
            // Log successful execution
            $this->logger->info('Route handler executed successfully', [
                'method' => $route['method'],
                'path' => $route['path']
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->logger->error('Route handler execution failed', [
                'method' => $route['method'],
                'path' => $route['path'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Send error response
     */
    private function sendErrorResponse($statusCode, $message)
    {
        $this->response = [
            'success' => false,
            'status_code' => $statusCode,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->sendResponse($this->response);
    }
    
    /**
     * Send response
     */
    private function sendResponse($data)
    {
        // Set headers
        if (!headers_sent()) {
            http_response_code($data['status_code'] ?? 200);
            header('Content-Type: application/json');
            header('Access-Control-Allow-Origin: ' . implode(', ', $this->allowedOrigins));
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }
        
        // Send JSON response
        echo json_encode($data);
        exit;
    }
    
    /**
     * Handle CORS preflight requests
     */
    public function handleCors()
    {
        if (!$this->corsEnabled) {
            return;
        }
        
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $method = $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] ?? '';
        $headers = $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] ?? '';
        
        // Check if origin is allowed
        if (in_array('*', $this->allowedOrigins) || in_array($origin, $this->allowedOrigins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
            header('Access-Control-Allow-Headers: ' . ($headers ?: 'Content-Type, Authorization, X-Requested-With'));
            header('Access-Control-Max-Age: 86400');
        }
        
        if ($this->request['method'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
    
    /**
     * Get all HTTP headers
     */
    private function getAllHeaders()
    {
        $headers = [];
        
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $headerKey = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$headerKey] = $value;
            }
        }
        
        return $headers;
    }
    
    /**
     * Check if request is JSON
     */
    private function isJsonRequest()
    {
        $contentType = $this->request['headers']['Content-Type'] ?? '';
        return strpos($contentType, 'application/json') !== false;
    }
    
    /**
     * Get client IP address
     */
    private function getClientIp()
    {
        $ipHeaders = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($ipHeaders as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                
                // Handle comma-separated IPs
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    }
    
    /**
     * Get request data
     */
    public function getRequest()
    {
        return $this->request;
    }
    
    /**
     * Get response data
     */
    public function getResponse()
    {
        return $this->response;
    }
    
    /**
     * Get middleware stack
     */
    public function getMiddlewareStack()
    {
        return $this->middlewareStack;
    }
    
    /**
     * Get route registry
     */
    public function getRouteRegistry()
    {
        return $this->routeRegistry;
    }
    
    /**
     * Add custom middleware
     */
    public function addSecurityMiddleware()
    {
        $this->registerMiddleware(self::STAGE_PRE_PROCESS, function($request, &$response) {
            // Security checks
            $suspiciousPatterns = [
                '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
                '/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi',
                '/javascript:/i',
                '/vbscript:/i'
            ];
            
            foreach ($suspiciousPatterns as $pattern) {
                foreach ($request as $key => $value) {
                    if (is_string($value) && preg_match($pattern, $value)) {
                        $this->logger->warning('Suspicious content detected', [
                            'key' => $key,
                            'pattern' => $pattern,
                            'ip' => $request['ip']
                        ]);
                        return false;
                    }
                }
            }
            
            return true;
        });
    }
    
    /**
     * Add rate limiting middleware
     */
    public function addRateLimitingMiddleware($maxRequests = 100, $timeWindow = 3600)
    {
        $this->registerMiddleware('rate_limit', function($request, &$response) use ($maxRequests, $timeWindow) {
            $ip = $request['ip'];
            $cacheKey = 'rate_limit_' . md5($ip);
            
            $requests = $this->session->get($cacheKey, []);
            $now = time();
            
            // Filter old requests
            $recentRequests = array_filter($requests, function($timestamp) use ($now, $timeWindow) {
                return ($now - $timestamp) < $timeWindow;
            });
            
            // Check rate limit
            if (count($recentRequests) >= $maxRequests) {
                $this->logger->warning('Rate limit exceeded', [
                    'ip' => $ip,
                    'requests' => count($recentRequests),
                    'max_allowed' => $maxRequests
                ]);
                return false;
            }
            
            // Add current request
            $recentRequests[] = $now;
            $this->session->set($cacheKey, $recentRequests);
            
            return true;
        });
    }
    
    /**
     * Add logging middleware
     */
    public function addLoggingMiddleware()
    {
        $this->registerMiddleware(self::STAGE_POST_PROCESS, function($request, &$response) {
            $this->logger->info('Request completed', [
                'method' => $request['method'],
                'uri' => $request['uri'],
                'ip' => $request['ip'],
                'status_code' => $response['status_code'] ?? 200,
                'response_time' => microtime(true) - ($request['timestamp'] / 1000000)
            ]);
            
            return true;
        });
    }
}

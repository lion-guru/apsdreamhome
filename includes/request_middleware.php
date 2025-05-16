<?php
/**
 * Advanced Middleware and Request Processing System
 * Provides robust request handling, routing, and middleware management
 */

// require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/config_manager.php';
require_once __DIR__ . '/security_middleware.php';
require_once __DIR__ . '/validator.php';
require_once __DIR__ . '/event_monitor.php';

class RequestMiddleware {
    // Request Types
    public const TYPE_HTTP = 'http';
    public const TYPE_CLI = 'cli';
    public const TYPE_WEBSOCKET = 'websocket';

    // HTTP Methods
    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';
    public const METHOD_PUT = 'PUT';
    public const METHOD_DELETE = 'DELETE';
    public const METHOD_PATCH = 'PATCH';

    // Middleware Stages
    public const STAGE_PRE_PROCESS = 'pre_process';
    public const STAGE_AUTH = 'authentication';
    public const STAGE_VALIDATION = 'validation';
    public const STAGE_PROCESSING = 'processing';
    public const STAGE_POST_PROCESS = 'post_process';

    // Request and Response Handling
    private $request = [];
    private $response = [];
    private $middlewareStack = [];
    private $routeRegistry = [];

    // System Dependencies
    private $logger;
    private $config;
    private $securityMiddleware;
    private $validator;
    private $eventMonitor;

    // Configuration Parameters
    private $corsEnabled = true;
    private $allowedOrigins = ['*'];
    private $maxRequestSize = 10 * 1024 * 1024; // 10MB

    public function __construct() {
        $this->logger = new Logger();
        $this->config = ConfigManager::getInstance();
        $this->securityMiddleware = new SecurityMiddleware();
        $this->validator = validator();
        $this->eventMonitor = new EventMonitor();

        // Load configuration
        $this->loadConfiguration();
    }

    /**
     * Load middleware configuration
     */
    private function loadConfiguration() {
        $this->corsEnabled = $this->config->get(
            'CORS_ENABLED', 
            true
        );
        $this->allowedOrigins = $this->config->get(
            'ALLOWED_ORIGINS', 
            ['*']
        );
        $this->maxRequestSize = $this->config->get(
            'MAX_REQUEST_SIZE', 
            10 * 1024 * 1024
        );
    }

    /**
     * Register a middleware handler
     * 
     * @param string $stage Middleware stage
     * @param callable $handler Middleware handler function
     */
    public function registerMiddleware($stage, callable $handler) {
        if (!isset($this->middlewareStack[$stage])) {
            $this->middlewareStack[$stage] = [];
        }

        $this->middlewareStack[$stage][] = $handler;
    }

    /**
     * Register a route with optional middleware
     * 
     * @param string $method HTTP method
     * @param string $path Route path
     * @param callable $handler Route handler
     * @param array $middleware Optional middleware
     */
    public function registerRoute(
        $method, 
        $path, 
        callable $handler, 
        array $middleware = []
    ) {
        $this->routeRegistry[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    /**
     * Process incoming request
     * 
     * @param array $request Request data
     * @param string $type Request type
     * @return array Processed response
     */
    public function processRequest(
        array $request, 
        $type = self::TYPE_HTTP
    ) {
        // Log request
        $this->eventMonitor->logEvent('REQUEST_RECEIVED', [
            'type' => $type,
            'method' => $request['method'] ?? 'UNKNOWN',
            'path' => $request['path'] ?? 'UNKNOWN'
        ]);

        // Validate request size
        $this->validateRequestSize($request);

        // Run pre-process middleware
        $this->runMiddleware(self::STAGE_PRE_PROCESS, $request);

        // CORS handling
        $this->handleCORS($request);

        // Authentication middleware
        $this->runMiddleware(self::STAGE_AUTH, $request);

        // Input validation
        $this->runMiddleware(self::STAGE_VALIDATION, $request);

        // Route matching and processing
        $route = $this->matchRoute($request);
        $this->runMiddleware(self::STAGE_PROCESSING, $request);

        // Execute route handler
        $response = $this->executeRouteHandler($route, $request);

        // Post-process middleware
        $this->runMiddleware(self::STAGE_POST_PROCESS, $request, $response);

        return $response;
    }

    /**
     * Validate request size
     * 
     * @param array $request Request data
     * @throws \RuntimeException If request exceeds size limit
     */
    private function validateRequestSize(array $request) {
        $requestSize = strlen(json_encode($request));
        if ($requestSize > $this->maxRequestSize) {
            throw new \RuntimeException(
                "Request size exceeds maximum limit of {$this->maxRequestSize} bytes"
            );
        }
    }

    /**
     * Handle Cross-Origin Resource Sharing (CORS)
     * 
     * @param array $request Request data
     */
    private function handleCORS(array $request) {
        if (!$this->corsEnabled) {
            return;
        }

        $origin = $request['origin'] ?? '';
        if (in_array('*', $this->allowedOrigins) || 
            in_array($origin, $this->allowedOrigins)) {
            // Set CORS headers
            header("Access-Control-Allow-Origin: $origin");
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type, Authorization");
        } else {
            // Reject request
            throw new \RuntimeException("CORS request not allowed");
        }
    }

    /**
     * Run middleware for a specific stage
     * 
     * @param string $stage Middleware stage
     * @param array $request Request data
     * @param array|null $response Response data
     */
    private function runMiddleware(
        $stage, 
        array &$request, 
        ?array &$response = null
    ) {
        if (!isset($this->middlewareStack[$stage])) {
            return;
        }

        foreach ($this->middlewareStack[$stage] as $middleware) {
            $result = call_user_func_array(
                $middleware, 
                [&$request, &$response]
            );

            // Allow middleware to modify request/response
            if ($result === false) {
                throw new \RuntimeException(
                    "Middleware at stage $stage rejected the request"
                );
            }
        }
    }

    /**
     * Match route based on request
     * 
     * @param array $request Request data
     * @return array Matched route
     */
    private function matchRoute(array $request) {
        $method = $request['method'] ?? '';
        $path = $request['path'] ?? '';

        foreach ($this->routeRegistry as $route) {
            if ($route['method'] === $method && 
                $this->matchRoutePath($route['path'], $path)) {
                // Run route-specific middleware
                foreach ($route['middleware'] as $middleware) {
                    $result = call_user_func_array(
                        $middleware, 
                        [&$request]
                    );

                    if ($result === false) {
                        throw new \RuntimeException(
                            "Route-specific middleware rejected the request"
                        );
                    }
                }

                return $route;
            }
        }

        throw new \RuntimeException("No matching route found");
    }

    /**
     * Match route path with request path
     * 
     * @param string $routePath Registered route path
     * @param string $requestPath Request path
     * @return bool Whether paths match
     */
    private function matchRoutePath($routePath, $requestPath) {
        // Support for dynamic route parameters
        $routeRegex = preg_replace(
            '/\{([^}]+)\}/', 
            '(?P<\1>[^/]+)', 
            $routePath
        );
        $routeRegex = str_replace('/', '\/', $routeRegex);

        return preg_match("/^{$routeRegex}$/", $requestPath);
    }

    /**
     * Execute route handler
     * 
     * @param array $route Matched route
     * @param array $request Request data
     * @return array Response data
     */
    private function executeRouteHandler(array $route, array $request) {
        try {
            $response = call_user_func_array(
                $route['handler'], 
                [$request]
            );

            // Log successful request
            $this->eventMonitor->logEvent('REQUEST_PROCESSED', [
                'route' => $route['path'],
                'method' => $route['method']
            ]);

            return $response;
        } catch (\Exception $e) {
            // Log error
            $this->logger->error('Route Handler Failed', [
                'route' => $route['path'],
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Demonstrate middleware and routing capabilities
     */
    public function demonstrateMiddleware() {
        // Register global middleware
        $this->registerMiddleware(
            self::STAGE_PRE_PROCESS, 
            function(&$request) {
                // Log request details
                $this->logger->info('Pre-process middleware', $request);
                return true;
            }
        );

        // Register authentication middleware
        $this->registerMiddleware(
            self::STAGE_AUTH, 
            function(&$request) {
                // Simple authentication check
                if (!isset($request['token'])) {
                    throw new \RuntimeException("Authentication required");
                }
                return true;
            }
        );

        // Register route
        $this->registerRoute(
            self::METHOD_GET, 
            '/users/{id}', 
            function($request) {
                // User retrieval logic
                return [
                    'user_id' => $request['params']['id'],
                    'name' => 'John Doe'
                ];
            },
            [
                // Route-specific middleware
                function(&$request) {
                    // Additional authorization check
                    return true;
                }
            ]
        );

        // Simulate request processing
        $request = [
            'method' => self::METHOD_GET,
            'path' => '/users/123',
            'token' => 'valid_token'
        ];

        $response = $this->processRequest($request);
        print_r($response);
    }
}

// Global helper function for middleware management
function middleware() {
    return new RequestMiddleware();
}

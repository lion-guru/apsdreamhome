<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

/**
 * Modern Request Middleware Service
 * Provides comprehensive request processing, validation, and security
 */
class RequestMiddlewareService
{
    private array $middlewareStack = [];
    private array $globalMiddleware = [];
    private array $routeMiddleware = [];
    private bool $corsEnabled = true;
    private array $allowedOrigins = ['*'];
    private int $maxRequestSize = 10 * 1024 * 1024; // 10MB

    public function __construct()
    {
        $this->loadConfiguration();
        $this->registerDefaultMiddleware();
    }

    /**
     * Load middleware configuration
     */
    private function loadConfiguration(): void
    {
        $this->corsEnabled = config('cors.enabled', true);
        $this->allowedOrigins = config('cors.allowed_origins', ['*']);
        $this->maxRequestSize = config('app.max_request_size', 10 * 1024 * 1024);
    }

    /**
     * Register default middleware
     */
    private function registerDefaultMiddleware(): void
    {
        // Global middleware
        $this->globalMiddleware = [
            'cors' => [$this, 'handleCors'],
            'request.size' => [$this, 'validateRequestSize'],
            'security.headers' => [$this, 'addSecurityHeaders'],
            'request.logging' => [$this, 'logRequest'],
            'rate.limit' => [$this, 'checkRateLimit']
        ];

        // Route middleware groups
        $this->routeMiddleware = [
            'auth' => [$this, 'authenticate'],
            'auth.api' => [$this, 'authenticateApi'],
            'permission' => [$this, 'checkPermission'],
            'throttle' => [$this, 'throttleRequest'],
            'validate' => [$this, 'validateInput']
        ];
    }

    /**
     * Handle CORS
     */
    public function handleCors(Request $request, Closure $next): Response
    {
        if (!$this->corsEnabled) {
            return $next($request);
        }

        $origin = $request->header('Origin');
        
        if (in_array('*', $this->allowedOrigins) || in_array($origin, $this->allowedOrigins)) {
            $response = $next($request);
            
            $response->headers->set('Access-Control-Allow-Origin', $origin ?: '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Max-Age', '86400');

            return $response;
        }

        return response()->json(['error' => 'CORS policy violation'], 403);
    }

    /**
     * Validate request size
     */
    public function validateRequestSize(Request $request, Closure $next): Response
    {
        $contentLength = $request->header('Content-Length');
        
        if ($contentLength && (int)$contentLength > $this->maxRequestSize) {
            return response()->json([
                'error' => 'Request size exceeds maximum limit',
                'max_size' => $this->maxRequestSize
            ], 413);
        }

        return $next($request);
    }

    /**
     * Add security headers
     */
    public function addSecurityHeaders(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('Content-Security-Policy', "default-src 'self'");
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }

    /**
     * Log request
     */
    public function logRequest(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        Log::info('Request received', [
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString()
        ]);

        $response = $next($request);
        
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        
        Log::info('Request processed', [
            'method' => $request->method(),
            'path' => $request->path(),
            'status' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'response_size' => strlen($response->getContent())
        ]);

        return $response;
    }

    /**
     * Check rate limit
     */
    public function checkRateLimit(Request $request, Closure $next): Response
    {
        $key = 'request:' . $request->ip() . ':' . $request->path();
        
        if (RateLimiter::tooManyAttempts($key, 60)) {
            $seconds = RateLimiter::availableIn($key);
            
            return response()->json([
                'error' => 'Too many requests',
                'retry_after' => $seconds
            ], 429);
        }

        RateLimiter::hit($key, 60);
        
        return $next($request);
    }

    /**
     * Authenticate user
     */
    public function authenticate(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        return $next($request);
    }

    /**
     * Authenticate API request
     */
    public function authenticateApi(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json(['error' => 'API token required'], 401);
        }

        // Validate token (implement your token validation logic)
        if (!$this->validateApiToken($token)) {
            return response()->json(['error' => 'Invalid API token'], 401);
        }

        return $next($request);
    }

    /**
     * Check permission
     */
    public function checkPermission(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->user()?->hasPermission($permission)) {
            return response()->json(['error' => 'Insufficient permissions'], 403);
        }

        return $next($request);
    }

    /**
     * Throttle request
     */
    public function throttleRequest(Request $request, Closure $next, int $maxAttempts = 60, int $minutes = 1): Response
    {
        $key = 'throttle:' . $request->ip() . ':' . $request->route()->getName();
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            
            return response()->json([
                'error' => 'Request throttled',
                'retry_after' => $seconds
            ], 429);
        }

        RateLimiter::hit($key, $minutes * 60);
        
        return $next($request);
    }

    /**
     * Validate input
     */
    public function validateInput(Request $request, Closure $next, array $rules = []): Response
    {
        if (empty($rules)) {
            return $next($request);
        }

        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        return $next($request);
    }

    /**
     * Validate API token
     */
    private function validateApiToken(string $token): bool
    {
        // Implement your token validation logic
        // This could check against database, JWT, etc.
        return strlen($token) >= 32; // Simple length check for demo
    }

    /**
     * Register custom middleware
     */
    public function registerMiddleware(string $name, callable $handler): void
    {
        $this->routeMiddleware[$name] = $handler;
    }

    /**
     * Get middleware by name
     */
    public function getMiddleware(string $name): ?callable
    {
        return $this->routeMiddleware[$name] ?? null;
    }

    /**
     * Apply middleware stack
     */
    public function applyMiddleware(Request $request, Closure $next, array $middleware = []): Response
    {
        $stack = $next;
        
        // Apply middleware in reverse order
        foreach (array_reverse($middleware) as $middlewareName) {
            if (is_string($middlewareName)) {
                if (strpos($middlewareName, ':') !== false) {
                    [$name, $parameters] = explode(':', $middlewareName, 2);
                    $parameters = explode(',', $parameters);
                } else {
                    $name = $middlewareName;
                    $parameters = [];
                }
                
                $handler = $this->routeMiddleware[$name] ?? null;
                
                if ($handler) {
                    $stack = function($request) use ($handler, $stack, $parameters) {
                        return $handler($request, $stack, ...$parameters);
                    };
                }
            }
        }
        
        return $stack($request);
    }

    /**
     * Get request metadata
     */
    public function getRequestMetadata(Request $request): array
    {
        return [
            'method' => $request->method(),
            'path' => $request->path(),
            'full_url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'content_type' => $request->header('Content-Type'),
            'content_length' => $request->header('Content-Length'),
            'accept' => $request->header('Accept'),
            'origin' => $request->header('Origin'),
            'referer' => $request->header('Referer'),
            'is_ajax' => $request->ajax(),
            'is_json' => $request->expectsJson(),
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'session_id' => session()->getId()
        ];
    }

    /**
     * Sanitize request input
     */
    public function sanitizeInput(Request $request): Request
    {
        $input = $request->all();
        
        array_walk_recursive($input, function(&$value) {
            if (is_string($value)) {
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        });
        
        $request->merge($input);
        
        return $request;
    }

    /**
     * Validate JSON request
     */
    public function validateJsonRequest(Request $request): bool
    {
        if (!$request->isJson()) {
            return false;
        }
        
        $content = $request->getContent();
        json_decode($content);
        
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Get client information
     */
    public function getClientInfo(Request $request): array
    {
        return [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'accept_language' => $request->header('Accept-Language'),
            'accept_encoding' => $request->header('Accept-Encoding'),
            'connection' => $request->header('Connection'),
            'cache_control' => $request->header('Cache-Control'),
            'pragma' => $request->header('Pragma'),
            'dnt' => $request->header('DNT'),
            'upgrade_insecure_requests' => $request->header('Upgrade-Insecure-Requests')
        ];
    }

    /**
     * Check for suspicious patterns
     */
    public function detectSuspiciousActivity(Request $request): array
    {
        $suspicious = [];
        $input = $request->all();
        
        // Check for common attack patterns
        $patterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi' => 'XSS attempt',
            '/union.*select/i' => 'SQL injection attempt',
            '/javascript:/i' => 'JavaScript injection',
            '/on\w+\s*=/i' => 'Event handler injection'
        ];
        
        array_walk_recursive($input, function($value) use ($patterns, &$suspicious) {
            if (is_string($value)) {
                foreach ($patterns as $pattern => $description) {
                    if (preg_match($pattern, $value)) {
                        $suspicious[] = $description;
                    }
                }
            }
        });
        
        return $suspicious;
    }

    /**
     * Create middleware response
     */
    public function createMiddlewareResponse(string $message, int $status = 400, array $data = []): Response
    {
        return response()->json(array_merge([
            'error' => $message,
            'timestamp' => now()->toISOString()
        ], $data), $status);
    }

    /**
     * Get middleware statistics
     */
    public function getMiddlewareStats(): array
    {
        return [
            'global_middleware_count' => count($this->globalMiddleware),
            'route_middleware_count' => count($this->routeMiddleware),
            'cors_enabled' => $this->corsEnabled,
            'max_request_size' => $this->maxRequestSize,
            'allowed_origins' => $this->allowedOrigins
        ];
    }
}

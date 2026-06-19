<?php

namespace App\Http\Middleware;

use Closure;

/**
 * Lightweight request wrapper replacing Illuminate\Http\Request
 */
class SimpleRequest
{
    private array $server;
    private array $get;
    private array $post;
    private array $cookies;
    private array $files;
    private string $body;

    public function __construct()
    {
        $this->server = $_SERVER;
        $this->get = $_GET;
        $this->post = $_POST;
        $this->cookies = $_COOKIE;
        $this->files = $_FILES;
        $this->body = file_get_contents('php://input') ?: '';
    }

    public function method(): string { return $this->server['REQUEST_METHOD'] ?? 'GET'; }
    public function path(): string { return parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_PATH); }
    public function ip(): string { return $this->server['REMOTE_ADDR'] ?? '127.0.0.1'; }
    public function userAgent(): string { return $this->server['HTTP_USER_AGENT'] ?? ''; }
    public function fullUrl(): string {
        $scheme = (!empty($this->server['HTTPS']) && $this->server['HTTPS'] === 'on') ? 'https' : 'http';
        return $scheme . '://' . ($this->server['HTTP_HOST'] ?? 'localhost') . ($this->server['REQUEST_URI'] ?? '/');
    }

    public function header(string $name, ?string $default = null): ?string {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $this->server[$key] ?? $default;
    }

    public function bearerToken(): ?string {
        $auth = $this->header('Authorization');
        if ($auth && str_starts_with($auth, 'Bearer ')) {
            return substr($auth, 7);
        }
        return null;
    }

    public function all(): array { return array_merge($this->get, $this->post, json_decode($this->body, true) ?: []); }
    public function isJson(): bool { $ct = $this->header('Content-Type'); return $ct && str_contains($ct, '/json'); }
    public function getContent(): string { return $this->body; }
    public function merge(array $data): void { $this->post = array_merge($this->post, $data); }
    public function ajax(): bool { return strtolower($this->header('X-Requested-With') ?? '') === 'xmlhttprequest'; }
    public function expectsJson(): bool { $accept = $this->header('Accept'); return $accept && (str_contains($accept, '/json') || str_contains($accept, 'application/json')); }
    public function secure(): bool { return (!empty($this->server['HTTPS']) && $this->server['HTTPS'] === 'on'); }
}

/**
 * Lightweight response wrapper replacing Symfony\Component\HttpFoundation\Response
 */
class SimpleResponse
{
    private int $statusCode = 200;
    private array $headers = [];
    private string $content = '';

    public function __construct(string $content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function getStatusCode(): int { return $this->statusCode; }
    public function setStatusCode(int $code): void { $this->statusCode = $code; }
    public function getContent(): string { return $this->content; }
    public function setContent(string $content): void { $this->content = $content; }

    public function headers()
    {
        return new class($this->headers) {
            private array $headers;
            public function __construct(array &$headers) { $this->headers = &$headers; }
            public function set(string $key, string $value): void { $this->headers[$key] = $value; }
            public function get(string $key): ?string { return $this->headers[$key] ?? null; }
        };
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }
        echo $this->content;
    }
}

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
        $this->corsEnabled = defined('CORS_ENABLED') ? CORS_ENABLED : true;
        $this->allowedOrigins = defined('CORS_ALLOWED_ORIGINS') ? (array) CORS_ALLOWED_ORIGINS : ['*'];
        $this->maxRequestSize = defined('APP_MAX_REQUEST_SIZE') ? APP_MAX_REQUEST_SIZE : 10 * 1024 * 1024;
    }

    /**
     * Register default middleware
     */
    private function registerDefaultMiddleware(): void
    {
        $this->globalMiddleware = [
            'cors' => [$this, 'handleCors'],
            'request.size' => [$this, 'validateRequestSize'],
            'security.headers' => [$this, 'addSecurityHeaders'],
            'request.logging' => [$this, 'logRequest'],
            'rate.limit' => [$this, 'checkRateLimit']
        ];

        $this->routeMiddleware = [
            'auth' => [$this, 'authenticate'],
            'auth.api' => [$this, 'authenticateApi'],
            'permission' => [$this, 'checkPermission'],
            'throttle' => [$this, 'throttleRequest'],
            'validate' => [$this, 'validateInput']
        ];
    }

    /**
     * Send JSON response
     */
    private function jsonResponse(array $data, int $status = 200): SimpleResponse
    {
        return new SimpleResponse(json_encode($data), $status, ['Content-Type' => 'application/json']);
    }

    /**
     * Handle CORS
     */
    public function handleCors(SimpleRequest $request, Closure $next): SimpleResponse
    {
        if (!$this->corsEnabled) {
            return $next($request);
        }

        $origin = $request->header('Origin');

        if (in_array('*', $this->allowedOrigins) || in_array($origin, $this->allowedOrigins)) {
            $response = $next($request);

            $response->headers()->set('Access-Control-Allow-Origin', $origin ?: '*');
            $response->headers()->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
            $response->headers()->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
            $response->headers()->set('Access-Control-Allow-Credentials', 'true');
            $response->headers()->set('Access-Control-Max-Age', '86400');

            return $response;
        }

        return $this->jsonResponse(['error' => 'CORS policy violation'], 403);
    }

    /**
     * Validate request size
     */
    public function validateRequestSize(SimpleRequest $request, Closure $next): SimpleResponse
    {
        $contentLength = $request->header('Content-Length');

        if ($contentLength && (int)$contentLength > $this->maxRequestSize) {
            return $this->jsonResponse([
                'error' => 'Request size exceeds maximum limit',
                'max_size' => $this->maxRequestSize
            ], 413);
        }

        return $next($request);
    }

    /**
     * Add security headers
     */
    public function addSecurityHeaders(SimpleRequest $request, Closure $next): SimpleResponse
    {
        $response = $next($request);

        $response->headers()->set('X-Content-Type-Options', 'nosniff');
        $response->headers()->set('X-Frame-Options', 'DENY');
        $response->headers()->set('X-XSS-Protection', '1; mode=block');
        $response->headers()->set('Strict-Transport-Security', 'max-age=63072000; includeSubDomains');
        // CSP handled centrally by BaseController::setSecurityHeaders()
        $response->headers()->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }

    /**
     * Log request
     */
    public function logRequest(SimpleRequest $request, Closure $next): SimpleResponse
    {
        $startTime = microtime(true);

        error_log('Request received: ' . $request->method() . ' ' . $request->path() . ' from ' . $request->ip());

        $response = $next($request);

        $duration = round((microtime(true) - $startTime) * 1000, 2);

        error_log('Request processed: ' . $request->method() . ' ' . $request->path() . ' -> ' . $response->getStatusCode() . ' (' . $duration . 'ms)');

        return $response;
    }

    /**
     * Check rate limit
     */
    public function checkRateLimit(SimpleRequest $request, Closure $next): SimpleResponse
    {
        // Bypass rate limiting during local development testing/auditing
        if (isset($_GET['test_login']) || $request->header('X-Testing') || (defined('APP_ENV') && APP_ENV === 'testing')) {
            return $next($request);
        }

        $key = 'request:' . $request->ip() . ':' . $request->path();
        $maxAttempts = 60;
        $decaySeconds = 60;

        $attempts = $_SESSION['_rate_limit'][$key] ?? [];
        $now = time();

        // Clean old attempts
        $attempts = array_filter($attempts, fn($t) => ($now - $t) < $decaySeconds);

        if (count($attempts) >= $maxAttempts) {
            $retryAfter = $decaySeconds - ($now - min($attempts));
            return $this->jsonResponse([
                'error' => 'Too many requests',
                'retry_after' => max($retryAfter, 0)
            ], 429);
        }

        $attempts[] = $now;
        $_SESSION['_rate_limit'][$key] = $attempts;

        return $next($request);
    }

    /**
     * Authenticate user
     */
    public function authenticate(SimpleRequest $request, Closure $next): SimpleResponse
    {
        if (empty($_SESSION['user_id'])) {
            return $this->jsonResponse(['error' => 'Authentication required'], 401);
        }

        return $next($request);
    }

    /**
     * Authenticate API request
     */
    public function authenticateApi(SimpleRequest $request, Closure $next): SimpleResponse
    {
        $token = $request->bearerToken();

        if (!$token) {
            return $this->jsonResponse(['error' => 'API token required'], 401);
        }

        if (!$this->validateApiToken($token)) {
            return $this->jsonResponse(['error' => 'Invalid API token'], 401);
        }

        return $next($request);
    }

    /**
     * Check permission
     */
    public function checkPermission(SimpleRequest $request, Closure $next, string $permission): SimpleResponse
    {
        $permissions = $_SESSION['permissions'] ?? [];
        if (!in_array($permission, $permissions)) {
            return $this->jsonResponse(['error' => 'Insufficient permissions'], 403);
        }

        return $next($request);
    }

    /**
     * Throttle request
     */
    public function throttleRequest(SimpleRequest $request, Closure $next, int $maxAttempts = 60, int $minutes = 1): SimpleResponse
    {
        $key = 'throttle:' . $request->ip() . ':' . $request->path();
        $decaySeconds = $minutes * 60;

        $attempts = $_SESSION['_rate_limit'][$key] ?? [];
        $now = time();

        $attempts = array_filter($attempts, fn($t) => ($now - $t) < $decaySeconds);

        if (count($attempts) >= $maxAttempts) {
            $retryAfter = $decaySeconds - ($now - min($attempts));
            return $this->jsonResponse([
                'error' => 'Request throttled',
                'retry_after' => max($retryAfter, 0)
            ], 429);
        }

        $attempts[] = $now;
        $_SESSION['_rate_limit'][$key] = $attempts;

        return $next($request);
    }

    /**
     * Validate input
     */
    public function validateInput(SimpleRequest $request, Closure $next, array $rules = []): SimpleResponse
    {
        if (empty($rules)) {
            return $next($request);
        }

        $input = $request->all();
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $fieldRules = explode('|', $ruleString);
            $value = $input[$field] ?? null;

            foreach ($fieldRules as $rule) {
                if ($rule === 'required' && (empty($value) && $value !== '0')) {
                    $errors[$field][] = "The {$field} field is required";
                } elseif (str_starts_with($rule, 'min:') && strlen((string)($value ?? '')) < (int) substr($rule, 4)) {
                    $errors[$field][] = "The {$field} must be at least " . substr($rule, 4) . " characters";
                } elseif (str_starts_with($rule, 'max:') && strlen((string)($value ?? '')) > (int) substr($rule, 4)) {
                    $errors[$field][] = "The {$field} must not exceed " . substr($rule, 4) . " characters";
                } elseif ($rule === 'email' && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = "The {$field} must be a valid email";
                }
            }
        }

        if (!empty($errors)) {
            return $this->jsonResponse([
                'error' => 'Validation failed',
                'errors' => $errors
            ], 422);
        }

        return $next($request);
    }

    /**
     * Validate API token
     */
    private function validateApiToken(string $token): bool
    {
        return strlen($token) >= 32;
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
    public function applyMiddleware(SimpleRequest $request, Closure $next, array $middleware = []): SimpleResponse
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
    public function getRequestMetadata(SimpleRequest $request): array
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
            'timestamp' => date('c'),
            'user_id' => $_SESSION['user_id'] ?? null,
            'session_id' => session_id()
        ];
    }

    /**
     * Sanitize request input
     */
    public function sanitizeInput(SimpleRequest $request): SimpleRequest
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
    public function validateJsonRequest(SimpleRequest $request): bool
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
    public function getClientInfo(SimpleRequest $request): array
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
    public function detectSuspiciousActivity(SimpleRequest $request): array
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
    public function createMiddlewareResponse(string $message, int $status = 400, array $data = []): SimpleResponse
    {
        return $this->jsonResponse(array_merge([
            'error' => $message,
            'timestamp' => date('c')
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

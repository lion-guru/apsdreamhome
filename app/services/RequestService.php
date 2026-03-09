<?php

namespace App\Services;

use App\Core\Database\Database;

/**
 * Custom Request Service - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 * Following APS Dream Home custom architecture patterns
 */
class RequestService
{
    private $db;
    private $routes = [];
    private $middleware = [];
    private $currentRequest;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Register a route
     */
    public function registerRoute($method, $path, $handler, $middleware = [])
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    /**
     * Register middleware
     */
    public function registerMiddleware($stage, $handler)
    {
        $this->middleware[$stage][] = $handler;
    }

    /**
     * Add security middleware
     */
    public function addSecurityMiddleware()
    {
        $this->registerMiddleware('before', function ($request) {
            // CSRF validation
            if (in_array($request['method'], ['POST', 'PUT', 'DELETE'])) {
                $token = $request['headers']['x-csrf-token'] ?? $request['post']['_token'] ?? '';
                if (!$this->validateCsrfToken($token)) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Invalid CSRF token']);
                    exit;
                }
            }

            return $request;
        });
    }

    /**
     * Add rate limiting middleware
     */
    public function addRateLimitingMiddleware($requests = 100, $window = 3600)
    {
        $this->registerMiddleware('before', function ($request) use ($requests, $window) {
            $ip = $request['ip'];
            $key = "rate_limit:$ip";

            $current = $this->db->fetchOne(
                "SELECT requests, window_start FROM rate_limits WHERE ip_address = ?",
                [$ip]
            );

            $now = time();
            $windowStart = $current ? strtotime($current['window_start']) : $now;

            if ($now - $windowStart > $window) {
                // Reset window
                $count = 1;
                $this->db->query(
                    "INSERT INTO rate_limits (ip_address, requests, window_start) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE requests = 1, window_start = VALUES(window_start)",
                    [$ip, 1, date('Y-m-d H:i:s', $now)]
                );
            } else {
                $count = $current['requests'] + 1;
                $this->db->query(
                    "UPDATE rate_limits SET requests = requests + 1 WHERE ip_address = ?",
                    [$ip]
                );
            }

            if ($count > $requests) {
                http_response_code(429);
                echo json_encode(['error' => 'Rate limit exceeded']);
                exit;
            }

            return $request;
        });
    }

    /**
     * Add logging middleware
     */
    public function addLoggingMiddleware()
    {
        $this->registerMiddleware('after', function ($request, $response) {
            $this->db->insert('api_logs', [
                'method' => $request['method'],
                'uri' => $request['uri'],
                'ip_address' => $request['ip'],
                'user_agent' => $request['user_agent'],
                'status_code' => $response['status'] ?? 200,
                'response_size' => strlen($response['body'] ?? ''),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return $response;
        });
    }

    /**
     * Handle CORS
     */
    public function handleCors()
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
        $methods = 'GET, POST, PUT, DELETE, OPTIONS';
        $headers = 'Content-Type, Authorization, X-CSRF-Token';

        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Methods: $methods");
        header("Access-Control-Allow-Headers: $headers");

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    /**
     * Process current request
     */
    public function processRequest()
    {
        $this->currentRequest = $this->parseRequest();

        // Run before middleware
        foreach ($this->middleware['before'] ?? [] as $middleware) {
            $this->currentRequest = $middleware($this->currentRequest);
        }

        // Find matching route
        $response = $this->dispatchRoute($this->currentRequest);

        // Run after middleware
        foreach ($this->middleware['after'] ?? [] as $middleware) {
            $response = $middleware($this->currentRequest, $response);
        }

        return $response;
    }

    /**
     * Parse incoming request
     */
    private function parseRequest()
    {
        return [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
            'uri' => parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH),
            'query' => $_GET,
            'post' => $_POST,
            'json' => $this->parseJsonBody(),
            'headers' => $this->parseHeaders(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];
    }

    /**
     * Parse JSON body
     */
    private function parseJsonBody()
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }

    /**
     * Parse headers
     */
    private function parseHeaders()
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (strpos($name, 'HTTP_') === 0) {
                $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$headerName] = $value;
            }
        }
        return $headers;
    }

    /**
     * Dispatch to matching route
     */
    private function dispatchRoute($request)
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request['method']) {
                continue;
            }

            $pattern = $this->convertPathToRegex($route['path']);
            if (preg_match($pattern, $request['uri'], $matches)) {
                // Run route middleware
                foreach ($route['middleware'] as $middleware) {
                    if (is_callable($middleware)) {
                        $request = $middleware($request);
                    }
                }

                // Call handler
                $handler = $route['handler'];
                if (is_array($handler)) {
                    $object = $handler[0];
                    $method = $handler[1];
                    return $object->$method($request);
                }

                return $handler($request);
            }
        }

        // 404 response
        http_response_code(404);
        return [
            'status' => 404,
            'body' => 'Not Found'
        ];
    }

    /**
     * Convert path to regex pattern
     */
    private function convertPathToRegex($path)
    {
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * Validate CSRF token
     */
    private function validateCsrfToken($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

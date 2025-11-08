<?php
/**
 * Enhanced Security API Middleware
 * Comprehensive API security for APS Dream Homes
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

/**
 * API Security Middleware Class
 */
class APISecurityMiddleware {

    private $security_log_file;
    private $rate_limit_file;
    private $max_requests_per_hour;
    private $allowed_origins;

    /**
     * Constructor - Initialize API security
     */
    public function __construct() {
        $this->security_log_file = __DIR__ . '/../logs/api_security.log';
        $this->rate_limit_file = __DIR__ . '/../logs/api_rate_limit.json';
        $this->max_requests_per_hour = 1000; // Configurable per endpoint

        // Configure allowed origins
        $this->allowed_origins = [
            'https://localhost',
            'http://localhost',
            'https://127.0.0.1',
            'http://127.0.0.1',
            'https://yoursite.com', // Replace with your actual domain
            'https://www.yoursite.com'
        ];

        // Ensure log directory exists
        ensureLogDirectory($this->security_log_file);
    }

    /**
     * Apply all security measures to API request
     */
    public function secureAPIRequest() {
        $this->validateHTTPS();
        $this->validateRequestHeaders();
        $this->applySecurityHeaders();
        $this->validateRateLimit();
        $this->validateOrigin();
        $this->logAPIRequest();
    }

    /**
     * Validate HTTPS connection
     */
    private function validateHTTPS() {
        if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
            $this->logSecurityEvent('HTTP API Access Attempt', [
                'ip' => getClientIP(),
                'endpoint' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            exit();
        }
    }

    /**
     * Validate request headers
     */
    private function validateRequestHeaders() {
        $headers = getallheaders();

        // Check for required headers
        $required_headers = [
            'User-Agent',
            'Accept',
            'Accept-Language'
        ];

        foreach ($required_headers as $header) {
            if (!isset($headers[$header]) || empty($headers[$header])) {
                $this->logSecurityEvent('Missing Required API Header', [
                    'ip' => getClientIP(),
                    'missing_header' => $header,
                    'headers' => $headers,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                $this->sendErrorResponse(400, 'Missing required headers');
            }
        }

        // Validate User-Agent
        $user_agent = $headers['User-Agent'];
        if (strlen($user_agent) < 10 || strlen($user_agent) > 500) {
            $this->logSecurityEvent('Invalid User-Agent Length', [
                'ip' => getClientIP(),
                'user_agent_length' => strlen($user_agent),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            $this->sendErrorResponse(400, 'Invalid User-Agent');
        }

        // Check for suspicious User-Agent patterns
        $suspicious_patterns = [
            '/bot/i',
            '/crawler/i',
            '/spider/i',
            '/scraper/i',
            '/sqlmap/i',
            '/nmap/i',
            '/nikto/i'
        ];

        foreach ($suspicious_patterns as $pattern) {
            if (preg_match($pattern, $user_agent)) {
                $this->logSecurityEvent('Suspicious User-Agent Detected', [
                    'ip' => getClientIP(),
                    'user_agent' => $user_agent,
                    'pattern' => $pattern,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                $this->sendErrorResponse(403, 'Access denied');
            }
        }
    }

    /**
     * Apply comprehensive security headers
     */
    private function applySecurityHeaders() {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; img-src 'self' data: https:; font-src 'self' https://cdnjs.cloudflare.com; connect-src 'self'; frame-ancestors 'none';");
        header('X-Permitted-Cross-Domain-Policies: none');
        header('Cross-Origin-Embedder-Policy: require-corp');
        header('Cross-Origin-Opener-Policy: same-origin');
        header('Cross-Origin-Resource-Policy: same-origin');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
    }

    /**
     * Validate rate limiting
     */
    private function validateRateLimit() {
        $ip = getClientIP();
        $rate_data = checkRateLimit($ip, $this->rate_limit_file, $this->max_requests_per_hour);

        if (!$rate_data['allowed']) {
            $this->logSecurityEvent('API Rate Limit Exceeded', [
                'ip' => $ip,
                'requests' => $rate_data['operations'],
                'reset_time' => $rate_data['reset_time'],
                'endpoint' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            $this->sendErrorResponse(429, 'Rate limit exceeded. Please try again later.', [
                'retry_after' => $rate_data['reset_time'],
                'requests_remaining' => $rate_data['remaining']
            ]);
        }
    }

    /**
     * Validate CORS origin
     */
    private function validateOrigin() {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if (!empty($origin) && !in_array($origin, $this->allowed_origins)) {
            $this->logSecurityEvent('Invalid CORS Origin', [
                'ip' => getClientIP(),
                'origin' => $origin,
                'allowed_origins' => $this->allowed_origins,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            $this->sendErrorResponse(403, 'Origin not allowed');
        }

        // Set CORS headers
        if (in_array($origin, $this->allowed_origins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-Key');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');
        }

        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    /**
     * Log API request
     */
    private function logAPIRequest() {
        $this->logSecurityEvent('API Request', [
            'ip' => getClientIP(),
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'endpoint' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Validate API authentication
     */
    public function validateAPIAuth($required = true) {
        $headers = getallheaders();

        // Check for API key
        $api_key = $headers['X-API-Key'] ?? $_GET['api_key'] ?? $_POST['api_key'] ?? null;

        if ($required && empty($api_key)) {
            $this->logSecurityEvent('Missing API Key', [
                'ip' => getClientIP(),
                'endpoint' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            $this->sendErrorResponse(401, 'API key required');
        }

        if ($api_key && !$this->validateAPIKey($api_key)) {
            $this->logSecurityEvent('Invalid API Key', [
                'ip' => getClientIP(),
                'endpoint' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            $this->sendErrorResponse(401, 'Invalid API key');
        }

        return true;
    }

    /**
     * Validate API key (implement your own logic)
     */
    private function validateAPIKey($api_key) {
        // This should be replaced with your actual API key validation logic
        // For now, we'll use a simple check
        $valid_keys = [
            'your-api-key-here', // Replace with actual API keys
            'development-key-123'
        ];

        return in_array($api_key, $valid_keys);
    }

    /**
     * Validate and sanitize input data
     */
    public function sanitizeInput($data, $type = 'string') {
        if (is_array($data)) {
            return array_map(function($item) use ($type) {
                return $this->sanitizeInput($item, $type);
            }, $data);
        }

        switch ($type) {
            case 'email':
                return filter_var(trim($data), FILTER_SANITIZE_EMAIL);
            case 'url':
                return filter_var(trim($data), FILTER_SANITIZE_URL);
            case 'int':
                return filter_var(trim($data), FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var(trim($data), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case 'string':
            default:
                return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
        }
    }

    /**
     * Validate request data
     */
    public function validateRequestData($rules = []) {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = $_POST[$field] ?? $_GET[$field] ?? null;

            // Required field check
            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[] = "Field '$field' is required";
                continue;
            }

            // Skip further validation if field is empty and not required
            if (empty($value) && strpos($rule, 'required') === false) {
                continue;
            }

            // Type validation
            if (strpos($rule, 'email') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Field '$field' must be a valid email address";
            }

            if (strpos($rule, 'url') !== false && !filter_var($value, FILTER_VALIDATE_URL)) {
                $errors[] = "Field '$field' must be a valid URL";
            }

            if (strpos($rule, 'numeric') !== false && !is_numeric($value)) {
                $errors[] = "Field '$field' must be numeric";
            }

            // Length validation
            if (preg_match('/min:(\d+)/', $rule, $matches) && strlen($value) < $matches[1]) {
                $errors[] = "Field '$field' must be at least {$matches[1]} characters long";
            }

            if (preg_match('/max:(\d+)/', $rule, $matches) && strlen($value) > $matches[1]) {
                $errors[] = "Field '$field' must not exceed {$matches[1]} characters";
            }
        }

        if (!empty($errors)) {
            $this->logSecurityEvent('Input Validation Failed', [
                'ip' => getClientIP(),
                'errors' => $errors,
                'endpoint' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            $this->sendErrorResponse(400, 'Validation failed', ['errors' => $errors]);
        }

        return true;
    }

    /**
     * Send error response
     */
    private function sendErrorResponse($status_code, $message, $additional_data = []) {
        http_response_code($status_code);
        header('Content-Type: application/json');

        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        if (!empty($additional_data)) {
            $response = array_merge($response, $additional_data);
        }

        echo json_encode($response);
        exit();
    }

    /**
     * Log security events
     */
    private function logSecurityEvent($event, $data = []) {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => getClientIP(),
            'session_id' => session_id() ?? 'NO_SESSION',
            'event' => $event,
            'data' => $data,
            'endpoint' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];

        $log_message = json_encode($log_entry) . PHP_EOL;
        file_put_contents($this->security_log_file, $log_message, FILE_APPEND | LOCK_EX);
    }

    /**
     * Get client IP address
     */
    private function getClientIP() {
        $ip_sources = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',  // Proxy
            'HTTP_X_FORWARDED',      // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP', // Cluster
            'HTTP_X_REAL_IP',        // Nginx
            'HTTP_CLIENT_IP',        // Client
            'REMOTE_ADDR'            // Default
        ];

        foreach ($ip_sources as $source) {
            if (!empty($_SERVER[$source])) {
                $ip = $_SERVER[$source];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    }

    /**
     * Check rate limit
     */
    private function checkRateLimit($ip, $rate_limit_file, $max_operations) {
        $current_time = time();
        $reset_time = $current_time + 3600; // 1 hour reset

        if (!file_exists($rate_limit_file)) {
            $dir = dirname($rate_limit_file);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        $rate_data = [];
        if (file_exists($rate_limit_file)) {
            $rate_data = json_decode(file_get_contents($rate_limit_file), true) ?? [];
        }

        if (!isset($rate_data[$ip])) {
            $rate_data[$ip] = [
                'operations' => 0,
                'reset_time' => $reset_time,
                'last_operation' => $current_time
            ];
        }

        if ($current_time > $rate_data[$ip]['reset_time']) {
            $rate_data[$ip] = [
                'operations' => 0,
                'reset_time' => $reset_time,
                'last_operation' => $current_time
            ];
        }

        $rate_data[$ip]['operations']++;
        $rate_data[$ip]['last_operation'] = $current_time;

        file_put_contents($rate_limit_file, json_encode($rate_data));

        $allowed = $rate_data[$ip]['operations'] <= $max_operations;

        return [
            'allowed' => $allowed,
            'operations' => $rate_data[$ip]['operations'],
            'reset_time' => $rate_data[$ip]['reset_time'],
            'remaining' => max(0, $max_operations - $rate_data[$ip]['operations'])
        ];
    }

    /**
     * Ensure log directory exists
     */
    private function ensureLogDirectory($log_file) {
        $log_dir = dirname($log_file);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
            chmod($log_dir, 0755);
        }
    }
}

/**
 * Helper function to create API security middleware
 */
function createAPISecurity() {
    return new APISecurityMiddleware();
}

/**
 * Quick API response helper
 */
function sendAPIResponse($data, $status_code = 200, $message = 'Success') {
    http_response_code($status_code);
    header('Content-Type: application/json');

    $response = [
        'success' => $status_code >= 200 && $status_code < 300,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    echo json_encode($response);
    exit();
}

/**
 * Quick API error helper
 */
function sendAPIError($message, $status_code = 400, $errors = []) {
    http_response_code($status_code);
    header('Content-Type: application/json');

    $response = [
        'success' => false,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    if (!empty($errors)) {
        $response['errors'] = $errors;
    }

    echo json_encode($response);
    exit();
}

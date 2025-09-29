<?php
/**
 * APS Dream Home - Security Configuration
 * Security settings and configurations
 */

// Security settings
define('SECURITY_HEADERS', [
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'SAMEORIGIN',
    'X-XSS-Protection' => '1; mode=block',
    'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains'
]);

// CSRF Protection
define('CSRF_TOKEN_LIFETIME', 3600); // 1 hour
define('CSRF_TOKEN_NAME', 'csrf_token');

// Rate Limiting
define('RATE_LIMIT_REQUESTS', 100); // requests per minute
define('RATE_LIMIT_WINDOW', 60); // seconds

// File Upload Security
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('UPLOAD_PATH', 'uploads/');

// Session Security
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_COOKIE_SECURE', false); // Set to true for HTTPS
define('SESSION_COOKIE_HTTPONLY', true);

// Password Security
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REQUIRE_SPECIAL_CHARS', true);
define('PASSWORD_REQUIRE_NUMBERS', true);
define('PASSWORD_REQUIRE_UPPERCASE', true);

// API Security
define('API_RATE_LIMIT', 1000); // API requests per hour
define('API_KEY_LENGTH', 32);

// Security Keys (Generate random keys for production)
define('ENCRYPTION_KEY', 'your-encryption-key-here-' . str_repeat('x', 32));
define('JWT_SECRET', 'your-jwt-secret-key-here-' . str_repeat('y', 32));

// Database Security
define('DB_PREFIX', 'aps_');
define('DB_CHARSET', 'utf8mb4');

// Security Functions
class SecurityConfig {
    public static function getSecurityHeaders() {
        return SECURITY_HEADERS;
    }

    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) &&
               $_SESSION['csrf_token'] === $token &&
               (time() - $_SESSION['csrf_token_time']) < CSRF_TOKEN_LIFETIME;
    }

    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([__CLASS__, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    public static function isValidFileType($filename) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($extension, ALLOWED_FILE_TYPES);
    }

    public static function generateSecureToken($length = 32) {
        return bin2hex(random_bytes($length));
    }

    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public static function encryptData($data, $key = null) {
        $key = $key ?: ENCRYPTION_KEY;
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    public static function decryptData($data, $key = null) {
        $key = $key ?: ENCRYPTION_KEY;
        $data = base64_decode($data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }
}

// Check if running in CLI mode to avoid header issues
        if (php_sapi_name() === 'cli') {
            return; // Skip header operations in CLI
        }
        
        // Set security headers
        foreach (SECURITY_HEADERS as $header => $value) {
            header("$header: $value");
        }

// Security middleware
if (!function_exists('securityMiddleware')) {
    function securityMiddleware() {
        // CSRF protection for POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!SecurityConfig::validateCSRFToken($_POST['csrf_token'] ?? '')) {
                http_response_code(403);
                die('CSRF token validation failed');
            }
        }

        // Rate limiting (basic implementation)
        $ip = $_SERVER['REMOTE_ADDR'];
        $time = time();
        $requests = $_SESSION['requests'] ?? [];

        // Clean old requests
        $requests = array_filter($requests, function($req_time) use ($time) {
            return ($time - $req_time) < RATE_LIMIT_WINDOW;
        });

        if (count($requests) >= RATE_LIMIT_REQUESTS) {
            http_response_code(429);
            die('Rate limit exceeded');
        }

        $requests[] = $time;
        $_SESSION['requests'] = $requests;
    }
}

// Initialize security
session_start();
securityMiddleware();
?>

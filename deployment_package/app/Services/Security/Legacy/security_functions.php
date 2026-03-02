<?php

namespace App\Services\Security\Legacy;
/**
 * Enhanced Security Functions for APS Dream Homes
 * Comprehensive security utilities for web application protection
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

/**
 * Get client IP address
 */
function getClientIP() {
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
            // Handle multiple IPs in X-Forwarded-For
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            // Validate IP format
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }

    return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
}

/**
 * Check rate limiting
 */
function checkRateLimit($ip, $rate_limit_file, $max_operations = 100) {
    $current_time = time();
    $reset_time = $current_time + 3600; // 1 hour reset

    // Ensure directory exists
    $dir = dirname($rate_limit_file);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    // Load existing rate limit data
    $rate_data = [];
    if (file_exists($rate_limit_file)) {
        $rate_data = json_decode(file_get_contents($rate_limit_file), true) ?? [];
    }

    // Clean up old entries (older than 1 hour)
    $rate_data = array_filter($rate_data, function($entry) use ($current_time) {
        return ($entry['reset_time'] ?? 0) > $current_time;
    });

    // Get or create entry for current IP
    if (!isset($rate_data[$ip])) {
        $rate_data[$ip] = [
            'operations' => 0,
            'reset_time' => $reset_time,
            'last_operation' => $current_time
        ];
    }

    // Check if reset is needed
    if ($current_time > $rate_data[$ip]['reset_time']) {
        $rate_data[$ip] = [
            'operations' => 0,
            'reset_time' => $reset_time,
            'last_operation' => $current_time
        ];
    }

    // Increment operation count
    $rate_data[$ip]['operations']++;
    $rate_data[$ip]['last_operation'] = $current_time;

    // Save updated data
    file_put_contents($rate_limit_file, json_encode($rate_data));

    // Check if rate limit exceeded
    $allowed = $rate_data[$ip]['operations'] <= $max_operations;

    return [
        'allowed' => $allowed,
        'operations' => $rate_data[$ip]['operations'],
        'reset_time' => $rate_data[$ip]['reset_time'],
        'remaining' => max(0, $max_operations - $rate_data[$ip]['operations'])
    ];
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) ||
        (time() - $_SESSION['csrf_token_time'] > 3600)) { // Token expires after 1 hour

        $_SESSION['csrf_token'] = bin2hex(\App\Helpers\SecurityHelper::secureRandomBytes(32));
        $_SESSION['csrf_token_time'] = time();
    }

    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCSRFToken($token) {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }

    // Check if token matches and hasn't expired
    if ($token === $_SESSION['csrf_token'] &&
        isset($_SESSION['csrf_token_time']) &&
        (time() - $_SESSION['csrf_token_time'] <= 3600)) {
        return true;
    }

    return false;
}

/**
 * Validate request headers
 */
function validateRequestHeaders($headers) {
    // Check for required headers
    $required_headers = [
        'User-Agent',
        'Accept',
        'Accept-Language'
    ];

    foreach ($required_headers as $header) {
        if (!isset($headers[$header]) || empty($headers[$header])) {
            return false;
        }
    }

    // Validate User-Agent (basic check)
    $user_agent = $headers['User-Agent'];
    if (strlen($user_agent) < 10 || strlen($user_agent) > 500) {
        return false;
    }

    // Check for suspicious patterns
    $suspicious_patterns = [
        '/bot/i',
        '/crawler/i',
        '/spider/i',
        '/scraper/i'
    ];

    foreach ($suspicious_patterns as $pattern) {
        if (preg_match($pattern, $user_agent)) {
            return false;
        }
    }

    return true;
}

/**
 * Log security events
 */
function logSecurityEvent($event, $data = [], $log_file = null) {
    $log_file = $log_file ?? __DIR__ . '/../logs/security.log';

    // Ensure log directory exists
    ensureLogDirectory($log_file);

    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'ip' => getClientIP(),
        'session_id' => session_id() ?? 'NO_SESSION',
        'event' => $event,
        'data' => $data,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
    ];

    $log_message = json_encode($log_entry) . PHP_EOL;

    // Write to log file
    if (file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX) === false) {
        error_log("Failed to write to security log: $log_file");
    }
}

/**
 * Ensure log directory exists
 */
function ensureLogDirectory($log_file) {
    $log_dir = dirname($log_file);

    if (!is_dir($log_dir)) {
        if (!mkdir($log_dir, 0755, true)) {
            error_log("Failed to create log directory: $log_dir");
            return false;
        }
    }

    // Set proper permissions
    chmod($log_dir, 0755);

    return true;
}

/**
 * Secure session management
 */
function secureSession() {
    // Set secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.gc_maxlifetime', 3600); // 1 hour

    // Regenerate session ID periodically
    if (!isset($_SESSION['last_regeneration'])) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

/**
 * Validate associate ID
 */
function isValidAssociateId($associate_id) {
    // Check if it's a valid format (alphanumeric, 5-20 characters)
    if (!preg_match('/^[a-zA-Z0-9]{5,20}$/', $associate_id)) {
        return false;
    }

    return true;
}

/**
 * Sanitize input data
 */
function sanitizeInput($data, $type = 'string') {
    if (is_array($data)) {
        return array_map(function($item) use ($type) {
            return sanitizeInput($item, $type);
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
            return h(trim($data));
    }
}

/**
 * Validate email address
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate URL
 */
function isValidURL($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Escape output for HTML
 */
function escapeHTML($data) {
    if (is_array($data)) {
        return array_map('escapeHTML', $data);
    }

    return h($data);
}

/**
 * Check if request is AJAX
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get current URL
 */
function getCurrentURL() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];

    return $protocol . '://' . $host . $uri;
}

/**
 * Generate secure random string
 */
function generateRandomString($length = 16) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $characters_length = strlen($characters);
    $random_string = '';

    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[\App\Helpers\SecurityHelper::secureRandomInt(0, $characters_length - 1)];
    }

    return $random_string;
}

/**
 * Hash password securely
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3
    ]);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Encrypt sensitive data
 */
function encryptData($data, $key = null) {
    $key = $key ?? $_ENV['ENCRYPTION_KEY'] ?? 'default_key_change_in_production';
    $method = 'AES-256-CBC';
    $iv = \App\Helpers\SecurityHelper::secureRandomBytes(16);

    $encrypted = openssl_encrypt($data, $method, $key, 0, $iv);

    return base64_encode($iv . $encrypted);
}

/**
 * Decrypt sensitive data
 */
function decryptData($encrypted_data, $key = null) {
    $key = $key ?? $_ENV['ENCRYPTION_KEY'] ?? 'default_key_change_in_production';
    $method = 'AES-256-CBC';
    $data = base64_decode($encrypted_data);

    if (strlen($data) < 16) {
        return false;
    }

    $iv = substr($data, 0, 16);
    $encrypted = substr($data, 16);

    return openssl_decrypt($encrypted, $method, $key, 0, $iv);
}

/**
 * Session timeout constant
 */
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

/**
 * Initialize security logging directory
 */
function initializeSecurityLogging() {
    $log_dir = __DIR__ . '/../logs';

    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
        chmod($log_dir, 0755);

        // Create .htaccess to protect logs
        $htaccess = $log_dir . '/.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Order deny,allow\nDeny from all\n");
        }
    }
}

// Initialize security logging on include
initializeSecurityLogging();
?>

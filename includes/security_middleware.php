<?php
/**
 * Security Middleware
 * Provides comprehensive security features for the application
 */

// require_once __DIR__ . '/logger.php'; // already commented
require_once __DIR__ . '/config_manager.php';
require_once __DIR__ . '/validator.php';

class SecurityMiddleware {
    private $logger; // legacy, unused
    private $config;
    private $ipWhitelist;
    private $csrfTokens;

    public function __construct() {
        $this->logger = null; // logger removed
        $this->config = ConfigManager::getInstance();
        $this->ipWhitelist = $this->loadIpWhitelist();
        $this->csrfTokens = [];
    }

    /**
     * Load IP Whitelist from configuration
     * 
     * @return array List of allowed IP addresses
     */
    private function loadIpWhitelist() {
        $whitelistConfig = $this->config->get('ALLOWED_IP_RANGES', '');
        if (empty($whitelistConfig)) {
            return [];
        }
        return array_map('trim', explode(',', $whitelistConfig));
    }

    /**
     * Check if IP is allowed
     * 
     * @param string $ip IP address to check
     * @return bool Whether IP is allowed
     */
    public function isIpAllowed($ip) {
        // If no whitelist, allow all
        if (empty($this->ipWhitelist)) {
            return true;
        }

        foreach ($this->ipWhitelist as $allowedIp) {
            if ($this->ipMatchesRange($ip, $allowedIp)) {
                return true;
            }
        }

        // Log blocked IP attempt
        // $this->logger->logSecurityEvent('IP_BLOCKED', 'Unauthorized IP access attempt');

        return false;
    }

    /**
     * Check if IP matches a range
     * 
     * @param string $ip IP to check
     * @param string $range IP range or single IP
     * @return bool Whether IP is in range
     */
    private function ipMatchesRange($ip, $range) {
        // Handle CIDR notation
        if (strpos($range, '/') !== false) {
            list($range, $bits) = explode('/', $range);
            $mask = -1 << (32 - $bits);
            return (ip2long($ip) & $mask) === (ip2long($range) & $mask);
        }

        // Direct IP match
        return $ip === $range;
    }

    /**
     * Generate CSRF Token
     * 
     * @return string CSRF Token
     */
    public function generateCsrfToken() {
        $token = bin2hex(random_bytes(32));
        $this->csrfTokens[$token] = time();
        
        // Clean up old tokens
        $this->cleanupCsrfTokens();
        
        return $token;
    }

    /**
     * Validate CSRF Token
     * 
     * @param string $token Token to validate
     * @return bool Whether token is valid
     */
    public function validateCsrfToken($token) {
        // Check if token exists
        if (!isset($this->csrfTokens[$token])) {
            // $this->logger->logSecurityEvent('CSRF_ATTEMPT', 'Invalid CSRF token', array('token' => $token));
            return false;
        }

        // Check token age (15 minutes max)
        $tokenAge = time() - $this->csrfTokens[$token];
        if ($tokenAge > 900) {
            unset($this->csrfTokens[$token]);
            return false;
        }

        // Remove used token
        unset($this->csrfTokens[$token]);
        return true;
    }

    /**
     * Clean up old CSRF tokens
     */
    private function cleanupCsrfTokens() {
        $now = time();
        foreach ($this->csrfTokens as $token => $timestamp) {
            if ($now - $timestamp > 900) {
                unset($this->csrfTokens[$token]);
            }
        }
    }

    /**
     * Sanitize input to prevent XSS
     * 
     * @param mixed $input Input to sanitize
     * @return mixed Sanitized input
     */
    public function sanitizeInput($input) {
        // Use validator for comprehensive sanitization
        $validator = validator();
        
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }

        if (!is_string($input)) {
            return $input;
        }

        // Sanitize input using validator
        return $validator->sanitize($input, 'string');
    }

    /**
     * Validate and sanitize all input
     * 
     * @param array $input Input to validate
     * @return array Validated and sanitized input
     */
    public function validateInput($input, $rules = []) {
        $validator = validator();
        
        // If no specific rules, perform basic sanitization
        if (empty($rules)) {
            $sanitized = [];
            foreach ($input as $key => $value) {
                $sanitized[$key] = $this->sanitizeInput($value);
            }
            return $sanitized;
        }

        // Validate input with specific rules
        if ($validator->validate($input, $rules)) {
            return $input;
        }

        // Log validation errors
        // $this->logger->warning('Input Validation Failed', array('errors' => $validator->getErrors()));

        return false;
    }

    /**
     * Rate limiting mechanism
     * 
     * @param string $key Unique identifier for rate limiting
     * @param int $limit Maximum number of attempts
     * @param int $window Time window in seconds
     * @return bool Whether request is allowed
     */
    public function rateLimit($key, $limit = 100, $window = 3600) {
        $cacheFile = sys_get_temp_dir() . '/rate_limit_' . md5($key);
        
        // Initialize or load existing rate limit data
        $data = file_exists($cacheFile) ? unserialize(file_get_contents($cacheFile)) : [];
        
        $now = time();
        
        // Remove old entries
        $data = array_filter($data, function($timestamp) use ($now, $window) {
            return $now - $timestamp < $window;
        });
        
        // Check if limit is exceeded
        if (count($data) >= $limit) {
            // $this->logger->logSecurityEvent('RATE_LIMIT_EXCEEDED', 'Rate limit reached', array('key' => $key, 'current_count' => count($data)));
            return false;
        }
        
        // Add current timestamp
        $data[] = $now;
        
        // Save updated data
        file_put_contents($cacheFile, serialize($data));
        
        return true;
    }

    /**
     * Secure password hashing
     * 
     * @param string $password Plain text password
     * @return string Hashed password
     */
    public function hashPassword($password) {
        $salt = $this->config->get('SECURITY_SALT', bin2hex(random_bytes(16)));
        return password_hash($password . $salt, PASSWORD_ARGON2ID);
    }

    /**
     * Verify password
     * 
     * @param string $password Plain text password
     * @param string $hash Stored password hash
     * @return bool Whether password is correct
     */
    public function verifyPassword($password, $hash) {
        $salt = $this->config->get('SECURITY_SALT', '');
        return password_verify($password . $salt, $hash);
    }
}

// Global security middleware instance
function security() {
    static $middleware = null;
    if ($middleware === null) {
        $middleware = new SecurityMiddleware();
    }
    return $middleware;
}

// Initialize security checks on every request
try {
    // IP Whitelisting
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
    if (!security()->isIpAllowed($clientIp)) {
        http_response_code(403);
        die('Access Denied');
    }

    // Rate Limiting
    if (!security()->rateLimit($clientIp)) {
        http_response_code(429);
        die('Too Many Requests');
    }
} catch (Exception $e) {
    // Log any security initialization errors
    error_log("Security Middleware Error: " . $e->getMessage());
}

<?php
/**
 * CSRF Protection Helper Class
 * Secure CSRF token generation, validation, and cookie handling
 */
class CSRFProtection {
    private static $token_name = 'csrf_token';
    private static $token_lifetime = 3600; // 1 hour
    private static $cookie_name = 'csrf_token';

    /**
     * Generate a secure CSRF token
     */
    public static function generateToken($regenerate_id = false) {
        // Regenerate session ID if needed (prevents session fixation)
        if ($regenerate_id && session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        // Use cryptographically secure random bytes
        $token = bin2hex(random_bytes(32));
        
        // Store in session
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        $_SESSION[self::$token_name] = $token;
        $_SESSION[self::$token_name . '_time'] = time();

        // Set secure cookie (PHP 8 compat: 'expires' not 'expires_or_seconds')
        setcookie(self::$cookie_name, $token, [
            'expires' => time() + self::$token_lifetime,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict',
        ]);

        return $token;
    }

    /**
     * Validate CSRF token
     */
    public static function validateToken($token = null) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Get token from parameter or header or cookie
        if ($token === null) {
            $token = $_POST[self::$token_name] ?? 
                     $_SERVER['HTTP_X_CSRF_TOKEN'] ?? 
                     $_COOKIE[self::$cookie_name] ?? 
                     '';
        }

        // Check if token exists in session
        if (!isset($_SESSION[self::$token_name])) {
            return false;
        }

        // Check token age (prevent replay attacks)
        $stored_time = $_SESSION[self::$token_name . '_time'] ?? 0;
        if (time() - $stored_time > self::$token_lifetime) {
            self::clearToken();
            return false;
        }

        // Validate token
        return hash_equals($_SESSION[self::$token_name], $token);
    }

    /**
     * Generate hidden form field with CSRF token
     */
    public static function csrfField($name = null) {
        $name = $name ?? self::$token_name;
        $token = $_SESSION[self::$token_name] ?? self::generateToken();
        return '<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Validate and respond (for AJAX)
     */
    public static function validateAjax($token = null) {
        $valid = self::validateToken($token);
        
        if (!$valid) {
            header('HTTP/1.1 403 Forbidden');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'CSRF token validation failed']);
            exit;
        }
        
        return true;
    }

    /**
     * Clear token (call after successful submission)
     */
    public static function clearToken() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        unset($_SESSION[self::$token_name]);
        unset($_SESSION[self::$token_name . '_time']);
        setcookie(self::$cookie_name, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
    }

    /**
     * Middleware function to include in controllers
     */
    public static function middleware() {
        // Auto-validate for POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Skip for login form (token is not yet set)
            if (strpos($_SERVER['REQUEST_URI'], '/login') === false) {
                self::validateAjax();
            }
        }
        
        // Auto-generate token for forms
        self::generateToken();
    }
}
?>

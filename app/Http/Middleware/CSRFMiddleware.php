<?php
/**
 * CSRF Protection Middleware
 * Validates CSRF tokens for POST requests
 */

class CSRFMiddleware {
    private static $tokenName = 'csrf_token';
    private static $sessionTokenKey = '_csrf_token';
    
    /**
     * Generate CSRF token
     */
    public static function generateToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::$sessionTokenKey] = $token;
        
        return $token;
    }
    
    /**
     * Get current CSRF token
     */
    public static function getToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION[self::$sessionTokenKey] ?? self::generateToken();
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $requestToken = $_POST[self::$tokenName] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $sessionToken = $_SESSION[self::$sessionTokenKey] ?? '';
        
        return hash_equals($sessionToken, $requestToken);
    }
    
    /**
     * Generate hidden input field
     */
    public static function field() {
        return '<input type="hidden" name="' . self::$tokenName . '" value="' . self::getToken() . '">';
    }
    
    /**
     * Require CSRF validation (dies if invalid)
     */
    public static function requireValidation() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!self::validateToken()) {
                http_response_code(403);
                die("CSRF token validation failed");
            }
        }
    }
}
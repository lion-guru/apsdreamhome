<?php

namespace App\Services\Security\Legacy;
/**
 * Security Middleware for APS Dream Home
 */

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/validator.php';
require_once __DIR__ . '/headers.php';

class SecurityMiddleware {
    public static function init() {
        require_once dirname(__DIR__) . '/session_helpers.php';
        ensureSessionStarted();
        
        // Set security headers
        SecurityHeaders::setAll();
        
        // Validate CSRF for dangerous methods
        CSRFProtection::validateRequest();
        
        // Generate CSRF token for forms
        CSRFProtection::generateToken();
    }
    
    public static function sanitizeInput() {
        // Sanitize GET parameters
        foreach ($_GET as $key => $value) {
            $_GET[$key] = InputValidator::sanitize($value);
        }
        
        // Sanitize POST data
        foreach ($_POST as $key => $value) {
            if (is_string($value)) {
                $_POST[$key] = InputValidator::sanitize($value);
            }
        }
    }
    
    public static function rateLimit($limit = 100, $window = 3600) {
        $key = 'rate_limit_' . md5($_SERVER['REMOTE_ADDR']);
        $count = $_SESSION[$key] ?? 0;
        $reset = $_SESSION[$key . '_reset'] ?? time() + $window;
        
        if (time() > $reset) {
            $count = 0;
            $reset = time() + $window;
        }
        
        if ($count >= $limit) {
            http_response_code(429);
            die('Rate limit exceeded');
        }
        
        $_SESSION[$key] = $count + 1;
        $_SESSION[$key . '_reset'] = $reset;
    }
}
?>

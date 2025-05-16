<?php
/**
 * Comprehensive Security Utility for APS Dream Homes
 * Provides input sanitization, validation, and security functions
 */
class SecurityUtility {
    /**
     * Sanitize and validate input
     * @param mixed $input
     * @param string $type
     * @return mixed
     */
    public static function sanitizeInput($input, $type = 'string') {
        if ($input === null) return null;

        switch ($type) {
            case 'email':
                $input = filter_var($input, FILTER_SANITIZE_EMAIL);
                return filter_var($input, FILTER_VALIDATE_EMAIL) ? $input : null;
            
            case 'int':
                return filter_var($input, FILTER_VALIDATE_INT);
            
            case 'float':
                return filter_var($input, FILTER_VALIDATE_FLOAT);
            
            case 'url':
                $input = filter_var($input, FILTER_SANITIZE_URL);
                return filter_var($input, FILTER_VALIDATE_URL) ? $input : null;
            
            case 'username':
                // Alphanumeric and underscore, 3-20 characters
                return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $input) ? $input : null;
            
            case 'password':
                // At least 8 characters, one uppercase, one lowercase, one number
                return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $input) ? $input : null;
            
            default:
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
    }

    /**
     * Generate CSRF token
     * @return string
     */
    public static function generateCSRFToken() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token
     * @param string $token
     * @return bool
     */
    public static function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && 
               hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Generate secure random password
     * @param int $length
     * @return string
     */
    public static function generateSecurePassword($length = 12) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+';
        $password = '';
        $max = strlen($chars) - 1;
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $max)];
        }
        
        return $password;
    }

    /**
     * Check if IP is from a trusted source
     * @param string $ip
     * @param array $trustedIPs
     * @return bool
     */
    public static function isTrustedIP($ip, $trustedIPs = []) {
        // Default trusted IPs (localhost and private networks)
        $defaultTrusted = [
            '127.0.0.1', 
            '::1', 
            '192.168.0.0/16', 
            '10.0.0.0/8', 
            '172.16.0.0/12'
        ];

        $trustedIPs = array_merge($defaultTrusted, $trustedIPs);

        foreach ($trustedIPs as $trustedIP) {
            if (self::ipInRange($ip, $trustedIP)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if an IP is within a given range
     * @param string $ip
     * @param string $range
     * @return bool
     */
    private static function ipInRange($ip, $range) {
        if (strpos($range, '/') !== false) {
            list($range, $netmask) = explode('/', $range, 2);
            $rangeDecimal = ip2long($range);
            $ipDecimal = ip2long($ip);
            $wildcardDecimal = pow(2, (32 - $netmask)) - 1;
            $netmaskDecimal = ~ $wildcardDecimal;
            return (($ipDecimal & $netmaskDecimal) == ($rangeDecimal & $netmaskDecimal));
        }
        
        return $ip === $range;
    }
}

// Helper function for global use
function sanitize($input, $type = 'string') {
    return SecurityUtility::sanitizeInput($input, $type);
}

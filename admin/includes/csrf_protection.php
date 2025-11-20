<?php
/**
 * Enhanced CSRF Protection System
 * Provides robust security against Cross-Site Request Forgery attacks
 */

class CSRFProtection {
    private const TOKEN_LENGTH = 32;
    private const TOKEN_EXPIRY = 3600; // 1 hour
    
    /**
     * Ensures a secure session is started with proper configuration
     */
    public static function initializeSession() {
        if (session_status() === PHP_SESSION_NONE) {
            // Development-friendly session settings
            try {
                session_start();
            } catch (Exception $e) {
                error_log('CSRF Session Initialization Error: ' . $e->getMessage());
                // Fallback to default session start
                @session_start();
            }
        }
    }
    
    /**
     * Generates a new CSRF token or returns existing valid token
     */
    public static function generateToken() {
        self::initializeSession();
        
        // Generate new token if none exists or if expired
        if (!isset($_SESSION['csrf']) ||
            !isset($_SESSION['csrf']['token']) ||
            !isset($_SESSION['csrf']['expires']) ||
            time() >= $_SESSION['csrf']['expires']) {
            
            $_SESSION['csrf'] = [
                'token' => bin2hex(random_bytes(self::TOKEN_LENGTH)),
                'expires' => time() + self::TOKEN_EXPIRY
            ];
        }
        
        return $_SESSION['csrf']['token'];
    }

    /**
     * Validates the CSRF token from the request
     */
    public static function validateToken($token = null) {
        if ($token === null) {
            $token = $_POST['csrf_token'] ?? null;
        }

        if (!isset($_SESSION['csrf']['token']) || !isset($_SESSION['csrf']['expires'])) {
            return false;
        }

        if (time() >= $_SESSION['csrf']['expires']) {
            return false;
        }

        if (!hash_equals($_SESSION['csrf']['token'], $token)) {
            return false;
        }

        return true;
    }

    /**
     * Returns HTML input field containing CSRF token
     */
    public static function getTokenField() {
        $token = self::generateToken();
        return sprintf(
            '<input type="hidden" name="csrf_token" value="%s">',
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Verifies the CSRF token and handles invalid tokens
     */
    public static function verifyToken() {
        if (!self::validateToken()) {
            http_response_code(403);
            die('Invalid CSRF token');
        }
    }

    /**
     * Refreshes the CSRF token
     */
    public static function refreshToken() {
        unset($_SESSION['csrf']);
        return self::generateToken();
    }

    /**
     * Verifies CSRF token for POST requests
     */
    public static function verifyRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            
            if (!self::validateToken($token)) {
                // Check if request is from admin area
                if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
                    header('Location: /admin/index.php?error=csrf');
                    exit();
                }
                
                http_response_code(403);
                die('Security token validation failed');
            }
        }
    }
}

// Initialize CSRF protection
CSRFProtection::initializeSession();
?>
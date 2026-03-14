<?php

namespace App\Services\Security;

use App\Core\Security;
use App\Helpers\SecurityHelper;

/**
 * Enhanced CSRF Protection System
 * Provides robust security against Cross-Site Request Forgery attacks
 */
class CSRFProtection {
    private const TOKEN_LENGTH = 32;
    private const TOKEN_EXPIRY = 3600; // 1 hour
    private static $token = null;

    /**
     * Ensures a secure session is started with proper configuration
     */
    public static function initializeSession() {
        if (session_status() === PHP_SESSION_NONE) {
            try {
                if (file_exists(dirname(__DIR__) . '/session_helpers.php')) {
                    require_once dirname(__DIR__) . '/session_helpers.php';
                    if (function_exists('ensureSessionStarted')) {
                        \ensureSessionStarted();
                    }
                } else {
                    @session_start();
                }
            } catch (\Exception $e) {
                error_log('CSRF Session Initialization Error: ' . $e->getMessage());
            }
        }
    }

    /**
     * Generates a new CSRF token or returns existing valid token
     */
    public static function generateToken() {
        self::initializeSession();

        // Generate new token if none exists or if expired
        if (!isset($_SESSION['csrf_token']) ||
            !isset($_SESSION['csrf_expires']) ||
            time() >= $_SESSION['csrf_expires']) {

            if (class_exists('App\Helpers\SecurityHelper')) {
                $_SESSION['csrf_token'] = bin2hex(SecurityHelper::secureRandomBytes(self::TOKEN_LENGTH));
            } else {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(self::TOKEN_LENGTH));
            }
            $_SESSION['csrf_expires'] = time() + self::TOKEN_EXPIRY;
        }

        self::$token = $_SESSION['csrf_token'];
        return self::$token;
    }

    /**
     * Compatibility method to get current token
     */
    public static function getToken() {
        return self::$token ?: self::generateToken();
    }

    /**
     * Validates the CSRF token from the request
     */
    public static function validateToken($token = null) {
        self::initializeSession();

        if ($token === null) {
            $token = Security::sanitize($_POST['csrf_token'] ?? '') ?: ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
        }

        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_expires'])) {
            return false;
        }

        if (time() >= $_SESSION['csrf_expires']) {
            return false;
        }

        if (!$token || !hash_equals($_SESSION['csrf_token'], $token)) {
            return false;
        }

        return true;
    }

    /**
     * Automatically validates POST/PUT/DELETE requests
     */
    public static function validateRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method === 'POST' || $method === 'PUT' || $method === 'DELETE') {
            if (!self::validateToken()) {
                Security::logSecurityEvent('CSRF token validation failed', [
                    'method' => $method,
                    'uri' => $_SERVER['REQUEST_URI']
                ]);
                http_response_code(403);
                die('CSRF token validation failed');
            }
        }
    }

    /**
     * Returns HTML input field containing CSRF token
     */
    public static function hiddenField() {
        $token = self::generateToken();
        $safeToken = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
        return sprintf(
            '<input type="hidden" name="csrf_token" value="%s">',
            $safeToken
        );
    }

    /**
     * Compatibility method for getTokenField
     */
    public static function getTokenField() {
        return self::hiddenField();
    }

    /**
     * Refreshes the CSRF token
     */
    public static function refreshToken() {
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_expires']);
        return self::generateToken();
    }
}

// Global functions for easier access
if (!function_exists('csrf_token')) {
    function csrf_token() {
        return CSRFProtection::generateToken();
    }
}

if (!function_exists('csrf_validate')) {
    function csrf_validate() {
        return CSRFProtection::validateRequest();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field() {
        return CSRFProtection::hiddenField();
    }
}

if (!function_exists('csrf_check')) {
    function csrf_check() {
        return CSRFProtection::validateToken();
    }
}

// Compatibility legacy functions
if (!function_exists('generateCSRFToken')) {
    function generateCSRFToken() {
        return CSRFProtection::generateToken();
    }
}

if (!function_exists('validateCSRFToken')) {
    function validateCSRFToken($token) {
        return CSRFProtection::validateToken($token);
    }
}

if (!function_exists('getCSRFTokenField')) {
    function getCSRFTokenField() {
        return CSRFProtection::hiddenField();
    }
}
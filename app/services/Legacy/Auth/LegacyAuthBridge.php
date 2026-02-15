<?php

namespace App\Services\Legacy\Auth;

use App\Core\Auth\UnifiedAuthService;
use App\Models\Admin;
use Exception;

/**
 * Modern Implementation of Legacy Authentication Bridge
 */
class LegacyAuthBridge {
    private static $authService = null;
    
    public static function init() {
        if (self::$authService === null) {
            self::$authService = UnifiedAuthService::getInstance();
        }
        return self::$authService;
    }
    
    /**
     * Check if admin is logged in (legacy compatibility)
     */
    public static function isAdmin() {
        $auth = self::init();
        return $auth->isAdminLoggedIn();
    }
    
    /**
     * Get current admin data (legacy compatibility)
     */
    public static function getCurrentAdmin() {
        $auth = self::init();
        return $auth->getCurrentAdmin();
    }
    
    /**
     * Redirect to login if not admin (legacy compatibility)
     */
    public static function requireAdminLogin() {
        if (!self::isAdmin()) {
            $baseUrl = defined('BASE_URL') ? BASE_URL : '';
            header('Location: ' . $baseUrl . '/admin/');
            exit;
        }
    }
    
    /**
     * Logout admin (legacy compatibility)
     */
    public static function adminLogout() {
        $auth = self::init();
        $auth->logout();
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        header('Location: ' . $baseUrl . '/admin/');
        exit;
    }
    
    /**
     * Get admin session variables (legacy compatibility)
     */
    public static function getAdminSessionVars() {
        $admin = self::getCurrentAdmin();
        
        if (!$admin) {
            return [
                'admin_logged_in' => false,
                'admin_id' => null,
                'admin_role' => null,
                'admin_username' => null
            ];
        }
        
        return [
            'admin_logged_in' => true,
            'admin_id' => $admin['id'] ?? null,
            'admin_role' => $admin['role'] ?? null,
            'admin_username' => $admin['username'] ?? null
        ];
    }
    
    /**
     * Sync legacy $_SESSION variables for backward compatibility
     */
    public static function syncLegacySession() {
        $sessionVars = self::getAdminSessionVars();
        
        foreach ($sessionVars as $key => $value) {
            $_SESSION[$key] = $value;
        }
    }
    
    /**
     * Login admin using legacy credentials
     */
    public static function loginAdmin($username, $password) {
        try {
            $adminModel = new Admin();
            $admin = $adminModel->query()
                ->where('auser', '=', $username)
                ->orWhere('aemail', '=', $username)
                ->first();

            if (!$admin) {
                return false;
            }

            // Check admin status
            if (($admin->status ?? '') !== 'active') {
                return false;
            }

            // Verify password
            $password_verified = false;
            $hash = $admin->apass ?? '';
            
            if (password_verify($password, $hash)) {
                $password_verified = true;
            } else if (preg_match('/^[a-f0-9]{40}$/i', $hash) && sha1($password) === $hash) {
                $password_verified = true;
            }

            if (!$password_verified) {
                return false;
            }

            // Success - Set session using unified helper
            $userData = (array)$admin;
            if (function_exists('App\Services\Legacy\setAuthSession')) {
                \App\Services\Legacy\setAuthSession($userData, 'admin', $admin->role ?? 'admin');
            } else {
                // Fallback session handling
                $_SESSION['auth'] = [
                    'id' => $admin->id,
                    'type' => 'admin',
                    'role' => $admin->role ?? 'admin',
                    'data' => $userData
                ];
            }
            
            // Sync legacy session variables
            self::syncLegacySession();
            
            return true;
        } catch (Exception $e) {
            error_log("LegacyAuthBridge::loginAdmin Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate CSRF token (legacy compatibility)
     */
    public static function csrf_token() {
        $auth = self::init();
        return $auth->generateCSRFToken();
    }
    
    /**
     * Validate CSRF token (legacy compatibility)
     */
    public static function validate_csrf($token) {
        $auth = self::init();
        return $auth->validateCSRFToken($token);
    }
}

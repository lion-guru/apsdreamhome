<?php
/**
 * Minimal legacy bootstrap for AJAX view scripts
 * These scripts are loaded through the Router -> Controller pipeline,
 * so the MVC bootstrap is already loaded. This file bridges the gap
 * between legacy global functions and the MVC service classes.
 */

// Autoloader is already registered by config/bootstrap.php
// Define global wrappers for functions expected by legacy AJAX scripts

// Helper functions needed by legacy scripts
if (!function_exists('isAdmin')) {
    function isAdmin() {
        return \App\Helpers\AuthHelper::isAdmin();
    }
}

if (!function_exists('h')) {
    function h($str) {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('verifyCSRFToken')) {
    function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && $_SESSION['csrf_token'] === $token;
    }
}

// Ensure DB connection is available as $db
if (!isset($db)) {
    try {
        $db = \App\Core\Database::getInstance();
    } catch (\Exception $e) {
        $db = null;
    }
}

// Define MlSupport fallback if class doesn't exist
if (!class_exists('MlSupport')) {
    class MlSupport {
        public function translate($text) { return $text; }
        public function __call($name, $args) { return $args[0] ?? null; }
    }
}

// Ensure $mlSupport is available
if (!isset($mlSupport)) {
    $mlSupport = new MlSupport();
}

// Session helper
if (!function_exists('ensureSessionStarted')) {
    function ensureSessionStarted() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}

// Role check helper
if (!function_exists('hasRole')) {
    function hasRole($role) {
        return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === $role;
    }
}

// Security class alias if not autoloaded
if (!class_exists('Security')) {
    class Security {
        public static function sanitize($input) {
            if (is_array($input)) {
                return array_map([self::class, 'sanitize'], $input);
            }
            return htmlspecialchars(trim($input ?? ''), ENT_QUOTES, 'UTF-8');
        }
    }
}

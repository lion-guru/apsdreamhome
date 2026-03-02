<?php

namespace App\Helpers;

use App\Services\Legacy\SessionHelpers;

class AuthHelper
{
    /**
     * Check if user is logged in
     *
     * @param string|null $role Required role (optional)
     * @return bool
     */
    public static function isLoggedIn($role = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Basic auth check
        $isAuthenticated = isset($_SESSION['auth']['authenticated']) && $_SESSION['auth']['authenticated'] === true;

        // Legacy auth check
        if (!$isAuthenticated) {
            if (isset($_SESSION['user_id']) || isset($_SESSION['admin_logged_in'])) {
                $isAuthenticated = true;
            }
        }

        if (!$isAuthenticated) {
            return false;
        }

        // If no specific role required, return true
        if ($role === null) {
            return true;
        }

        // Check specific role
        $currentRole = $_SESSION['auth']['role'] ?? $_SESSION['user_role'] ?? $_SESSION['role'] ?? null;
        
        // Admin role check
        if ($role === 'admin') {
            if ($currentRole === 'admin') return true;
            if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) return true;
            
            // Check sub-roles if needed (e.g. superadmin is also admin)
            $subRole = $_SESSION['auth']['sub_role'] ?? $_SESSION['admin_role'] ?? null;
            if ($subRole && in_array($subRole, ['superadmin', 'admin', 'manager', 'sales', 'hr', 'finance', 'ceo', 'director', 'coo', 'cfo', 'cto', 'it', 'marketing', 'cm', 'accounting', 'legal', 'operations', 'builder'])) {
                return true; // Any admin sub-role counts as admin access for general checks
            }
        }

        return $currentRole === $role;
    }

    /**
     * Get current user ID
     */
    public static function id()
    {
        return $_SESSION['auth']['user_id'] ?? $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null;
    }

    /**
     * Get current user role
     */
    public static function role()
    {
        return $_SESSION['auth']['role'] ?? $_SESSION['user_role'] ?? $_SESSION['role'] ?? null;
    }

    /**
     * Check if user is admin
     */
    public static function isAdmin()
    {
        return self::isLoggedIn('admin');
    }
}

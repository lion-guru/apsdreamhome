<?php

namespace App\Services\Legacy;
/**
 * Authentication Check Helper
 *
 * Provides functions to check user authentication and permissions
 */

require_once __DIR__ . '/SessionHelpers.php';

class AuthCheck {
    /**
     * Check if user is logged in and has a valid session
     *
     * @return bool True if user is authenticated, false otherwise
     */
    public static function isLoggedIn() {
        // Ensure session is started
        ensureSessionStarted();

        // Check if user is logged in
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Check if user has a specific permission
     *
     * @param string $permission The permission to check (e.g., 'leads.view')
     * @param int $userId The user ID to check permissions for
     * @return bool True if user has permission, false otherwise
     */
    public static function checkUserPermission($permission, $userId) {
        // For now, return true for all checks
        // In a real application, implement proper permission checking logic
        return true;
    }

    /**
     * Get user access restrictions
     *
     * @param int $userId The user ID
     * @param string $resource The resource type (e.g., 'leads')
     * @return array Array of access restrictions
     */
    public static function getUserAccessRestrictions($userId, $resource) {
        // Return empty restrictions by default
        // In a real application, implement proper access restriction logic
        return [];
    }
}

/**
 * Global delegators for procedural calls
 */
function isLoggedIn() {
    return AuthCheck::isLoggedIn();
}

function checkUserPermission($permission, $userId) {
    return AuthCheck::checkUserPermission($permission, $userId);
}

function getUserAccessRestrictions($userId, $resource) {
    return AuthCheck::getUserAccessRestrictions($userId, $resource);
}

<?php

namespace App\Http\Middleware;

use App\Core\Database;
use Exception;

/**
 * RBAC Manager Class
 * Role-Based Access Control Management
 */
class RBACManager
{
    // Predefined roles
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';
    const ROLE_USER = 'user';
    const ROLE_ASSOCOCIATE = 'associate';
    const ROLE_GUEST = 'guest';

    // Predefined permissions
    const PERMISSION_DASHBOARD = 'dashboard';
    const PERMISSION_USER_MANAGEMENT = 'user_management';
    const PERMISSION_PROPERTY_MANAGEMENT = 'property_management';
    const PERMISSION_SYSTEM_SETTINGS = 'system_settings';
    const PERMISSION_REPORTS = 'reports';
    const PERMISSION_BANKING = 'banking';
    const PERMISSION_COMMUNICATION = 'communication';

    /**
     * Get all available roles
     * @return array Roles list
     */
    public static function getRoles()
    {
        return [
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_MANAGER => 'Manager',
            self::ROLE_USER => 'User',
            self::ROLE_ASSOCOCIATE => 'Associate',
            self::ROLE_GUEST => 'Guest'
        ];
    }

    /**
     * Get all available permissions
     * @return array Permissions list
     */
    public static function getPermissions()
    {
        return [
            self::PERMISSION_DASHBOARD => 'Dashboard Access',
            self::PERMISSION_USER_MANAGEMENT => 'User Management',
            self::PERMISSION_PROPERTY_MANAGEMENT => 'Property Management',
            self::PERMISSION_SYSTEM_SETTINGS => 'System Settings',
            self::PERMISSION_REPORTS => 'Reports Access',
            self::PERMISSION_BANKING => 'Banking Operations',
            self::PERMISSION_COMMUNICATION => 'Communication Services'
        ];
    }

    /**
     * Get user role hierarchy level
     * @param string $role User role
     * @return int Role level
     */
    public static function getRoleLevel($role)
    {
        $levels = [
            self::ROLE_SUPER_ADMIN => 5,
            self::ROLE_ADMIN => 4,
            self::ROLE_MANAGER => 3,
            self::ROLE_USER => 2,
            self::ROLE_ASSOCOCIATE => 1,
            self::ROLE_GUEST => 0
        ];

        return $levels[$role] ?? 0;
    }

    /**
     * Get user role from session or database
     * @param string|null $userId User ID
     * @return string|null User role
     */
    public static function getUserRole($userId = null)
    {
        // If user ID provided, get from database
        if ($userId) {
            try {
                $db = Database::getInstance();
                $user = $db->fetch("SELECT role FROM users WHERE id = ?", [$userId]);
                return $user ? $user['role'] : null;
            } catch (Exception $e) {
                error_log("RBAC getUserRole error: " . $e->getMessage());
                return null;
            }
        }

        // Get from session
        return $_SESSION['admin_role'] ?? null;
    }

    /**
     * Set user role in session
     * @param string $role User role
     * @param string|null $userId User ID
     */
    public static function setUserRole($role, $userId = null)
    {
        $_SESSION['admin_role'] = $role;

        if ($userId) {
            try {
                $db = Database::getInstance();
                $db->query("UPDATE users SET role = ? WHERE id = ?", [$role, $userId]);
            } catch (Exception $e) {
                error_log("RBAC setUserRole error: " . $e->getMessage());
            }
        }
    }

    /**
     * Check if user has specific permission
     * @param string $permission Permission to check
     * @param string|null $userId User ID
     * @return bool Has permission
     */
    public static function hasPermission($permission, $userId = null)
    {
        $userRole = self::getUserRole($userId);
        if (!$userRole) {
            return false;
        }

        $rolePermissions = self::getRolePermissions($userRole);
        return in_array($permission, $rolePermissions);
    }

    /**
     * Get all available roles
     * @return array Available roles
     */
    public static function getAllRoles()
    {
        return [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN,
            self::ROLE_MANAGER,
            self::ROLE_USER,
            self::ROLE_ASSOCOCIATE,
            self::ROLE_GUEST
        ];
    }

    /**
     * Validate role string
     * @param string $role Role to validate
     * @return bool Valid role
     */
    public static function isValidRole($role)
    {
        return in_array($role, self::getAllRoles());
    }
}

<?php

/**
 * Associate Permissions System
 * Functions for checking and managing associate permissions
 */

// Initialize database connection
$db = \App\Core\App::database();

/**
 * Check if associate has permission for a specific module and action
 */
function hasPermission($associate_id, $module, $permission_type = 'read')
{
    $db = \App\Core\App::database();

    try {
        $query = "SELECT is_allowed FROM associate_permissions
                  WHERE associate_id = :associate_id AND module_name = :module AND permission_type = :permission_type";

        $result = $db->fetch($query, [
            'associate_id' => $associate_id,
            'module' => $module,
            'permission_type' => $permission_type
        ], false);

        if ($result) {
            return (bool) $result['is_allowed'];
        }

        // If no specific permission found, check level-based permissions
        return checkLevelBasedPermission($associate_id, $module, $permission_type);
    } catch (Exception $e) {
        error_log("Error checking permission: " . $e->getMessage());
        return checkLevelBasedPermission($associate_id, $module, $permission_type);
    }
}

/**
 * Check permissions based on associate level
 */
function checkLevelBasedPermission($associate_id, $module, $permission_type)
{
    $db = \App\Core\App::database();

    try {
        $query = "SELECT current_level FROM mlm_agents WHERE id = :associate_id";
        $row = $db->fetch($query, ['associate_id' => $associate_id], false);

        if ($row) {
            $level = $row['current_level'];

            // Define level-based permissions
            $level_permissions = [
                'Associate' => [
                    'dashboard' => ['read', 'write'],
                    'customers' => ['read'],
                    'crm' => ['read', 'write'],
                    'profile' => ['read', 'write']
                ],
                'Sr. Associate' => [
                    'dashboard' => ['read', 'write'],
                    'customers' => ['read', 'write'],
                    'crm' => ['read', 'write'],
                    'profile' => ['read', 'write'],
                    'reports' => ['read']
                ],
                'BDM' => [
                    'dashboard' => ['read', 'write'],
                    'customers' => ['read', 'write'],
                    'crm' => ['read', 'write'],
                    'team_management' => ['read', 'write'],
                    'profile' => ['read', 'write'],
                    'reports' => ['read', 'write']
                ],
                'Sr. BDM' => [
                    'dashboard' => ['read', 'write'],
                    'customers' => ['read', 'write'],
                    'crm' => ['read', 'write', 'admin'],
                    'team_management' => ['read', 'write'],
                    'commission_management' => ['read', 'write'],
                    'profile' => ['read', 'write'],
                    'reports' => ['read', 'write']
                ],
                'Vice President' => [
                    'dashboard' => ['read', 'write', 'admin'],
                    'customers' => ['read', 'write', 'admin'],
                    'crm' => ['read', 'write', 'admin'],
                    'team_management' => ['read', 'write', 'admin'],
                    'commission_management' => ['read', 'write', 'admin'],
                    'profile' => ['read', 'write'],
                    'reports' => ['read', 'write', 'admin']
                ],
                'President' => [
                    'dashboard' => ['read', 'write', 'admin'],
                    'customers' => ['read', 'write', 'admin'],
                    'crm' => ['read', 'write', 'admin'],
                    'team_management' => ['read', 'write', 'admin'],
                    'commission_management' => ['read', 'write', 'admin'],
                    'profile' => ['read', 'write'],
                    'reports' => ['read', 'write', 'admin']
                ],
                'Site Manager' => [
                    'dashboard' => ['read', 'write', 'admin'],
                    'customers' => ['read', 'write', 'admin'],
                    'crm' => ['read', 'write', 'admin'],
                    'team_management' => ['read', 'write', 'admin'],
                    'commission_management' => ['read', 'write', 'admin'],
                    'profile' => ['read', 'write'],
                    'reports' => ['read', 'write', 'admin']
                ]
            ];

            // Check if level has permission for this module and type
            if (isset($level_permissions[$level][$module])) {
                return in_array($permission_type, $level_permissions[$level][$module]);
            }
        }

        return false;
    } catch (Exception $e) {
        error_log("Error checking level-based permission: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all permissions for an associate
 */
function getAssociatePermissions($associate_id)
{
    $db = \App\Core\App::database();

    try {
        $query = "SELECT module_name, permission_type, is_allowed
                  FROM associate_permissions WHERE associate_id = :associate_id";

        $results = $db->fetch($query, ['associate_id' => $associate_id]);

        $permissions = [];
        foreach ($results as $row) {
            $permissions[$row['module_name']][$row['permission_type']] = (bool) $row['is_allowed'];
        }

        return $permissions;
    } catch (Exception $e) {
        error_log("Error getting associate permissions: " . $e->getMessage());
        return [];
    }
}

/**
 * Update associate permission
 */
function updateAssociatePermission($associate_id, $module, $permission_type, $is_allowed)
{
    $db = \App\Core\App::database();

    try {
        $query = "INSERT INTO associate_permissions (associate_id, module_name, permission_type, is_allowed)
                  VALUES (:associate_id, :module, :permission_type, :is_allowed)
                  ON DUPLICATE KEY UPDATE is_allowed = :is_allowed_update, updated_at = CURRENT_TIMESTAMP";

        $db->execute($query, [
            'associate_id' => $associate_id,
            'module' => $module,
            'permission_type' => $permission_type,
            'is_allowed' => $is_allowed,
            'is_allowed_update' => $is_allowed
        ]);

        return true;
    } catch (Exception $e) {
        error_log("Error updating associate permission: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if associate can access a specific page/module
 */
function canAccessModule($associate_id, $module)
{
    return hasPermission($associate_id, $module, 'read');
}

/**
 * Check if associate can perform an action on a module
 */
function canPerformAction($associate_id, $module, $action = 'read')
{
    return hasPermission($associate_id, $module, $action);
}

/**
 * Get accessible modules for an associate
 */
function getAccessibleModules($associate_id)
{
    $modules = [
        'dashboard' => 'Dashboard',
        'customers' => 'Customers',
        'crm' => 'CRM System',
        'team_management' => 'Team Management',
        'commission_management' => 'Commission Management',
        'reports' => 'Reports',
        'profile' => 'Profile'
    ];

    $accessible = [];

    foreach ($modules as $module_key => $module_name) {
        if (canAccessModule($associate_id, $module_key)) {
            $accessible[$module_key] = $module_name;
        }
    }

    return $accessible;
}

/**
 * Initialize permissions for new associate
 */
function initializeAssociatePermissions($associate_id)
{
    $default_modules = [
        ['module_name' => 'dashboard', 'permission_type' => 'read', 'is_allowed' => true],
        ['module_name' => 'customers', 'permission_type' => 'read', 'is_allowed' => true],
        ['module_name' => 'crm', 'permission_type' => 'read', 'is_allowed' => true],
        ['module_name' => 'profile', 'permission_type' => 'read', 'is_allowed' => true],
        ['module_name' => 'profile', 'permission_type' => 'write', 'is_allowed' => true]
    ];

    foreach ($default_modules as $permission) {
        updateAssociatePermission(
            $associate_id,
            $permission['module_name'],
            $permission['permission_type'],
            $permission['is_allowed']
        );
    }
}

/**
 * Check if associate is admin (highest level)
 */
function isAssociateAdmin($associate_id)
{
    $db = \App\Core\App::database();

    try {
        $query = "SELECT current_level FROM mlm_agents WHERE id = :associate_id";
        $row = $db->fetch($query, ['associate_id' => $associate_id], false);

        if ($row) {
            $level = $row['current_level'];
            return in_array($level, ['Vice President', 'President', 'Site Manager']);
        }

        return false;
    } catch (Exception $e) {
        error_log("Error checking admin status: " . $e->getMessage());
        return false;
    }
}

/**
 * Get associate level hierarchy
 */
function getAssociateLevelHierarchy()
{
    return [
        'Associate' => 1,
        'Sr. Associate' => 2,
        'BDM' => 3,
        'Sr. BDM' => 4,
        'Vice President' => 5,
        'President' => 6,
        'Site Manager' => 7
    ];
}

/**
 * Check if associate can manage other associates
 */
function canManageAssociates($associate_id)
{
    return canPerformAction($associate_id, 'team_management', 'write');
}

/**
 * Check if associate can manage commissions
 */
function canManageCommissions($associate_id)
{
    return canPerformAction($associate_id, 'commission_management', 'write');
}

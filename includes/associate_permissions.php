<?php
/**
 * Associate Permissions System
 * Functions for checking and managing associate permissions
 */

// Initialize database connection if not already done
if (!isset($GLOBALS['conn'])) {
    require_once __DIR__ . '/../includes/config.php';
    $config = AppConfig::getInstance();
    $GLOBALS['conn'] = $config->getDatabaseConnection();

    // Check if connection is successful
    if ($GLOBALS['conn']->connect_error) {
        error_log("Database connection failed in associate_permissions.php: " . $GLOBALS['conn']->connect_error);
    }
}

/**
 * Check if associate has permission for a specific module and action
 */
function hasPermission($associate_id, $module, $permission_type = 'read') {
    global $conn;

    // Check if connection is available
    if (!$conn || $conn->connect_error) {
        error_log("Database connection not available in hasPermission");
        return checkLevelBasedPermission($associate_id, $module, $permission_type);
    }

    try {
        $query = "SELECT is_allowed FROM associate_permissions
                  WHERE associate_id = ? AND module_name = ? AND permission_type = ?";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return checkLevelBasedPermission($associate_id, $module, $permission_type);
        }

        $stmt->bind_param("iss", $associate_id, $module, $permission_type);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return (bool) $result->fetch_assoc()['is_allowed'];
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
function checkLevelBasedPermission($associate_id, $module, $permission_type) {
    global $conn;

    // Check if connection is available
    if (!$conn || $conn->connect_error) {
        error_log("Database connection not available in checkLevelBasedPermission");
        return false;
    }

    try {
        $query = "SELECT current_level FROM mlm_agents WHERE id = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $associate_id);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $level = $result->fetch_assoc()['current_level'];

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
function getAssociatePermissions($associate_id) {
    global $conn;

    // Check if connection is available
    if (!$conn || $conn->connect_error) {
        error_log("Database connection not available in getAssociatePermissions");
        return [];
    }

    try {
        $query = "SELECT module_name, permission_type, is_allowed
                  FROM associate_permissions WHERE associate_id = ?";

        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $associate_id);
        $stmt->execute();

        $permissions = [];
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
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
function updateAssociatePermission($associate_id, $module, $permission_type, $is_allowed) {
    global $conn;

    // Check if connection is available
    if (!$conn || $conn->connect_error) {
        error_log("Database connection not available in updateAssociatePermission");
        return false;
    }

    try {
        $query = "INSERT INTO associate_permissions (associate_id, module_name, permission_type, is_allowed)
                  VALUES (?, ?, ?, ?)
                  ON DUPLICATE KEY UPDATE is_allowed = ?, updated_at = CURRENT_TIMESTAMP";

        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("issii", $associate_id, $module, $permission_type, $is_allowed, $is_allowed);
        $stmt->execute();

        return true;

    } catch (Exception $e) {
        error_log("Error updating associate permission: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if associate can access a specific page/module
 */
function canAccessModule($associate_id, $module) {
    return hasPermission($associate_id, $module, 'read');
}

/**
 * Check if associate can perform an action on a module
 */
function canPerformAction($associate_id, $module, $action = 'read') {
    return hasPermission($associate_id, $module, $action);
}

/**
 * Get accessible modules for an associate
 */
function getAccessibleModules($associate_id) {
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
function initializeAssociatePermissions($associate_id) {
    $default_modules = [
        ['module_name' => 'dashboard', 'permission_type' => 'read', 'is_allowed' => true],
        ['module_name' => 'customers', 'permission_type' => 'read', 'is_allowed' => true],
        ['module_name' => 'crm', 'permission_type' => 'read', 'is_allowed' => true],
        ['module_name' => 'profile', 'permission_type' => 'read', 'is_allowed' => true],
        ['module_name' => 'profile', 'permission_type' => 'write', 'is_allowed' => true]
    ];

    foreach ($default_modules as $permission) {
        updateAssociatePermission($associate_id, $permission['module_name'],
                                $permission['permission_type'], $permission['is_allowed']);
    }
}

/**
 * Check if associate is admin (highest level)
 */
function isAssociateAdmin($associate_id) {
    global $conn;

    // Check if connection is available
    if (!$conn || $conn->connect_error) {
        error_log("Database connection not available in isAssociateAdmin");
        return false;
    }

    try {
        $query = "SELECT current_level FROM mlm_agents WHERE id = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $associate_id);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $level = $result->fetch_assoc()['current_level'];
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
function getAssociateLevelHierarchy() {
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
function canManageAssociates($associate_id) {
    return canPerformAction($associate_id, 'team_management', 'write');
}

/**
 * Check if associate can manage commissions
 */
function canManageCommissions($associate_id) {
    return canPerformAction($associate_id, 'commission_management', 'write');
}
?>

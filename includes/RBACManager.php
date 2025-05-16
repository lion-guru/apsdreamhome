<?php
/**
 * Advanced Role-Based Access Control (RBAC) Manager
 * Provides granular access control and permission management
 */
class RBACManager {
    // Predefined roles with hierarchical permissions
    private const ROLES = [
        'super_admin' => [
            'description' => 'Full system access',
            'permissions' => [
                'dashboard_view',
                'user_management',
                'system_settings',
                'database_access',
                'full_admin_panel',
                'security_logs',
                'user_create',
                'user_edit',
                'user_delete'
            ]
        ],
        'admin' => [
            'description' => 'Advanced administrative access',
            'permissions' => [
                'dashboard_view',
                'user_management',
                'system_settings',
                'user_create',
                'user_edit'
            ]
        ],
        'manager' => [
            'description' => 'Limited administrative access',
            'permissions' => [
                'dashboard_view',
                'limited_user_management',
                'report_view'
            ]
        ],
        'editor' => [
            'description' => 'Content management access',
            'permissions' => [
                'dashboard_view',
                'content_edit',
                'content_publish'
            ]
        ]
    ];

    /**
     * Check if user has specific permission
     * @param string $userId
     * @param string $permission
     * @return bool
     */
    public static function hasPermission($userId, $permission) {
        try {
            $pdo = DatabaseConnection::getInstance();
            
            // Fetch user role and permissions
            $stmt = $pdo->prepare("
                SELECT r.permissions 
                FROM user_roles ur
                JOIN roles r ON ur.role_id = r.id
                WHERE ur.user_id = ?
            ");
            $stmt->execute([$userId]);
            $roleData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$roleData) {
                // Log unauthorized access attempt
                AdminLogger::log('PERMISSION_CHECK_FAILED', [
                    'user_id' => $userId,
                    'requested_permission' => $permission,
                    'reason' => 'No role assigned'
                ]);
                return false;
            }

            // Decode and check permissions
            $userPermissions = json_decode($roleData['permissions'], true);
            $hasPermission = in_array($permission, $userPermissions);

            // Log permission check
            AdminLogger::log('PERMISSION_CHECK', [
                'user_id' => $userId,
                'requested_permission' => $permission,
                'result' => $hasPermission ? 'GRANTED' : 'DENIED'
            ]);

            return $hasPermission;
        } catch (PDOException $e) {
            AdminLogger::logError('PERMISSION_CHECK_ERROR', [
                'message' => $e->getMessage(),
                'user_id' => $userId
            ]);
            return false;
        }
    }

    /**
     * Assign role to user
     * @param string $userId
     * @param string $roleName
     * @return bool
     */
    public static function assignRole($userId, $roleName) {
        if (!isset(self::ROLES[$roleName])) {
            throw new InvalidArgumentException("Invalid role: $roleName");
        }

        try {
            $pdo = DatabaseConnection::getInstance();
            
            // Begin transaction
            $pdo->beginTransaction();

            // Get role ID
            $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
            $stmt->execute([$roleName]);
            $role = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$role) {
                // Create role if not exists
                $stmt = $pdo->prepare("
                    INSERT INTO roles (name, permissions, description) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([
                    $roleName, 
                    json_encode(self::ROLES[$roleName]['permissions']),
                    self::ROLES[$roleName]['description']
                ]);
                $roleId = $pdo->lastInsertId();
            } else {
                $roleId = $role['id'];
            }

            // Assign role to user
            $stmt = $pdo->prepare("
                INSERT INTO user_roles (user_id, role_id) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE role_id = ?
            ");
            $stmt->execute([$userId, $roleId, $roleId]);

            // Commit transaction
            $pdo->commit();

            // Log role assignment
            AdminLogger::log('ROLE_ASSIGNED', [
                'user_id' => $userId,
                'role' => $roleName,
                'assigned_by' => $_SESSION['admin_username'] ?? 'SYSTEM'
            ]);

            return true;
        } catch (PDOException $e) {
            // Rollback transaction
            $pdo->rollBack();

            AdminLogger::logError('ROLE_ASSIGNMENT_ERROR', [
                'message' => $e->getMessage(),
                'user_id' => $userId,
                'role' => $roleName
            ]);

            return false;
        }
    }

    /**
     * Get user's current role
     * @param string $userId
     * @return string|null
     */
    public static function getUserRole($userId) {
        try {
            $pdo = DatabaseConnection::getInstance();
            
            $stmt = $pdo->prepare("
                SELECT r.name 
                FROM user_roles ur
                JOIN roles r ON ur.role_id = r.id
                WHERE ur.user_id = ?
            ");
            $stmt->execute([$userId]);
            $role = $stmt->fetch(PDO::FETCH_ASSOC);

            return $role ? $role['name'] : null;
        } catch (PDOException $e) {
            AdminLogger::logError('GET_USER_ROLE_ERROR', [
                'message' => $e->getMessage(),
                'user_id' => $userId
            ]);
            return null;
        }
    }

    /**
     * Enforce role-based access control
     * @param string $userId
     * @param string $requiredPermission
     * @throws AccessDeniedException
     */
    public static function enforceAccess($userId, $requiredPermission) {
        if (!self::hasPermission($userId, $requiredPermission)) {
            // Log access violation
            AdminLogger::securityAlert('ACCESS_DENIED', [
                'user_id' => $userId,
                'requested_permission' => $requiredPermission,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
            ]);

            // Throw custom exception or redirect
            throw new AccessDeniedException("You do not have permission to access this resource.");
        }
    }
}

// Custom exception for access control
class AccessDeniedException extends Exception {}

// Global helper functions
function check_permission($permission) {
    $userId = $_SESSION['admin_user_id'] ?? null;
    return $userId ? RBACManager::hasPermission($userId, $permission) : false;
}

function enforce_permission($permission) {
    $userId = $_SESSION['admin_user_id'] ?? null;
    RBACManager::enforceAccess($userId, $permission);
}

<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Http\Middleware\RBACManager;

/**
 * Admin Menu Service - RBAC-based Sidebar Menu System
 * 
 * This service provides a unified sidebar menu system that dynamically
 * shows menu items based on user role, with support for custom permissions.
 */
class AdminMenuService
{
    private $db;
    private $currentRole;
    private $currentUserId;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->currentRole = RBACManager::getUserRole();
        $this->currentUserId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null;
    }

    /**
     * Get menu items for the current user based on role and custom permissions
     */
    public function getMenuItems(?string $role = null, ?int $userId = null): array
    {
        $role = $role ?? $this->currentRole;
        $userId = $userId ?? $this->currentUserId;

        // Super admin and admin see everything
        if ($role === RBACManager::ROLE_SUPER_ADMIN || $role === RBACManager::ROLE_ADMIN) {
            return $this->getAllMenuItems();
        }

        // Get menu items based on role permissions
        if ($role) {
            $menuItems = $this->getMenuItemsByRole($role);
        } else {
            // If no role, return empty menu
            $menuItems = [];
        }

        // Apply custom user permissions if any
        if ($userId) {
            $menuItems = $this->applyCustomUserPermissions($menuItems, $userId);
        }

        return $this->buildMenuTree($menuItems);
    }

    /**
     * Get all menu items (for super admin and admin)
     */
    private function getAllMenuItems(): array
    {
        $query = "SELECT * FROM admin_menu_items WHERE is_active = 1 ORDER BY order_index ASC";
        return $this->db->fetchAll($query);
    }

    /**
     * Get menu items based on role permissions
     */
    private function getMenuItemsByRole(string $role): array
    {
        $query = "
            SELECT mi.*, rp.can_view, rp.can_create, rp.can_edit, rp.can_delete
            FROM admin_menu_items mi
            LEFT JOIN admin_role_menu_permissions rp ON mi.id = rp.menu_item_id AND rp.role = ?
            WHERE mi.is_active = 1 
            AND (rp.can_view = 1 OR rp.role IS NULL)
            ORDER BY mi.order_index ASC
        ";
        return $this->db->fetchAll($query, [$role]);
    }

    /**
     * Apply custom user permissions (override role permissions)
     */
    private function applyCustomUserPermissions(array $menuItems, int $userId): array
    {
        $customPermissions = $this->getCustomUserPermissions($userId);

        foreach ($menuItems as &$item) {
            $itemId = $item['id'];
            if (isset($customPermissions[$itemId])) {
                // Override with custom permissions
                $item['can_view'] = $customPermissions[$itemId]['can_view'] ?? $item['can_view'];
                $item['can_create'] = $customPermissions[$itemId]['can_create'] ?? $item['can_create'];
                $item['can_edit'] = $customPermissions[$itemId]['can_edit'] ?? $item['can_edit'];
                $item['can_delete'] = $customPermissions[$itemId]['can_delete'] ?? $item['can_delete'];
            }
        }

        // Filter out items that user cannot view
        return array_filter($menuItems, function ($item) {
            return $item['can_view'] == 1;
        });
    }

    /**
     * Get custom permissions for a specific user
     */
    private function getCustomUserPermissions(int $userId): array
    {
        $query = "
            SELECT menu_item_id, can_view, can_create, can_edit, can_delete
            FROM admin_user_menu_permissions
            WHERE user_id = ?
        ";
        $permissions = $this->db->fetchAll($query, [$userId]);

        $result = [];
        foreach ($permissions as $perm) {
            $result[$perm['menu_item_id']] = $perm;
        }

        return $result;
    }

    /**
     * Build hierarchical menu tree from flat menu items
     */
    private function buildMenuTree(array $menuItems): array
    {
        $tree = [];
        $children = [];

        // First pass: separate parents and children
        foreach ($menuItems as $item) {
            if ($item['parent_id'] === null) {
                $tree[$item['id']] = $item;
                $tree[$item['id']]['children'] = [];
            } else {
                $children[$item['parent_id']][] = $item;
            }
        }

        // Second pass: attach children to parents
        foreach ($children as $parentId => $childItems) {
            if (isset($tree[$parentId])) {
                $tree[$parentId]['children'] = $childItems;
            }
        }

        return array_values($tree);
    }

    /**
     * Check if user has permission for a specific menu item
     */
    public function hasMenuAccess(int $menuItemId, ?string $role = null, ?int $userId = null): bool
    {
        $role = $role ?? $this->currentRole;
        $userId = $userId ?? $this->currentUserId;

        // Super admin and admin have access to everything
        if ($role === RBACManager::ROLE_SUPER_ADMIN || $role === RBACManager::ROLE_ADMIN) {
            return true;
        }

        // Check role permission
        $query = "
            SELECT can_view 
            FROM admin_role_menu_permissions 
            WHERE role = ? AND menu_item_id = ?
        ";
        $rolePermission = $this->db->fetch($query, [$role, $menuItemId]);

        // If no role permission, deny access
        if (!$rolePermission || $rolePermission['can_view'] != 1) {
            return false;
        }

        // Check custom user permission (can override role permission)
        if ($userId) {
            $customQuery = "
                SELECT can_view 
                FROM admin_user_menu_permissions 
                WHERE user_id = ? AND menu_item_id = ?
            ";
            $customPermission = $this->db->fetch($customQuery, [$userId, $menuItemId]);

            if ($customPermission) {
                return $customPermission['can_view'] == 1;
            }
        }

        return $rolePermission['can_view'] == 1;
    }

    /**
     * Grant custom menu permission to a user
     */
    public function grantUserPermission(int $userId, int $menuItemId, array $permissions): bool
    {
        $query = "
            INSERT INTO admin_user_menu_permissions 
            (user_id, menu_item_id, can_view, can_create, can_edit, can_delete, granted_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            can_view = VALUES(can_view),
            can_create = VALUES(can_create),
            can_edit = VALUES(can_edit),
            can_delete = VALUES(can_delete),
            granted_by = VALUES(granted_by)
        ";

        try {
            $this->db->query($query, [
                $userId,
                $menuItemId,
                $permissions['can_view'] ?? 1,
                $permissions['can_create'] ?? 0,
                $permissions['can_edit'] ?? 0,
                $permissions['can_delete'] ?? 0,
                $this->currentUserId
            ]);
            return true;
        } catch (\Exception $e) {
            error_log("AdminMenuService::grantUserPermission error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Revoke custom menu permission from a user
     */
    public function revokeUserPermission(int $userId, int $menuItemId): bool
    {
        $query = "DELETE FROM admin_user_menu_permissions WHERE user_id = ? AND menu_item_id = ?";

        try {
            $this->db->query($query, [$userId, $menuItemId]);
            return true;
        } catch (\Exception $e) {
            error_log("AdminMenuService::revokeUserPermission error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Grant role menu permission
     */
    public function grantRolePermission(string $role, int $menuItemId, array $permissions): bool
    {
        $query = "
            INSERT INTO admin_role_menu_permissions 
            (role, menu_item_id, can_view, can_create, can_edit, can_delete)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            can_view = VALUES(can_view),
            can_create = VALUES(can_create),
            can_edit = VALUES(can_edit),
            can_delete = VALUES(can_delete)
        ";

        try {
            $this->db->query($query, [
                $role,
                $menuItemId,
                $permissions['can_view'] ?? 1,
                $permissions['can_create'] ?? 0,
                $permissions['can_edit'] ?? 0,
                $permissions['can_delete'] ?? 0
            ]);
            return true;
        } catch (\Exception $e) {
            error_log("AdminMenuService::grantRolePermission error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all menu items with their role permissions
     */
    public function getAllMenuItemsWithPermissions(): array
    {
        $query = "
            SELECT mi.*, 
                   GROUP_CONCAT(
                       CONCAT(rp.role, ':', rp.can_view, ':', rp.can_create, ':', rp.can_edit, ':', rp.can_delete)
                       SEPARATOR '||'
                   ) as role_permissions
            FROM admin_menu_items mi
            LEFT JOIN admin_role_menu_permissions rp ON mi.id = rp.menu_item_id
            WHERE mi.is_active = 1
            GROUP BY mi.id
            ORDER BY mi.order_index ASC
        ";

        $items = $this->db->fetchAll($query);

        // Parse role permissions
        foreach ($items as &$item) {
            $item['role_permissions'] = $this->parseRolePermissions($item['role_permissions']);
        }

        return $this->buildMenuTree($items);
    }

    /**
     * Parse role permissions string
     */
    private function parseRolePermissions(?string $permissionsString): array
    {
        if (empty($permissionsString)) {
            return [];
        }

        $result = [];
        $rolePerms = explode('||', $permissionsString);

        foreach ($rolePerms as $perm) {
            $parts = explode(':', $perm);
            if (count($parts) === 5) {
                $result[$parts[0]] = [
                    'can_view' => (int)$parts[1],
                    'can_create' => (int)$parts[2],
                    'can_edit' => (int)$parts[3],
                    'can_delete' => (int)$parts[4]
                ];
            }
        }

        return $result;
    }
}

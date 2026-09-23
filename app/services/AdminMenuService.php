<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Core\Cache;
use App\Http\Middleware\RBACManager;
use \App\Traits\ServiceTenantTrait;

/**
 * Admin Menu Service - RBAC-based Sidebar Menu System
 * 
 * This service provides a unified sidebar menu system that dynamically
 * shows menu items based on user role, with support for custom permissions.
 */
class AdminMenuService
{
    use \App\Traits\ServiceTenantTrait;

    private $currentRole;
    private $currentUserId;
    private $db;

    /**
     * URLs that should only be visible to the platform owner (tenant_id = 1).
     * SaaS tenant admins never see these.
     */
    private const SUPERADMIN_ONLY_URLS = [
        '/admin/tenants',
        '/admin/tenants/dashboard',
        '/admin/godmode',
        '/admin/billing',
        '/admin/billing/plans',
        '/admin/menu-permissions',
        '/admin/features/registrations',
        '/admin/backup',
        '/admin/cache',
        '/admin/production-checklist',
        '/admin/settings',         // General Settings — platform-level
        '/admin/company/settings',
        '/admin/company-credentials',
        '/admin/localization',
        '/admin/api/integrations',
        '/admin/api/developers',
        '/admin/api-docs',
        '/admin/webhooks',
    ];

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

        // Super admin sees everything (filtered by tenant if not platform owner)
        if ($role === RBACManager::ROLE_SUPER_ADMIN) {
            $items = $this->getAllMenuItems();
            return $this->filterForTenant($items);
        }

        // Admin and other roles: get menu items based on role permissions
        // Admin no longer sees everything - only what they have explicit permission for
        // This ensures CEO/CFO/Finance Director dashboards are only visible to those roles

        // Employee role: resolve designation → sub-role for granular access
        $baseRole = $role;
        if ($role === 'employee') {
            $subRole = $this->resolveEmployeeSubRole($userId);
            if ($subRole) {
                $role = $subRole;
            }
        }

        // Get menu items based on role permissions
        if ($role) {
            $menuItems = $this->getMenuItemsByRole($role);
            // Unmapped designation (e.g. sub-role 'employee_general' with zero
            // permissions): fall back to base-role items instead of an empty sidebar.
            if (empty($menuItems) && $role !== $baseRole) {
                $menuItems = $this->getMenuItemsByRole($baseRole);
            }
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
     * Get the resolved employee sub-role for the given user.
     * Returns null if not an employee.
     */
    public function getEmployeeSubRole(?int $userId = null): ?string
    {
        $userId = $userId ?? $this->currentUserId;
        if ($this->currentRole !== 'employee') return null;
        return $this->resolveEmployeeSubRole($userId);
    }

    /**
     * Get the dashboard view path for the current employee.
     */
    public function getEmployeeDashboardView(?int $userId = null): string
    {
        $userId = $userId ?? $this->currentUserId;
        if ($this->currentRole !== 'employee') {
            return 'admin/dashboard';
        }

        $cacheKey = 'employee_dashboard_view_' . $userId;
        return Cache::remember($cacheKey, function () use ($userId) {
            try {
                $emp = $this->db->fetchOne(
                    "SELECT e.designation, e.department
                     FROM employees e WHERE e.user_id = ? LIMIT 1",
                    [$userId]
                );
                if (!$emp || empty($emp['designation'])) {
                    return 'employee/dashboard';
                }

                $mapping = $this->db->fetchOne(
                    "SELECT dashboard_view FROM employee_designation_roles
                     WHERE designation = ? AND (department = ? OR department IS NULL)
                     LIMIT 1",
                    [$emp['designation'], $emp['department']]
                );

                return ($mapping && !empty($mapping['dashboard_view']))
                    ? $mapping['dashboard_view']
                    : 'employee/dashboard';
            } catch (\Exception $e) {
                return 'employee/dashboard';
            }
        }, 3600);
    }

    /**
     * Get all menu items (for super admin and admin)
     */
    private function getAllMenuItems(): array
    {
        $query = "SELECT * FROM admin_menu_items WHERE is_active = 1 ORDER BY order_index ASC";
        return Cache::remember('admin_sidebar_all', function () use ($query) {
            return $this->db->fetchAll($query);
        }, 3600);
    }

    /**
     * Filter out superadmin-only menu items for non-platform-owner tenants.
     * SaaS tenant admins should not see: tenants mgmt, godmode, billing plans,
     * backup, cache, menu permissions, platform settings, etc.
     */
    private function filterForTenant(array $items): array
    {
        $tenantId = 1;
        try {
            if (class_exists('\App\Core\Middleware\TenantContext')) {
                $tenantId = \App\Core\Middleware\TenantContext::getId();
            } elseif (!empty($_SESSION['tenant_id'])) {
                $tenantId = (int)$_SESSION['tenant_id'];
            }
        } catch (\Throwable $e) {
        // Default to 1 (platform owner)
        error_log($e->getMessage());
        }

        // Platform owner sees everything
        if ($tenantId <= 1) {
            return $items;
        }

        // Filter out superadmin-only items
        return array_filter($items, function ($item) {
            $url = $item['url'] ?? '';
            foreach (self::SUPERADMIN_ONLY_URLS as $blocked) {
                if ($url === $blocked || strpos($url, $blocked . '/') === 0) {
                    return false;
                }
            }
            return true;
        });
    }

    /**
     * Get menu items based on role permissions
     */
    private function getMenuItemsByRole(string $role): array
    {
        // Use INNER JOIN: only show items where the role has an explicit can_view=1 permission.
        // This prevents unpermitted items from leaking through via LEFT JOIN NULL.
        $query = "
            SELECT mi.*, rp.can_view, rp.can_create, rp.can_edit, rp.can_delete
            FROM admin_menu_items mi
            INNER JOIN admin_role_menu_permissions rp ON mi.id = rp.menu_item_id AND rp.role = ?
            WHERE mi.is_active = 1 AND rp.can_view = 1
            ORDER BY mi.order_index ASC
        ";
        $cacheKey = 'admin_sidebar_role_' . md5($role);
        return Cache::remember($cacheKey, function () use ($query, $role) {
            return $this->db->fetchAll($query, [$role]);
        }, 3600);
    }

    /**
     * Resolve employee designation → sub-role for granular menu filtering.
     * Looks up employees table by user_id, then employee_designation_roles table.
     * Falls back to 'employee_general' if no mapping found.
     */
    private function resolveEmployeeSubRole(?int $userId): ?string
    {
        if (!$userId) return 'employee_general';

        $cacheKey = 'employee_sub_role_' . $userId;
        return Cache::remember($cacheKey, function () use ($userId) {
            try {
                // Look up employee designation + department
                $emp = $this->db->fetchOne(
                    "SELECT e.designation, e.department
                     FROM employees e
                     WHERE e.user_id = ?
                     LIMIT 1",
                    [$userId]
                );

                if (!$emp || empty($emp['designation'])) {
                    return 'employee_general';
                }

                // Try exact match: designation + department
                $mapping = $this->db->fetchOne(
                    "SELECT sub_role FROM employee_designation_roles
                     WHERE designation = ? AND department = ?
                     LIMIT 1",
                    [$emp['designation'], $emp['department']]
                );

                if ($mapping) {
                    return $mapping['sub_role'];
                }

                // Try designation-only match (department = NULL = wildcard)
                $mapping = $this->db->fetchOne(
                    "SELECT sub_role FROM employee_designation_roles
                     WHERE designation = ? AND department IS NULL
                     LIMIT 1",
                    [$emp['designation']]
                );

                if ($mapping) {
                    return $mapping['sub_role'];
                }

                // Default fallback
                return 'employee_general';

            } catch (\Exception $e) {
                error_log('AdminMenuService::resolveEmployeeSubRole error: ' . $e->getMessage());
                return 'employee_general';
            }
        }, 3600);
    }

    /**
     * Apply custom user permissions to menu items
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
     * Get custom permissions for a specific user (cached 300s)
     */
    private function getCustomUserPermissions(int $userId): array
    {
        return \App\Core\Cache::remember("admin_menu_perms_{$userId}", function () use ($userId) {
            $query = "
                SELECT menu_item_id, can_view, can_create, can_edit, can_delete
                FROM admin_user_menu_permissions
                WHERE user_id = ?
            ";
            try {
                $permissions = $this->db->fetchAll($query, [$userId]);
            } catch (\Throwable $e) {
                error_log("AdminMenuService::getCustomUserPermissions error: " . $e->getMessage());
                $permissions = [];
            }

            $result = [];
            foreach ($permissions as $perm) {
                $result[$perm['menu_item_id']] = $perm;
            }
            return $result;
        }, 300);
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
            } else {
                // Orphaned children (role can see the child but not its parent):
                // promote to top level instead of silently dropping them.
                foreach ($childItems as $child) {
                    $child['children'] = [];
                    $tree[$child['id']] = $child;
                }
            }
        }

        return array_values($tree);
    }

    /**
     * Check if user has permission for a specific menu item by ID
     */
    public function hasMenuAccess(int $menuItemId, ?string $role = null, ?int $userId = null): bool
    {
        $role = $role ?? $this->currentRole;
        $userId = $userId ?? $this->currentUserId;

        // Super admin has access to everything
        if ($role === RBACManager::ROLE_SUPER_ADMIN) {
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
            try {
                $customPermission = $this->db->fetch($customQuery, [$userId, $menuItemId]);
            } catch (\Throwable $e) {
                // Gracefully handle dropped table ref
                $customPermission = null;
            }

            if ($customPermission) {
                return $customPermission['can_view'] == 1;
            }
        }

        return $rolePermission['can_view'] == 1;
    }

    /**
     * Check if user has permission for a menu item by URL
     */
    public function hasMenuAccessByUrl(string $url, ?string $role = null, ?int $userId = null): bool
    {
        $role = $role ?? $this->currentRole;
        $userId = $userId ?? $this->currentUserId;

        // Super admin has access to everything
        if ($role === RBACManager::ROLE_SUPER_ADMIN) {
            return true;
        }

        // Get menu item by URL
        $menuItem = $this->db->fetchRow("SELECT id FROM admin_menu_items WHERE url = ? AND is_active = 1 LIMIT 1", [$url]);
        if (!$menuItem) {
            return false;
        }

        return $this->hasMenuAccess($menuItem['id'], $role, $userId);
    }

    /**
     * Grant custom menu permission to a user
     */
    public function grantUserPermission(int $userId, int $menuItemId, array $permissions): bool
    {
        $insertData = $this->tenantInsertData();
        $columns = ['user_id', 'menu_item_id', 'can_view', 'can_create', 'can_edit', 'can_delete'];
        $placeholders = ['?', '?', '?', '?', '?', '?'];
        if (!empty($insertData)) {
            $columns = array_merge($columns, array_keys($insertData));
            $placeholders = array_merge($placeholders, array_fill(0, count($insertData), '?'));
        }
        $query = "
            INSERT INTO admin_user_menu_permissions 
            (" . implode(',', $columns) . ")
            VALUES (" . implode(',', $placeholders) . ")
            ON DUPLICATE KEY UPDATE
            can_view = VALUES(can_view),
            can_create = VALUES(can_create),
            can_edit = VALUES(can_edit),
            can_delete = VALUES(can_delete)
        ";

        try {
            $params = [
                $userId,
                $menuItemId,
                $permissions['can_view'] ?? 1,
                $permissions['can_create'] ?? 0,
                $permissions['can_edit'] ?? 0,
                $permissions['can_delete'] ?? 0,
            ];
            if (!empty($insertData)) $params = array_merge($params, array_values($insertData));
            $this->db->query($query, $params);
            $this->clearMenuCache();
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
            $this->clearMenuCache();
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
            $this->clearMenuCache();
            return true;
        } catch (\Exception $e) {
            error_log("AdminMenuService::grantRolePermission error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Static menu manifest (config/admin_menu_manifest.php) — future-proof
     * snapshot of admin_menu_items keyed by URL. Used for sidebar fallback
     * and self-heal re-seeding when DB menu rows go missing.
     */
    private static ?array $manifestCache = null;

    public static function manifest(): array
    {
        if (self::$manifestCache === null) {
            $file = dirname(__DIR__, 2) . '/config/admin_menu_manifest.php';
            self::$manifestCache = is_readable($file) ? (require $file) : [];
            if (!is_array(self::$manifestCache)) self::$manifestCache = [];
        }
        return self::$manifestCache;
    }

    /**
     * Self-heal: re-seed any manifest menu rows / role permissions missing
     * from the DB (crash restores, partial deletes). Idempotent — only
     * INSERTs what is absent. Returns report; $dryRun writes nothing.
     */
    public function ensureMenuIntegrity(bool $dryRun = false): array
    {
        $report = ['missing_items' => [], 'missing_perms' => 0, 'wrote' => false];
        $manifest = self::manifest();
        if (empty($manifest)) return $report;

        $existing = [];
        foreach ($this->db->fetchAll("SELECT id, url FROM admin_menu_items") as $row) {
            $existing[$row['url']] = (int)$row['id'];
        }
        $havePerms = [];
        foreach ($this->db->fetchAll("SELECT role, menu_item_id FROM admin_role_menu_permissions") as $row) {
            $havePerms[$row['role'] . ':' . $row['menu_item_id']] = true;
        }

        $itemStmt = $this->db->prepare(
            "INSERT INTO admin_menu_items (name, icon, url, parent_id, section, order_index, permission_key, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, 1)"
        );
        $permStmt = $this->db->prepare(
            "INSERT IGNORE INTO admin_role_menu_permissions (role, menu_item_id, can_view, can_create, can_edit, can_delete)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        foreach ($manifest as $url => $m) {
            if (!isset($existing[$url])) {
                $report['missing_items'][] = $url;
                if ($dryRun) continue;
                $parentId = (!empty($m['parent_url']) && isset($existing[$m['parent_url']]))
                    ? $existing[$m['parent_url']] : null;
                $itemStmt->execute([
                    $m['name'], $m['icon'], $url, $parentId,
                    $m['section'], (int)$m['order'], $m['perm'],
                ]);
                $id = (int)$this->db->lastInsertId();
                if ($id > 0) $existing[$url] = $id;
                $report['wrote'] = true;
            }
            if (isset($existing[$url]) && !empty($m['roles'])) {
                foreach ($m['roles'] as $role => $p) {
                    if (isset($havePerms[$role . ':' . $existing[$url]])) continue;
                    $report['missing_perms']++;
                    if ($dryRun) continue;
                    $permStmt->execute([
                        $role, $existing[$url],
                        (int)$p['view'], (int)$p['create'], (int)$p['edit'], (int)$p['delete'],
                    ]);
                    $havePerms[$role . ':' . $existing[$url]] = true;
                    $report['wrote'] = true;
                }
            }
        }

        if ($report['wrote']) $this->clearMenuCache();
        return $report;
    }

    /**
     * Fallback menu items from the static manifest (DB unreachable/empty).
     * Shaped like DB rows so the sidebar renders unchanged. Route guards
     * (requireAdmin/checkMenuPermission) remain the authorization enforcer.
     */
    public function fallbackMenuItems(?string $role = null): array
    {
        $role = $role ?? $this->currentRole;
        $items = [];
        $order = 0;
        foreach (self::manifest() as $url => $m) {
            if (!in_array($role, ['super_admin', 'admin'], true) && !isset($m['roles'][$role])) {
                continue;
            }
            $items[] = [
                'id' => 0,
                'name' => $m['name'],
                'icon' => $m['icon'] ?: 'fas fa-circle',
                'url' => $url,
                'parent_id' => null,
                'section' => $m['section'],
                'order_index' => $order++,
                'permission_key' => $m['perm'],
                'is_active' => 1,
            ];
        }
        return $items;
    }

    /**
     * Clear all cached sidebar menu data
     * Called automatically when permissions are modified
     */
    public function clearMenuCache(): void
    {
        // Clear legacy key names (kept for backward compat with the file cache)
        Cache::delete('admin_sidebar_all');
        $roles = ['super_admin', 'admin', 'manager', 'telecaller', 'employee', 'associate', 'agent', 'customer'];
        foreach ($roles as $role) {
            Cache::delete('admin_sidebar_role_' . md5($role));
        }
        // Clear new CacheService entries (Redis + file, both layers, by pattern)
        \App\Services\CacheService::invalidateAdminMenu();
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

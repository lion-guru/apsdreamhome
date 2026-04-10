<?php

namespace App\Http\Controllers\Admin;

use App\Services\AdminMenuService;
use App\Http\Middleware\RBACManager;

class AdminMenuPermissionController
{
    private $menuService;

    public function __construct()
    {
        $this->menuService = new AdminMenuService();
    }

    /**
     * Display menu permissions management page
     */
    public function index()
    {
        // Check if user is super admin or admin
        $currentRole = RBACManager::getUserRole();
        if ($currentRole !== RBACManager::ROLE_SUPER_ADMIN && $currentRole !== RBACManager::ROLE_ADMIN) {
            header('Location: ' . BASE_URL . '/admin/dashboard');
            exit;
        }

        $menuItems = $this->menuService->getAllMenuItemsWithPermissions();
        
        include APP_PATH . '/views/admin/layouts/header.php';
        include APP_PATH . '/views/admin/menu-permissions/index.php';
        include APP_PATH . '/views/admin/layouts/footer.php';
    }

    /**
     * Update role menu permissions
     */
    public function updateRolePermissions()
    {
        // Check if user is super admin or admin
        $currentRole = RBACManager::getUserRole();
        if ($currentRole !== RBACManager::ROLE_SUPER_ADMIN && $currentRole !== RBACManager::ROLE_ADMIN) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $role = $_POST['role'] ?? '';
        $menuItemId = (int)($_POST['menu_item_id'] ?? 0);
        $canView = (int)($_POST['can_view'] ?? 0);
        $canCreate = (int)($_POST['can_create'] ?? 0);
        $canEdit = (int)($_POST['can_edit'] ?? 0);
        $canDelete = (int)($_POST['can_delete'] ?? 0);

        $permissions = [
            'can_view' => $canView,
            'can_create' => $canCreate,
            'can_edit' => $canEdit,
            'can_delete' => $canDelete
        ];

        $result = $this->menuService->grantRolePermission($role, $menuItemId, $permissions);

        echo json_encode(['success' => $result]);
        exit;
    }

    /**
     * Update user menu permissions (custom overrides)
     */
    public function updateUserPermissions()
    {
        // Check if user is super admin or admin
        $currentRole = RBACManager::getUserRole();
        if ($currentRole !== RBACManager::ROLE_SUPER_ADMIN && $currentRole !== RBACManager::ROLE_ADMIN) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        $menuItemId = (int)($_POST['menu_item_id'] ?? 0);
        $canView = (int)($_POST['can_view'] ?? 0);
        $canCreate = (int)($_POST['can_create'] ?? 0);
        $canEdit = (int)($_POST['can_edit'] ?? 0);
        $canDelete = (int)($_POST['can_delete'] ?? 0);

        $permissions = [
            'can_view' => $canView,
            'can_create' => $canCreate,
            'can_edit' => $canEdit,
            'can_delete' => $canDelete
        ];

        $result = $this->menuService->grantUserPermission($userId, $menuItemId, $permissions);

        echo json_encode(['success' => $result]);
        exit;
    }

    /**
     * Revoke user menu permission
     */
    public function revokeUserPermission()
    {
        // Check if user is super admin or admin
        $currentRole = RBACManager::getUserRole();
        if ($currentRole !== RBACManager::ROLE_SUPER_ADMIN && $currentRole !== RBACManager::ROLE_ADMIN) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        $menuItemId = (int)($_POST['menu_item_id'] ?? 0);

        $result = $this->menuService->revokeUserPermission($userId, $menuItemId);

        echo json_encode(['success' => $result]);
        exit;
    }

    /**
     * Get users list for permission management
     */
    public function getUsers()
    {
        // Check if user is super admin or admin
        $currentRole = RBACManager::getUserRole();
        if ($currentRole !== RBACManager::ROLE_SUPER_ADMIN && $currentRole !== RBACManager::ROLE_ADMIN) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $db = \App\Core\Database\Database::getInstance();
        $users = $db->fetchAll("SELECT id, name, email, role FROM users WHERE role IS NOT NULL ORDER BY role, name");

        echo json_encode(['success' => true, 'users' => $users]);
        exit;
    }

    /**
     * Get user's custom menu permissions
     */
    public function getUserPermissions()
    {
        // Check if user is super admin or admin
        $currentRole = RBACManager::getUserRole();
        if ($currentRole !== RBACManager::ROLE_SUPER_ADMIN && $currentRole !== RBACManager::ROLE_ADMIN) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $userId = (int)($_GET['user_id'] ?? 0);
        
        $db = \App\Core\Database\Database::getInstance();
        $query = "
            SELECT ump.*, mi.name as menu_name, mi.url as menu_url
            FROM admin_user_menu_permissions ump
            JOIN admin_menu_items mi ON ump.menu_item_id = mi.id
            WHERE ump.user_id = ?
        ";
        $permissions = $db->fetchAll($query, [$userId]);

        echo json_encode(['success' => true, 'permissions' => $permissions]);
        exit;
    }
}

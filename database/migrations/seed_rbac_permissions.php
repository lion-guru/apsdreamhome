<?php
/**
 * Migration to seed admin_role_menu_permissions based on RBACManager::$rolePermissions
 */

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../app/Core/Database/Database.php';
require __DIR__ . '/../../app/Http/Middleware/RBACManager.php';

use App\Core\Database\Database;
use App\Http\Middleware\RBACManager;

echo "Seeding RBAC Menu Permissions...\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getPdo();

    // Get all roles
    $stmt = $pdo->query("SELECT DISTINCT role FROM users");
    $roles = $stmt->fetchAll(\PDO::FETCH_COLUMN);

    // Ensure we also cover default roles in case they don't have users yet
    $allRoles = array_unique(array_merge($roles, [
        'super_admin', 'admin', 'manager', 'associate', 'agent', 'employee', 'telecaller', 'ceo', 'cfo', 'coo', 'cto', 'cmo', 'chro'
    ]));

    // Get all menu items
    $menuItems = $db->fetchAll("SELECT id, permission_key, name FROM admin_menu_items");

    $insertStmt = $pdo->prepare("
        INSERT IGNORE INTO admin_role_menu_permissions (role, menu_item_id, can_view, can_create, can_edit, can_delete) 
        VALUES (?, ?, 1, 0, 0, 0)
    ");

    $count = 0;

    foreach ($allRoles as $role) {
        // Skip super_admin and admin as they bypass this or have full access
        if (in_array($role, ['super_admin', 'admin'])) {
            // But let's insert for them too just in case
            foreach ($menuItems as $item) {
                $insertStmt->execute([$role, $item['id']]);
                $count++;
            }
            continue;
        }

        // Get permissions for this role
        $rolePerms = RBACManager::getRolePermissions($role);
        
        // Also map legacy generic permissions to specific module views if necessary
        // Many menu items have permission_key like 'property.view', 'leads.view', etc.
        $grantedMenus = 0;

        foreach ($menuItems as $item) {
            $permKey = $item['permission_key'];
            
            // If the role has this exact permission OR it has the generic module permission
            if (empty($permKey) || in_array($permKey, $rolePerms) || 
                // Some legacy mapping logic: if role has dashboard.view, they can see dashboard
                (strpos($permKey, 'dashboard.') === 0 && in_array('dashboard.view', $rolePerms))
            ) {
                $insertStmt->execute([$role, $item['id']]);
                $count++;
                $grantedMenus++;
            }
        }
        echo "Role: {$role} - Granted access to {$grantedMenus} menus.\n";
    }

    echo "Successfully seeded {$count} permission records!\n";

    // Clear cache
    $files = glob(__DIR__ . '/../../storage/cache/*.cache');
    foreach ($files as $file) {
        @unlink($file);
    }
    echo "Cache cleared.\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

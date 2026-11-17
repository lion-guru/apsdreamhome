<?php
/**
 * Seed the "Work Distribution" admin menu item (S682).
 * Idempotent: checks by URL before inserting; skips roles already granted.
 */
require __DIR__ . '/../../config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance();

$url = '/admin/work-distribution';
$exists = $db->fetchOne("SELECT id FROM admin_menu_items WHERE url = ? LIMIT 1", [$url]);
$menuId = (int)($exists['id'] ?? 0);

if ($menuId) {
    echo "SKIP menu item (already exists, id=$menuId)\n";
} else {
    $db->execute(
        "INSERT INTO admin_menu_items
            (tenant_id, name, icon, url, parent_id, section, order_index, permission_key, is_active, created_at, updated_at)
         VALUES (1, 'Work Distribution', 'fas fa-tasks', ?, NULL, 'hrm', 916, 'work_distribution', 1, NOW(), NOW())",
        [$url]
    );
    $menuId = (int)($db->fetchOne("SELECT id FROM admin_menu_items WHERE url = ? LIMIT 1", [$url])['id'] ?? 0);
    echo "SEEDED menu item id=$menuId\n";
}

$fullRoles = ['super_admin', 'admin'];
$viewRoles = ['manager', 'hr_manager', 'hr_director', 'operations_manager', 'department_manager'];

foreach (array_merge($fullRoles, $viewRoles) as $role) {
    $granted = $db->fetchOne(
        "SELECT id FROM admin_role_menu_permissions WHERE role = ? AND menu_item_id = ? LIMIT 1",
        [$role, $menuId]
    );
    if ($granted) {
        echo "SKIP role $role (already granted id=" . (int)$granted['id'] . ")\n";
        continue;
    }
    $full = in_array($role, $fullRoles, true) ? 1 : 0;
    $db->execute(
        "INSERT INTO admin_role_menu_permissions
            (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete, created_at, updated_at)
         VALUES (1, ?, ?, 1, ?, ?, ?, NOW(), NOW())",
        [$role, $menuId, $full, $full, $full]
    );
    echo "GRANTED $role\n";
}
echo "done\n";

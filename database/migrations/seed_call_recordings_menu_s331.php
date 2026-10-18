<?php
require_once 'config/bootstrap.php';
$pdo = \App\Core\Database\Database::getInstance()->getConnection();
$exists = $pdo->query("SELECT id FROM admin_menu_items WHERE url = '/admin/crm/call-recordings' LIMIT 1")->fetchColumn();
if (!$exists) {
    $pdo->exec("INSERT INTO admin_menu_items (tenant_id, name, url, icon, parent_id, section, order_index, permission_key, is_active, created_at, updated_at) VALUES (1, 'Call Recordings', '/admin/crm/call-recordings', 'fas fa-headphones', 177, 'crm', 90, 'crm.view', 1, NOW(), NOW())");
    $menuId = (int)$pdo->lastInsertId();
    foreach (['admin','super_admin','manager','telecaller'] as $role) {
        $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete, created_at, updated_at) VALUES (1, '$role', $menuId, 1, 0, 0, 0, NOW(), NOW())");
    }
    echo "SEEDED menu id=$menuId\n";
} else {
    echo "SKIP menu\n";
}

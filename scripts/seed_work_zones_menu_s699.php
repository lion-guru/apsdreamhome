<?php
// Seed: GPS Work Zones menu item under HRM section (idempotent, S699)
require_once __DIR__ . '/../config/bootstrap.php';
use App\Core\Database\Database;
$pdo = Database::getInstance()->getConnection();

$items = [
    ['GPS Work Zones', 'fas fa-map-marked-alt', '/admin/hrm/work-zones', 'hrm', 30, 'hrm.view'],
];
$check = $pdo->prepare('SELECT id FROM admin_menu_items WHERE url = ? AND tenant_id = 1 LIMIT 1');
$ins = $pdo->prepare('INSERT INTO admin_menu_items (tenant_id, name, icon, url, parent_id, section, order_index, permission_key, is_active, created_at, updated_at) VALUES (1, ?, ?, ?, NULL, ?, ?, ?, 1, NOW(), NOW())');
foreach ($items as $i) {
    [$name, $icon, $url, $section, $order, $perm] = $i;
    $check->execute([$url]);
    if ($check->fetchColumn()) {
        echo "SKIP: $name\n";
        continue;
    }
    $ins->execute([$name, $icon, $url, $section, $order, $perm]);
    $id = (int) $pdo->lastInsertId();
    echo "SEEDED: $name (id=$id)\n";
    $roles = ['super_admin' => [1,1,1,1], 'admin' => [1,1,1,1], 'manager' => [1,0,0,0], 'hr_manager' => [1,0,0,0], 'hr_director' => [1,1,1,0], 'operations_manager' => [1,1,1,0], 'coo' => [1,1,1,0], 'director' => [1,1,1,0], 'ceo' => [1,1,1,0]];
    foreach ($roles as $role => $perms) {
        [$v, $c, $e, $d] = $perms;
        $ins2 = $pdo->prepare('INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete, created_at, updated_at) VALUES (1, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
        try { $ins2->execute([$role, $id, $v, $c, $e, $d]); echo "  grant $role\n"; } catch (\Throwable $ex) { echo "  grant $role FAIL: " . $ex->getMessage() . "\n"; }
    }
}

// Bust the menu cache so the new item shows immediately (pattern from S690)
try {
    \App\Core\Cache::delete('admin_sidebar_all');
    foreach (['super_admin','admin','manager','hr_manager','hr_director','operations_manager','coo','director','ceo'] as $role) {
        \App\Core\Cache::delete('admin_sidebar_role_' . md5($role));
    }
    echo "sidebar cache busted\n";
} catch (\Throwable $ex) {
    echo "cache bust skipped: " . $ex->getMessage() . "\n";
}
echo "done\n";

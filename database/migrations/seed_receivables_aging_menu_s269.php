<?php

$pdo = new PDO("mysql:host=127.0.0.1;dbname=apsdreamhome;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$check = $pdo->prepare("SELECT id FROM admin_menu_items WHERE url = ? LIMIT 1");
$check->execute(["/admin/reports/receivables-aging"]);
$existing = $check->fetchColumn();

if (!$existing) {
    $pdo->prepare("INSERT INTO admin_menu_items (tenant_id, name, url, icon, parent_id, section, order_index, permission_key, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())")
        ->execute([1, "Receivables Aging", "/admin/reports/receivables-aging", "fas fa-file-invoice-dollar", 127, "reports", 2, "reports.view", 1]);
    $menuId = (int)$pdo->lastInsertId();
    echo "SEEDED menu id=$menuId\n";
} else {
    $menuId = (int)$existing;
    echo "SKIP menu id=$menuId\n";
}

$roles = ["admin", "super_admin", "manager", "ceo", "cfo", "accountant"];
foreach ($roles as $role) {
    $exists = $pdo->prepare("SELECT id FROM admin_role_menu_permissions WHERE tenant_id = ? AND role = ? AND menu_item_id = ? LIMIT 1");
    $exists->execute([1, $role, $menuId]);
    if (!$exists->fetchColumn()) {
        $pdo->prepare("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete, created_at, updated_at) VALUES (?, ?, ?, 1, 0, 0, 0, NOW(), NOW())")
            ->execute([1, $role, $menuId]);
        echo "SEEDED perm $role\n";
    } else {
        echo "SKIP perm $role\n";
    }
}
echo "DONE\n";

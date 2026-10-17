<?php
/**
 * Migration: Create GRN items table (Gap 6 - Goods Receipt Note)
 * Session: 326
 * Idempotent: Safe to re-run, prints SKIP if table/columns already exist
 *
 * grn_records table already exists (created by upgrade_purchase_orders_s268.php).
 * This migration adds grn_items to track received/accepted/rejected qty per GRN line item.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$exists = $pdo->query("SHOW TABLES LIKE 'grn_items'")->fetchColumn();
if ($exists) {
    echo "SKIP grn_items - already exists\n";
} else {
    $pdo->exec("CREATE TABLE grn_items (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        grn_id BIGINT UNSIGNED NOT NULL,
        purchase_order_item_id BIGINT UNSIGNED DEFAULT NULL,
        item_name VARCHAR(255) NOT NULL,
        item_description TEXT,
        unit VARCHAR(50) DEFAULT 'Nos',
        ordered_qty DECIMAL(10,2) DEFAULT 0,
        received_qty DECIMAL(10,2) DEFAULT 0,
        accepted_qty DECIMAL(10,2) DEFAULT 0,
        rejected_qty DECIMAL(10,2) DEFAULT 0,
        rejection_reason TEXT,
        unit_price DECIMAL(15,2) DEFAULT 0.00,
        line_total DECIMAL(15,2) DEFAULT 0.00,
        quality_remarks TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_grn (grn_id),
        INDEX idx_po_item (purchase_order_item_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "CREATED grn_items\n";
}

// Seed GRN menu item under finance section (sibling of Purchase Orders #349)
$maxIdRow = $pdo->query("SELECT MAX(id) AS max_id FROM admin_menu_items")->fetch();
$nextId = (int)($maxIdRow['max_id'] ?? 0) + 1;

$menuItems = [
    [
        'id' => $nextId,
        'tenant_id' => 1,
        'name' => 'Goods Receipt Note (GRN)',
        'icon' => 'fas fa-truck',
        'url' => '/admin/grn',
        'parent_id' => null,
        'section' => 'finance',
        'order_index' => 29,
        'permission_key' => 'financial.view',
        'is_active' => 1
    ]
];

foreach ($menuItems as $item) {
    $exists = $pdo->query("SELECT id FROM admin_menu_items WHERE id = {$item['id']} LIMIT 1")->fetchColumn();
    if ($exists) {
        echo "SKIP admin_menu_items#{$item['id']} ({$item['name']}) - already exists\n";
        continue;
    }
    $stmt = $pdo->prepare("
        INSERT INTO admin_menu_items (id, tenant_id, name, icon, url, parent_id, section, order_index, permission_key, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $item['id'], $item['tenant_id'], $item['name'], $item['icon'], $item['url'],
        $item['parent_id'], $item['section'], $item['order_index'], $item['permission_key'], $item['is_active']
    ]);
    echo "SEEDED admin_menu_items#{$item['id']} ({$item['name']})\n";
}

// Role permissions
$rolesFull = ['admin', 'super_admin'];
$rolesLimited = ['manager', 'ceo', 'cfo', 'coo', 'finance_director', 'finance_manager'];
$rolesViewOnly = ['accountant'];

$permStmt = $pdo->prepare("
    INSERT IGNORE INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete)
    VALUES (1, ?, ?, 1, 1, 1, 1)
");

foreach ($menuItems as $item) {
    $menuId = $item['id'];
    foreach ($rolesFull as $role) {
        $exists = $pdo->query("SELECT id FROM admin_role_menu_permissions WHERE role = '$role' AND menu_item_id = $menuId LIMIT 1")->fetchColumn();
        if ($exists) { echo "SKIP admin_role_menu_permissions ($role, menu#$menuId)\n"; }
        else { $permStmt->execute([$role, $menuId]); echo "SEEDED admin_role_menu_permissions ($role, menu#$menuId) - full\n"; }
    }
    foreach ($rolesLimited as $role) {
        $exists = $pdo->query("SELECT id FROM admin_role_menu_permissions WHERE role = '$role' AND menu_item_id = $menuId LIMIT 1")->fetchColumn();
        if (!$exists) { $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, '$role', $menuId, 1, 1, 1, 0)"); echo "SEEDED admin_role_menu_permissions ($role, menu#$menuId) - limited\n"; }
        else { echo "SKIP admin_role_menu_permissions ($role, menu#$menuId)\n"; }
    }
    foreach ($rolesViewOnly as $role) {
        $exists = $pdo->query("SELECT id FROM admin_role_menu_permissions WHERE role = '$role' AND menu_item_id = $menuId LIMIT 1")->fetchColumn();
        if (!$exists) { $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, '$role', $menuId, 1, 0, 0, 0)"); echo "SEEDED admin_role_menu_permissions ($role, menu#$menuId) - view only\n"; }
        else { echo "SKIP admin_role_menu_permissions ($role, menu#$menuId)\n"; }
    }
}

echo "Done.\n";
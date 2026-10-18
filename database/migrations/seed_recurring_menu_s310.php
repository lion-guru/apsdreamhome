<?php
/**
 * Migration: Seed Recurring Transactions menu item
 * Session: 310
 * Idempotent: Safe to re-run, prints SKIP for existing items
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

// Determine next available ID
$maxId = $pdo->query("SELECT MAX(id) FROM admin_menu_items")->fetchColumn();
$nextId = (int)$maxId + 1;

$menuItems = [
    [
        'id' => $nextId,
        'tenant_id' => 1,
        'name' => 'Recurring Transactions',
        'icon' => 'fas fa-sync-alt',
        'url' => '/admin/recurring',
        'parent_id' => null,
        'section' => 'finance',
        'order_index' => 12,
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

// Seed role permissions for the menu item
$rolesFull = ['admin', 'super_admin'];
$rolesLimited = ['manager', 'ceo', 'cfo', 'coo', 'finance_director', 'finance_manager', 'senior_accountant', 'chartered_accountant'];
$rolesViewOnly = ['accountant'];

$permStmt = $pdo->prepare("
    INSERT IGNORE INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete)
    VALUES (1, ?, ?, 1, 1, 1, 1)
");

foreach ($menuItems as $item) {
    $menuId = $item['id'];
    
    // Full permissions for admin/super_admin
    foreach ($rolesFull as $role) {
        $exists = $pdo->query("SELECT id FROM admin_role_menu_permissions WHERE role = '$role' AND menu_item_id = $menuId LIMIT 1")->fetchColumn();
        if ($exists) {
            echo "SKIP admin_role_menu_permissions ($role, menu#$menuId)\n";
        } else {
            $permStmt->execute([$role, $menuId]);
            echo "SEEDED admin_role_menu_permissions ($role, menu#$menuId) - full\n";
        }
    }
    
    // Limited permissions for restricted roles
    foreach ($rolesLimited as $role) {
        $exists = $pdo->query("SELECT id FROM admin_role_menu_permissions WHERE role = '$role' AND menu_item_id = $menuId LIMIT 1")->fetchColumn();
        if (!$exists) {
            $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, '$role', $menuId, 1, 1, 1, 0)");
            echo "SEEDED admin_role_menu_permissions ($role, menu#$menuId) - limited\n";
        } else {
            echo "SKIP admin_role_menu_permissions ($role, menu#$menuId)\n";
        }
    }
    
    // View-only for accountant
    foreach ($rolesViewOnly as $role) {
        $exists = $pdo->query("SELECT id FROM admin_role_menu_permissions WHERE role = '$role' AND menu_item_id = $menuId LIMIT 1")->fetchColumn();
        if (!$exists) {
            $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, '$role', $menuId, 1, 0, 0, 0)");
            echo "SEEDED admin_role_menu_permissions ($role, menu#$menuId) - view only\n";
        } else {
            echo "SKIP admin_role_menu_permissions ($role, menu#$menuId)\n";
        }
    }
}

echo "Done.\n";
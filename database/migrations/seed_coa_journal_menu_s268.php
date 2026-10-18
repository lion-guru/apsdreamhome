<?php
/**
 * Migration: Seed Chart of Accounts and Journal Entries menu items
 * Session: 268
 * Idempotent: Safe to re-run, prints SKIP for existing items
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

// Determine next available IDs
$maxId = $pdo->query("SELECT MAX(id) FROM admin_menu_items")->fetchColumn();
$nextId = (int)$maxId + 1;

$menuItems = [
    [
        'id' => $nextId,
        'tenant_id' => 1,
        'name' => 'Chart of Accounts',
        'icon' => 'fas fa-sitemap',
        'url' => '/admin/chart-of-accounts',
        'parent_id' => null,
        'section' => 'finance',
        'order_index' => 5,
        'permission_key' => 'financial.view',
        'is_active' => 1
    ],
    [
        'id' => $nextId + 1,
        'tenant_id' => 1,
        'name' => 'Journal Entries',
        'icon' => 'fas fa-book',
        'url' => '/admin/journal-entries',
        'parent_id' => null,
        'section' => 'finance',
        'order_index' => 6,
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

// Seed role permissions for each menu item
$roles = ['admin', 'super_admin', 'manager', 'ceo', 'cfo', 'coo', 'finance_director', 'finance_manager', 'senior_accountant', 'chartered_accountant', 'accountant', 'franchise_owner'];

$permStmt = $pdo->prepare("
    INSERT IGNORE INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete)
    VALUES (1, ?, ?, 1, 1, 1, 1)
");

$limitedRoles = ['manager', 'ceo', 'cfo', 'coo', 'finance_director', 'finance_manager', 'senior_accountant', 'chartered_accountant'];
$restrictedRoles = ['accountant'];

for ($i = 0; $i < count($menuItems); $i++) {
    $menuId = $menuItems[$i]['id'];
    
    // Full permissions for admin/super_admin
    foreach (['admin', 'super_admin'] as $role) {
        $exists = $pdo->query("SELECT id FROM admin_role_menu_permissions WHERE role = '$role' AND menu_item_id = $menuId LIMIT 1")->fetchColumn();
        if ($exists) {
            echo "SKIP admin_role_menu_permissions ($role, menu#$menuId)\n";
        } else {
            $permStmt->execute([$role, $menuId]);
            echo "SEEDED admin_role_menu_permissions ($role, menu#$menuId) - full\n";
        }
    }
    
    // Limited permissions for restricted roles
    foreach ($limitedRoles as $role) {
        $exists = $pdo->query("SELECT id FROM admin_role_menu_permissions WHERE role = '$role' AND menu_item_id = $menuId LIMIT 1")->fetchColumn();
        if (!$exists) {
            $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, '$role', $menuId, 1, 1, 1, 0)");
            echo "SEEDED admin_role_menu_permissions ($role, menu#$menuId) - limited\n";
        } else {
            echo "SKIP admin_role_menu_permissions ($role, menu#$menuId)\n";
        }
    }
    
    // View-only for accountant
    foreach ($restrictedRoles as $role) {
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
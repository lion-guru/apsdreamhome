<?php
/**
 * Migration: Seed Budget Management menu items
 * Session: 268 (continued)
 * Idempotent: Safe to re-run, prints SKIP for existing items
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$maxId = (int)$pdo->query("SELECT MAX(id) FROM admin_menu_items")->fetchColumn();
$nextId = $maxId + 1;

$menuItems = [
    [
        'id' => $nextId,
        'tenant_id' => 1,
        'name' => 'Budget Management',
        'icon' => 'fas fa-chart-pie',
        'url' => '/admin/budgets',
        'parent_id' => null,
        'section' => 'finance',
        'order_index' => 24,
        'permission_key' => 'budget.view',
        'is_active' => 1
    ],
    [
        'id' => $nextId + 1,
        'tenant_id' => 1,
        'name' => 'Budget Planning',
        'icon' => 'fas fa-clipboard-list',
        'url' => '/admin/budgets/planning',
        'parent_id' => null,
        'section' => 'finance',
        'order_index' => 25,
        'permission_key' => 'budget.view',
        'is_active' => 1
    ],
    [
        'id' => $nextId + 2,
        'tenant_id' => 1,
        'name' => 'Budget Variance Report',
        'icon' => 'fas fa-chart-bar',
        'url' => '/admin/budgets/variance-report',
        'parent_id' => null,
        'section' => 'finance',
        'order_index' => 26,
        'permission_key' => 'budget.view',
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

// Seed permissions (same pattern as COA/JE migration)
$roles = ['admin', 'super_admin', 'manager', 'ceo', 'cfo', 'coo', 'finance_director', 'finance_manager', 'senior_accountant', 'chartered_accountant', 'accountant', 'franchise_owner'];

$limitedRoles = ['manager', 'ceo', 'cfo', 'coo', 'finance_director', 'finance_manager', 'senior_accountant', 'chartered_accountant'];
$restrictedRoles = ['accountant'];

$permStmt = $pdo->prepare("
    INSERT IGNORE INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete)
    VALUES (1, ?, ?, 1, 1, 1, 1)
");

for ($i = 0; $i < count($menuItems); $i++) {
    $menuId = $menuItems[$i]['id'];

    foreach (['admin', 'super_admin'] as $role) {
        $exists = $pdo->query("SELECT id FROM admin_role_menu_permissions WHERE role = '$role' AND menu_item_id = $menuId LIMIT 1")->fetchColumn();
        if (!$exists) {
            $permStmt->execute([$role, $menuId]);
            echo "SEEDED admin_role_menu_permissions ($role, menu#$menuId) - full\n";
        } else {
            echo "SKIP admin_role_menu_permissions ($role, menu#$menuId)\n";
        }
    }

    foreach ($limitedRoles as $role) {
        $exists = $pdo->query("SELECT id FROM admin_role_menu_permissions WHERE role = '$role' AND menu_item_id = $menuId LIMIT 1")->fetchColumn();
        if (!$exists) {
            $pdo->exec("INSERT IGNORE INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, '$role', $menuId, 1, 1, 1, 0)");
            echo "SEEDED admin_role_menu_permissions ($role, menu#$menuId) - limited\n";
        } else {
            echo "SKIP admin_role_menu_permissions ($role, menu#$menuId)\n";
        }
    }

    foreach ($restrictedRoles as $role) {
        $exists = $pdo->query("SELECT id FROM admin_role_menu_permissions WHERE role = '$role' AND menu_item_id = $menuId LIMIT 1")->fetchColumn();
        if (!$exists) {
            $pdo->exec("INSERT IGNORE INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, '$role', $menuId, 1, 0, 0, 0)");
            echo "SEEDED admin_role_menu_permissions ($role, menu#$menuId) - view only\n";
        } else {
            echo "SKIP admin_role_menu_permissions ($role, menu#$menuId)\n";
        }
    }
}

echo "Done.\n";
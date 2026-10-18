<?php
/**
 * Migration: Create Google Calendar tokens table + seed menu (Gap 23)
 * Session: 333
 * Idempotent: Safe to re-run, prints SKIP if table/columns already exist
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

// 1) google_calendar_tokens table
$exists = $pdo->query("SHOW TABLES LIKE 'google_calendar_tokens'")->fetchColumn();
if ($exists) {
    echo "SKIP google_calendar_tokens - already exists\n";
} else {
    $pdo->exec("CREATE TABLE google_calendar_tokens (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        user_id INT UNSIGNED NOT NULL,
        email VARCHAR(255) DEFAULT NULL,
        access_token TEXT NOT NULL,
        refresh_token TEXT DEFAULT NULL,
        scope VARCHAR(255) DEFAULT NULL,
        expires_at DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_user_tenant (tenant_id, user_id),
        INDEX idx_expires (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "CREATED google_calendar_tokens\n";
}

// 2) seed menu item
$maxIdRow = $pdo->query("SELECT MAX(id) AS max_id FROM admin_menu_items")->fetch();
$nextId = (int)($maxIdRow['max_id'] ?? 0) + 1;

$menuItems = [
    [
        'id' => $nextId,
        'tenant_id' => 1,
        'name' => 'Calendar Sync',
        'icon' => 'fas fa-calendar-alt',
        'url' => '/admin/calendar-sync',
        'parent_id' => null,
        'section' => 'finance',
        'order_index' => 32,
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
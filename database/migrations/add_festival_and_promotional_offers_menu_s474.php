<?php
/**
 * Migration S474: Add Festival & Promotional Offers to Admin Menu & Permissions
 *
 * Adds:
 * 1. "Associate Festival Offers" (/admin/associate-offers) under MLM section
 * 2. "Promotional Offers" (/admin/promotional-offers) under Marketing section
 *
 * 100% Idempotent. Re-runs safely skip existing entries.
 */

require_once __DIR__ . '/../../config/bootstrap.php';

try {
    $pdo = \App\Core\Database\Database::getInstance()->getPdo();
} catch (\Throwable $e) {
    die("DB connection failed: " . $e->getMessage() . "\n");
}

echo "=== MIGRATION S474: Add Festival & Promotional Offers Menu Items ===\n";

$menuEntries = [
    [
        'name'           => 'Associate Festival Offers',
        'icon'           => 'fas fa-gift',
        'url'            => '/admin/associate-offers',
        'section'        => 'mlm',
        'order_index'    => 20,
        'permission_key' => 'mlm.offers',
    ],
    [
        'name'           => 'Promotional Offers',
        'icon'           => 'fas fa-tags',
        'url'            => '/admin/promotional-offers',
        'section'        => 'marketing',
        'order_index'    => 11,
        'permission_key' => 'marketing.promotions',
    ],
];

$roles = ['super_admin', 'admin', 'sales_director', 'marketing_director'];

foreach ($menuEntries as $entry) {
    $stmt = $pdo->prepare("SELECT id FROM admin_menu_items WHERE url = ? LIMIT 1");
    $stmt->execute([$entry['url']]);
    $menuId = $stmt->fetchColumn();

    if ($menuId) {
        echo "  [=] Menu item '{$entry['name']}' already exists (ID: {$menuId})\n";
    } else {
        $ins = $pdo->prepare("
            INSERT INTO admin_menu_items (tenant_id, name, icon, url, section, order_index, permission_key, is_active, created_at, updated_at)
            VALUES (1, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
        ");
        $ins->execute([
            $entry['name'],
            $entry['icon'],
            $entry['url'],
            $entry['section'],
            $entry['order_index'],
            $entry['permission_key'],
        ]);
        $menuId = (int)$pdo->lastInsertId();
        echo "  [+] Added menu item '{$entry['name']}' (ID: {$menuId})\n";
    }

    // Role permissions
    foreach ($roles as $r) {
        $permCheck = $pdo->prepare("SELECT id FROM admin_role_menu_permissions WHERE menu_item_id = ? AND role = ? LIMIT 1");
        $permCheck->execute([$menuId, $r]);
        if (!$permCheck->fetchColumn()) {
            $permIns = $pdo->prepare("
                INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete, created_at, updated_at)
                VALUES (1, ?, ?, 1, 1, 1, 1, NOW(), NOW())
            ");
            $permIns->execute([$r, $menuId]);
            echo "      [+] Granted permission for role '{$r}' on menu {$menuId}\n";
        }
    }
}

// Clear admin sidebar cache if Cache class exists
try {
    if (class_exists('\App\Core\Cache')) {
        \App\Core\Cache::forget('admin_sidebar_all');
        echo "  [+] Cleared admin sidebar cache\n";
    }
} catch (\Throwable $e) {
    // ignore
}

echo "=== MIGRATION S474 COMPLETE ===\n";

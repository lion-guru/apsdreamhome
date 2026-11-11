<?php
/**
 * Migration: Seed Manual Calling Desk menu item (Jio Fiber Free Calling)
 * Idempotent
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

// Find the telecalling parent menu item
$parent = $pdo->fetchOne("SELECT id FROM admin_menu_items WHERE url = '/employee/telecalling-dashboard' LIMIT 1");
$parentId = $parent['id'] ?? null;

$stmt = $pdo->prepare("
    INSERT IGNORE INTO `admin_menu_items` (`name`, `url`, `icon`, `section`, `order_index`, `is_active`, `permission_key`, `parent_id`)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->execute(['Manual Calling Desk', '/employee/manual-calling', 'fa-phone-volume', 'employee', 5, 1, 'telecalling.view', $parentId]);
echo "Seeded: Manual Calling Desk menu item.\n";

// Also seed for employee role
$menuItem = $pdo->fetchOne("SELECT id FROM admin_menu_items WHERE url = '/employee/manual-calling' LIMIT 1");
if ($menuItem) {
    $roles = ['employee', 'agent', 'telecaller', 'manager', 'admin', 'super_admin'];
    foreach ($roles as $role) {
        $stmt2 = $pdo->prepare("
            INSERT IGNORE INTO `admin_role_menu_permissions` (`role`, `menu_item_id`, `can_view`, `can_create`, `can_edit`, `can_delete`)
            VALUES (?, ?, 1, 0, 0, 0)
        ");
        $stmt2->execute([$role, $menuItem['id']]);
    }
    echo "Granted permissions to " . count($roles) . " roles.\n";
}

echo "DONE\n";
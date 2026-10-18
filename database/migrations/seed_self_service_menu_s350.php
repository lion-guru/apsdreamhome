<?php
/**
 * Migration: Seed Missing Employee Self-Service Menu Items (Training, Org Chart, Surveys)
 * Session: 350
 * Idempotent: Safe to re-run, checks url existence before inserting.
 */

require_once __DIR__ . '/../../config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

echo "Running seed_self_service_menu_s350...\n";

// 1. Rename item 215 from "Quality Control" to "Employee Self Service"
$updParent = $pdo->prepare("
    UPDATE admin_menu_items
    SET name = 'Employee Self Service', icon = 'fas fa-user-cog'
    WHERE id = 215 AND url = '/employee/self-service'
");
$updParent->execute();
echo "Updated parent menu item 215 to 'Employee Self Service'\n";

// Parent ID
$parentId = 215;

$newItems = [
    [
        'name' => 'Training & Skills',
        'url' => '/employee/self-service/training',
        'icon' => 'fas fa-graduation-cap'
    ],
    [
        'name' => 'Org Chart',
        'url' => '/employee/self-service/org-chart',
        'icon' => 'fas fa-sitemap'
    ],
    [
        'name' => 'Engagement Surveys',
        'url' => '/employee/self-service/surveys',
        'icon' => 'fas fa-poll-h'
    ]
];

$empRoles = ['employee', 'agent', 'telecaller', 'manager', 'admin', 'super_admin'];

foreach ($newItems as $it) {
    $stmt = $pdo->prepare("SELECT id FROM admin_menu_items WHERE url = ? LIMIT 1");
    $stmt->execute([$it['url']]);
    $menuId = $stmt->fetchColumn();

    if ($menuId) {
        echo "SKIP admin_menu_items ({$it['url']}) - already exists with ID: {$menuId}\n";
    } else {
        $maxOrderRow = $pdo->query("SELECT COALESCE(MAX(order_index), 0) AS max_order FROM admin_menu_items WHERE section = 'employee'")->fetch();
        $nextOrder = (int)($maxOrderRow['max_order'] ?? 0) + 1;

        $ins = $pdo->prepare("
            INSERT INTO admin_menu_items (tenant_id, name, icon, url, parent_id, section, order_index, permission_key, is_active, created_at, updated_at)
            VALUES (1, ?, ?, ?, ?, 'employee', ?, 'employee', 1, NOW(), NOW())
        ");
        $ins->execute([$it['name'], $it['icon'], $it['url'], $parentId, $nextOrder]);
        $menuId = $pdo->lastInsertId();
        echo "SEEDED admin_menu_items#{$menuId} ({$it['name']} -> {$it['url']})\n";
    }

    if ($menuId) {
        foreach ($empRoles as $role) {
            $chk = $pdo->prepare("SELECT id FROM admin_role_menu_permissions WHERE role = ? AND menu_item_id = ? LIMIT 1");
            $chk->execute([$role, $menuId]);
            if (!$chk->fetchColumn()) {
                $insPerm = $pdo->prepare("
                    INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete, created_at, updated_at)
                    VALUES (1, ?, ?, 1, 1, 1, 0, NOW(), NOW())
                ");
                $insPerm->execute([$role, $menuId]);
                echo "SEEDED perm for {$role} on menu#{$menuId}\n";
            }
        }
    }
}

echo "Migration seed_self_service_menu_s350 completed successfully.\n";

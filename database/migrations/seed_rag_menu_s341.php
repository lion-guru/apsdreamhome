<?php
/**
 * Migration: Seed RAG Knowledge Base admin menu
 * Session: 341
 * Idempotent: safe to re-run, prints SKIP when already present.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

// Already seeded? (check by url — the stable identifier)
$exists = $pdo->prepare("SELECT id FROM admin_menu_items WHERE url = ? LIMIT 1");
$exists->execute(['/admin/rag']);
$existingId = $exists->fetchColumn();

if ($existingId) {
    echo "SKIP admin_menu_items#{$existingId} (/admin/rag) - already exists\n";

    // Still ensure role permissions exist (idempotent insert-ignore).
    $perm = $pdo->prepare("SELECT id FROM admin_role_menu_permissions WHERE menu_item_id = ? AND role = ?");
    $ins  = $pdo->prepare("INSERT IGNORE INTO admin_role_menu_permissions (menu_item_id, role, can_view, can_edit, can_delete) VALUES (?, ?, ?, ?, ?)");
    $roles = ['super_admin' => [1,1,1], 'admin' => [1,1,1], 'manager' => [1,0,0], 'cto' => [1,1,1]];
    foreach ($roles as $role => [$v,$e,$d]) {
        $perm->execute([$existingId, $role]);
        if (!$perm->fetchColumn()) {
            $ins->execute([$existingId, $role, $v, $e, $d]);
            echo "  + perm {$role} for #{$existingId}\n";
        }
    }
    echo "DONE (idempotent, permissions ensured)\n";
    exit(0);
}

$max = $pdo->query("SELECT MAX(id) AS m FROM admin_menu_items")->fetch();
$id = (int)($max['m'] ?? 0) + 1;

// Parent 20 = Plots Inventory (same hub the AI/knowledge tooling lives under).
$parentId = 20;

$pdo->prepare("
    INSERT INTO admin_menu_items
        (id, tenant_id, name, icon, url, parent_id, section, order_index, permission_key, is_active)
    VALUES (?, 1, ?, ?, ?, ?, ?, ?, ?, 1)
")->execute([
    $id, 'RAG Knowledge Base', 'fas fa-brain', '/admin/rag', $parentId, 'ai', 60, 'ai.rag',
]);
echo "ADDED admin_menu_items#{$id} (/admin/rag)\n";

$ins = $pdo->prepare("INSERT IGNORE INTO admin_role_menu_permissions (menu_item_id, role, can_view, can_edit, can_delete) VALUES (?, ?, ?, ?, ?)");
$roles = ['super_admin' => [1,1,1], 'admin' => [1,1,1], 'manager' => [1,0,0], 'cto' => [1,1,1]];
foreach ($roles as $role => [$v,$e,$d]) {
    $ins->execute([$id, $role, $v, $e, $d]);
    echo "  + perm {$role}\n";
}

echo "DONE\n";
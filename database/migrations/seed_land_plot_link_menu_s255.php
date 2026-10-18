<?php
/**
 * Plot-Gata-Kisan Link Desk menu seed (Session 255).
 * Idempotent: inserts ONLY when absent. Safe to re-run after any DB restore.
 * Standing Rule 1: no one-off live SQL — every seed lives here.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$exists = $pdo->query("SELECT id FROM admin_menu_items WHERE id = 342 LIMIT 1")->fetchColumn();
if ($exists) {
    echo "SKIP admin_menu_items#342 (already seeded)\n";
} else {
    // Parent under the Bainama desk when present (same section + permission family).
    $parent = $pdo->query("SELECT id, section, permission_key FROM admin_menu_items WHERE url = '/admin/farmer-acquisitions' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $parentId = $parent ? (int)$parent['id'] : 20;
    $section = $parent['section'] ?? 'land';
    $pkey = $parent['permission_key'] ?? 'land.acquisitions';
    $st = $pdo->prepare("INSERT INTO admin_menu_items (id, tenant_id, name, icon, url, parent_id, section, order_index, permission_key, is_active)
                VALUES (342, 1, 'Plot-Gata-Kisan Link', 'fas fa-link', '/admin/land-plot-link', ?, ?, 5, ?, 1)");
    $st->execute([$parentId, $section, $pkey]);
    echo "SEEDED admin_menu_items#342 (parent $parentId)\n";
}

foreach (['admin', 'super_admin'] as $role) {
    $grant = $pdo->query("SELECT id FROM admin_role_menu_permissions WHERE menu_item_id = 342 AND role = " . $pdo->quote($role) . " LIMIT 1")->fetchColumn();
    if ($grant) {
        echo "SKIP $role grant 342 (already seeded)\n";
    } else {
        $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete)
                VALUES (1, '$role', 342, 1, 1, 1, 0)");
        echo "SEEDED $role grant 342\n";
    }
}

// Sidebar is cached (S219 lesson) — best-effort bust so the link renders now.
try {
    if (class_exists('App\\Core\\Cache')) {
        App\Core\Cache::delete('admin_sidebar_all');
    }
} catch (Throwable $e) { /* cache layer optional */ }

echo "Done.\n";

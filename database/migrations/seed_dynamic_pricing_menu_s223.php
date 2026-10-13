<?php
/**
 * Dynamic Pricing AI menu seed (S219 created, S223 re-seeded post-restore, S225 filed).
 * Idempotent: inserts ONLY when absent. Safe to re-run after any DB restore.
 * Standing Rule 1: no one-off live SQL — every seed lives here.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

// --- menu item 341 (mirrors PLC #320's shape: parent 20, section plots) ---
$exists = $pdo->query("SELECT id FROM admin_menu_items WHERE id = 341 LIMIT 1")->fetchColumn();
if ($exists) {
    echo "SKIP admin_menu_items#341 (already seeded)\n";
} else {
    $pdo->exec("INSERT INTO admin_menu_items (id, tenant_id, name, icon, url, parent_id, section, order_index, permission_key, is_active)
                VALUES (341, 1, 'Dynamic Pricing AI', 'fas fa-chart-line', '/admin/dynamic-pricing', 20, 'plots', 12, 'plots.development', 1)");
    echo "SEEDED admin_menu_items#341\n";
}

// --- admin grant (mirrors #320's role set: admin 1/1/1/0) ---
$grant = $pdo->query("SELECT id FROM admin_role_menu_permissions WHERE menu_item_id = 341 AND role = 'admin' LIMIT 1")->fetchColumn();
if ($grant) {
    echo "SKIP admin grant 341 (already seeded)\n";
} else {
    $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete)
                VALUES (1, 'admin', 341, 1, 1, 1, 0)");
    echo "SEEDED admin grant 341\n";
}

echo "Done.\n";

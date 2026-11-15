<?php
// Idempotent: grant RBAC permissions for the new AI Hub sidebar items (S538).
// Grants default admin/super_admin/it_manager/senior_developer 1/1/1/0 across all roles.
// Re-runs skip everything.

$pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
]);

$urls = [
    '/admin/ai/hub',
    '/admin/ai-analytics/insights',
    '/admin/ai-management',
    '/admin/ai-management/integrations',
    '/admin/ai-management/lead-scores',
    '/admin/ai-management/chatbot-logs',
    '/admin/ai-management/context-memory',
    '/admin/ai-management/usage-analytics',
];

$roleGrants = [
    'super_admin' => [1, 1, 1, 1],
    'admin'       => [1, 1, 1, 0],
    'it_manager'  => [1, 1, 1, 0],
    'senior_developer' => [1, 1, 1, 0],
    'manager'     => [1, 0, 0, 0],
    'ceo'         => [1, 1, 1, 0],
    'cfo'         => [1, 0, 0, 0],
    'coo'         => [1, 0, 0, 0],
    'sales_manager' => [1, 0, 0, 0],
];

// Resolve menu item ids by URL
$ids = [];
foreach ($urls as $u) {
    $s = $pdo->prepare("SELECT id FROM admin_menu_items WHERE url = ? LIMIT 1");
    $s->execute([$u]);
    $id = $s->fetchColumn();
    if ($id) { $ids[$u] = (int)$id; }
}

foreach ($ids as $u => $menuId) {
    foreach ($roleGrants as $role => [$v, $c, $e, $d]) {
        $exists = $pdo->prepare(
            "SELECT id FROM admin_role_menu_permissions WHERE menu_item_id = ? AND role = ? LIMIT 1"
        );
        $exists->execute([$menuId, $role]);
        if ($exists->fetchColumn()) {
            echo "SKIP [{$u}] [{$role}]\n";
            continue;
        }
        $stmt = $pdo->prepare(
            "INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete)
             VALUES (1, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$role, $menuId, $v, $c, $e, $d]);
        echo "SEEDED [{$u}] [{$role}]\n";
    }
}

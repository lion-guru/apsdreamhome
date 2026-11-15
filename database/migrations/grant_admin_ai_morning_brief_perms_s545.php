<?php
// Idempotent: grant RBAC permissions for the AI Morning Brief sidebar item (S545).
// Re-runs skip everything.

$pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
]);

$s = $pdo->prepare("SELECT id FROM admin_menu_items WHERE url = '/admin/ai-morning-brief' LIMIT 1");
$s->execute();
$menuId = (int)$s->fetchColumn();
if (!$menuId) {
    echo "MISSING ai-morning-brief menu item — run seed_admin_ai_morning_brief_menu_s545.php first\n";
    exit(1);
}

$grants = [
    'super_admin' => [1, 1, 1, 1],
    'admin'       => [1, 1, 1, 0],
    'ceo'         => [1, 1, 1, 0],
    'sales_director' => [1, 1, 1, 0],
    'sales_manager' => [1, 0, 0, 0],
    'manager'     => [1, 0, 0, 0],
    'coo'         => [1, 0, 0, 0],
    'cfo'         => [1, 0, 0, 0],
];

foreach ($grants as $role => [$v, $c, $e, $d]) {
    $exists = $pdo->prepare(
        "SELECT id FROM admin_role_menu_permissions WHERE menu_item_id = ? AND role = ? LIMIT 1"
    );
    $exists->execute([$menuId, $role]);
    if ($exists->fetchColumn()) {
        echo "SKIP [{$role}]\n";
        continue;
    }
    $stmt = $pdo->prepare(
        "INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete)
         VALUES (1, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$role, $menuId, $v, $c, $e, $d]);
    echo "SEEDED [{$role}]\n";
}

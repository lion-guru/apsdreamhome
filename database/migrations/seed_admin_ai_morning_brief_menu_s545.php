<?php
// Idempotent: seed the AI Morning Brief sidebar item into admin_menu_items (S545).
// RFC: a page that isn't in the sidebar is invisible (S539 lesson). Safe to re-run.

$pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
]);

$check = $pdo->prepare("SELECT id FROM admin_menu_items WHERE url = '/admin/ai-morning-brief' LIMIT 1");
$check->execute();
$existing = $check->fetchColumn();

if ($existing) {
    echo "SKIP /admin/ai-morning-brief (exists as id={$existing})\n";
    exit(0);
}

$stmt = $pdo->prepare(
    "INSERT INTO admin_menu_items
        (tenant_id, name, icon, url, parent_id, section, order_index, permission_key, is_active)
     VALUES (1, 'AI Morning Brief', 'fas fa-sun-rise', '/admin/ai-morning-brief', NULL, 'ai_tech', 213, 'ai.morning_brief', 1)"
);
$stmt->execute();
echo "SEEDED AI Morning Brief as id=" . $pdo->lastInsertId() . "\n";

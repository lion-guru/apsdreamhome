<?php
$config = require dirname(__DIR__) . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$exists = $pdo->query("SELECT COUNT(*) FROM admin_menu_items WHERE url = '/admin/commission/recalculations'")->fetchColumn();
if ((int)$exists > 0) {
    echo "Sidebar item already exists\n";
    exit;
}

// Find commission parent
$parentId = $pdo->query("SELECT id FROM admin_menu_items WHERE name = 'Commission' AND section = 'commission' LIMIT 1")->fetchColumn();
if (!$parentId) {
    $parentId = $pdo->query("SELECT id FROM admin_menu_items WHERE url = '/admin/mlm' LIMIT 1")->fetchColumn();
}
$parentId = (int)($parentId ?: 0);

$maxOrder = $pdo->query("SELECT COALESCE(MAX(order_index), 0) FROM admin_menu_items WHERE parent_id = $parentId")->fetchColumn();

$ins = $pdo->prepare("
    INSERT INTO admin_menu_items (name, icon, url, parent_id, section, order_index, is_active, created_at)
    VALUES (?, 'fas fa-calculator', '/admin/commission/recalculations', ?, 'commission', ?, 1, NOW())
");
$ins->execute(['Recalculations', $parentId, (int)$maxOrder + 1]);
echo "Sidebar item inserted: id=" . $pdo->lastInsertId() . "\n";?>
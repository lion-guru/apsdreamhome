<?php
$config = require dirname(__DIR__) . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Add Payout Batches sidebar item
$exists = $pdo->query("SELECT COUNT(*) FROM admin_menu_items WHERE url = '/admin/payout-batches'")->fetchColumn();
if ((int)$exists > 0) {
    echo "Payout Batches sidebar item already exists\n";
} else {
    $parentId = $pdo->query("SELECT id FROM admin_menu_items WHERE name = 'Commission' AND section = 'commission' LIMIT 1")->fetchColumn();
    $parentId = (int)($parentId ?: 0);
    $maxOrder = $pdo->query("SELECT COALESCE(MAX(order_index), 0) FROM admin_menu_items WHERE parent_id = $parentId")->fetchColumn();

    $ins = $pdo->prepare("
        INSERT INTO admin_menu_items (name, icon, url, parent_id, section, order_index, is_active, created_at)
        VALUES (?, 'fas fa-money-check-alt', '/admin/payout-batches', ?, 'commission', ?, 1, NOW())
    ");
    $ins->execute(['Payout Batches', $parentId, (int)$maxOrder + 1]);
    echo "Payout Batches sidebar inserted: id=" . $pdo->lastInsertId() . "\n";
}?>
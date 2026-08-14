<?php
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$items = [
    ['CRM Settings', '/admin/crm/settings', 'fas fa-cogs', 'marketing', 98],
    ['Lead Trash', '/admin/leads/trash', 'fas fa-trash-alt', 'marketing', 99],
];

foreach ($items as [$name, $url, $icon, $section, $order]) {
    $stmt = $pdo->prepare('SELECT id FROM admin_menu_items WHERE url = ?');
    $stmt->execute([$url]);
    if ($stmt->fetch()) {
        echo "SKIP: $name (already exists)\n";
        continue;
    }
    $stmt = $pdo->prepare('INSERT INTO admin_menu_items (name, url, icon, section, order_index, is_active) VALUES (?, ?, ?, ?, ?, 1)');
    $stmt->execute([$name, $url, $icon, $section, $order]);
    echo "ADDED: $name (id: {$pdo->lastInsertId()})\n";
}

echo "\nDone.\n";?>
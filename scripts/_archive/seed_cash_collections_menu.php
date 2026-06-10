<?php
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'], $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $items = [
        ['Cash Collections', '/admin/finance/collections', 'fa-money-bill', 'finance', 35],
        ['Collection Reconciliation', '/admin/finance/reconciliation-collections', 'fa-balance-scale', 'finance', 36],
    ];

    foreach ($items as $i) {
        $stmt = $pdo->prepare("INSERT INTO admin_menu_items (name, url, icon, section, order_index, permission_key, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, 'admin', 1, NOW())
            ON DUPLICATE KEY UPDATE url = VALUES(url), icon = VALUES(icon), order_index = VALUES(order_index)");
        $stmt->execute([$i[0], $i[1], $i[2], $i[3], $i[4]]);
        echo "Inserted: {$i[0]} (order {$i[4]})" . PHP_EOL;
    }

    echo "Done" . PHP_EOL;
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

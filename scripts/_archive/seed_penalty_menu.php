<?php
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'], $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $r = $pdo->query("SELECT MAX(order_index) m FROM admin_menu_items WHERE section='finance'");
    $maxOrder = (int)$r->fetchColumn();
    $newOrder = max($maxOrder + 5, 45);

    $pdo->prepare("INSERT INTO admin_menu_items (name, url, icon, section, order_index, permission_key, is_active, created_at)
        VALUES (?, ?, ?, 'finance', ?, 'admin', 1, NOW())
        ON DUPLICATE KEY UPDATE url = VALUES(url), icon = VALUES(icon), order_index = VALUES(order_index)")
        ->execute(['EMI Penalties', '/admin/finance/penalties', 'fa-exclamation-triangle', $newOrder]);

    echo "Menu item inserted (order $newOrder)" . PHP_EOL;

    $r = $pdo->query("SELECT id, name, url, section, order_index FROM admin_menu_items WHERE url='/admin/finance/penalties'");
    $row = $r->fetch(PDO::FETCH_ASSOC);
    echo $row ? json_encode($row) . PHP_EOL : "ERROR: menu item not found" . PHP_EOL;

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

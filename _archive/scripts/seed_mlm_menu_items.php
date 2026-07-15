<?php
/**
 * APS Dream Home - Seed MLM Admin Menu Items
 * Run via: php scripts/seed_mlm_menu_items.php
 * Idempotent: INSERT IGNORE using unique URL per section
 */
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';
try {
    $pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $menuItems = [
        ['name' => 'Clawbacks', 'url' => '/admin/mlm/clawbacks', 'icon' => 'fa-undo', 'section' => 'mlm', 'order_index' => 50],
        ['name' => 'Pending Payouts', 'url' => '/admin/mlm/payouts', 'icon' => 'fa-money-bill', 'section' => 'mlm', 'order_index' => 40],
        ['name' => 'Manual Rank Promotion', 'url' => '/admin/mlm/associate-ranks', 'icon' => 'fa-arrow-up', 'section' => 'mlm', 'order_index' => 55],
        ['name' => 'Rank Benefits', 'url' => '/admin/mlm/rank-benefits', 'icon' => 'fa-trophy', 'section' => 'mlm', 'order_index' => 60],
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO admin_menu_items (name, url, icon, section, order_index, is_active, permission_key) VALUES (?, ?, ?, ?, ?, 1, 'admin')");
    $inserted = 0;
    foreach ($menuItems as $item) {
        $stmt->execute([$item['name'], $item['url'], $item['icon'], $item['section'], $item['order_index']]);
        $inserted += $stmt->rowCount();
    }
    echo "✓ MLM menu items: {$inserted} inserted, " . (count($menuItems) - $inserted) . " already existed.\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

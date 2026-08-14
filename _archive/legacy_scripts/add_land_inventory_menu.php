<?php
// Run: php scripts/add_land_inventory_menu.php
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'], $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Check current menu items
    $stmt = $pdo->query("SELECT id, name, url, section, order_index FROM admin_menu_items WHERE section='inventory' OR url LIKE '%land-inventory%' ORDER BY section, order_index");
    $existing = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Existing land/inventory menu items:\n";
    foreach ($existing as $row) {
        echo "  [{$row['id']}] {$row['section']} | order {$row['order_index']} | {$row['name']} -> {$row['url']}\n";
    }
    echo "\n";

    // Insert/Update the 3 new menu items
    $items = [
        [
            'name' => 'Land Acquisition',
            'url'   => '/admin/land-inventory/leads',
            'icon'  => 'fa-mountain',
            'section' => 'inventory',
            'order_index' => 5,
        ],
        [
            'name' => 'Land Acquisitions',
            'url'   => '/admin/land-inventory/acquisitions',
            'icon'  => 'fa-file-contract',
            'section' => 'inventory',
            'order_index' => 6,
        ],
        [
            'name' => 'Land Brokers',
            'url'   => '/admin/land-inventory/brokers',
            'icon'  => 'fa-user-tie',
            'section' => 'inventory',
            'order_index' => 7,
        ],
    ];

    foreach ($items as $it) {
        $check = $pdo->prepare("SELECT id FROM admin_menu_items WHERE url = ?");
        $check->execute([$it['url']]);
        $row = $check->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            echo "  EXISTS: {$it['name']} (id={$row['id']})\n";
            continue;
        }
        $ins = $pdo->prepare("INSERT INTO admin_menu_items (name, url, icon, section, order_index, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
        $ins->execute([$it['name'], $it['url'], $it['icon'], $it['section'], $it['order_index']]);
        echo "  ADDED : {$it['name']} -> {$it['url']}\n";
    }
    echo "\nâœ“ Land inventory menu items synced.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}?>
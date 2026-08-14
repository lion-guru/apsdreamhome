<?php
$config = require dirname(__DIR__) . '/config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$rows = $pdo->query("SELECT id, name, url, section, parent_id FROM admin_menu_items WHERE is_active=1 ORDER BY section, order_index, id")->fetchAll(PDO::FETCH_ASSOC);

// Group by section
$grouped = [];
foreach ($rows as $r) {
    $grouped[$r['section']][] = $r;
}

echo "=== ADMIN MENU ITEMS: " . count($rows) . " total ===\n\n";

foreach ($grouped as $section => $items) {
    echo "SECTION: $section (" . count($items) . " items)\n";
    foreach ($items as $item) {
        echo "  [{$item['id']}] {$item['name']} â†’ {$item['url']}\n";
    }
    echo "\n";
}?>
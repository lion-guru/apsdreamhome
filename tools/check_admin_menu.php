<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check table structure
$cols = $pdo->query("SHOW COLUMNS FROM admin_menu_items")->fetchAll(PDO::FETCH_ASSOC);
echo "=== admin_menu_items COLUMNS ===\n";
foreach ($cols as $c) echo "  {$c['Field']} ({$c['Type']})\n";

// Get all menu items ordered properly
$items = $pdo->query("SELECT * FROM admin_menu_items ORDER BY section, order_index, id")->fetchAll(PDO::FETCH_ASSOC);
echo "\n=== ALL MENU ITEMS (" . count($items) . ") ===\n";
foreach ($items as $item) {
    $children = [];
    if (!empty($item['children'])) {
        $children = json_decode($item['children'], true) ?? [];
    }
    $childCount = is_array($children) ? count($children) : 0;
    echo "\n[{$item['section']}] {$item['name']} -> {$item['url']} (icon: {$item['icon']}, sort: {$item['sort_order']}, children: $childCount)";
    if ($childCount > 0) {
        foreach ($children as $ci => $child) {
            echo "\n  ├─ Child $ci: " . ($child['name'] ?? 'N/A') . ' -> ' . ($child['url'] ?? 'N/A');
        }
    }
}
echo "\n\n=== SECTION SUMMARY ===\n";
$sections = $pdo->query("SELECT section, COUNT(*) as cnt FROM admin_menu_items GROUP BY section ORDER BY section")->fetchAll(PDO::FETCH_ASSOC);
foreach ($sections as $s) echo "  {$s['section']}: {$s['cnt']} items\n";

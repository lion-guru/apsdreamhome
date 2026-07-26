<?php
require_once __DIR__ . '/../vendor/autoload.php';
$db = \App\Core\Database\Database::getInstance();

// Check duplicates
$items = $db->fetchAll("SELECT id, name, url, section, is_active FROM admin_menu_items WHERE url IN ('/admin/visits', '/admin/campaigns') ORDER BY url, section");
foreach ($items as $row) {
    echo "{$row['id']} | {$row['name']} | {$row['url']} | {$row['section']} | active={$row['is_active']}" . PHP_EOL;
}

echo PHP_EOL . "=== EMPLOYEE MENU ITEMS ANALYSIS ===" . PHP_EOL;

// Check employee menu items with empty/dash URLs or section grouping
$empItems = $db->fetchAll("SELECT id, name, url, parent_id, section, order_index FROM admin_menu_items WHERE section = 'employee' ORDER BY order_index");
foreach ($empItems as $item) {
    echo "{$item['id']} | {$item['name']} | {$item['url']} | parent:{$item['parent_id']}" . PHP_EOL;
}

echo PHP_EOL . "=== TOTAL per section ===" . PHP_EOL;
$counts = $db->fetchAll("SELECT section, COUNT(*) as cnt FROM admin_menu_items GROUP BY section ORDER BY cnt DESC");
foreach ($counts as $c) {
    echo "{$c['section']}: {$c['cnt']}" . PHP_EOL;
}
echo "TOTAL: " . array_sum(array_column($counts, 'cnt')) . PHP_EOL;

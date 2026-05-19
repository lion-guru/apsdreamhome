<?php
require 'config/bootstrap.php';
$db = App\Core\Database\Database::getInstance();
$rs = $db->query("SELECT * FROM admin_menu_items ORDER BY section, id");
$items = $rs->fetchAll();

echo "Total Menu Items: " . count($items) . "\n\n";

$bySection = [];
foreach ($items as $item) {
    $sec = $item['section'] ?? 'main';
    $bySection[$sec][] = $item;
}

foreach ($bySection as $section => $items) {
    echo "=== $section (" . count($items) . ") ===\n";
    foreach ($items as $item) {
        echo "  - {$item['name']}: {$item['url']}\n";
    }
    echo "\n";
}
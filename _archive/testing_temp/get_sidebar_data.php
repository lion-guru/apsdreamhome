<?php
header('Content-Type: text/plain; charset=UTF-8');
require_once __DIR__ . '/../config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance();
$items = $db->fetchAll("SELECT * FROM admin_menu_items ORDER BY section, order_index, id");

echo "=== Total Menu Items: " . count($items) . " ===\n\n";

$bySection = [];
foreach ($items as $it) {
    $bySection[$it['section']][] = $it;
}

foreach ($bySection as $sec => $its) {
    echo "=== Section: " . strtoupper($sec) . " ===\n";
    foreach ($its as $it) {
        printf("  [%3d] Order: %3d | %-30s | Url: %-45s | Active: %d\n", 
            $it['id'], $it['order_index'], $it['name'], $it['url'], $it['is_active']);
    }
    echo "\n";
}?>
<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
$items = $pdo->query("SELECT id, name, url, section FROM admin_menu_items ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
echo "Total items: " . count($items) . "\n\n";
$sections = [];
foreach ($items as $i) {
    $sections[$i['section']][] = $i;
}
foreach ($sections as $s => $items) {
    echo "=== $s (" . count($items) . ") ===\n";
    foreach ($items as $i) echo "  {$i['id']}: {$i['name']} → {$i['url']}\n";
    echo "\n";
}

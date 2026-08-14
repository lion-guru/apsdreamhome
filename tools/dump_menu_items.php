<?php
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8','root','');
$stmt = $db->query('SELECT id, section, name, url, icon, parent_id, order_index FROM admin_menu_items WHERE url IS NOT NULL AND url != "" ORDER BY section, order_index');
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sections = [];
foreach($items as $r) { $sections[$r['section']][] = $r; }

echo "Total menu items with URL: " . count($items) . "\n\n";

echo str_pad("ID",6).str_pad("Section",22).str_pad("Name",40).str_pad("URL",55)."Icon\n";
echo str_repeat("-",140)."\n";
foreach($items as $r) {
    echo str_pad($r['id'],6).str_pad($r['section'],22).str_pad($r['name'],40).str_pad($r['url'],55).$r['icon']."\n";
}

echo "\n\n=== BY SECTION ===\n";
foreach($sections as $sec => $rows) {
    echo str_pad($sec,25).": ".count($rows)." items\n";
}

echo "\n\n=== ALL UNIQUE URL PATHS (for route checking) ===\n";
$urls = array_unique(array_map(function($r){ return $r['url']; }, $items));
sort($urls);
foreach($urls as $u) {
    echo $u . "\n";
}?>
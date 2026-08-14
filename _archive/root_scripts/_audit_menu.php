<?php
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome','root','');

// Get columns
echo "--- admin_menu_items columns ---\n";
$cols = $db->query('DESCRIBE admin_menu_items')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo "  " . $c['Field'] . " (" . $c['Type'] . ")\n";
}

// Try name column
$nameCol = 'menu_name';
$try = ['menu_name', 'name', 'title', 'label', 'menu_label', 'item_name', 'menu_title'];
foreach ($try as $col) {
    try {
        $test = $db->query("SELECT $col FROM admin_menu_items LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        echo "\nFound name column: $col = " . ($test[$col] ?? 'NULL') . "\n";
        $nameCol = $col;
        break;
    } catch (Exception $e) {
        // try next
    }
}

// Now get full data
$q = $db->query("SELECT id, $nameCol as item_name, url, section, is_active FROM admin_menu_items ORDER BY section, order_index");
$items = $q->fetchAll(PDO::FETCH_ASSOC);
echo "\n--- All menu items (" . count($items) . ") ---\n";
echo str_pad('ID',4) . ' ' . str_pad('Section',20) . ' ' . str_pad('Name',35) . ' URL' . "\n";
echo str_repeat('-', 120) . "\n";
foreach ($items as $i) {
    echo str_pad($i['id'],4) . ' ' . str_pad($i['section'],20) . ' ' . str_pad($i['item_name'],35) . ' ' . $i['url'] . "\n";
}?>
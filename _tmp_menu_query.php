<?php
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8', 'root', '');
$stmt = $db->query('SELECT section, label, url, icon, parent_id, order_index, is_active FROM admin_menu_items ORDER BY section, order_index');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$groups = [];
foreach ($rows as $r) {
    $groups[$r['section']][] = $r;
}

foreach ($groups as $section => $items) {
    echo "\n=== " . strtoupper($section) . " ===\n";
    foreach ($items as $i) {
        $active = $i['is_active'] ? 'Y' : 'N';
        $parent = $i['parent_id'] ?? '-';
        printf("  [%s] %-40s %-50s icon=%-20s parent=%-4s order=%d\n", $active, $i['label'], $i['url'], $i['icon'], $parent, $i['order_index']);
    }
}

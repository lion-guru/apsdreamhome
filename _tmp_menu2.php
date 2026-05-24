<?php
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8', 'root', '');
$stmt = $db->query('SELECT id, name, section, url, icon, parent_id, order_index, permission_key, is_active FROM admin_menu_items ORDER BY section, parent_id IS NOT NULL DESC, parent_id, order_index');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$groups = [];
$parents = [];
// collect parents first
foreach ($rows as $r) {
    if (!$r['parent_id']) {
        $parents[$r['id']] = $r;
    }
}
$children = [];
foreach ($rows as $r) {
    if ($r['parent_id']) {
        $children[$r['parent_id']][] = $r;
    }
}

foreach ($rows as $r) {
    if ($r['parent_id']) continue; // skip children in top-level
    $active = $r['is_active'] ? 'Y' : 'N';
    printf("  [%s] %-50s (url=%-45s icon=%-20s order=%d perm=%s)\n", $active, $r['name'], $r['url'], $r['icon'], $r['order_index'], $r['permission_key']);
    if (isset($children[$r['id']])) {
        foreach ($children[$r['id']] as $c) {
            $active = $c['is_active'] ? 'Y' : 'N';
            printf("  [%s]   |- %-47s (url=%-45s icon=%-20s order=%d perm=%s)\n", $active, $c['name'], $c['url'], $c['icon'], $c['order_index'], $c['permission_key']);
        }
    }
}

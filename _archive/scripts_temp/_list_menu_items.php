<?php
require_once __DIR__ . '/../app/Core/autoload.php';
$db = \App\Core\Database::getInstance();
$rows = $db->fetchAll('SELECT id, name, url, section FROM admin_menu_items WHERE is_active = 1 ORDER BY order_index');
foreach ($rows as $r) {
    echo $r['id'] . ' | ' . $r['section'] . ' | ' . $r['url'] . ' | ' . $r['name'] . "\n";
}
echo "Total: " . count($rows) . "\n";

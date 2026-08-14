<?php
define('APP_ROOT', dirname(__DIR__));
require 'C:\xampp\htdocs\apsdreamhome\app\core\Autoloader.php';
\App\Core\ConfigService::getInstance();
$db = \App\Core\Database\Database::getInstance();

// Check section='finance' current items
$rows = $db->fetchAll("SELECT id, name, url, order_index FROM admin_menu_items WHERE section = 'finance' ORDER BY order_index");
echo "Existing finance section items: " . count($rows) . PHP_EOL;
foreach ($rows as $r) {
    echo "  #{$r['id']} [{$r['order_index']}] {$r['name']} -> {$r['url']}" . PHP_EOL;
}

echo PHP_EOL . "Inserting Module 3 menu items..." . PHP_EOL;

$items = [
    ['name' => 'Cash Book',          'url' => '/admin/finance/cash-book',         'icon' => 'fa-book',               'order' => 10],
    ['name' => 'Bank Reconciliation','url' => '/admin/finance/reconciliation',    'icon' => 'fa-balance-scale',      'order' => 20],
    ['name' => 'TDS Register',       'url' => '/admin/finance/tds',               'icon' => 'fa-file-invoice-dollar','order' => 30],
    ['name' => 'Vendor Payments',    'url' => '/admin/finance/vendors',           'icon' => 'fa-truck',              'order' => 40],
];

foreach ($items as $it) {
    $exists = $db->fetchOne("SELECT id FROM admin_menu_items WHERE section='finance' AND name=?", [$it['name']]);
    if ($exists) {
        echo "  EXISTS (skip): {$it['name']} #{$exists['id']}" . PHP_EOL;
        continue;
    }
    $db->execute(
        "INSERT INTO admin_menu_items (parent_id, section, name, url, icon, order_index, is_active, permission_key, created_at, updated_at)
         VALUES (NULL, 'finance', ?, ?, ?, ?, 1, 'admin', NOW(), NOW())",
        [$it['name'], $it['url'], $it['icon'], $it['order']]
    );
    $newId = $db->lastInsertId();
    echo "  INSERTED: #{$newId} [{$it['order']}] {$it['name']} -> {$it['url']}" . PHP_EOL;
}

echo PHP_EOL . "Final finance section items:" . PHP_EOL;
$rows = $db->fetchAll("SELECT id, name, url, order_index FROM admin_menu_items WHERE section = 'finance' ORDER BY order_index");
foreach ($rows as $r) {
    echo "  #{$r['id']} [{$r['order_index']}] {$r['name']} -> {$r['url']}" . PHP_EOL;
}?>
<?php
define('APP_ROOT', dirname(__DIR__));
require 'C:\xampp\htdocs\apsdreamhome\app\core\Autoloader.php';
$db = \App\Core\Database\Database::getInstance();
echo "Module 3 menu items in finance section:" . PHP_EOL;
foreach ($db->fetchAll("SELECT id, name, url, order_index, is_active FROM admin_menu_items WHERE url LIKE '/admin/finance/%' ORDER BY order_index") as $r) {
    echo "  #{$r['id']} [{$r['order_index']}] (active=" . ($r['is_active'] ? 'Y' : 'N') . ") {$r['name']} -> {$r['url']}" . PHP_EOL;
}?>
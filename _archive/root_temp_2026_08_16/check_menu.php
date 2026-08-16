<?php
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();
$rows = $db->query("SELECT id, name, url, parent_id, icon, section, order_index FROM admin_menu_items WHERE is_active = 1 ORDER BY order_index")->fetchAll(\PDO::FETCH_ASSOC);
print_r($rows);
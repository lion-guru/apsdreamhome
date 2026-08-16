<?php
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();
$rows = $db->query('SELECT * FROM admin_menu_items WHERE url LIKE "%visits%" OR url LIKE "%api-keys%"')->fetchAll(\PDO::FETCH_ASSOC);
print_r($rows);
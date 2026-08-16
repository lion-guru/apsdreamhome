<?php
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();
$cols = $db->query('SHOW COLUMNS FROM admin_menu_items')->fetchAll(\PDO::FETCH_COLUMN, 0);
print_r($cols);
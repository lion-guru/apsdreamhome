<?php
define('APP_ROOT', dirname(__DIR__));
require 'C:\xampp\htdocs\apsdreamhome\app\core\Autoloader.php';
$db = \App\Core\Database\Database::getInstance();
echo "admin_menu_items schema:" . PHP_EOL;
foreach ($db->fetchAll('DESCRIBE admin_menu_items') as $c) {
    echo "  " . $c['Field'] . " " . $c['Type'] . " null=" . $c['Null'] . " key=" . $c['Key'] . PHP_EOL;
}?>
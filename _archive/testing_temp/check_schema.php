<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance()->getPdo();
$row = $db->query("SELECT * FROM admin_menu_items LIMIT 1")->fetch(PDO::FETCH_ASSOC);
print_r($row);?>
<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance()->getPdo();
$rows = $db->query("SELECT * FROM admin_menu_items WHERE name LIKE '%Notif%' OR name LIKE '%Inquir%' OR name LIKE '%Lead%'")->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);

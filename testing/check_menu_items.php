<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance()->getPdo();
$countMenuItems = $db->query("SELECT COUNT(*) FROM admin_menu_items")->fetchColumn();
echo "Menu Items Count in DB: $countMenuItems\n";

<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance()->getPdo();
$countManager = $db->query("SELECT COUNT(*) FROM admin_role_menu_permissions WHERE role = 'manager'")->fetchColumn();
$countAdmin = $db->query("SELECT COUNT(*) FROM admin_role_menu_permissions WHERE role = 'admin'")->fetchColumn();
echo "Permissions for manager: $countManager\n";
echo "Permissions for admin: $countAdmin\n";?>
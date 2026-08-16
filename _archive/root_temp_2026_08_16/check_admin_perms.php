<?php
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();

// Check admin permissions
$rows = $db->query("SELECT * FROM admin_role_menu_permissions WHERE role = 'admin'")->fetchAll(\PDO::FETCH_ASSOC);
print_r($rows);

// Also check for super_admin
$rows2 = $db->query("SELECT * FROM admin_role_menu_permissions WHERE role = 'super_admin'")->fetchAll(\PDO::FETCH_ASSOC);
print_r($rows2);
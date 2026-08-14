<?php
define('APP_ROOT', dirname(__DIR__));
require 'C:\xampp\htdocs\apsdreamhome\app\core\Autoloader.php';
$db = \App\Core\Database\Database::getInstance();
echo "Test users:" . PHP_EOL;
foreach ($db->fetchAll("SELECT id, name, email, role FROM users WHERE name LIKE '%test%' OR email LIKE '%test%' OR role IN ('super_admin','admin') LIMIT 10") as $r) {
    echo "  #{$r['id']} {$r['name']} <{$r['email']}> role={$r['role']}" . PHP_EOL;
}?>
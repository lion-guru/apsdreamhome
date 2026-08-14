<?php
define('APP_ROOT', dirname(__DIR__));
require 'C:\xampp\htdocs\apsdreamhome\app\core\Autoloader.php';
$db = \App\Core\Database\Database::getInstance();
foreach ($db->fetchAll('DESCRIBE `expense_approvals`') as $c) {
    echo "  {$c['Field']} {$c['Type']} " . ($c['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . PHP_EOL;
}?>
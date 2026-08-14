<?php
define('APP_ROOT', dirname(__DIR__));
require 'C:\xampp\htdocs\apsdreamhome\app\core\Autoloader.php';
$db = \App\Core\Database\Database::getInstance();

// Both tables
echo "bank_accounts_master: " . $db->fetchOne("SELECT COUNT(*) c FROM bank_accounts_master")['c'] . " rows" . PHP_EOL;
echo "bank_accounts:        " . $db->fetchOne("SELECT COUNT(*) c FROM bank_accounts")['c'] . " rows" . PHP_EOL;

echo PHP_EOL . "Latest 5 in bank_accounts_master:" . PHP_EOL;
foreach ($db->fetchAll("SELECT id, account_name, bank_name, account_number, created_at FROM bank_accounts_master ORDER BY id DESC LIMIT 5") as $r) {
    echo "  #{$r['id']} {$r['account_name']} | {$r['bank_name']} | {$r['account_number']} | {$r['created_at']}" . PHP_EOL;
}?>
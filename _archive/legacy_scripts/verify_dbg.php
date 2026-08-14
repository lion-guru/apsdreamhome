<?php
define('APP_ROOT', dirname(__DIR__));
require 'C:\xampp\htdocs\apsdreamhome\app\core\Autoloader.php';
$db = \App\Core\Database\Database::getInstance();
$row = $db->fetchOne("SELECT * FROM bank_accounts WHERE bank_name = ?", ['DBG Bank']);
echo 'DBG Bank row: ' . ($row ? 'YES id=' . $row['id'] . ' name=' . $row['account_name'] : 'NO') . PHP_EOL;
echo 'Total bank_accounts: ' . $db->fetchOne("SELECT COUNT(*) c FROM bank_accounts")['c'] . PHP_EOL;

// All recent bank accounts
echo PHP_EOL . "Latest 5:" . PHP_EOL;
foreach ($db->fetchAll("SELECT id, account_name, bank_name, account_number, created_at FROM bank_accounts ORDER BY id DESC LIMIT 5") as $r) {
    echo "  #{$r['id']} {$r['account_name']} | {$r['bank_name']} | {$r['account_number']} | {$r['created_at']}" . PHP_EOL;
}?>
<?php
define('APP_ROOT', dirname(__DIR__));
require 'C:\xampp\htdocs\apsdreamhome\app\core\Autoloader.php';
$db = \App\Core\Database\Database::getInstance();
$tables = ['tds_register', 'gst_transactions', 'expenses', 'bank_accounts', 'cheque_register', 'petty_cash', 'vendor_payments', 'payment_transactions', 'demand_letter_templates'];
foreach ($tables as $t) {
    echo "=== $t ===" . PHP_EOL;
    foreach ($db->fetchAll("DESCRIBE `$t`") as $c) {
        $null = $c['Null'] === 'YES' ? 'NULL' : 'NOT NULL';
        $def = $c['Default'] === null ? '' : " DEFAULT '{$c['Default']}'";
        echo "  {$c['Field']} {$c['Type']} $null$def" . PHP_EOL;
    }
    echo PHP_EOL;
}?>
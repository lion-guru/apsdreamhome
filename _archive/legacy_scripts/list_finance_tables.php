<?php
define('APP_ROOT', dirname(__DIR__));
require 'C:\xampp\htdocs\apsdreamhome\app\core\Autoloader.php';
$db = \App\Core\Database\Database::getInstance();
$rows = $db->fetchAll("SHOW TABLES");
$financeTables = [];
foreach ($rows as $r) {
    $t = array_values($r)[0];
    if (preg_match('/(cash|petty|cheque|recon|tds|gst|expense|vendor|payment|forecast|voucher|account|book|finance|bank)/i', $t)) {
        $financeTables[] = $t;
    }
}
sort($financeTables);
echo "Module 3 / finance-related tables (" . count($financeTables) . "):" . PHP_EOL;
foreach ($financeTables as $t) {
    $c = $db->fetchOne("SELECT COUNT(*) AS c FROM `$t`")['c'];
    echo "  $t : $c rows" . PHP_EOL;
}?>
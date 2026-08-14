<?php
define('APP_ROOT', dirname(__DIR__));
require 'C:\xampp\htdocs\apsdreamhome\app\core\Autoloader.php';
$db = \App\Core\Database\Database::getInstance();
foreach ($db->fetchAll("SHOW TABLES") as $r) {
    $t = array_values($r)[0];
    if (stripos($t, 'demand') !== false || stripos($t, 'letter') !== false || stripos($t, 'template') !== false || stripos($t, 'forecast') !== false || stripos($t, 'voucher') !== false || stripos($t, 'reconciliation') !== false) {
        echo $t . " : " . $db->fetchOne("SELECT COUNT(*) c FROM `$t`")['c'] . " rows" . PHP_EOL;
    }
}?>
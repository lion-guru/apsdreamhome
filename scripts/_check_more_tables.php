<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();

$tables = ['ai_agent_logs', 'ai_calling_schedule'];
foreach ($tables as $t) {
    $r = $db->query("SELECT COUNT(*) as c FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = '$t'");
    $row = $r->fetch();
    if ($row['c'] > 0) {
        $cols = $db->query("SHOW COLUMNS FROM `$t` WHERE Field='tenant_id'");
        $hasTid = $cols->fetch() !== false;
        echo "$t: EXISTS, tenant_id=" . ($hasTid ? 'YES' : 'NO') . "\n";
    } else {
        echo "$t: DOES NOT EXIST\n";
    }
}

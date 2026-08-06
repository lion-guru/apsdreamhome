<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();

$tables = ['site_visits', 'crm_interactions', 'crm_tasks', 'sms_queue', 'payments', 'emi_payments', 'ai_chat_messages', 'agent_task_logs', 'agent_escalations', 'agent_escalations'];
foreach ($tables as $t) {
    $r = $db->query("SELECT COUNT(*) as c FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = '$t'");
    $row = $r->fetch();
    if ($row['c'] > 0) {
        $cols = $db->query("SHOW COLUMNS FROM `$t` WHERE Field='tenant_id'");
        $hasTid = $cols->fetch() !== false;
        $r2 = $db->query("SELECT COUNT(*) as c FROM `$t`");
        $row2 = $r2->fetch();
        echo "$t: EXISTS(rows=$row2[c]), tenant_id=" . ($hasTid ? 'YES' : 'NO') . "\n";
    } else {
        echo "$t: DOES NOT EXIST\n";
    }
}

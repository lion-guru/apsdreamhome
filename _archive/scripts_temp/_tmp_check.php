<?php
require 'vendor/autoload.php';
$db = \App\Core\Database\Database::getInstance();
$tables = ['ai_calling_schedule','ai_call_sessions','calls_log','leads'];
foreach($tables as $t){
    try {
        $c = $db->fetch("SELECT COUNT(*) as c FROM $t");
        echo "$t: {$c['c']} rows\n";
    } catch(\Throwable $e){
        echo "$t: MISSING (".$e->getMessage().")\n";
    }
}

<?php
require 'vendor/autoload.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();
$tables = ['ai_customer_segments','ai_chatbot_feedback','ai_config'];
foreach ($tables as $t) {
    try {
        $c = $db->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "$t: $c rows\n";
    } catch (Exception $e) {
        echo "$t: MISSING\n";
    }
}?>
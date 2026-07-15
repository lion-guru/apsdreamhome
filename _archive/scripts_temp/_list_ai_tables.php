<?php
require 'vendor/autoload.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();
$tables = $db->query("SHOW TABLES LIKE 'ai%'")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $t) {
    $c = $db->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    echo "$t: $c rows\n";
}
echo "\n=== Non-ai AI tables ===\n";
$other = ['chatbot_sessions','chatbot_messages','property_chatbot_sessions','property_chatbot_messages'];
foreach ($other as $t) {
    try {
        $c = $db->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "$t: $c rows\n";
    } catch (Exception $e) {
        echo "$t: MISSING\n";
    }
}
<?php
require 'vendor/autoload.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();
$tables = ['ai_lead_scores','ai_property_recommendations','ai_chatbot_conversations','ai_activity_log','ai_prediction_accuracy'];
foreach ($tables as $t) {
    try {
        $c = $db->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "$t: $c rows\n";
    } catch (Exception $e) {
        echo "$t: MISSING - " . $e->getMessage() . "\n";
    }
}
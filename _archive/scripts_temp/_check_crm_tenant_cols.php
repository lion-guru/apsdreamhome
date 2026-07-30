<?php
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/config/bootstrap.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();
$tables = ['crm_lead_sources_extended', 'crm_form_submissions', 'crm_tasks', 'crm_assignments', 'crm_interactions', 'crm_campaigns', 'crm_lead_forms', 'lead_deals', 'lead_activities', 'lead_notes', 'lead_scores', 'crm_segments'];
foreach ($tables as $t) {
    try {
        $r = $db->query("SHOW COLUMNS FROM `$t` LIKE 'tenant_id'")->fetch();
        echo "$t: " . ($r ? 'HAS tenant_id' : 'NO tenant_id') . PHP_EOL;
    } catch (Exception $e) {
        echo "$t: TABLE MISSING" . PHP_EOL;
    }
}

<?php
require_once __DIR__ . '/../app/Core/autoload.php';
$db = \App\Core\Database::getInstance();
$tables = ['leads', 'properties', 'plots', 'bookings', 'plot_bookings', 'users', 'user_properties', 'lead_deals', 'crm_interactions', 'crm_tasks', 'crm_campaigns', 'crm_lead_forms', 'crm_assignments'];
foreach ($tables as $t) {
    try {
        $cols = $db->fetchAll("SHOW COLUMNS FROM `$t` LIKE 'tenant_id'");
        echo $t . ': ' . (count($cols) > 0 ? 'HAS tenant_id' : 'NO tenant_id') . "\n";
    } catch (\Exception $e) {
        echo $t . ': TABLE NOT FOUND (' . $e->getMessage() . ")\n";
    }
}

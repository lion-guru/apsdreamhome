<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$tables = ['crm_interactions', 'crm_tasks', 'lead_deals', 'lead_activities', 'crm_segments', 'crm_lead_scores_history', 'leads'];

foreach ($tables as $t) {
    $stmt = $pdo->query("SHOW INDEX FROM `$t` WHERE Column_name = 'tenant_id'");
    if ($stmt->rowCount() > 0) {
        echo "  $t: tenant_id index EXISTS\n";
    } else {
        try {
            $pdo->exec("ALTER TABLE `$t` ADD INDEX idx_tenant_id (tenant_id)");
            echo "  $t: index CREATED\n";
        } catch (Throwable $e) {
            echo "  $t: ERROR - " . $e->getMessage() . "\n";
        }
    }
}
echo "Done.\n";?>
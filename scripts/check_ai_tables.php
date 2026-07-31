<?php
require __DIR__.'/../config/bootstrap.php';
$pdo = \App\Core\Database\Database::getInstance()->getConnection();
$tables = ['crm_interactions','plot_bookings','colonies','plots','crm_tasks','agent_task_logs','agent_escalations','crm_meetings','crm_interactions','lead_activities'];
foreach ($tables as $t) {
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM `$t`")->fetchAll(PDO::FETCH_COLUMN);
        $hasTenant = in_array('tenant_id', $cols);
        echo $t . ': tenant_id=' . ($hasTenant ? 'YES' : 'NO') . PHP_EOL;
    } catch (\Throwable $e) {
        echo $t . ': ERROR - table not found' . PHP_EOL;
    }
}
echo "Done.\n";
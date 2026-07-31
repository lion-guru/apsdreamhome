<?php
require __DIR__ . '/config/bootstrap.php';
$pdo = App\Core\Database\Database::getInstance()->getConnection();

$tables = ['mlm_monthly_incentives', 'lead_scores', 'crm_meetings', 'lead_assignment_approvals', 'mlm_goals', 'mlm_goal_progress'];
foreach ($tables as $t) {
    $cols = $pdo->query("SHOW COLUMNS FROM `$t`")->fetchAll(PDO::FETCH_COLUMN, 0);
    echo "$t: " . implode(', ', $cols) . "\n";
    echo "  has tenant_id: " . (in_array('tenant_id', $cols) ? 'YES' : 'NO') . "\n\n";
}
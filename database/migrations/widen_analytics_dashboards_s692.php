<?php
/**
 * Widen analytics_dashboards to the designed contract so the live
 * AnalyticsService::listDashboards/createDashboard pathway functions end-to-end.
 *
 * Additive only. Existing rows are zero (stub) so this is zero-risk.
 */
$path = 'C:/xampp/htdocs/apsdreamhome';
require_once $path . '/config/bootstrap.php';
$db = App\Core\Database\Database::getInstance();

$cols = $db->fetchAll('SHOW COLUMNS FROM analytics_dashboards');
$have = array_map(fn($c) => $c['Field'], $cols);
$add = [];
$have_set = array_flip($have);
$wanted = [
    'user_id'    => 'BIGINT NULL AFTER tenant_id',
    'name'       => "VARCHAR(255) NOT NULL DEFAULT '' AFTER user_id",
    'widgets'    => 'LONGTEXT NULL AFTER name',
    'is_public'  => 'TINYINT(1) NOT NULL DEFAULT 0 AFTER widgets',
];
foreach ($wanted as $col => $def) {
    if (!isset($have_set[$col])) $add[] = "ADD COLUMN `$col` $def";
}

if (empty($add)) {
    echo "SKIP: all columns already present.\n";
    exit;
}

$sql = 'ALTER TABLE analytics_dashboards ' . implode(', ', $add);
try {
    $db->query($sql);
    echo "WIDENED analytics_dashboards.\n";
} catch (Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

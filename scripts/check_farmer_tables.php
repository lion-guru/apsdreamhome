<?php
require __DIR__.'/../config/bootstrap.php';
$pdo = \App\Core\Database\Database::getInstance()->getConnection();
$tables = ['land_allocations','documents','farmer_commissions','farmer_activities','land_plots','farmer_commission_structures'];
foreach ($tables as $t) {
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM `$t`")->fetchAll(PDO::FETCH_COLUMN);
        $hasTenant = in_array('tenant_id', $cols);
        echo $t . ': tenant_id=' . ($hasTenant ? 'YES' : 'NO') . ' (' . count($cols) . ' cols: ' . implode(',', $cols) . ')' . PHP_EOL;
    } catch (\Throwable $e) {
        echo $t . ': ERROR - ' . $e->getMessage() . PHP_EOL;
    }
}
echo "Done.\n";
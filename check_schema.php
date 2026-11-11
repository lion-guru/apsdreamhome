<?php
require_once __DIR__ . '/config/bootstrap.php';
$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$tables = ['users', 'plot_bookings', 'payments', 'wallet_packages', 'wallet_points', 'mlm_commission_ledger', 'leads', 'maintenance_dues', 'township_resident_grievances', 'site_visits', 'township_water_logs', 'township_genset_logs', 'township_cctv_logs', 'township_waste_logs', 'township_horticulture_logs', 'township_security_rosters', 'township_security_incidents', 'township_lighting_logs', 'township_rwh_logs', 'colonies', 'plots', 'associates', 'mlm_profiles'];

foreach($tables as $table) {
    try {
        echo "\n=== TABLE: $table ===\n";
        $cols = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
        foreach($cols as $c) {
            echo "  {$c['Field']} ({$c['Type']}) " . ($c['Null'] === 'NO' ? 'NOT NULL' : 'NULL') . " " . ($c['Key'] === 'PRI' ? 'PRIMARY KEY' : ($c['Key'] === 'UNI' ? 'UNIQUE' : '')) . " DEFAULT: " . ($c['Default'] ?? 'NULL') . "\n";
        }
    } catch(\Throwable $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
    }
}
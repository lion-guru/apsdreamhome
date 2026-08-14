<?php
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$tables = ['data_change_log','import_jobs','business_overview','property_performance','property_summary','revenue_summary','user_summary','audit_log_archive','workflow_actions'];

$dropped = 0;
foreach ($tables as $t) {
    try {
        $pdo->exec("DROP TABLE IF EXISTS `$t`");
        echo "DROPPED: $t\n";
        $dropped++;
    } catch (\Throwable $e) {
        echo "FAILED: $t - {$e->getMessage()}\n";
    }
}

$count = $pdo->query('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA="apsdreamhome"')->fetchColumn();
echo "\nDropped: $dropped, Remaining: $count\n";?>
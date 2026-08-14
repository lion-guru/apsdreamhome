<?php
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// These are VIEWS, not tables â€” DROP TABLE doesn't work
$views = ['business_overview','property_performance','property_summary','revenue_summary','user_summary'];

foreach ($views as $v) {
    try {
        $pdo->exec("DROP VIEW IF EXISTS `$v`");
        echo "DROPPED VIEW: $v\n";
    } catch (\Throwable $e) {
        echo "FAILED: $v - {$e->getMessage()}\n";
    }
}

$count = $pdo->query('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA="apsdreamhome"')->fetchColumn();
echo "\nRemaining tables: $count\n";

// Also count views
$vcount = $pdo->query("SELECT COUNT(*) FROM information_schema.VIEWS WHERE TABLE_SCHEMA='apsdreamhome'")->fetchColumn();
echo "Remaining views: $vcount\n";?>
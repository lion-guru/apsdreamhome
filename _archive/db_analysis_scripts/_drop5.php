<?php
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

foreach (['business_overview','property_performance','property_summary','revenue_summary','user_summary'] as $t) {
    $pdo->exec("DROP TABLE IF EXISTS `$t`");
    echo "DROPPED: $t\n";
}
$count = $pdo->query('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA="apsdreamhome"')->fetchColumn();
echo "Remaining: $count\n";?>
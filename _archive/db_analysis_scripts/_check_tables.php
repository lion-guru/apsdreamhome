<?php
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$check = ['data_change_log','import_jobs','business_overview','property_performance','property_summary','revenue_summary','user_summary','audit_log_archive','workflow_actions'];
foreach ($check as $t) {
    $exists = $pdo->query("SHOW TABLES LIKE '$t'")->fetch();
    echo "$t: " . ($exists ? 'EXISTS' : 'GONE') . "\n";
}?>
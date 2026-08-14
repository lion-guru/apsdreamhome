<?php
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'], $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Check mlm_levels
$r = $pdo->query("SHOW TABLES LIKE 'mlm_levels'");
echo "mlm_levels table: " . ($r->fetch() ? 'EXISTS' : 'NOT FOUND') . "\n";

// Check mlm_commission_levels
$r = $pdo->query("SHOW TABLES LIKE 'mlm_commission_levels'");
echo "mlm_commission_levels table: " . ($r->fetch() ? 'EXISTS' : 'NOT FOUND') . "\n";

// Check commissions table
$r = $pdo->query("SHOW TABLES LIKE 'commissions'");
echo "commissions table: " . ($r->fetch() ? 'EXISTS' : 'NOT FOUND') . "\n";

// Check mlm_commission_ledger
$r = $pdo->query("SHOW TABLES LIKE 'mlm_commission_ledger'");
echo "mlm_commission_ledger table: " . ($r->fetch() ? 'EXISTS' : 'NOT FOUND') . "\n";

// Check mlm_associate_metrics
$r = $pdo->query("SHOW TABLES LIKE 'mlm_associate_metrics'");
echo "mlm_associate_metrics table: " . ($r->fetch() ? 'EXISTS' : 'NOT FOUND') . "\n";

// Check mlm_leaderboard_snapshots
$r = $pdo->query("SHOW TABLES LIKE 'mlm_leaderboard_snapshots'");
echo "mlm_leaderboard_snapshots table: " . ($r->fetch() ? 'EXISTS' : 'NOT FOUND') . "\n";

// Check mlm_joining_packages
$r = $pdo->query("SHOW TABLES LIKE 'mlm_joining_packages'");
echo "mlm_joining_packages table: " . ($r->fetch() ? 'EXISTS' : 'NOT FOUND') . "\n";

// Check mlm_associate_registrations
$r = $pdo->query("SHOW TABLES LIKE 'mlm_associate_registrations'");
echo "mlm_associate_registrations table: " . ($r->fetch() ? 'EXISTS' : 'NOT FOUND') . "\n";?>
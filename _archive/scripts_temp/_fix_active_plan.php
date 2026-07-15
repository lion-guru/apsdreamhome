<?php
$config = require dirname(__DIR__) . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "=== Deactivating duplicate plans (keeping plan #6 as primary) ===\n";

// Deactivate plans 7-10
$pdo->exec("UPDATE mlm_commission_plans SET status = 'inactive' WHERE id IN (7,8,9,10)");
echo "  Deactivated plans #7, #8, #9, #10\n";

// Verify only one active plan
$r = $pdo->query("SELECT id, plan_name, status FROM mlm_commission_plans WHERE status = 'active'");
$active = $r->fetchAll(PDO::FETCH_ASSOC);
echo "  Active plans: " . count($active) . "\n";
foreach ($active as $p) {
    echo "    #{$p['id']} {$p['plan_name']} ({$p['status']})\n";
}

echo "\n=== DONE ===\n";

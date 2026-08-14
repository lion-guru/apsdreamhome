<?php
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'], $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$tables = [
    'agent_commission_rates', 'mlm_rank_benefits', 'mlm_commission_ledger',
    'mlm_payouts', 'mlm_commission_levels', 'telecaller_commission_rules',
    'telecaller_commissions', 'mlm_payout_batches', 'revenue_commission_daily',
    'mlm_commission_ledger_legacy'
];

echo '=== Table existence check ===' . PHP_EOL;
foreach ($tables as $t) {
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$t]);
    $exists = $stmt->rowCount() > 0;
    
    $count = 0;
    if ($exists) {
        $r = $pdo->query("SELECT COUNT(*) as c FROM `$t`");
        $count = $r->fetch()['c'];
    }
    echo ($exists ? 'EXISTS' : 'MISSING') . " ($count rows): $t" . PHP_EOL;
}?>
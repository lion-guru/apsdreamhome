<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$rows = $pdo->query("SELECT commission_type, COUNT(*) as cnt FROM mlm_commission_ledger GROUP BY commission_type ORDER BY cnt DESC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo sprintf("  %-25s %d\n", $r['commission_type'], $r['cnt']);
}

// Check invalid types
$invalid = $pdo->query("
    SELECT commission_type, COUNT(*) as cnt FROM mlm_commission_ledger
    WHERE commission_type NOT IN ('direct_sale','team_bonus','performance_bonus','escrow','clawback','salary_incentive','override','level_bonus','slab_differential','milestone_escrow')
    GROUP BY commission_type
")->fetchAll(PDO::FETCH_ASSOC);
echo "\nInvalid types:\n";
foreach ($invalid as $r) {
    echo sprintf("  %-25s %d\n", $r['commission_type'], $r['cnt']);
}
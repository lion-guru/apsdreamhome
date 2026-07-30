<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

echo "=== mlm_rank_benefits columns ===\n";
$cols = $pdo->query('SHOW COLUMNS FROM mlm_rank_benefits')->fetchAll(PDO::FETCH_COLUMN);
echo implode(', ', $cols) . "\n";

echo "\n=== mlm_rank_benefits data ===\n";
$rows = $pdo->query('SELECT * FROM mlm_rank_benefits ORDER BY min_leg_count ASC, min_qualifying_volume ASC')->fetchAll(PDO::FETCH_ASSOC);
echo count($rows) . " rows\n";
foreach ($rows as $r) echo "  " . $r['rank_name'] . " | legs>=" . $r['min_leg_count'] . " vol>=" . $r['min_qualifying_volume'] . " | direct=" . ($r['direct_pct'] ?? $r['direct_commission_pct'] ?? 'N/A') . "% L1=" . ($r['l1_pct'] ?? 'N/A') . "% L2=" . ($r['l2_pct'] ?? 'N/A') . "% L3=" . ($r['l3_pct'] ?? 'N/A') . "%\n";

echo "\n=== mlm_commission_levels ===\n";
$rows = $pdo->query('SELECT * FROM mlm_commission_levels ORDER BY level ASC')->fetchAll(PDO::FETCH_ASSOC);
echo count($rows) . " rows\n";
foreach ($rows as $r) echo "  level=" . $r['level'] . " name=" . ($r['name'] ?? 'N/A') . " rate=" . ($r['rate'] ?? 'N/A') . "\n";

echo "\n=== DIFFERENTIAL ISSUES ===\n";
echo "DifferentialCommissionCalculator casts current_level to (int) — 'Ass.' becomes 0\n";
echo "AssociateAuthController sets current_level to 1 (integer) — should be 'Ass.'\n";
echo "MLMCommissionEngine RANK_ORDER = lowercase ['associate','bronze','silver','gold','platinum','diamond']\n";
echo "mlm_rank_benefits uses ['Ass.','Sr. Ass.','BDM','Sr. BDM','V.P.','President','Site Manager']\n";
echo "mlm_levels uses ['Associate','Bronze','Silver','Gold','Platinum','Diamond','Crown','Ambassador','Royal Ambassador','Global Director']\n";
echo "\nTHREE DIFFERENT NAMING CONVENTIONS!\n";

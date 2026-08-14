<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

echo "=== mlm_levels table ===\n";
$rows = $pdo->query('SELECT * FROM mlm_levels ORDER BY level_number ASC')->fetchAll(PDO::FETCH_ASSOC);
echo count($rows) . " rows\n";
foreach ($rows as $r) echo "  " . $r['level_number'] . ': ' . $r['level_name'] . "\n";

echo "\n=== mlm_rank_benefits table ===\n";
$rows = $pdo->query('SELECT rank_name, min_leg_count, min_qualifying_volume, direct_commission_pct, l1_pct, l2_pct, l3_pct FROM mlm_rank_benefits ORDER BY min_leg_count ASC, min_qualifying_volume ASC')->fetchAll(PDO::FETCH_ASSOC);
echo count($rows) . " rows\n";
foreach ($rows as $r) echo "  " . $r['rank_name'] . " | legs>=" . $r['min_leg_count'] . " vol>=" . $r['min_qualifying_volume'] . " | direct=" . $r['direct_commission_pct'] . "% L1=" . $r['l1_pct'] . "% L2=" . $r['l2_pct'] . "% L3=" . $r['l3_pct'] . "%\n";

echo "\n=== MLMCommissionEngine::RANK_ORDER ===\n";
echo "associate, bronze, silver, gold, platinum, diamond\n";

echo "\n=== mlm_commission_levels table ===\n";
$rows = $pdo->query('SELECT * FROM mlm_commission_levels ORDER BY level ASC')->fetchAll(PDO::FETCH_ASSOC);
echo count($rows) . " rows\n";
foreach ($rows as $r) echo "  level=" . $r['level'] . " rate=" . ($r['rate'] ?? 'N/A') . " name=" . ($r['name'] ?? 'N/A') . "\n";?>
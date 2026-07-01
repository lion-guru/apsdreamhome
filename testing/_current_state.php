<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');

echo "=== 1. mlm_profiles.current_level ===\n";
$rows = $pdo->query("SELECT current_level, COUNT(*) as cnt FROM mlm_profiles GROUP BY current_level ORDER BY cnt DESC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  '" . ($r['current_level'] ?: '(empty)') . "' => {$r['cnt']}\n";

echo "\n=== 2. mlm_rank_benefits ===\n";
$r = $pdo->query("SHOW COLUMNS FROM mlm_rank_benefits LIKE 'rank_name'")->fetch(PDO::FETCH_ASSOC);
echo "  rank_name type: " . $r['Type'] . "\n";
$cols = $pdo->query("SHOW COLUMNS FROM mlm_rank_benefits")->fetchAll(PDO::FETCH_COLUMN);
echo "  Columns: " . implode(", ", $cols) . "\n";
$r2 = $pdo->query("SELECT * FROM mlm_rank_benefits ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($r2 as $row) {
    echo "  #{$row['id']} {$row['rank_name']} | direct=" . ($row['direct_sale_pct'] ?? 'NULL') . "% l1={$row['l1_pct']}% l2={$row['l2_pct']}% l3={$row['l3_pct']}%\n";
}

echo "\n=== 3. mlm_commission_ledger columns ===\n";
$r = $pdo->query("SHOW COLUMNS FROM mlm_commission_ledger")->fetchAll(PDO::FETCH_COLUMN);
$newCols = ['source_user_name', 'payment_amount', 'rank_at_time', 'period'];
foreach ($newCols as $c) echo "  $c: " . (in_array($c, $r) ? 'EXISTS' : 'MISSING') . "\n";

echo "\n=== 4. mlm_commission_ledger backfill ===\n";
$rows = $pdo->query("SELECT source_user_name IS NULL as no_name, period IS NULL as no_period, COUNT(*) as cnt FROM mlm_commission_ledger GROUP BY no_name, no_period")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  name_null=" . ($r['no_name'] ? 'Y' : 'N') . " period_null=" . ($r['no_period'] ? 'Y' : 'N') . " cnt={$r['cnt']}\n";

echo "\n=== 5. mlm_network_tree unique index ===\n";
$idx = $pdo->query("SHOW INDEX FROM mlm_network_tree WHERE Key_name != 'PRIMARY'")->fetchAll(PDO::FETCH_ASSOC);
foreach ($idx as $i) echo "  {$i['Column_name']} -> {$i['Key_name']} (unique=" . ($i['Non_unique'] == 0 ? 'YES' : 'no') . ")\n";

echo "\n=== 6. associates.level ===\n";
$rows = $pdo->query("SELECT level, COUNT(*) as cnt FROM associates GROUP BY level")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  '" . ($r['level'] ?: '(empty)') . "' => {$r['cnt']}\n";

echo "\n=== 7. mlm_commission_levels table ===\n";
$exists = $pdo->query("SHOW TABLES LIKE 'mlm_commission_levels'")->fetchAll();
echo "  Table exists: " . (count($exists) > 0 ? 'YES' : 'NO') . "\n";
if (count($exists) > 0) {
    $cols = $pdo->query("SHOW COLUMNS FROM mlm_commission_levels")->fetchAll(PDO::FETCH_COLUMN);
    echo "  Columns: " . implode(", ", $cols) . "\n";
    $rows = $pdo->query("SELECT * FROM mlm_commission_levels ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        echo "  id={$r['id']} " . implode(' | ', array_map(fn($k,$v) => "$k=$v", array_keys($r), array_values($r))) . "\n";
    }
}

echo "\n=== 8. MLMCommissionEngine::RANK_ORDER (code) ===\n";
echo "  ['associate','bronze','silver','gold','platinum','diamond'] (6 ranks)\n";
echo "  DB ENUM: Ass., Sr. Ass., BDM, Sr. BDM, V.P., President, Site Manager (7 ranks)\n";
echo "  NO MATCH → evaluateRankPromotion() ALWAYS FAILS\n";

echo "\n=== 9. Users with role=associate but no mlm_profile ===\n";
$rows = $pdo->query("
    SELECT u.id, u.name, u.email, m.id as mlm_id, m.current_level
    FROM users u
    LEFT JOIN mlm_profiles m ON m.user_id = u.id
    WHERE u.role = 'associate'
    ORDER BY u.id
")->fetchAll(PDO::FETCH_ASSOC);
$missing = 0;
foreach ($rows as $r) {
    if (!$r['mlm_id']) {
        echo "  MISSING: user {$r['id']} {$r['name']} ({$r['email']})\n";
        $missing++;
    }
}
if ($missing == 0) echo "  All associates have profiles.\n";

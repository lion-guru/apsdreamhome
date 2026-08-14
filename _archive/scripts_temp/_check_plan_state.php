<?php
$config = require dirname(__DIR__) . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "=== mlm_commission_plans ===\n";
$r = $pdo->query("SELECT id, plan_name, plan_code, version, status, global_cap_pct, track_a_pct, track_b_pct, track_c_pct, royalty_pool_pct FROM mlm_commission_plans ORDER BY id");
while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
    echo "  #{$row['id']} | {$row['plan_name']} | v{$row['version']} | {$row['status']} | cap={$row['global_cap_pct']}% | A={$row['track_a_pct']}% B={$row['track_b_pct']}% C={$row['track_c_pct']}% | royalty={$row['royalty_pool_pct']}%\n";
}

echo "\n=== mlm_plan_levels ===\n";
$r = $pdo->query("SELECT l.id, l.plan_id, l.level_order, l.level_name, l.direct_commission, l.team_commission, l.level_bonus FROM mlm_plan_levels l ORDER BY l.plan_id, l.level_order");
while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
    echo "  plan#{$row['plan_id']} | L{$row['level_order']} {$row['level_name']} | direct={$row['direct_commission']}% team={$row['team_commission']}% bonus={$row['level_bonus']}%\n";
}

echo "\n=== mlm_rank_slabs ===\n";
$r = $pdo->query("SELECT id, rank_slug, rank_name, min_gbv, max_gbv, commission_rate FROM mlm_rank_slabs ORDER BY min_gbv");
while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$row['rank_slug']} ({$row['rank_name']}) | â‚¹" . number_format($row['min_gbv']) . " - " . ($row['max_gbv'] > 0 ? 'â‚¹' . number_format($row['max_gbv']) : 'âˆž') . " | {$row['commission_rate']}%\n";
}

echo "\n=== mlm_commission_ledger (counts by type) ===\n";
$r = $pdo->query("SELECT commission_type, COUNT(*) as cnt, SUM(amount) as total FROM mlm_commission_ledger GROUP BY commission_type ORDER BY total DESC");
while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$row['commission_type']}: {$row['cnt']} entries, â‚¹" . number_format($row['total']) . "\n";
}

echo "\n=== commission_recalculations ===\n";
$count = $pdo->query("SELECT COUNT(*) FROM commission_recalculations")->fetchColumn();
echo "  {$count} entries\n";

echo "\n=== mlm_commission_ledger new cols ===\n";
$r = $pdo->query("SELECT plan_id, plan_version, calculation_engine FROM mlm_commission_ledger LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if ($r) {
    echo "  plan_id=" . var_export($r['plan_id'], true) . " plan_version=" . var_export($r['plan_version'], true) . " engine=" . var_export($r['calculation_engine'], true) . "\n";
}?>
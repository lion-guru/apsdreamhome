<?php
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "=== ALL MLM SETTINGS ===" . PHP_EOL;
$all = $pdo->query("SELECT setting_key, setting_value, description FROM mlm_settings ORDER BY setting_key")->fetchAll(PDO::FETCH_ASSOC);
foreach ($all as $a) {
    echo "  {$a['setting_key']}: {$a['setting_value']}";
    if (!empty($a['description'])) echo "  ({$a['description']})";
    echo PHP_EOL;
}

echo PHP_EOL . "=== RANK RATES ===" . PHP_EOL;
$ranks = $pdo->query("SHOW COLUMNS FROM mlm_rank_benefits")->fetchAll(PDO::FETCH_COLUMN);
$orderBy = in_array('order_rank', $ranks) ? 'order_rank' : 'id';
$ranks = $pdo->query("SELECT rank_name, direct_sale_pct, min_leg_count, min_qualifying_volume FROM mlm_rank_benefits ORDER BY $orderBy")->fetchAll(PDO::FETCH_ASSOC);
foreach ($ranks as $r) {
    echo "  {$r['rank_name']}: {$r['direct_sale_pct']}% direct | min_legs: {$r['min_leg_count']} | min_vol: Rs" . number_format($r['min_qualifying_volume']) . PHP_EOL;
}

echo PHP_EOL . "=== LEVEL BONUS (Upline Override) ===" . PHP_EOL;
$cols = $pdo->query("SHOW COLUMNS FROM mlm_levels")->fetchAll(PDO::FETCH_COLUMN);
$lvl = $pdo->query("SELECT * FROM mlm_levels ORDER BY " . (in_array('level_number', $cols) ? 'level_number' : 'id'))->fetchAll(PDO::FETCH_ASSOC);
foreach ($lvl as $l) {
    echo "  Level {$l['level_number']}: {$l['title']}";
    if (isset($l['override_pct']) && $l['override_pct'] !== null) echo " | override_pct={$l['override_pct']}%";
    if (isset($l['bonus_fixed']) && $l['bonus_fixed'] !== null) echo " | bonus_fixed=Rs{$l['bonus_fixed']}";
    echo PHP_EOL;
}

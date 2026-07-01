<?php
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'], $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "=== CURRENT mlm_rank_benefits ===\n";
$rows = $pdo->query('SELECT rank_name, rank_order, direct_sale_pct, l1_pct, l2_pct, l3_pct, min_leg_count, min_qualifying_volume FROM mlm_rank_benefits ORDER BY rank_order')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    printf("%-20s Direct=%s%%  L1=%s%%  L2=%s%%  L3=%s%%  Legs=%s  Vol=%s\n",
        $r['rank_name'], $r['direct_sale_pct'], $r['l1_pct'], $r['l2_pct'], $r['l3_pct'],
        $r['min_leg_count'], number_format($r['min_qualifying_volume'])
    );
}

echo "\n=== HybridCommissionEngine RANK_SLABS (from code) ===\n";
$slabs = [
    'associate'      => ['rate' =>  5, 'min_gbv' =>        0],
    'sr_associate'   => ['rate' =>  7, 'min_gbv' =>  1000000],
    'bdm'            => ['rate' => 10, 'min_gbv' =>  3500000],
    'sr_bdm'         => ['rate' => 12, 'min_gbv' =>  7000000],
    'vice_president' => ['rate' => 15, 'min_gbv' => 15000000],
    'president'      => ['rate' => 18, 'min_gbv' => 30000000],
    'site_manager'   => ['rate' => 20, 'min_gbv' => 50000000],
];
foreach ($slabs as $name => $s) {
    printf("%-20s Direct=%s%%  GBV>=Rs%s\n", $name, $s['rate'], number_format($s['min_gbv']));
}

echo "\n=== DIFFERENCE (DB - Engine) ===\n";
foreach ($rows as $r) {
    $engineRate = $slabs[$r['rank_name']]['rate'] ?? '?';
    $diff = $r['direct_sale_pct'] != $engineRate ? '*** MISMATCH ***' : 'OK';
    printf("%-20s DB=%s%%  Engine=%s%%  %s\n", $r['rank_name'], $r['direct_sale_pct'], $engineRate, $diff);
}

<?php
require __DIR__ . '/../app/Core/Autoloader.php';
\App\Core\Autoloader::getInstance();

$pdo = \App\Core\Database\Database::getInstance();
if (method_exists($pdo, 'getPdo')) $pdo = $pdo->getPdo();

echo "=== BEFORE ===\n";
$rows = $pdo->query("SELECT rank_name, direct_sale_pct, l1_pct, l2_pct, l3_pct FROM mlm_rank_benefits ORDER BY rank_order")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo sprintf("  %-20s direct=%s%%  l1=%s%%  l2=%s%%  l3=%s%%\n", $r['rank_name'], $r['direct_sale_pct'], $r['l1_pct'], $r['l2_pct'], $r['l3_pct']);
}

// Update to 5%-20% differential model
$updates = [
    ['associate',        5.0,  0.0, 0.0, 0.0],
    ['senior_associate', 7.0,  0.0, 0.0, 0.0],
    ['bdm',             10.0,  0.0, 0.0, 0.0],
    ['sr_bdm',          12.0,  0.0, 0.0, 0.0],
    ['vice_president',  15.0,  0.0, 0.0, 0.0],
    ['president',       18.0,  0.0, 0.0, 0.0],
    ['site_manager',    20.0,  0.0, 0.0, 0.0],
];

$stmt = $pdo->prepare('UPDATE mlm_rank_benefits SET direct_sale_pct = ?, l1_pct = ?, l2_pct = ?, l3_pct = ? WHERE rank_name = ?');
foreach ($updates as $u) {
    $stmt->execute([$u[1], $u[2], $u[3], $u[4], $u[0]]);
    echo "Updated: {$u[0]} -> direct={$u[1]}%\n";
}

echo "\n=== AFTER ===\n";
$rows = $pdo->query("SELECT rank_name, direct_sale_pct, l1_pct, l2_pct, l3_pct FROM mlm_rank_benefits ORDER BY rank_order")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo sprintf("  %-20s direct=%s%%  l1=%s%%  l2=%s%%  l3=%s%%\n", $r['rank_name'], $r['direct_sale_pct'], $r['l1_pct'], $r['l2_pct'], $r['l3_pct']);
}

echo "\nDone. Rates updated to 5%-20% differential model.\n";?>
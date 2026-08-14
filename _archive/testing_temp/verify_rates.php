<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$rows = $pdo->query('SELECT rank_name, direct_sale_pct, l1_pct, l2_pct, l3_pct FROM mlm_rank_benefits ORDER BY rank_order')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo sprintf("  %-20s direct=%s%%  l1=%s%%  l2=%s%%  l3=%s%%\n", $r['rank_name'], $r['direct_sale_pct'], $r['l1_pct'], $r['l2_pct'], $r['l3_pct']);
}
echo "\nDone.\n";?>
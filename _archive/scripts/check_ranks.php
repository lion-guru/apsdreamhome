<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$rows = $pdo->query('SELECT * FROM mlm_rank_benefits ORDER BY rank_order')->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $r) {
    echo $r['rank_name'] . ' | min_vol: ' . $r['min_qualifying_volume'] . ' | min_legs: ' . ($r['min_leg_count'] ?? 'N/A') . ' | direct_sale_pct: ' . ($r['direct_sale_pct'] ?? 'N/A') . PHP_EOL;
}

echo PHP_EOL . "=== mlm_settings ===" . PHP_EOL;
$rows2 = $pdo->query('SELECT * FROM mlm_settings')->fetchAll(PDO::FETCH_ASSOC);
foreach($rows2 as $r) { echo $r['setting_key'] . ' = ' . $r['setting_value'] . PHP_EOL; }

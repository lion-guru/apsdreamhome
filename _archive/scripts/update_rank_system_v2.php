<?php
/**
 * Update Rank System v2 - Corrected thresholds per user specification
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

echo "=== Updating mlm_rank_benefits v2 ===\n";

$ranks = [
    // rank_name, min_volume, team_required (direct legs needed below), commission_rate
    ['associate', 1000000, 0, 5],           // 10 Lakh - entry level
    ['senior_associate', 3500000, 5, 7],    // 35 Lakh - 5 associates needed
    ['bdm', 7000000, 3, 10],                // 70 Lakh - 3 Sr Associates needed
    ['sr_bdm', 15000000, 3, 12],            // 1.5 Cr - 3 BDMs needed
    ['vice_president', 30000000, 3, 15],    // 3 Cr - 3 Sr BDMs needed
    ['president', 50000000, 3, 18],         // 5 Cr - 3 VPs needed
    ['site_manager', 50000001, 3, 20],      // 5 Cr+ - 3 Presidents needed
];

foreach ($ranks as $r) {
    $stmt = $pdo->prepare("UPDATE mlm_rank_benefits SET min_qualifying_volume = ?, min_leg_count = ?, direct_sale_pct = ? WHERE rank_name = ?");
    $stmt->execute([$r[1], $r[2], $r[3], $r[0]]);
    echo "Updated: {$r[0]} -> Volume: " . number_format($r[1]) . ", Team: {$r[2]}, Rate: {$r[3]}%\n";
}

// Verify
echo "\n=== Verification ===\n";
$rows = $pdo->query('SELECT rank_name, min_qualifying_volume, min_leg_count, direct_sale_pct, reward_item FROM mlm_rank_benefits ORDER BY rank_order')->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $r) {
    echo sprintf("%-20s | Vol: %12s | Team: %s | Rate: %s%% | Reward: %s\n",
        $r['rank_name'],
        number_format($r['min_qualifying_volume']),
        $r['min_leg_count'],
        $r['direct_sale_pct'],
        $r['reward_item'] ?? 'N/A'
    );
}
echo "\nDone!\n";?>
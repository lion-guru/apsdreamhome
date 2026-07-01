<?php
/**
 * Update Rank System with new thresholds, rewards, and team requirements
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

echo "=== Updating mlm_rank_benefits ===\n";

// Update rank thresholds as per user's specification
$ranks = [
    ['senior_associate', 1000000, 5, 7],    // 10 Lakh - 5 associates needed
    ['bdm', 3500000, 3, 10],                 // 35 Lakh - 3 Sr Associates needed
    ['sr_bdm', 7000000, 3, 12],              // 70 Lakh - 3 BDMs needed
    ['vice_president', 15000000, 3, 15],     // 1.5 Cr - 3 Sr BDMs needed
    ['president', 30000000, 3, 18],          // 3 Cr - 3 VPs needed
    ['site_manager', 50000000, 3, 20],       // 5 Cr - 3 Presidents needed
];

foreach ($ranks as $r) {
    $stmt = $pdo->prepare("UPDATE mlm_rank_benefits SET min_qualifying_volume = ?, min_leg_count = ?, direct_sale_pct = ? WHERE rank_name = ?");
    $stmt->execute([$r[1], $r[2], $r[3], $r[0]]);
    echo "Updated: {$r[0]} -> Volume: {$r[1]}, Legs: {$r[2]}, Rate: {$r[3]}%\n";
}

// Add new columns for rewards and team requirements
echo "\n=== Adding new columns ===\n";

try {
    $pdo->exec("ALTER TABLE mlm_rank_benefits ADD COLUMN IF NOT EXISTS reward_item VARCHAR(100) AFTER direct_sale_pct");
    echo "Added reward_item column\n";
} catch (Throwable $e) {
    echo "Column reward_item may already exist: " . $e->getMessage() . "\n";
}

try {
    $pdo->exec("ALTER TABLE mlm_rank_benefits ADD COLUMN IF NOT EXISTS team_requirement INT DEFAULT 0 AFTER min_leg_count");
    echo "Added team_requirement column\n";
} catch (Throwable $e) {
    echo "Column team_requirement may already exist: " . $e->getMessage() . "\n";
}

// Update rewards
echo "\n=== Updating rewards ===\n";
$rewards = [
    ['associate', 'Mobile', 0],
    ['senior_associate', 'Tablet', 5],
    ['bdm', 'Laptop', 3],
    ['sr_bdm', 'Tour Package', 3],
    ['vice_president', 'Bike', 3],
    ['president', 'Royal Enfield Bullet', 3],
    ['site_manager', 'Car', 3],
];

foreach ($rewards as $rw) {
    try {
        $stmt = $pdo->prepare("UPDATE mlm_rank_benefits SET reward_item = ?, team_requirement = ? WHERE rank_name = ?");
        $stmt->execute([$rw[1], $rw[2], $rw[0]]);
        echo "Updated reward: {$rw[0]} -> {$rw[1]} (Team: {$rw[2]})\n";
    } catch (Throwable $e) {
        echo "Error updating {$rw[0]}: " . $e->getMessage() . "\n";
    }
}

// Update rank_bonus_amounts in mlm_settings
echo "\n=== Updating mlm_settings ===\n";
$rankBonuses = [
    'senior_associate' => 5000,
    'bdm' => 15000,
    'sr_bdm' => 35000,
    'vice_president' => 75000,
    'president' => 150000,
    'site_manager' => 300000,
];
$stmt = $pdo->prepare("UPDATE mlm_settings SET setting_value = ? WHERE setting_key = 'rank_bonus_amounts'");
$stmt->execute([json_encode($rankBonuses)]);
echo "Updated rank_bonus_amounts\n";

// Verify
echo "\n=== Verification ===\n";
$rows = $pdo->query('SELECT rank_name, min_qualifying_volume, min_leg_count, direct_sale_pct, reward_item, team_requirement FROM mlm_rank_benefits ORDER BY rank_order')->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $r) {
    echo sprintf("%-20s | Vol: %12s | Legs: %s | Rate: %s%% | Reward: %-20s | Team: %s\n",
        $r['rank_name'],
        number_format($r['min_qualifying_volume']),
        $r['min_leg_count'],
        $r['direct_sale_pct'],
        $r['reward_item'] ?? 'N/A',
        $r['team_requirement'] ?? 0
    );
}

echo "\nDone!\n";

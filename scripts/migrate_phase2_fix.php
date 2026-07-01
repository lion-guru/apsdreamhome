<?php
/**
 * Phase 2 Fix — Insert settings, network tree, extend ENUM
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

echo "=== PHASE 2 FIX ===\n\n";
$ok = 0;
$skip = 0;

function run($pdo, $label, $sql) {
    global $ok, $skip;
    try {
        $pdo->exec($sql);
        echo "  [OK] {$label}\n";
        $ok++;
    } catch (Exception $e) {
        echo "  [SKIP] {$label}: {$e->getMessage()}\n";
        $skip++;
    }
}

// 1. Extend ENUM
echo "--- 1. Extend Ledger ENUM ---\n";
$newTypes = 'referral,direct_sale,team_bonus,level_bonus,performance_bonus,special_reward,override,associate_referral,agent_referral,team_override,mlm_level_1,mlm_level_2,mlm_level_3,investment_sale,royalty_pool,clawback,generation_bonus,infinity_override,matching_bonus,rank_bonus';
run($pdo, 'Extend ENUM', "ALTER TABLE mlm_commission_ledger MODIFY COLUMN commission_type ENUM('{$newTypes}') NOT NULL DEFAULT 'direct_sale'");

// 2. Insert settings (no created_at column!)
echo "\n--- 2. Insert Settings ---\n";
$newSettings = [
    ['generation_bonus_pct', '5', 'Total pool for generation bonuses'],
    ['generation_bonus_enabled', '1', 'Enable generation bonus'],
    ['gen1_match_pct', '100', 'Match % for Gen 1 leaders'],
    ['gen2_match_pct', '50', 'Match % for Gen 2 leaders'],
    ['gen3_match_pct', '25', 'Match % for Gen 3+ leaders'],
    ['infinity_override_pct', '1', 'Infinity override % (VP+)'],
    ['infinity_override_enabled', '1', 'Enable infinity overrides'],
    ['infinity_min_rank', 'vice_president', 'Min rank for infinity'],
    ['matching_bonus_enabled', '1', 'Enable matching bonuses'],
    ['matching_max_levels', '3', 'Max generations to match'],
    ['rank_bonus_enabled', '1', 'Enable rank bonuses'],
    ['min_monthly_volume', '10000', 'Min monthly volume to stay Active'],
    ['qualification_required', '1', 'Enforce qualification'],
    ['rank_bonus_amounts', '{"senior_associate":5000,"bdm":15000,"sr_bdm":35000,"vice_president":75000,"president":150000,"site_manager":300000}', 'One-time rank bonus amounts'],
];

foreach ($newSettings as [$key, $value, $desc]) {
    run($pdo, "Setting: {$key}",
        "INSERT INTO mlm_settings (setting_key, setting_value, description) 
         VALUES ('{$key}', '{$value}', '{$desc}')
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
}

// 3. Network tree for Deep SM users
echo "\n--- 3. Network Tree for Deep SM ---\n";
$networkTree = [
    [320, null, 0],   // SiteManager (top)
    [321, 320, 1],   // President under SiteManager
    [322, 321, 2],   // VP under President
    [323, 322, 3],   // SrBDM under VP
    [324, 323, 4],   // BDM under SrBDM
    [325, 324, 5],   // SrAssociate under BDM
    [319, 325, 6],   // Associate under SrAssociate
];

foreach ($networkTree as [$assocId, $parentId, $level]) {
    $parentVal = $parentId ? $parentId : 'NULL';
    run($pdo, "NT: assoc={$assocId}",
        "INSERT INTO mlm_network_tree (associate_id, parent_id, level) 
         VALUES ({$assocId}, {$parentVal}, {$level})
         ON DUPLICATE KEY UPDATE parent_id = VALUES(parent_id), level = VALUES(level)");
}

// ============================================================
// VERIFY
// ============================================================
echo "\n=== VERIFICATION ===\n";

// Ledger ENUM
$r = $pdo->query("SHOW COLUMNS FROM mlm_commission_ledger LIKE 'commission_type'");
$col = $r->fetch();
preg_match("/enum\((.+)\)/", $col['Type'], $m);
$count = count(explode(',', $m[1]));
echo "  Ledger ENUM types: {$count}\n";

// Settings count
$r = $pdo->query("SELECT COUNT(*) as cnt FROM mlm_settings");
echo "  Settings total: " . $r->fetch()['cnt'] . "\n";

// Network tree Deep SM
$r = $pdo->query("SELECT COUNT(*) as cnt FROM mlm_network_tree WHERE associate_id IN (319,320,321,322,323,324,325)");
echo "  Network tree Deep SM: " . $r->fetch()['cnt'] . "\n";

// Show tree
$r = $pdo->query('SELECT nt.associate_id, nt.parent_id, nt.level, u.name FROM mlm_network_tree nt LEFT JOIN associates a ON a.id = nt.associate_id LEFT JOIN users u ON u.id = a.user_id WHERE nt.associate_id IN (319,320,321,322,323,324,325) ORDER BY nt.level');
while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
    echo "    Level {$row['level']}: assoc={$row['associate_id']} parent={$row['parent_id']} user={$row['name']}\n";
}

// Verify all 5 new tables
$tables = ['mlm_generation_commissions', 'mlm_infinity_overrides', 'mlm_matching_bonuses', 'mlm_rank_bonuses', 'mlm_qualification_log'];
foreach ($tables as $tbl) {
    $r = $pdo->query("SHOW TABLES LIKE '{$tbl}'");
    echo "  {$tbl}: " . ($r->fetch() ? 'OK' : 'MISSING') . "\n";
}

echo "\nDONE: {$ok} ok, {$skip} skipped\n";

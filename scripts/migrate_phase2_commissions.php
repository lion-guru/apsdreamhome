<?php
/**
 * Phase 2 Commission Streams — DB Migration + Test Data
 * 
 * 1. Extend mlm_commission_ledger ENUM with new types
 * 2. Create mlm_generation_commissions table
 * 3. Create mlm_infinity_overrides table
 * 4. Create mlm_matching_bonuses table
 * 5. Create mlm_rank_bonuses table
 * 6. Create mlm_qualification_log table
 * 7. Add network_tree entries for Deep SM test users
 * 8. Add mlm_settings for new streams
 * 9. Seed rank bonus amounts
 */

$root = dirname(__DIR__);
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

echo "=== PHASE 2 COMMISSION STREAMS MIGRATION ===\n\n";
$success = 0;
$errors = 0;

function run($pdo, $label, $sql) {
    global $success, $errors;
    try {
        $pdo->exec($sql);
        echo "  [OK] {$label}\n";
        $success++;
    } catch (Exception $e) {
        echo "  [SKIP] {$label}: {$e->getMessage()}\n";
        $errors++;
    }
}

// ============================================================
// 1. Extend mlm_commission_ledger ENUM with new commission types
// ============================================================
echo "\n--- 1. Extend Commission Ledger ENUM ---\n";

// Add new types to ENUM: generation_bonus, infinity_override, matching_bonus, rank_bonus
$existingTypes = 'referral,direct_sale,team_bonus,level_bonus,performance_bonus,special_reward,override,associate_referral,agent_referral,team_override,mlm_level_1,mlm_level_2,mlm_level_3,investment_sale,royalty_pool,clawback';
$newTypes = $existingTypes . ',generation_bonus,infinity_override,matching_bonus,rank_bonus';

run($pdo, 'Extend ledger ENUM', 
    "ALTER TABLE mlm_commission_ledger MODIFY COLUMN commission_type ENUM('{$newTypes}') NOT NULL DEFAULT 'direct_sale'");

// ============================================================
// 2. Create mlm_generation_commissions table
// ============================================================
echo "\n--- 2. Create mlm_generation_commissions ---\n";

run($pdo, 'Create mlm_generation_commissions', "
CREATE TABLE IF NOT EXISTS mlm_generation_commissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    beneficiary_user_id BIGINT UNSIGNED NOT NULL,
    source_user_id BIGINT UNSIGNED NOT NULL,
    booking_id BIGINT UNSIGNED DEFAULT NULL,
    generation_number INT UNSIGNED NOT NULL COMMENT '1 = direct downline leader group, 2 = next generation, etc.',
    team_volume DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT 'Total sales volume in this generation',
    generation_pct DECIMAL(5,2) NOT NULL DEFAULT 0 COMMENT 'Percentage earned from this generation',
    commission_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    period_start DATE NOT NULL COMMENT 'Start of calculation period',
    period_end DATE NOT NULL COMMENT 'End of calculation period',
    status ENUM('pending','approved','paid','clawback') NOT NULL DEFAULT 'pending',
    approved_by BIGINT UNSIGNED DEFAULT NULL,
    approved_at DATETIME DEFAULT NULL,
    paid_at DATETIME DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_gen_beneficiary (beneficiary_user_id),
    KEY idx_gen_source (source_user_id),
    KEY idx_gen_booking (booking_id),
    KEY idx_gen_period (period_start, period_end),
    KEY idx_gen_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// ============================================================
// 3. Create mlm_infinity_overrides table
// ============================================================
echo "\n--- 3. Create mlm_infinity_overrides ---\n";

run($pdo, 'Create mlm_infinity_overrides', "
CREATE TABLE IF NOT EXISTS mlm_infinity_overrides (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    beneficiary_user_id BIGINT UNSIGNED NOT NULL,
    source_user_id BIGINT UNSIGNED NOT NULL COMMENT 'User whose sale triggered this override',
    booking_id BIGINT UNSIGNED DEFAULT NULL,
    depth_level INT UNSIGNED NOT NULL COMMENT 'Depth in upline (1 = direct, 2 = grand, etc.)',
    sale_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    override_pct DECIMAL(5,2) NOT NULL DEFAULT 0 COMMENT 'Always 1% for qualified VP+',
    commission_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    status ENUM('pending','approved','paid','clawback') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_inf_beneficiary (beneficiary_user_id),
    KEY idx_inf_source (source_user_id),
    KEY idx_inf_booking (booking_id),
    KEY idx_inf_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// ============================================================
// 4. Create mlm_matching_bonuses table
// ============================================================
echo "\n--- 4. Create mlm_matching_bonuses ---\n";

run($pdo, 'Create mlm_matching_bonuses', "
CREATE TABLE IF NOT EXISTS mlm_matching_bonuses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    beneficiary_user_id BIGINT UNSIGNED NOT NULL COMMENT 'Leader earning the match',
    matched_user_id BIGINT UNSIGNED NOT NULL COMMENT 'Downline leader whose commission is matched',
    match_level INT UNSIGNED NOT NULL COMMENT '1 = first generation leader, 2 = second, etc.',
    matched_commission_type VARCHAR(50) NOT NULL COMMENT 'What type of commission is being matched',
    matched_amount DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT 'Amount earned by downline leader',
    match_pct DECIMAL(5,2) NOT NULL DEFAULT 0 COMMENT 'Percentage match (25%-100%)',
    bonus_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    status ENUM('pending','approved','paid','clawback') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_match_beneficiary (beneficiary_user_id),
    KEY idx_match_matched (matched_user_id),
    KEY idx_match_period (period_start, period_end),
    KEY idx_match_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// ============================================================
// 5. Create mlm_rank_bonuses table
// ============================================================
echo "\n--- 5. Create mlm_rank_bonuses ---\n";

run($pdo, 'Create mlm_rank_bonuses', "
CREATE TABLE IF NOT EXISTS mlm_rank_bonuses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    from_rank VARCHAR(50) NOT NULL,
    to_rank VARCHAR(50) NOT NULL,
    bonus_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    status ENUM('pending','approved','paid') NOT NULL DEFAULT 'pending',
    paid_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_rank_bonus_user (user_id),
    KEY idx_rank_bonus_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// ============================================================
// 6. Create mlm_qualification_log table
// ============================================================
echo "\n--- 6. Create mlm_qualification_log ---\n";

run($pdo, 'Create mlm_qualification_log', "
CREATE TABLE IF NOT EXISTS mlm_qualification_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    qualification_month DATE NOT NULL COMMENT 'First day of qualification month',
    personal_volume DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT 'Total personal sales this month',
    team_volume DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT 'Total team sales this month',
    is_active TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Met minimum qualifying volume',
    qualified_for_rank TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Qualified for current rank',
    qualified_for_generations TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Qualified for gen bonus',
    qualified_for_infinity TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Qualified for infinity override',
    qualified_for_matching TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Qualified for matching bonus',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_qual_user (user_id),
    KEY idx_qual_month (qualification_month),
    UNIQUE KEY uniq_user_month (user_id, qualification_month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// ============================================================
// 7. Add mlm_settings for new streams
// ============================================================
echo "\n--- 7. Add New MLM Settings ---\n";

$newSettings = [
    // Generation Bonus settings
    ['generation_bonus_pct', '5', 'Total pool for generation bonuses (% of qualifying sales)'],
    ['generation_bonus_enabled', '1', 'Enable/disable generation bonus calculations'],
    ['gen1_match_pct', '100', 'Match percentage for Generation 1 leaders'],
    ['gen2_match_pct', '50', 'Match percentage for Generation 2 leaders'],
    ['gen3_match_pct', '25', 'Match percentage for Generation 3+ leaders'],
    
    // Infinity Override settings
    ['infinity_override_pct', '1', 'Infinity override percentage (for VP+ ranks)'],
    ['infinity_override_enabled', '1', 'Enable/disable infinity overrides'],
    ['infinity_min_rank', 'vice_president', 'Minimum rank for infinity override eligibility'],
    
    // Matching Bonus settings
    ['matching_bonus_enabled', '1', 'Enable/disable matching bonuses'],
    ['matching_max_levels', '3', 'Max generations to match (1st gen = 100%, 2nd = 50%, 3rd = 25%)'],
    
    // Rank Advancement Bonus settings
    ['rank_bonus_enabled', '1', 'Enable/disable rank advancement bonuses'],
    
    // Qualification settings
    ['min_monthly_volume', '10000', 'Minimum monthly personal volume to stay Active'],
    ['qualification_required', '1', 'Enforce qualification requirements'],
];

foreach ($newSettings as [$key, $value, $desc]) {
    run($pdo, "Setting: {$key}", 
        "INSERT IGNORE INTO mlm_settings (setting_key, setting_value, description, created_at) 
         VALUES ('{$key}', '{$value}', '{$desc}', NOW())");
}

// ============================================================
// 8. Seed Rank Advancement Bonus amounts
// ============================================================
echo "\n--- 8. Seed Rank Bonus Amounts ---\n";

$rankBonuses = [
    ['senior_associate', 5000, 'Rank up to Senior Associate'],
    ['bdm', 15000, 'Rank up to BDM'],
    ['sr_bdm', 35000, 'Rank up to Sr BDM'],
    ['vice_president', 75000, 'Rank up to Vice President'],
    ['president', 150000, 'Rank up to President'],
    ['site_manager', 300000, 'Rank up to Site Manager'],
];

// Store in mlm_settings as JSON
$bonusJson = json_encode(array_column($rankBonuses, null, 0));
run($pdo, 'Seed rank bonus amounts',
    "INSERT INTO mlm_settings (setting_key, setting_value, description, created_at) 
     VALUES ('rank_bonus_amounts', '" . addslashes($bonusJson) . "', 'One-time bonus amounts per rank promotion', NOW())
     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

// ============================================================
// 9. Add network_tree entries for Deep SM test users
// ============================================================
echo "\n--- 9. Add Network Tree for Deep SM Users ---\n";

// Deep SM chain: 2106(top) ← 2107 ← 2108 ← 2109 ← 2110 ← 2111 ← 2112(seller)
// Associates: 320, 321, 322, 323, 324, 325, 319
$networkTree = [
    // assoc_id, parent_assoc_id, level (depth from top)
    [320, null, 0],   // SiteManager (top)
    [321, 320, 1],   // President → under SiteManager
    [322, 321, 2],   // VP → under President
    [323, 322, 3],   // SrBDM → under VP
    [324, 323, 4],   // BDM → under SrBDM
    [325, 324, 5],   // SrAssociate → under BDM
    [319, 325, 6],   // Associate → under SrAssociate (bottom/seller)
];

foreach ($networkTree as [$assocId, $parentId, $level]) {
    $parentIdVal = $parentId ? $parentId : 'NULL';
    run($pdo, "Network tree: assoc={$assocId}",
        "INSERT IGNORE INTO mlm_network_tree (associate_id, parent_id, level, created_at, updated_at) 
         VALUES ({$assocId}, {$parentIdVal}, {$level}, NOW(), NOW())");
}

// ============================================================
// SUMMARY
// ============================================================
echo "\n=== MIGRATION COMPLETE ===\n";
echo "  Success: {$success}\n";
echo "  Skipped/Errors: {$errors}\n";

// Verify
echo "\n--- Verification ---\n";
$r = $pdo->query("SELECT COUNT(*) as cnt FROM mlm_network_tree WHERE associate_id IN (319,320,321,322,323,324,325)");
echo "  Network tree entries for Deep SM: {$r->fetch()['cnt']}\n";

$r = $pdo->query("SHOW COLUMNS FROM mlm_commission_ledger LIKE 'commission_type'");
$col = $r->fetch();
echo "  Ledger ENUM: " . substr($col['Type'], 0, 100) . "...\n";

$tables = ['mlm_generation_commissions', 'mlm_infinity_overrides', 'mlm_matching_bonuses', 'mlm_rank_bonuses', 'mlm_qualification_log'];
foreach ($tables as $tbl) {
    $r = $pdo->query("SHOW TABLES LIKE '{$tbl}'");
    echo "  {$tbl}: " . ($r->fetch() ? 'EXISTS' : 'MISSING') . "\n";
}

$r = $pdo->query("SELECT COUNT(*) as cnt FROM mlm_settings WHERE setting_key LIKE 'generation%' OR setting_key LIKE 'infinity%' OR setting_key LIKE 'matching%' OR setting_key LIKE 'rank_bonus%' OR setting_key LIKE 'min_monthly%' OR setting_key LIKE 'qualification%'");
echo "  New settings: {$r->fetch()['cnt']}\n";

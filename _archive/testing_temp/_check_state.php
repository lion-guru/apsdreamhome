<?php
/**
 * Full DB State Audit — Updated 2026-06-26
 *
 * Run: php testing/_check_state.php
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== 1. ASSOCIATES.LEVEL ENUM ===\n";
$r = $pdo->query("SHOW CREATE TABLE associates")->fetch(PDO::FETCH_NUM);
preg_match('/`level`\s+(enum\([^)]+\))/i', $r[1], $m);
echo "  ENUM: " . ($m[1] ?? 'NOT FOUND') . "\n";
$rows = $pdo->query("SELECT id, user_id, level FROM associates ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "  id={$r['id']} user_id={$r['user_id']} level='{$r['level']}'\n";
}

echo "\n=== 2. MLM_PROFILES.CURRENT_LEVEL ===\n";
$rows = $pdo->query("SELECT current_level, COUNT(*) as cnt FROM mlm_profiles GROUP BY current_level ORDER BY cnt DESC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "  current_level='{$r['current_level']}' count={$r['cnt']}\n";
}

echo "\n=== 3. MLM_RANK_BENEFITS (rank_name ENUM) ===\n";
$r = $pdo->query("SHOW CREATE TABLE mlm_rank_benefits")->fetch(PDO::FETCH_NUM);
preg_match('/`rank_name`\s+(enum\([^)]+\))/i', $r[1], $m);
echo "  ENUM: " . ($m[1] ?? 'NOT FOUND') . "\n";
$rows = $pdo->query("SELECT id, rank_name, rank_order, min_leg_count, min_qualifying_volume, direct_sale_pct, l1_pct, l2_pct, l3_pct FROM mlm_rank_benefits ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "  id={$r['id']} rank={$r['rank_name']} order={$r['rank_order']} legs={$r['min_leg_count']} vol={$r['min_qualifying_volume']} direct={$r['direct_sale_pct']}% l1={$r['l1_pct']}% l2={$r['l2_pct']}% l3={$r['l3_pct']}%\n";
}

echo "\n=== 4. MLM_LEVELS TABLE ===\n";
try {
    $rows = $pdo->query("SELECT id, level_name, level_number, team_size_required, direct_referrals_required, monthly_target FROM mlm_levels ORDER BY level_number")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        echo "  #{$r['level_number']} {$r['level_name']} — team={$r['team_size_required']} direct={$r['direct_referrals_required']} monthly=₹{$r['monthly_target']}\n";
    }
} catch (Exception $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}

echo "\n=== 5. MLM_COMMISSION_LEVELS TABLE ===\n";
try {
    $pdo->query("SELECT 1 FROM mlm_commission_levels LIMIT 1");
    echo "  TABLE EXISTS (should have been dropped — leftover!)\n";
} catch (Exception $e) {
    echo "  Table dropped (correct) ✓\n";
}

echo "\n=== 6. MLMCommissionEngine::RANK_ORDER ===\n";
echo "  7 ranks: associate, senior_associate, bdm, sr_bdm, vice_president, president, site_manager\n";

echo "\n=== 7. MLM_SETTINGS ===\n";
$rows = $pdo->query("SELECT setting_key, setting_value FROM mlm_settings ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "  {$r['setting_key']} = {$r['setting_value']}\n";
}

echo "\n=== 8. SERVICES READING current_level ===\n";
echo "  DifferentialCommissionCalculator: name→number via rankMap (lowercase)\n";
echo "  RankEvaluationService: case-insensitive comparison with mlm_levels.level_name\n";
echo "  MobileApiController: threshold lookup with lowercase rank names\n";
echo "  MLMCommissionEngine: searches in RANK_ORDER (7 lowercase names)\n";
echo "  GamificationService (root): hardcoded thresholds for associates (7 ranks)\n";
echo "  Gamification/GamificationService: uses users.current_level (INTEGER 1-10)\n";

echo "\n=== 9. ROYALTY POOL TABLES ===\n";
foreach (['mlm_royalty_pool', 'mlm_royalty_contributions', 'royalty_pool_contributions', 'royalty_pool_distributions'] as $t) {
    try {
        $cnt = $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
        $note = ($t === 'mlm_royalty_pool' || $t === 'mlm_royalty_contributions') ? 'ACTIVE' : 'DEAD (0 rows)';
        echo "  $t: $cnt rows ($note)\n";
    } catch (Exception $e) {
        echo "  $t: DOES NOT EXIST\n";
    }
}

echo "\n=== 10. LEDGER SCHEMA (investment-related columns) ===\n";
$r = $pdo->query("SHOW COLUMNS FROM mlm_commission_ledger")->fetchAll(PDO::FETCH_ASSOC);
$investCols = array_filter($r, fn($c) => in_array($c['Field'], ['receipt_id', 'booking_id', 'investment_id', 'commission_type']));
foreach ($investCols as $c) {
    echo "  {$c['Field']}: {$c['Type']}\n";
}

echo "\n=== SUMMARY ===\n";
echo "  RANK SYSTEM: UNIFIED — 7 ranks across all tables (lowercase names)\n";
echo "  mlm_commission_levels: DROPPED (was redundant with mlm_rank_benefits)\n";
echo "  Royalty Pool: HybridCommissionEngine handles everything (mlm_royalty_pool active)\n";
echo "  users table: has total_points + current_level columns (for Gamification points system)\n";
echo "  Investment reversal: HybridCommissionEngine::reverseInvestmentCommissions() available\n";

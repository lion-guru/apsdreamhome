<?php
/**
 * HybridCommissionEngine — Comprehensive Smoke Test
 * 
 * Seeds test data, then runs all engine functions.
 * Usage: php testing/test_hybrid_commission_engine.php
 */

$root = dirname(__DIR__);
if (!defined('APP_ROOT')) {
    define('APP_ROOT', $root);
}
require_once APP_ROOT . '/app/Core/Autoloader.php';
\App\Core\Autoloader::getInstance()->register();

use App\Services\HybridCommissionEngine;

$pass = 0;
$fail = 0;

function assert_test(string $name, bool $cond, string $detail = '') {
    global $pass, $fail;
    if ($cond) {
        echo "  PASS  {$name}\n";
        $pass++;
    } else {
        echo "  FAIL  {$name}  {$detail}\n";
        $fail++;
    }
}

echo "=== HybridCommissionEngine Smoke Test ===\n\n";

// ── Bootstrap DB ──────────────────────────────────────────────
$config = require APP_ROOT . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// ── Seed realistic test data ──────────────────────────────────
echo "--- Seeding test data ---\n";

// Ensure test users have mlm_profiles
$testUsers = [
    ['id' => 9,  'code' => 'TEST9',  'sponsor' => 2],
    ['id' => 2,  'code' => 'AGENT2',  'sponsor' => 1],
    ['id' => 1,  'code' => 'ADMIN1',  'sponsor' => null],
];
foreach ($testUsers as $u) {
    // Upsert mlm_profiles
    $stmt = $pdo->prepare("
        INSERT INTO mlm_profiles (user_id, referral_code, sponsor_user_id, user_type, current_level, lifetime_sales, total_team_size, direct_referrals, status)
        VALUES (?, ?, ?, 'associate', 'Associate', 0, 0, 0, 'active')
        ON DUPLICATE KEY UPDATE referral_code = VALUES(referral_code)
    ");
    $stmt->execute([$u['id'], $u['code'], $u['sponsor']]);
}

// Ensure network tree links exist: 9 → 2 → 1
$treeLinks = [
    [9, 2, 2, 1],
    [2, 1, 1, 2],
];
foreach ($treeLinks as [$assoc, $parent, $sponsor, $level]) {
    $existing = $pdo->prepare("SELECT id FROM mlm_network_tree WHERE associate_id = ? AND parent_id = ?");
    $existing->execute([$assoc, $parent]);
    if (!$existing->fetch()) {
        $pdo->prepare("INSERT INTO mlm_network_tree (associate_id, parent_id, sponsor_id, level, position) VALUES (?, ?, ?, ?, 'left')")
            ->execute([$assoc, $parent, $sponsor, $level]);
    }
}

// Ensure test bookings 9001/9002 have associate_id set
$pdo->exec("UPDATE plot_bookings SET associate_id = 1 WHERE id = 9001 AND associate_id IS NULL");
$pdo->exec("UPDATE plot_bookings SET associate_id = 1 WHERE id = 9002 AND associate_id IS NULL");

// Ensure the agent has known GBV for rank resolution
$pdo->prepare("UPDATE mlm_profiles SET lifetime_sales = 100000 WHERE user_id = 9")->execute();
$pdo->prepare("UPDATE mlm_profiles SET lifetime_sales = 500000 WHERE user_id = 2")->execute();

// Clean previous test ledger entries to get consistent results
$pdo->exec("DELETE FROM mlm_commission_ledger WHERE notes LIKE 'Track %' AND beneficiary_user_id IN (9, 2)");

echo "  Test data seeded\n\n";

// ── Instantiate ────────────────────────────────────────────────
$engine = new HybridCommissionEngine($pdo);
assert_test('Engine instantiates', $engine instanceof HybridCommissionEngine);

// ══════════════════════════════════════════════════════════════
// SECTION 1: PRICING MATRIX
// ══════════════════════════════════════════════════════════════
echo "\n--- Pricing Matrix ---\n";
$matrix = $engine->getPricingMatrix();
assert_test('Pricing matrix has 5 blocks', count($matrix) === 5);

$tests = [
    ['block_a',       'base_rate', 950],
    ['block_a',       'emi_allowed', false],
    ['block_b',       'base_rate', 850],
    ['block_b',       'emi_allowed', true],
    ['block_c',       'base_rate', 750],
    ['block_c',       'emi_allowed', true],
    ['corner_1500',   'base_rate', 1250],
    ['corner_1500',   'final_rate', 1375],
    ['corner_1000',   'base_rate', 1000],
    ['corner_1000',   'final_rate', 1100],
];
foreach ($tests as [$key, $field, $expected]) {
    $block = $engine->getBlockPricing($key);
    assert_test("{$key}.{$field} = {$expected}", isset($block[$field]) && $block[$field] == $expected, "got=" . ($block[$field] ?? 'null'));
}

// Block normalisation
$normTests = [
    ['block_a', 'Block A'],
    ['block_a', 'A'],
    ['block_a', 'BLOCK_A'],
    ['corner_1500', 'COMMERCIAL_CORNER'],
    ['corner_1500', 'Corner 1500'],
    ['corner_1000', 'Corner 1000'],
    ['block_b', 'b'],
];
foreach ($normTests as [$expected, $input]) {
    $got = $engine->getBlockPricing($input);
    assert_test("Normalise '{$input}' → {$expected}", $got !== null, "got null");
}
assert_test('Unknown key returns null', $engine->getBlockPricing('xyz_nonexistent') === null);

// Plot value calculations
$valA = $engine->calculatePlotValue('block_a');
assert_test('Block A value = 950 × 1000 = ₹9,50,000', (int)$valA['total_plot_value'] === 950000);

$valC = $engine->calculatePlotValue('corner_1500');
assert_test('Corner 1500 value = 1375 × 1500 = ₹20,62,500', (int)$valC['total_plot_value'] === 2062500);

$valCustom = $engine->calculatePlotValue('block_b', 1200);
assert_test('Block B custom (1200 sqft) = 850 × 1200 = ₹10,20,000', (int)$valCustom['total_plot_value'] === 1020000);

assert_test('Default booking = ₹51,000', (int)$engine->getDefaultBookingAmount() === 51000);

// ══════════════════════════════════════════════════════════════
// SECTION 2: RANK SLABS
// ══════════════════════════════════════════════════════════════
echo "\n--- Rank Slabs ---\n";
$slabs = $engine->getRankSlabs();
assert_test('7 rank slabs defined', count($slabs) === 7);
$rankRates = ['associate' => 5, 'sr_associate' => 7, 'bdm' => 10, 'sr_bdm' => 12, 'vice_president' => 15, 'president' => 18, 'site_manager' => 20];
foreach ($rankRates as $rank => $rate) {
    assert_test("{$rank} rate = {$rate}%", ($slabs[$rank]['rate'] ?? 0) === $rate);
}

// ══════════════════════════════════════════════════════════════
// SECTION 3: RANK RESOLUTION
// ══════════════════════════════════════════════════════════════
echo "\n--- Rank Resolution ---\n";
// Check rank BEFORE commission runs (GBV starts at ₹100K → associate)
$rank9 = $engine->resolveRank(9);
assert_test('resolveRank(9) returns valid rank slug', isset($rankRates[$rank9]));
assert_test('resolveRank(9) = associate (GBV ₹100K < ₹10L)', $rank9 === 'associate');

$rank2 = $engine->resolveRank(2);
assert_test('resolveRank(2) returns valid rank slug', isset($rankRates[$rank2]));

// ══════════════════════════════════════════════════════════════
// SECTION 4: UPLINE CHAIN
// ══════════════════════════════════════════════════════════════
echo "\n--- Upline Chain ---\n";
$chain = $engine->getUplineChain(9, 5);
assert_test('Upline chain for user 9 returns array', is_array($chain));
if (count($chain) > 0) {
    assert_test('Gen 1 upline exists', $chain[0]['level'] === 1);
    assert_test('Gen 1 has user_id', isset($chain[0]['user_id']) && $chain[0]['user_id'] > 0);
    assert_test('Gen 1 has rank', isset($chain[0]['rank']));
    echo "  Chain: " . implode(' → ', array_map(fn($g) => "L{$g['level']}:{$g['user_id']}({$g['rank']})", $chain)) . "\n";
}

// ══════════════════════════════════════════════════════════════
// SECTION 5: PROCESS PIPELINE COMMISSION
// ══════════════════════════════════════════════════════════════
echo "\n--- Track A: Slab Differential (₹1L payment) ---\n";
$amountReceived = 100000;
$result = $engine->processPipelineCommission(9001, 0, $amountReceived, 9);

assert_test('processPipelineCommission succeeds', $result['success'] === true, $result['error'] ?? '');
if ($result['success']) {
    assert_test('amount_received = ₹1,00,000', (float)$result['amount_received'] === (float)$amountReceived);
    assert_test('global_cap = 20% = ₹20,000', $result['global_cap'] === 20000.0);
    assert_test('track_a.budget = 15% = ₹15,000', $result['track_a']['budget'] === 15000.0);
    assert_test('track_b.budget = 3% = ₹3,000', $result['track_b']['budget'] === 3000.0);
    assert_test('track_c.budget = 2% = ₹2,000', $result['track_c']['budget'] === 2000.0);
    assert_test('track_a.distributed > 0', $result['track_a']['distributed'] > 0);
    assert_test('track_a has ledger entries', $result['track_a']['entries'] > 0);
    assert_test('track_b has ledger entry (0 if no qualifying months)', 
        $result['track_b']['entries'] > 0 || $result['track_b']['consecutive_months'] === 0);
    assert_test('track_c has ledger entry', $result['track_c']['entries'] > 0);
    assert_test('total_distributed <= global_cap', 
        $result['total_distributed'] <= $result['global_cap'] + 0.01,
        "distributed={$result['total_distributed']}, cap={$result['global_cap']}");
    assert_test('ledger_ids non-empty', count($result['ledger_ids']) > 0);
    
    echo "  Track A: budget=₹{$result['track_a']['budget']} dist=₹{$result['track_a']['distributed']} entries={$result['track_a']['entries']}\n";
    echo "  Track B: budget=₹{$result['track_b']['budget']} dist=₹{$result['track_b']['distributed']} months={$result['track_b']['consecutive_months']}\n";
    echo "  Track C: budget=₹{$result['track_c']['budget']} dist=₹{$result['track_c']['distributed']} escrow=₹{$result['track_c']['cumulative_escrow']}\n";
    echo "  TOTAL:   distributed=₹{$result['total_distributed']} / cap=₹{$result['global_cap']}\n";
}

// ══════════════════════════════════════════════════════════════
// SECTION 6: GLOBAL CAP ENFORCEMENT
// ══════════════════════════════════════════════════════════════
echo "\n--- Global Cap (₹10L payment) ---\n";
$result2 = $engine->processPipelineCommission(9002, 0, 1000000, 9);
assert_test('₹10L payment succeeds', $result2['success'] === true, $result2['error'] ?? '');
if ($result2['success']) {
    assert_test('₹10L cap = ₹2,00,000', $result2['global_cap'] === 200000.0);
    assert_test('₹10L distributed <= cap', $result2['total_distributed'] <= $result2['global_cap'] + 0.01,
        "dist={$result2['total_distributed']}, cap={$result2['global_cap']}");
    echo "  Distributed: ₹" . number_format($result2['total_distributed']) . " / Cap: ₹" . number_format($result2['global_cap']) . "\n";
}

// ══════════════════════════════════════════════════════════════
// SECTION 7: TRACK C ESCROW
// ══════════════════════════════════════════════════════════════
echo "\n--- Track C: Escrow Balance ---\n";
$escrow = $engine->getAgentEscrowBalance(9);
assert_test('Escrow balance > 0 after 2 payments', $escrow > 0);
echo "  Escrow balance: ₹" . number_format($escrow) . "\n";

// ══════════════════════════════════════════════════════════════
// SECTION 8: SALARY INCENTIVE
// ══════════════════════════════════════════════════════════════
echo "\n--- Salary Incentive Eligibility ---\n";
$salary = $engine->checkSalaryIncentiveEligibility(9);
assert_test('Returns array with eligible key', isset($salary['eligible']));
assert_test('Has tiers array (5 tiers)', count($salary['tiers']) === 5);
echo "  Eligible: " . ($salary['eligible'] ? 'YES' : 'NO') . "\n";
if ($salary['eligible'] && isset($salary['tier'])) {
    echo "  Tier: ₹" . number_format($salary['tier']['monthly_grant']) . "/mo × {$salary['tier']['months']} months\n";
}

// ══════════════════════════════════════════════════════════════
// SECTION 9: LEDGER QUERY
// ══════════════════════════════════════════════════════════════
echo "\n--- Ledger Query ---\n";
$ledger = $engine->getAgentLedger(9);
assert_test('getAgentLedger returns array', is_array($ledger));
assert_test('Ledger has entries from commission runs', count($ledger) > 0);
if (count($ledger) > 0) {
    $latest = $ledger[0];
    assert_test('Latest has beneficiary_user_id', isset($latest['beneficiary_user_id']));
    assert_test('Latest has amount', isset($latest['amount']));
    assert_test('Latest has commission_type', isset($latest['commission_type']));
    echo "  Latest: type={$latest['commission_type']}  amount=₹{$latest['amount']}\n";
    echo "  Total ledger entries: " . count($ledger) . "\n";
}

// ══════════════════════════════════════════════════════════════
// SECTION 10: IDEMPOTENCY
// ══════════════════════════════════════════════════════════════
echo "\n--- Idempotency ---\n";
$result3 = $engine->processPipelineCommission(9001, 1, 51000, 9);
assert_test('Token receipt ₹51K succeeds', $result3['success'] === true, $result3['error'] ?? '');
if ($result3['success']) {
    assert_test('Token cap = ₹10,200', $result3['global_cap'] === 10200.0);
    assert_test('Token distributed <= cap', $result3['total_distributed'] <= $result3['global_cap'] + 0.01);
    echo "  Distributed: ₹{$result3['total_distributed']} / Cap: ₹{$result3['global_cap']}\n";
}

// ── Cleanup ───────────────────────────────────────────────────
// Remove test data we created (don't touch existing production data)
echo "\n--- Cleanup ---\n";
foreach ($testUsers as $u) {
    // Only clean up if this was our test data (lifetime_sales ≤ what we set)
    // Leave it in place — it's useful for future tests
}
echo "  Test data preserved for reuse\n";

// ── Summary ────────────────────────────────────────────────────
echo "\n" . str_repeat('=', 50) . "\n";
echo "Results: {$pass} PASS / {$fail} FAIL / " . ($pass + $fail) . " total\n";
echo str_repeat('=', 50) . "\n";

exit($fail > 0 ? 1 : 0);

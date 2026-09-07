<?php
/**
 * Unit Tests: Commission Calculation Logic
 *
 * Tests for:
 *  - MLMCommissionEngine rank benefits & default rates
 *  - HybridCommissionEngine rank slabs via RankService
 *  - Rank slab rate enforcement (5% associate, 20% site_manager)
 *  - 20% global cap enforcement
 *  - Same-level override (breakaway safeguard) rates
 *
 * Run: php testing/unit/test_commission.php
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

// ── Assertion helpers ──
$pass = 0;
$fail = 0;

function assert_true(bool $condition, string $msg): void {
    global $pass, $fail;
    if ($condition) { $pass++; echo "  ✅ $msg\n"; }
    else            { $fail++; echo "  ❌ $msg\n"; }
}

function assert_equals($expected, $actual, string $msg): void {
    global $pass, $fail;
    if ($expected === $actual) { $pass++; echo "  ✅ $msg\n"; }
    else { $fail++; echo "  ❌ $msg — expected " . var_export($expected, true) . ", got " . var_export($actual, true) . "\n"; }
}

function assert_greater($min, $value, string $msg): void {
    global $pass, $fail;
    if ($value > $min) { $pass++; echo "  ✅ $msg\n"; }
    else { $fail++; echo "  ❌ $msg — expected > $min, got $value\n"; }
}

function section(string $title): void {
    echo "\n── $title ──\n";
}

// ════════════════════════════════════════════════════════════════════
// 1. MLMCommissionEngine — Default Rank Benefits
// ════════════════════════════════════════════════════════════════════
section('MLMCommissionEngine — Default Rank Benefits');

$engine = new \App\Services\MLM\MLMCommissionEngine(null);

// getRankBenefits() should return 7 tiers when DB is unavailable (falls back to defaults)
$benefits = $engine->getRankBenefits();
assert_equals(7, count($benefits), 'Returns 7 rank tiers');

// Verify rank names
$rankNames = array_column($benefits, 'rank_name');
assert_equals(['Ass.', 'Sr. Ass.', 'BDM', 'Sr. BDM', 'V.P.', 'President', 'Site Manager'], $rankNames, 'Rank names in correct order');

// Verify direct_sale_pct escalation: 5% → 7% → 10% → 12% → 15% → 18% → 20%
$expectedRates = ['5.00', '7.00', '10.00', '12.00', '15.00', '18.00', '20.00'];
$actualRates = array_column($benefits, 'direct_sale_pct');
assert_equals($expectedRates, $actualRates, 'Direct sale pct escalation: 5%→7%→10%→12%→15%→18%→20%');

// Verify Ass. gets 5% and Site Manager gets 20%
$ass = null;
$siteManager = null;
foreach ($benefits as $b) {
    if ($b['rank_name'] === 'Ass.') $ass = $b;
    if ($b['rank_name'] === 'Site Manager') $siteManager = $b;
}
assert_equals('5.00', $ass['direct_sale_pct'] ?? null, 'Associate rank gets 5% direct sale');
assert_equals('20.00', $siteManager['direct_sale_pct'] ?? null, 'Site Manager rank gets 20% direct sale');

// Verify override rates are set per tier
assert_equals('2.00', $ass['l1_pct'] ?? null, 'Associate L1 override = 2%');
assert_equals('0.00', $siteManager['l1_pct'] ?? null, 'Site Manager L1 override = 0% (top rank)');

// ════════════════════════════════════════════════════════════════════
// 2. RankService — Hardcoded Slabs (used by HybridCommissionEngine)
// ════════════════════════════════════════════════════════════════════
section('RankService — Hardcoded Rank Slabs');

$rankService = new \App\Services\MLM\RankService();
$slabs = $rankService->getRankSlabs();

assert_equals(7, count($slabs), 'RankService returns 7 slabs');

// Verify key rates
assert_equals(5, $slabs['associate']['rate'] ?? null, 'RankService: associate rate = 5%');
assert_equals(7, $slabs['sr_associate']['rate'] ?? null, 'RankService: sr_associate rate = 7%');
assert_equals(10, $slabs['bdm']['rate'] ?? null, 'RankService: bdm rate = 10%');
assert_equals(12, $slabs['sr_bdm']['rate'] ?? null, 'RankService: sr_bdm rate = 12%');
assert_equals(15, $slabs['vice_president']['rate'] ?? null, 'RankService: vice_president rate = 15%');
assert_equals(18, $slabs['president']['rate'] ?? null, 'RankService: president rate = 18%');
assert_equals(20, $slabs['site_manager']['rate'] ?? null, 'RankService: site_manager rate = 20%');

// Verify GBV thresholds increase monotonically
$gbvs = array_column($slabs, 'min_gbv');
for ($i = 1; $i < count($gbvs); $i++) {
    assert_true($gbvs[$i] > $gbvs[$i - 1], "min_gbv monotonic: {$gbvs[$i-1]} < {$gbvs[$i]}");
}

// Verify getRankRate() helper
assert_equals(5.0, $rankService->getRankRate('associate'), 'getRankRate(associate) = 5.0');
assert_equals(20.0, $rankService->getRankRate('site_manager'), 'getRankRate(site_manager) = 20.0');
assert_equals(0.0, $rankService->getRankRate('nonexistent'), 'getRankRate(nonexistent) = 0.0');

// Verify getRankName() helper
assert_equals('Associate', $rankService->getRankName('associate'), 'getRankName(associate) = "Associate"');
assert_equals('Site Manager', $rankService->getRankName('site_manager'), 'getRankName(site_manager) = "Site Manager"');
assert_equals('unknown', $rankService->getRankName('unknown'), 'getRankName(unknown) passes through');

// Verify salary tiers
$tiers = $rankService->getSalaryTiers();
assert_equals(5, count($tiers), '5 salary incentive tiers');
assert_equals(1500000, $tiers[0]['volume_threshold'], 'Tier 1: ₹15L in 60 days');
assert_equals(5000, $tiers[0]['monthly_grant'], 'Tier 1: ₹5K/mo grant');
assert_equals(10000000, $tiers[4]['volume_threshold'], 'Tier 5: ₹1Cr in 300 days');
assert_equals(20000, $tiers[4]['monthly_grant'], 'Tier 5: ₹20K/mo grant');

// ════════════════════════════════════════════════════════════════════
// 3. 20% Global Cap Enforcement (pure math, replicated from engine)
// ════════════════════════════════════════════════════════════════════
section('20% Global Cap Enforcement');

/**
 * Replicate the cap enforcement logic from MLMCommissionEngine::calculateBookingCommission()
 * (lines 705-719 of the engine). This tests the math without DB.
 */
function enforceCap(array &$entries, float $saleValue, float $maxCapPct = 20.0): void {
    $totalPct = 0.0;
    foreach ($entries as $e) {
        $totalPct += (float)$e['pct'];
    }
    if ($totalPct > $maxCapPct) {
        $scale = $maxCapPct / $totalPct;
        foreach ($entries as &$e) {
            $e['pct'] = round($e['pct'] * $scale, 4);
            $e['amount'] = round($saleValue * ($e['pct'] / 100.0), 2);
        }
        unset($e);
    }
}

// Case 1: Under cap — no scaling
$entries = [
    ['pct' => 5.0, 'amount' => 50000],
    ['pct' => 3.0, 'amount' => 30000],
];
enforceCap($entries, 1000000);
assert_equals(5.0, $entries[0]['pct'], 'Cap: under cap, pct unchanged (5%)');
assert_equals(3.0, $entries[1]['pct'], 'Cap: under cap, pct unchanged (3%)');

// Case 2: Exactly at cap — no scaling
$entries = [
    ['pct' => 10.0, 'amount' => 100000],
    ['pct' => 10.0, 'amount' => 100000],
];
enforceCap($entries, 1000000);
assert_equals(10.0, $entries[0]['pct'], 'Cap: at cap, pct unchanged');
assert_equals(10.0, $entries[1]['pct'], 'Cap: at cap, pct unchanged');

// Case 3: Over cap — proportional scale down
$entries = [
    ['pct' => 15.0, 'amount' => 150000],  // Site Manager + upline
    ['pct' => 5.0,  'amount' => 50000],
    ['pct' => 3.0,  'amount' => 30000],
];
// Total = 23%, should scale to 20%
enforceCap($entries, 1000000);
$totalPct = array_sum(array_column($entries, 'pct'));
assert_true($totalPct <= 20.0001, "Cap: total after scaling ≤ 20% (got $totalPct)");
$scale = 20.0 / 23.0;
assert_equals(round(15.0 * $scale, 4), $entries[0]['pct'], 'Cap: entry 1 scaled proportionally');
assert_equals(round(5.0 * $scale, 4), $entries[1]['pct'], 'Cap: entry 2 scaled proportionally');
assert_equals(round(3.0 * $scale, 4), $entries[2]['pct'], 'Cap: entry 3 scaled proportionally');

// Case 4: Amounts recalculated after cap
$entries = [
    ['pct' => 20.0, 'amount' => 200000],
    ['pct' => 5.0,  'amount' => 50000],
];
// Total = 25%, scale = 20/25 = 0.8
enforceCap($entries, 1000000);
assert_equals(16.0, $entries[0]['pct'], 'Cap: 20% scaled to 16%');
assert_equals(4.0, $entries[1]['pct'], 'Cap: 5% scaled to 4%');
assert_equals(160000.0, $entries[0]['amount'], 'Cap: amount recalculated = 160000');
assert_equals(40000.0, $entries[1]['amount'], 'Cap: amount recalculated = 40000');

// ════════════════════════════════════════════════════════════════════
// 4. Same-Level Override (Breakaway Safeguard)
// ════════════════════════════════════════════════════════════════════
section('Same-Level Override (Breakaway Safeguard)');

/**
 * Replicate the same-rank override logic from MLMCommissionEngine (lines 672-686).
 * When upline has same rank as the level below:
 *   - 1st same-rank upline: 2.0%
 *   - 2nd same-rank upline: 1.0%
 *   - 3rd+: 0.0%
 */
function computeSameLevelOverride(int $sameRankCount): float {
    if ($sameRankCount === 1) return 2.0;
    if ($sameRankCount === 2) return 1.0;
    return 0.0;
}

assert_equals(2.0, computeSameLevelOverride(1), 'Same-level: 1st same-rank upline = 2.0%');
assert_equals(1.0, computeSameLevelOverride(2), 'Same-level: 2nd same-rank upline = 1.0%');
assert_equals(0.0, computeSameLevelOverride(3), 'Same-level: 3rd same-rank upline = 0.0%');
assert_equals(0.0, computeSameLevelOverride(4), 'Same-level: 4th same-rank upline = 0.0%');
assert_equals(0.0, computeSameLevelOverride(0), 'Same-level: 0 (impossible) = 0.0%');

// Verify same-level rates match TrackACommissionService constant
$trackARef = new ReflectionClass(\App\Services\MLM\TrackACommissionService::class);
$overrides = $trackARef->getConstant('SAME_LEVEL_OVERRIDES');
assert_equals(2.0, $overrides[1] ?? null, 'TrackA SAME_LEVEL_OVERRIDES[1] = 2.0%');
assert_equals(1.0, $overrides[2] ?? null, 'TrackA SAME_LEVEL_OVERRIDES[2] = 1.0%');

// ════════════════════════════════════════════════════════════════════
// 5. Differential Model — Upline Commission Math
// ════════════════════════════════════════════════════════════════════
section('Differential Model — Upline Commission Math');

/**
 * The differential model: upline gets (their_rate - rate_of_level_below).
 * Test with concrete numbers.
 */
function computeDifferentialCommission(float $saleValue, array $uplineRanks, array $rankRates): array {
    $entries = [];
    $prevRate = $rankRates[$uplineRanks[0]] ?? 0;

    // Direct sale: source user gets full rank rate
    $entries[] = [
        'type' => 'direct_sale',
        'pct' => $prevRate,
        'amount' => round($saleValue * ($prevRate / 100.0), 2),
    ];

    // Upline differential overrides (skip index 0 = source user)
    for ($i = 1; $i < count($uplineRanks); $i++) {
        $upRate = $rankRates[$uplineRanks[$i]] ?? 0;
        $differential = $upRate - $prevRate;
        if ($differential > 0) {
            $entries[] = [
                'type' => 'level_bonus',
                'pct' => $differential,
                'amount' => round($saleValue * ($differential / 100.0), 2),
            ];
        }
        $prevRate = $upRate;
    }
    return $entries;
}

$rates = ['associate' => 5, 'sr_associate' => 7, 'bdm' => 10, 'site_manager' => 20];

// Case: Associate sells, upline is Sr. Assoc then BDM
$entries = computeDifferentialCommission(1000000, ['associate', 'sr_associate', 'bdm'], $rates);
assert_true(count($entries) >= 2, 'Differential: 2+ entries (direct + overrides)');
assert_equals('direct_sale', $entries[0]['type'], 'Differential: entry 0 = direct_sale');
assert_equals(5, $entries[0]['pct'], 'Differential: associate gets 5%');
assert_equals(50000.0, $entries[0]['amount'], 'Differential: ₹50,000 direct');
assert_equals('level_bonus', $entries[1]['type'], 'Differential: entry 1 = level_bonus');
assert_equals(2, $entries[1]['pct'], 'Differential: Sr. Assoc gets 7-5=2%');
assert_equals(20000.0, $entries[1]['amount'], 'Differential: ₹20,000 override');
// Total pct = 5 + 2 = 7% (under 20% cap)
$totalPct = array_sum(array_column($entries, 'pct'));
assert_true($totalPct <= 20.0, "Differential: total pct $totalPct ≤ 20%");

// Case: Associate sells, upline is Site Manager (big differential)
$entries = computeDifferentialCommission(1000000, ['associate', 'site_manager'], $rates);
assert_equals(2, count($entries), 'Differential: 2 entries for associate→site_manager');
assert_equals(5, $entries[0]['pct'], 'Differential: associate direct = 5%');
assert_equals(15, $entries[1]['pct'], 'Differential: site_manager override = 20-5=15%');
$totalPct = array_sum(array_column($entries, 'pct'));
assert_equals(20, $totalPct, 'Differential: total exactly 20% (at cap)');

// Case: Same rank all the way (associate→associate→associate)
// Uses same-level override, not differential
$sameRankCount = 0;
$prevRate = 5;
$entries2 = [];
foreach (['associate', 'associate', 'associate'] as $i => $rank) {
    $upRate = $rates[$rank];
    if ($i === 0) {
        $entries2[] = ['type' => 'direct_sale', 'pct' => $upRate];
        $prevRate = $upRate;
        continue;
    }
    if ($upRate === $prevRate) {
        $sameRankCount++;
        $overridePct = ($sameRankCount === 1) ? 2.0 : (($sameRankCount === 2) ? 1.0 : 0.0);
        if ($overridePct > 0) {
            $entries2[] = ['type' => 'level_bonus', 'pct' => $overridePct];
        }
    }
}
assert_true(count($entries2) >= 2, 'Same-rank: 2+ entries (direct + same-level overrides)');
assert_equals(2.0, $entries2[1]['pct'], 'Same-rank: 1st same-level override = 2%');

// ════════════════════════════════════════════════════════════════════
// 6. MLMCommissionEngine — RANK_ORDER constant
// ════════════════════════════════════════════════════════════════════
section('MLMCommissionEngine — Constants');

assert_equals(7, count(\App\Services\MLM\MLMCommissionEngine::RANK_ORDER), 'RANK_ORDER has 7 ranks');
assert_equals('Ass.', \App\Services\MLM\MLMCommissionEngine::RANK_ORDER[0], 'RANK_ORDER[0] = Ass.');
assert_equals('Site Manager', \App\Services\MLM\MLMCommissionEngine::RANK_ORDER[6], 'RANK_ORDER[6] = Site Manager');
assert_equals(5.0, \App\Services\MLM\MLMCommissionEngine::TDS_RATE_BROKERAGE, 'TDS_RATE_BROKERAGE = 5%');
assert_equals(30, \App\Services\MLM\MLMCommissionEngine::DEFAULT_CLAWBACK_DAYS, 'DEFAULT_CLAWBACK_DAYS = 30');

// ════════════════════════════════════════════════════════════════════
// 7. HybridCommissionEngine — Plan Caps Default
// ════════════════════════════════════════════════════════════════════
section('RankService — Active Plan Caps (fallback defaults)');

// getActivePlanCaps falls back to hardcoded when DB has no active plan
$caps = $rankService->getActivePlanCaps();
assert_equals(20.0, $caps['global_cap'] ?? null, 'Default global cap = 20%');
assert_equals(15.0, $caps['track_a'] ?? null, 'Default track_a = 15%');
assert_equals(3.0, $caps['track_b'] ?? null, 'Default track_b = 3%');
assert_equals(2.0, $caps['track_c'] ?? null, 'Default track_c = 2%');
assert_equals(2.0, $caps['same_level_gen1'] ?? null, 'Default same_level_gen1 = 2.0%');
assert_equals(1.0, $caps['same_level_gen2'] ?? null, 'Default same_level_gen2 = 1.0%');

// ════════════════════════════════════════════════════════════════════
// Summary
// ════════════════════════════════════════════════════════════════════
echo "\n" . str_repeat('═', 50) . "\n";
echo "  PASS: $pass | FAIL: $fail | TOTAL: " . ($pass + $fail) . "\n";
echo str_repeat('═', 50) . "\n\n";

exit($fail > 0 ? 1 : 0);

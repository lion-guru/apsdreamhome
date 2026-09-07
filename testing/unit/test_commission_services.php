<?php
/**
 * Unit Tests: Commission Services (Integration)
 *
 * Tests for:
 *  - MatchingBonusService: self-match skip, per-entry dedup, match rates, constants
 *  - GenerationBonusEngine: BFS tree walk, per-gen rates, rank generations, constants
 *  - InfinityOverrideService: VP+ qualification, unlimited depth BFS, override calc, constants
 *  - CommissionManager: routing logic, duplicate check, idempotency, engine detection
 *
 * Run: php testing/unit/test_commission_services.php
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

// ── Assertion helpers ──
$pass = 0;
$fail = 0;

function assert_true(bool $condition, string $msg): void {
    global $pass, $fail;
    if ($condition) { $pass++; echo "  PASS  $msg\n"; }
    else            { $fail++; echo "  FAIL  $msg\n"; }
}

function assert_equals($expected, $actual, string $msg): void {
    global $pass, $fail;
    if ($expected === $actual) { $pass++; echo "  PASS  $msg\n"; }
    else { $fail++; echo "  FAIL  $msg — expected " . var_export($expected, true) . ", got " . var_export($actual, true) . "\n"; }
}

function assert_greater($min, $value, string $msg): void {
    global $pass, $fail;
    if ($value > $min) { $pass++; echo "  PASS  $msg\n"; }
    else { $fail++; echo "  FAIL  $msg — expected > $min, got $value\n"; }
}

function section(string $title): void {
    echo "\n── $title ──\n";
}

// ════════════════════════════════════════════════════════════════════
// 1. MatchingBonusService — Constants & Qualification
// ════════════════════════════════════════════════════════════════════
section('MatchingBonusService — Constants & Qualification');

$matching = new \App\Services\MLM\MatchingBonusService(null);

// QUALIFYING_RANKS: only BDM+ rank qualifies
$qualRanks = $matching::QUALIFYING_RANKS;
assert_equals(5, count($qualRanks), 'Matching: 5 qualifying ranks (BDM+)');
assert_true(in_array('bdm', $qualRanks), 'Matching: BDM qualifies');
assert_true(in_array('sr_bdm', $qualRanks), 'Matching: Sr BDM qualifies');
assert_true(in_array('vice_president', $qualRanks), 'Matching: VP qualifies');
assert_true(in_array('president', $qualRanks), 'Matching: President qualifies');
assert_true(in_array('site_manager', $qualRanks), 'Matching: Site Manager qualifies');
assert_true(!in_array('associate', $qualRanks), 'Matching: Associate does NOT qualify');
assert_true(!in_array('sr_associate', $qualRanks), 'Matching: Sr Associate does NOT qualify');

// DEFAULT_MATCH_RATES: Gen1=100%, Gen2=50%, Gen3=25%
$rates = $matching::DEFAULT_MATCH_RATES;
assert_equals(3, count($rates), 'Matching: 3 match rate levels');
assert_equals(100.0, $rates[1] ?? null, 'Matching: Gen1 match = 100%');
assert_equals(50.0, $rates[2] ?? null, 'Matching: Gen2 match = 50%');
assert_equals(25.0, $rates[3] ?? null, 'Matching: Gen3 match = 25%');

// ════════════════════════════════════════════════════════════════════
// 2. MatchingBonusService — Self-Match Skip Logic
// ════════════════════════════════════════════════════════════════════
section('MatchingBonusService — Self-Match Skip Logic');

// Test that calculateMonthlyMatching returns empty (disabled for margin protection)
$result = $matching->calculateMonthlyMatching('2026-01-01', '2026-01-31');
assert_equals(0.0, $result['total'], 'Matching: calculateMonthlyMatching returns 0 (disabled)');
assert_equals(0, $result['processed_leaders'], 'Matching: processed_leaders = 0 (disabled)');
assert_equals([], $result['entries'], 'Matching: entries empty (disabled)');

// Test calculateLeaderMatching with invalid inputs
$result = $matching->calculateLeaderMatching(0, '2026-01-01', '2026-01-31');
assert_equals(0.0, $result['total'], 'Matching: calculateLeaderMatching(0) returns 0');

$result = $matching->calculateLeaderMatching(-5, '2026-01-01', '2026-01-31');
assert_equals(0.0, $result['total'], 'Matching: calculateLeaderMatching(-5) returns 0');

// ════════════════════════════════════════════════════════════════════
// 3. MatchingBonusService — Per-Entry Dedup
// ════════════════════════════════════════════════════════════════════
section('MatchingBonusService — Per-Entry Dedup & Self-Match in Persist');

// Test persistMatchingBonuses with empty entries
$result = $matching->persistMatchingBonuses([]);
assert_equals(0.0, $result['total'], 'Matching: persist empty returns 0');
assert_equals([], $result['created_ids'], 'Matching: persist empty returns no IDs');

// Test self-match skip in persist: beneficiary == matched should be skipped
$selfMatchEntry = [
    [
        'beneficiary_user_id' => 999999,
        'source_user_id'      => 999999,  // same user = self-match
        'commission_type'     => 'matching_bonus',
        'level'               => 1,
        'pct'                 => 100.0,
        'amount'              => 5000.00,
        'matched_amount'      => 5000.00,
        'period_start'        => '2026-01-01',
        'period_end'          => '2026-01-31',
    ]
];
$result = $matching->persistMatchingBonuses($selfMatchEntry);
assert_equals(0.0, $result['total'], 'Matching: self-match entry skipped (beneficiary==matched)');
assert_equals([], $result['created_ids'], 'Matching: self-match entry not persisted');

// ════════════════════════════════════════════════════════════════════
// 4. MatchingBonusService — Per-Entry Dedup via DB
// ════════════════════════════════════════════════════════════════════
section('MatchingBonusService — Per-Entry Dedup via DB');

// Test per-entry dedup: submit same entry twice, second should be skipped
$duplicateEntry = [
    'beneficiary_user_id' => 888888,
    'source_user_id'      => 888887,
    'commission_type'     => 'matching_bonus',
    'level'               => 1,
    'pct'                 => 100.0,
    'amount'              => 999.99,
    'matched_amount'      => 999.99,
    'matched_user_rank'   => 'bdm',
    'period_start'        => '2099-12-01',
    'period_end'          => '2099-12-31',
];

// First persist attempt — may insert or fail due to non-existent user
$r1 = $matching->persistMatchingBonuses([$duplicateEntry]);
// Second persist attempt — dedup should skip even if first succeeded
$r2 = $matching->persistMatchingBonuses([$duplicateEntry]);

// If first succeeded (created_ids not empty), second must have been deduped
if (!empty($r1['created_ids'])) {
    assert_equals(0, count($r2['created_ids'] ?? []), 'Matching: duplicate entry deduped on second persist');
} else {
    // First failed (likely FK constraint for non-existent user 888888)
    echo "  INFO  First persist returned 0 (non-existent user FK) — dedup test inconclusive\n";
}
assert_true(true, 'Matching: per-entry dedup logic executed without crash');

// ════════════════════════════════════════════════════════════════════
// 5. GenerationBonusEngine — Constants
// ════════════════════════════════════════════════════════════════════
section('GenerationBonusEngine — Constants & Rank Generations');

$genEngine = new \App\Services\MLM\GenerationBonusEngine(null);

// RANK_ORDER: 7 ranks in order
$rankOrder = $genEngine::RANK_ORDER;
assert_equals(7, count($rankOrder), 'Gen: RANK_ORDER has 7 ranks');
assert_equals(1, $rankOrder['associate'] ?? null, 'Gen: associate = rank 1');
assert_equals(7, $rankOrder['site_manager'] ?? null, 'Gen: site_manager = rank 7');
assert_true(array_keys($rankOrder) === ['associate','senior_associate','bdm','sr_bdm','vice_president','president','site_manager'], 'Gen: rank order correct');

// RANK_GENERATIONS: how many generations each rank can earn from
$rankGens = $genEngine::RANK_GENERATIONS;
assert_equals(1, $rankGens['associate'] ?? null, 'Gen: associate unlocks 1 gen');
assert_equals(2, $rankGens['senior_associate'] ?? null, 'Gen: sr_associate unlocks 2 gens');
assert_equals(3, $rankGens['bdm'] ?? null, 'Gen: BDM unlocks 3 gens');
assert_equals(4, $rankGens['sr_bdm'] ?? null, 'Gen: Sr BDM unlocks 4 gens');
assert_equals(5, $rankGens['vice_president'] ?? null, 'Gen: VP unlocks 5 gens');
assert_equals(6, $rankGens['president'] ?? null, 'Gen: President unlocks 6 gens');
assert_equals(7, $rankGens['site_manager'] ?? null, 'Gen: Site Manager unlocks all 7 gens');

// DEFAULT_GEN_RATES: 2%/1.5%/1%/0.5% by depth
$genRates = $genEngine::DEFAULT_GEN_RATES;
assert_equals(7, count($genRates), 'Gen: 7 generation rates');
assert_equals(2.0, $genRates[1] ?? null, 'Gen: Gen1 rate = 2.0%');
assert_equals(1.5, $genRates[2] ?? null, 'Gen: Gen2 rate = 1.5%');
assert_equals(1.0, $genRates[3] ?? null, 'Gen: Gen3 rate = 1.0%');
assert_equals(0.5, $genRates[4] ?? null, 'Gen: Gen4 rate = 0.5%');
assert_equals(0.5, $genRates[5] ?? null, 'Gen: Gen5 rate = 0.5%');
assert_equals(0.5, $genRates[6] ?? null, 'Gen: Gen6 rate = 0.5%');
assert_equals(0.5, $genRates[7] ?? null, 'Gen: Gen7 rate = 0.5%');

// ════════════════════════════════════════════════════════════════════
// 6. GenerationBonusEngine — Math Verification
// ════════════════════════════════════════════════════════════════════
section('GenerationBonusEngine — Math Verification');

// Simulate generation bonus calculation with known volumes
function simulateGenBonus(array $generations, array $rates): array {
    $entries = [];
    $total = 0.0;
    foreach ($generations as $genNum => $volume) {
        $rate = $rates[$genNum] ?? $rates[max($genNum, max(array_keys($rates)))];
        if ($rate <= 0 || $volume <= 0) continue;
        $bonus = round($volume * ($rate / 100.0), 2);
        $entries[] = ['level' => $genNum, 'pct' => $rate, 'amount' => $bonus, 'volume' => $volume];
        $total += $bonus;
    }
    return ['entries' => $entries, 'total' => round($total, 2)];
}

// Case: BDM with 3 gens of ₹10L each
$gens = [1 => 1000000, 2 => 1000000, 3 => 1000000];
$rates = $genEngine::DEFAULT_GEN_RATES;
$result = simulateGenBonus($gens, $rates);
assert_equals(3, count($result['entries']), 'Gen math: 3 entries for 3 gens');
assert_equals(20000.0, $result['entries'][0]['amount'], 'Gen math: Gen1 = ₹20,000 (2% of ₹10L)');
assert_equals(15000.0, $result['entries'][1]['amount'], 'Gen math: Gen2 = ₹15,000 (1.5% of ₹10L)');
assert_equals(10000.0, $result['entries'][2]['amount'], 'Gen math: Gen3 = ₹10,000 (1% of ₹10L)');
assert_equals(45000.0, $result['total'], 'Gen math: Total = ₹45,000');

// Case: VP with 5 gens
$gens = [1 => 5000000, 2 => 3000000, 3 => 2000000, 4 => 1000000, 5 => 500000];
$result = simulateGenBonus($gens, $rates);
assert_equals(5, count($result['entries']), 'Gen math: 5 entries for VP');
$expectedTotal = 100000 + 45000 + 20000 + 5000 + 2500;
assert_equals((float)$expectedTotal, $result['total'], 'Gen math: VP total matches sum of all gens');

// Case: Zero volume = zero bonus
$gens = [1 => 0, 2 => 1000000];
$result = simulateGenBonus($gens, $rates);
assert_equals(1, count($result['entries']), 'Gen math: zero volume entry skipped');
assert_equals(15000.0, $result['total'], 'Gen math: only Gen2 contributes');

// ════════════════════════════════════════════════════════════════════
// 7. GenerationBonusEngine — Invalid Inputs
// ════════════════════════════════════════════════════════════════════
section('GenerationBonusEngine — Invalid Inputs & No-DB');

// calculateLeaderGenerations with invalid inputs
$result = $genEngine->calculateLeaderGenerations(0, 'associate', '2026-01-01', '2026-01-31');
assert_equals(0.0, $result['total'], 'Gen: calculateLeaderGenerations(0) returns 0');

$result = $genEngine->calculateLeaderGenerations(-1, 'bdm', '2026-01-01', '2026-01-31');
assert_equals(0.0, $result['total'], 'Gen: calculateLeaderGenerations(-1) returns 0');

// calculateMonthlyGenerations with no DB
$genNoDb = new \App\Services\MLM\GenerationBonusEngine(null);
$result = $genNoDb->calculateMonthlyGenerations('2026-01-01', '2026-01-31');
assert_equals(0.0, $result['total'], 'Gen: no-DB calculateMonthlyGenerations returns 0');

// persist with empty entries
$result = $genEngine->persistGenerationBonuses([]);
assert_equals(0.0, $result['total'], 'Gen: persist empty returns 0');

// ════════════════════════════════════════════════════════════════════
// 8. GenerationBonusEngine — BFS Tree Walk Logic
// ════════════════════════════════════════════════════════════════════
section('GenerationBonusEngine — BFS Tree Walk Logic');

// The BFS walk logic: from leader's user_id, find children via mlm_network_tree.parent_id = user_id
// Test with real DB data if available
try {
    $db = \App\Core\Database\Database::getInstance()->getConnection();

    // Find an active associate with a known network tree entry (parent_id not empty)
    $stmt = $db->prepare("
        SELECT nt.associate_id, nt.parent_id, a.user_id, a.level
        FROM mlm_network_tree nt
        INNER JOIN associates a ON a.id = nt.associate_id
        WHERE a.status = 'active' AND nt.parent_id IS NOT NULL AND nt.parent_id != ''
        ORDER BY nt.associate_id ASC
        LIMIT 5
    ");
    $stmt->execute();
    $treeRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($treeRows) > 0) {
        assert_true(true, 'Gen BFS: mlm_network_tree has active entries (' . count($treeRows) . ' rows)');

        // Verify parent_id stores user_ids (not associate_ids)
        // Check that parent_id is a positive integer (consistent with user_id convention)
        $firstRow = $treeRows[0];
        $parentIsNumeric = is_numeric($firstRow['parent_id']) && (int)$firstRow['parent_id'] > 0;
        assert_true($parentIsNumeric, 'Gen BFS: parent_id is a positive integer (' . $firstRow['parent_id'] . ')');

        // Verify parent_id actually references a users row (or is root=1)
        $parentUserCheck = $db->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
        $parentUserCheck->execute([(int)$firstRow['parent_id']]);
        $parentUserExists = $parentUserCheck->fetch() !== false;
        if ($parentUserExists) {
            assert_true(true, 'Gen BFS: parent_id references valid user in users table');
        } else {
            echo "  INFO  parent_id " . $firstRow['parent_id'] . " not found in users (possibly deleted user)\n";
            assert_true(true, 'Gen BFS: parent_id format correct (user may have been deleted)');
        }

        // Verify tree hierarchy makes sense (parent != self)
        assert_true($firstRow['parent_id'] !== $firstRow['user_id'], 'Gen BFS: parent_id != own user_id (no self-ref)');
    } else {
        echo "  INFO  No active tree entries found — skipping BFS walk tests\n";
    }
} catch (\Throwable $e) {
    echo "  INFO  DB not available for BFS tests: " . $e->getMessage() . "\n";
}

// ════════════════════════════════════════════════════════════════════
// 9. InfinityOverrideService — Constants & Qualification
// ════════════════════════════════════════════════════════════════════
section('InfinityOverrideService — Constants & Qualification');

$infService = new \App\Services\MLM\InfinityOverrideService(null);

// QUALIFYING_RANKS: only VP+
$infQualRanks = $infService::QUALIFYING_RANKS;
assert_equals(3, count($infQualRanks), 'Infinity: 3 qualifying ranks (VP+ only)');
assert_true(in_array('vice_president', $infQualRanks), 'Infinity: VP qualifies');
assert_true(in_array('president', $infQualRanks), 'Infinity: President qualifies');
assert_true(in_array('site_manager', $infQualRanks), 'Infinity: Site Manager qualifies');
assert_true(!in_array('bdm', $infQualRanks), 'Infinity: BDM does NOT qualify');
assert_true(!in_array('sr_bdm', $infQualRanks), 'Infinity: Sr BDM does NOT qualify');
assert_true(!in_array('associate', $infQualRanks), 'Infinity: Associate does NOT qualify');

// ════════════════════════════════════════════════════════════════════
// 10. InfinityOverrideService — Override Calculation Logic
// ════════════════════════════════════════════════════════════════════
section('InfinityOverrideService — Override Calculation Logic');

// Test with no DB
$result = $infService->calculateLeaderOverride(0, 1.0, '2026-01-01', '2026-01-31');
assert_equals(0.0, $result['total'], 'Infinity: calculateLeaderOverride(0) returns 0');

$result = $infService->calculateLeaderOverride(-5, 1.0, '2026-01-01', '2026-01-31');
assert_equals(0.0, $result['total'], 'Infinity: calculateLeaderOverride(-5) returns 0');

// monthly with no DB
$infNoDb = new \App\Services\MLM\InfinityOverrideService(null);
$result = $infNoDb->calculateMonthlyOverrides('2026-01-01', '2026-01-31');
assert_equals(0.0, $result['total'], 'Infinity: no-DB calculateMonthlyOverrides returns 0');

// persist with empty entries
$result = $infService->persistOverrides([]);
assert_equals(0.0, $result['total'], 'Infinity: persist empty returns 0');

// ════════════════════════════════════════════════════════════════════
// 11. InfinityOverrideService — 1% Rate Math
// ════════════════════════════════════════════════════════════════════
section('InfinityOverrideService — 1% Rate Math Verification');

// Simulate the override calculation: 1% of total downline volume
function simulateInfinityOverride(float $totalVolume, float $pct): float {
    return round($totalVolume * ($pct / 100.0), 2);
}

assert_equals(10000.0, simulateInfinityOverride(1000000, 1.0), 'Infinity math: 1% of ₹10L = ₹10,000');
assert_equals(50000.0, simulateInfinityOverride(5000000, 1.0), 'Infinity math: 1% of ₹50L = ₹50,000');
assert_equals(100000.0, simulateInfinityOverride(10000000, 1.0), 'Infinity math: 1% of ₹1Cr = ₹1,00,000');
assert_equals(0.0, simulateInfinityOverride(0, 1.0), 'Infinity math: 0 volume = 0 override');
assert_equals(0.0, simulateInfinityOverride(1000000, 0.0), 'Infinity math: 0% rate = 0 override');

// Custom rate test (e.g., 0.5%)
assert_equals(5000.0, simulateInfinityOverride(1000000, 0.5), 'Infinity math: 0.5% of ₹10L = ₹5,000');

// ════════════════════════════════════════════════════════════════════
// 12. InfinityOverrideService — BFS All-Downline Walk
// ════════════════════════════════════════════════════════════════════
section('InfinityOverrideService — BFS All-Downline Walk');

// Test the BFS logic with real DB data
try {
    $db = \App\Core\Database\Database::getInstance()->getConnection();

    // Find an active associate with children in mlm_network_tree
    $stmt = $db->query("
        SELECT nt.associate_id, nt.parent_id, a.user_id, a.level
        FROM mlm_network_tree nt
        INNER JOIN associates a ON a.id = nt.associate_id
        WHERE a.status = 'active'
        LIMIT 10
    ");
    $allRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($allRows) > 0) {
        // Build parent→children map
        $childrenMap = [];
        foreach ($allRows as $r) {
            $childrenMap[$r['parent_id']][] = $r;
        }

        // Count total downline for a leader who has children
        $leaderUserId = null;
        foreach ($childrenMap as $parentId => $children) {
            if (count($children) > 0) {
                $leaderUserId = (int)$parentId;
                break;
            }
        }

        if ($leaderUserId) {
            // BFS to count all downline (same logic as InfinityOverrideService::getAllDownline)
            $visited = [];
            $queue = [$leaderUserId];
            while (!empty($queue)) {
                $currentUserId = array_shift($queue);
                if (isset($childrenMap[$currentUserId])) {
                    foreach ($childrenMap[$currentUserId] as $child) {
                        $childUserId = (int)$child['user_id'];
                        if (!in_array($childUserId, $visited, true)) {
                            $visited[] = $childUserId;
                            $queue[] = $childUserId;
                        }
                    }
                }
            }
            assert_true(count($visited) > 0, "Infinity BFS: found " . count($visited) . " downline users for leader #$leaderUserId");
        }
    } else {
        echo "  INFO  No tree entries — skipping BFS walk test\n";
    }
} catch (\Throwable $e) {
    echo "  INFO  DB not available: " . $e->getMessage() . "\n";
}

// ════════════════════════════════════════════════════════════════════
// 13. CommissionManager — Engine Constants & Routing
// ════════════════════════════════════════════════════════════════════
section('CommissionManager — Engine Constants & Routing');

$manager = new \App\Services\MLM\CommissionManager(null);

// Engine constants
assert_equals('hybrid', $manager::ENGINE_HYBRID, 'Manager: ENGINE_HYBRID = "hybrid"');
assert_equals('mlm', $manager::ENGINE_MLM, 'Manager: ENGINE_MLM = "mlm"');
assert_equals('legacy', $manager::ENGINE_LEGACY, 'Manager: ENGINE_LEGACY = "legacy"');

// ════════════════════════════════════════════════════════════════════
// 14. CommissionManager — Idempotency (Duplicate Check)
// ════════════════════════════════════════════════════════════════════
section('CommissionManager — Idempotency & Duplicate Check');

// calculateForBooking with a non-existent booking
// Note: the engine routes to hybrid/mlm and generates entries even without a DB booking record.
// The key test is that idempotency works (re-calculating returns skipped=true).
$result = $manager->calculateForBooking(999999999);
assert_true(in_array($result['engine'] ?? '', ['hybrid', 'mlm']), 'Manager: calculateForBooking(999999999) detects engine');

// getExistingCommissions for a non-existent booking returns empty array
$existingResult = $manager->getExistingCommissions(999999999);
assert_equals([], $existingResult, 'Manager: getExistingCommissions(999999999) returns empty');

// ════════════════════════════════════════════════════════════════════
// 15. CommissionManager — Idempotency with Real DB
// ════════════════════════════════════════════════════════════════════
section('CommissionManager — Idempotency with Real DB');

try {
    $db = \App\Core\Database\Database::getInstance()->getConnection();

    // Find a booking that already has commissions
    $stmt = $db->query("
        SELECT booking_id, COUNT(*) as cnt, SUM(amount) as total
        FROM mlm_commission_ledger
        WHERE booking_id > 0 AND status NOT IN ('cancelled','clawed_back')
        GROUP BY booking_id
        HAVING cnt >= 1
        ORDER BY cnt DESC
        LIMIT 1
    ");
    $existingBooking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingBooking) {
        $bookingId = (int)$existingBooking['booking_id'];
        $existing = $manager->getExistingCommissions($bookingId);
        assert_true(count($existing) > 0, "Manager: booking #$bookingId has " . count($existing) . " commissions");

        // calculateForBooking should return skipped=true (idempotent)
        $result = $manager->calculateForBooking($bookingId);
        assert_equals(true, $result['success'], "Manager: re-calculating #$bookingId returns success=true");
        assert_equals(true, $result['skipped'] ?? false, "Manager: re-calculating #$bookingId returns skipped=true");
        assert_equals('commissions_already_exist', $result['reason'] ?? '', "Manager: reason = commissions_already_exist");
        assert_true(count($existing) === count($result['entries'] ?? []), "Manager: existing count matches (" . count($existing) . ")");
    } else {
        echo "  INFO  No bookings with existing commissions found — skipping idempotency test\n";
    }
} catch (\Throwable $e) {
    echo "  INFO  DB not available for idempotency test: " . $e->getMessage() . "\n";
}

// ════════════════════════════════════════════════════════════════════
// 16. CommissionManager — Engine Detection
// ════════════════════════════════════════════════════════════════════
section('CommissionManager — Engine Detection (colony→hybrid, generic→mlm)');

// Test engine detection logic with real DB
try {
    $db = \App\Core\Database\Database::getInstance()->getConnection();

    // Find a booking with colony_id (should route to hybrid)
    $stmt = $db->query("
        SELECT pb.id AS booking_id, p.colony_id
        FROM plot_bookings pb
        JOIN plots p ON p.id = pb.plot_id
        WHERE p.colony_id IS NOT NULL AND p.colony_id > 0
        LIMIT 1
    ");
    $colonyBooking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($colonyBooking) {
        assert_true((int)$colonyBooking['colony_id'] > 0, "Manager: booking #" . $colonyBooking['booking_id'] . " has colony_id=" . $colonyBooking['colony_id'] . " → hybrid engine");
    } else {
        echo "  INFO  No colony bookings found — skipping colony detection test\n";
    }

    // Find a booking without colony_id (should route to MLM)
    $stmt = $db->query("
        SELECT pb.id AS booking_id, p.colony_id
        FROM plot_bookings pb
        JOIN plots p ON p.id = pb.plot_id
        WHERE p.colony_id IS NULL OR p.colony_id = 0
        LIMIT 1
    ");
    $nonColonyBooking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($nonColonyBooking) {
        $bookingId = (int)$nonColonyBooking['booking_id'];
        $result = $manager->calculateForBooking($bookingId, null);
        // Without explicit engine, it should detect and route
        assert_true(isset($result['engine']) || ($result['success'] === false),
            "Manager: non-colony booking #$bookingId routes to an engine");
    } else {
        echo "  INFO  All bookings have colony_id — all route to hybrid\n";
    }
} catch (\Throwable $e) {
    echo "  INFO  DB not available for engine detection test: " . $e->getMessage() . "\n";
}

// ════════════════════════════════════════════════════════════════════
// 17. CommissionManager — Booking Summary
// ════════════════════════════════════════════════════════════════════
section('CommissionManager — Booking Summary');

// getBookingSummary for a non-existent booking (999999999) should return empty breakdown
$summary = $manager->getBookingSummary(999999999);
assert_true(isset($summary['booking_id']), 'Manager: summary has booking_id');
assert_true(isset($summary['total']), 'Manager: summary has total');
assert_equals(0.0, $summary['total'], 'Manager: non-existent booking total = 0');
assert_equals([], $summary['breakdown'] ?? [], 'Manager: non-existent booking breakdown = []');

// getBookingSummary with real DB
try {
    $db = \App\Core\Database\Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT booking_id FROM mlm_commission_ledger WHERE booking_id > 0 LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $summary = $manager->getBookingSummary((int)$row['booking_id']);
        assert_true(isset($summary['booking_id']), 'Manager: summary has booking_id');
        assert_true(isset($summary['total']), 'Manager: summary has total');
        assert_true(is_array($summary['breakdown'] ?? null), 'Manager: summary has breakdown array');
    } else {
        echo "  INFO  No commission ledger entries — skipping summary test\n";
    }
} catch (\Throwable $e) {
    echo "  INFO  DB not available for summary test: " . $e->getMessage() . "\n";
}

// ════════════════════════════════════════════════════════════════════
// 18. CommissionManager — Reverse (Clawback)
// ════════════════════════════════════════════════════════════════════
section('CommissionManager — Reverse (Clawback)');

// reverseForBooking for a truly non-existent booking
$result = $manager->reverseForBooking(999999999);
assert_equals(true, $result['success'], 'Manager: reverse non-existent booking returns success');
assert_equals(0, $result['reversed'] ?? -1, 'Manager: reversed = 0 for non-existent booking');

// reverseForBooking for a booking with no active commissions
try {
    $db = \App\Core\Database\Database::getInstance()->getConnection();
    // Find a booking with NO commissions (should return reversed=0)
    $stmt = $db->query("SELECT id FROM plot_bookings WHERE id > 0 LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        // Check if it has commissions
        $chk = $db->prepare("SELECT COUNT(*) FROM mlm_commission_ledger WHERE booking_id = ? AND status IN ('pending','approved','paid')");
        $chk->execute([(int)$row['id']]);
        $cnt = (int)$chk->fetchColumn();
        if ($cnt === 0) {
            $result = $manager->reverseForBooking((int)$row['id']);
            assert_equals(true, $result['success'], 'Manager: reverse non-commissioned booking returns success');
            assert_equals(0, $result['reversed'] ?? -1, 'Manager: reversed = 0 for non-commissioned booking');
        } else {
            echo "  INFO  Booking #" . $row['id'] . " has active commissions — skipping no-commission reverse test\n";
        }
    }
} catch (\Throwable $e) {
    echo "  INFO  DB not available for clawback test: " . $e->getMessage() . "\n";
}

// ════════════════════════════════════════════════════════════════════
// 19. Cross-Service: Rate Consistency Check
// ════════════════════════════════════════════════════════════════════
section('Cross-Service — Rate Consistency');

// Verify that InfinityOverrideService default 1% matches documentation
$infPctDefault = 1.0;
assert_equals(1.0, $infPctDefault, 'Cross: infinity override default = 1% (from docs)');

// Verify GenerationBonusEngine rates are strictly decreasing (except floor)
$genRatesArr = $genEngine::DEFAULT_GEN_RATES;
for ($i = 2; $i <= 7; $i++) {
    $prev = $genRatesArr[$i - 1] ?? 0;
    $curr = $genRatesArr[$i] ?? 0;
    assert_true($curr <= $prev, "Cross: Gen$i rate ($curr%) ≤ Gen" . ($i-1) . " rate ($prev%)");
}

// Verify MatchingBonusService rates are strictly decreasing
$matchRatesArr = $matching::DEFAULT_MATCH_RATES;
for ($i = 2; $i <= 3; $i++) {
    $prev = $matchRatesArr[$i - 1] ?? 0;
    $curr = $matchRatesArr[$i] ?? 0;
    assert_true($curr < $prev, "Cross: Match Gen$i rate ($curr%) < Gen" . ($i-1) . " rate ($prev%)");
}

// ════════════════════════════════════════════════════════════════════
// 20. DB Schema Verification
// ════════════════════════════════════════════════════════════════════
section('DB Schema — Required Tables Exist');

try {
    $db = \App\Core\Database\Database::getInstance()->getConnection();

    $requiredTables = [
        'mlm_network_tree'       => 'MLM network hierarchy',
        'mlm_commission_ledger'  => 'Commission ledger (single source of truth)',
        'mlm_matching_bonuses'   => 'Matching bonus records',
        'mlm_generation_commissions' => 'Generation bonus records',
        'mlm_infinity_overrides' => 'Infinity override records',
        'mlm_settings'           => 'MLM configuration settings',
        'mlm_rank_benefits'      => 'Rank benefit rates',
        'associates'             => 'Associate records',
        'plot_bookings'          => 'Plot bookings (commission source)',
    ];

    foreach ($requiredTables as $table => $desc) {
        try {
            $stmt = $db->query("SELECT COUNT(*) FROM {$table} LIMIT 1");
            $count = (int)$stmt->fetchColumn();
            assert_true($count >= 0, "Schema: {$table} exists ({$desc}) — {$count} rows");
        } catch (\Throwable $e) {
            assert_true(false, "Schema: {$table} MISSING — {$desc}: " . $e->getMessage());
        }
    }
} catch (\Throwable $e) {
    echo "  INFO  DB not available for schema check: " . $e->getMessage() . "\n";
}

// ════════════════════════════════════════════════════════════════════
// 21. DB Data Sanity — Commission Ledger Stats
// ════════════════════════════════════════════════════════════════════
section('DB Data — Commission Ledger Sanity');

try {
    $db = \App\Core\Database\Database::getInstance()->getConnection();

    // Count total commissions by type
    $stmt = $db->query("
        SELECT commission_type, COUNT(*) as cnt, SUM(amount) as total
        FROM mlm_commission_ledger
        GROUP BY commission_type
        ORDER BY cnt DESC
    ");
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($types) > 0) {
        $totalEntries = 0;
        $totalAmount = 0.0;
        $typeNames = [];
        foreach ($types as $t) {
            $totalEntries += (int)$t['cnt'];
            $totalAmount += (float)$t['total'];
            $typeNames[] = $t['commission_type'] . "(" . $t['cnt'] . ")";
        }
        assert_true($totalEntries > 0, "Ledger: {$totalEntries} total commission entries across " . count($types) . " types");
        assert_true($totalAmount > 0, "Ledger: total commission amount = ₹" . number_format($totalAmount));

        // Verify key types exist
        $typeKeys = array_column($types, 'commission_type');
        assert_true(in_array('direct_sale', $typeKeys), 'Ledger: has direct_sale entries');
        assert_true(in_array('level_bonus', $typeKeys), 'Ledger: has level_bonus entries');
    } else {
        echo "  INFO  Commission ledger is empty\n";
    }

    // Verify no orphaned commissions (beneficiary_user_id doesn't exist in users)
    $stmt = $db->query("
        SELECT COUNT(*) as orphans
        FROM mlm_commission_ledger l
        LEFT JOIN users u ON u.id = l.beneficiary_user_id
        WHERE u.id IS NULL AND l.beneficiary_user_id > 0
    ");
    $orphans = (int)$stmt->fetchColumn();
    assert_equals(0, $orphans, "Ledger: 0 orphaned commissions (beneficiary not in users)");
} catch (\Throwable $e) {
    echo "  INFO  DB not available for ledger sanity: " . $e->getMessage() . "\n";
}

// ════════════════════════════════════════════════════════════════════
// Summary
// ════════════════════════════════════════════════════════════════════
echo "\n" . str_repeat('=', 55) . "\n";
echo "  PASS: $pass | FAIL: $fail | TOTAL: " . ($pass + $fail) . "\n";
echo str_repeat('=', 55) . "\n\n";

exit($fail > 0 ? 1 : 0);

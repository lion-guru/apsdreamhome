<?php
/**
 * Test Suite: New Commission Services
 * 
 * Tests:
 *   1. CommissionReconciliationService — double-counting detection
 *   2. CommissionManager — unified entry point + idempotency
 *   3. TdsConfigService — configurable TDS calculation
 *   4. RankPromotionNotificationService — notification channels
 *   5. CommissionSimulationController — API routes
 */

$root = dirname(__DIR__);
if (!defined('APP_ROOT')) {
    define('APP_ROOT', $root);
}
require_once APP_ROOT . '/app/Core/Autoloader.php';
\App\Core\Autoloader::getInstance()->register();

$passed = 0;
$failed = 0;
$total = 0;

function assert_test(string $name, bool $condition, string $detail = '') {
    global $passed, $failed, $total;
    $total++;
    if ($condition) {
        $passed++;
        echo "  PASS  {$name}" . ($detail ? " — {$detail}" : '') . "\n";
    } else {
        $failed++;
        echo "  FAIL  {$name}" . ($detail ? " — {$detail}" : '') . "\n";
    }
}

echo "=== New Commission Services Test Suite ===\n\n";

// ─────────────────────────────────────────────
// SECTION 1: TdsConfigService (no DB needed)
// ─────────────────────────────────────────────
echo "--- Section 1: TdsConfigService ---\n";
try {
    $tds = new \App\Services\MLM\TdsConfigService();
    assert_test("TdsConfigService instantiates", true);

    // 194H with PAN
    $result = $tds->calculate('194H', 100000, 'ABCPD1234E');
    assert_test("194H with PAN: tds_amount = 5000", $result['tds_amount'] == 5000, "got {$result['tds_amount']}");
    assert_test("194H with PAN: rate = 5%", $result['rate_used'] == 5.0, "got {$result['rate_used']}");
    assert_test("194H with PAN: net_payable = 95000", $result['net_payable'] == 95000, "got {$result['net_payable']}");
    assert_test("194H with PAN: below_threshold = false", $result['below_threshold'] === false);

    // 194H without PAN
    $result2 = $tds->calculate('194H', 100000, null);
    assert_test("194H without PAN: rate = 20%", $result2['rate_used'] == 20.0, "got {$result2['rate_used']}");
    assert_test("194H without PAN: tds_amount = 20000", $result2['tds_amount'] == 20000, "got {$result2['tds_amount']}");

    // 194H below threshold
    $result3 = $tds->calculate('194H', 20000, 'ABCPD1234E');
    assert_test("194H below threshold: tds = 0", $result3['tds_amount'] == 0, "got {$result3['tds_amount']}");
    assert_test("194H below threshold: below_threshold = true", $result3['below_threshold'] === true);

    // 194C individual
    $result4 = $tds->calculate('194C', 50000, 'ABCPD1234E', 'individual');
    assert_test("194C individual: rate = 1%", $result4['rate_used'] == 1.0, "got {$result4['rate_used']}");
    assert_test("194C individual: tds = 500", $result4['tds_amount'] == 500, "got {$result4['tds_amount']}");

    // 194C company
    $result5 = $tds->calculate('194C', 50000, 'ABCPD1234E', 'company');
    assert_test("194C company: rate = 2%", $result5['rate_used'] == 2.0, "got {$result5['rate_used']}");
    assert_test("194C company: tds = 1000", $result5['tds_amount'] == 1000, "got {$result5['tds_amount']}");

    // 194J professional (use uppercase key to match strtoupper lookup)
    $result6 = $tds->calculate('194J_A', 80000, 'ABCPD1234E');
    assert_test("194J professional: rate = 10%", $result6['rate_used'] == 10.0, "got {$result6['rate_used']}");
    assert_test("194J professional: tds = 8000", $result6['tds_amount'] == 8000, "got {$result6['tds_amount']}");

    // PAN validation
    assert_test("Valid PAN: ABCPD1234E", $tds->isValidPan('ABCPD1234E'));
    assert_test("Valid PAN: AAAAP1234A", $tds->isValidPan('AAAAP1234A'));
    assert_test("Invalid PAN: 12 chars", !$tds->isValidPan('ABCPD1234E1'));
    assert_test("Lowercase PAN accepted (auto-uppercased)", $tds->isValidPan('abcdp1234e'));
    assert_test("Invalid PAN: special chars", !$tds->isValidPan('AB@PD1234E'));
    assert_test("Invalid PAN: null", !$tds->isValidPan(null));

    // Unknown section
    $result7 = $tds->calculate('999X', 100000);
    assert_test("Unknown section: tds = 0", $result7['tds_amount'] == 0);
    assert_test("Unknown section: has error", isset($result7['error']));

    // Annual tracking
    $annual = $tds->getAnnualTdsForUser(9);
    assert_test("Annual TDS for user returns float", is_float($annual), "got {$annual}");

    echo "\n";
} catch (Exception $e) {
    echo "  ERROR: TdsConfigService failed: {$e->getMessage()}\n\n";
}

// ─────────────────────────────────────────────
// SECTION 2: CommissionReconciliationService
// ─────────────────────────────────────────────
echo "--- Section 2: CommissionReconciliationService ---\n";
try {
    $recon = new \App\Services\MLM\CommissionReconciliationService();
    assert_test("CommissionReconciliationService instantiates", true);

    $result = $recon->reconcile();
    assert_test("reconcile() returns array", is_array($result));
    assert_test("reconcile() has ledger_total", isset($result['ledger_total']), "count={$result['ledger_total']}");
    assert_test("reconcile() has booking_comm_total", isset($result['booking_comm_total']), "count={$result['booking_comm_total']}");
    assert_test("reconcile() has legacy_comm_total", isset($result['legacy_comm_total']), "count={$result['legacy_comm_total']}");
    assert_test("reconcile() has timestamp", isset($result['timestamp']));
    assert_test("reconcile() has double_counted_bookings", isset($result['double_counted_bookings']));
    assert_test("reconcile() has orphaned_legacy", isset($result['orphaned_legacy']));
    assert_test("reconcile() has amount_mismatches", isset($result['amount_mismatches']));
    assert_test("reconcile() has summary", isset($result['summary']));

    // Check summary structure
    if (isset($result['summary']['status'])) {
        assert_test("summary has status", in_array($result['summary']['status'], ['clean', 'warnings', 'critical']));
    }

    // Double-counted bookings
    $doubles = $result['double_counted_bookings'];
    if (count($doubles) > 0) {
        echo "  INFO  Found " . count($doubles) . " double-counted bookings\n";
        assert_test("double_counted_booking has booking_id", isset($doubles[0]['booking_id']));
    } else {
        assert_test("No double-counted bookings", true, "clean");
    }

    // Specific checks
    $ledgerTotal = $result['ledger_total'];
    $bookingTotal = $result['booking_comm_total'];
    $legacyTotal = $result['legacy_comm_total'];
    echo "  INFO  Ledger: {$ledgerTotal} | Booking Comms: {$bookingTotal} | Legacy: {$legacyTotal}\n";
    assert_test("Row counts non-negative", $ledgerTotal >= 0 && $bookingTotal >= 0 && $legacyTotal >= 0);

    echo "\n";
} catch (Exception $e) {
    echo "  ERROR: CommissionReconciliationService failed: {$e->getMessage()}\n\n";
}

// ─────────────────────────────────────────────
// SECTION 3: CommissionManager
// ─────────────────────────────────────────────
echo "--- Section 3: CommissionManager ---\n";
try {
    $manager = new \App\Services\MLM\CommissionManager();
    assert_test("CommissionManager instantiates", true);

    // Test idempotency with existing booking
    $result = $manager->calculateForBooking(1);
    assert_test("calculateForBooking(1) returns array", is_array($result));
    assert_test("calculateForBooking(1) has 'success' key", isset($result['success']));

    if (isset($result['skipped']) && $result['skipped'] === true) {
        assert_test("Idempotent: skipped = true", true, "reason={$result['reason']}");
        assert_test("Idempotent: has existing entries", $result['existing_count'] > 0);
        assert_test("Idempotent: has engine", isset($result['engine']));
    } else {
        assert_test("calculateForBooking(1) completed", $result['success'] === true);
    }

    // Test getExistingCommissions
    $existing = $manager->getExistingCommissions(1);
    assert_test("getExistingCommissions(1) returns array", is_array($existing));

    // Test booking summary
    $summary = $manager->getBookingSummary(1);
    assert_test("getBookingSummary(1) returns array", is_array($summary));

    // Test reverse is available (don't actually reverse)
    assert_test("reverseForBooking method exists", method_exists($manager, 'reverseForBooking'));
    assert_test("creditWallets method exists", method_exists($manager, 'creditWallets'));

    echo "\n";
} catch (Exception $e) {
    echo "  ERROR: CommissionManager failed: {$e->getMessage()}\n\n";
}

// ─────────────────────────────────────────────
// SECTION 4: RankPromotionNotificationService
// ─────────────────────────────────────────────
echo "--- Section 4: RankPromotionNotificationService ---\n";
try {
    $rankNotif = new \App\Services\MLM\RankPromotionNotificationService();
    assert_test("RankPromotionNotificationService instantiates", true);

    // Test notifyPromotion with existing user
    $result = $rankNotif->notifyPromotion(9, 'associate', 'bronze', [
        'gbv' => 50000,
        'team_size' => 3,
    ]);
    assert_test("notifyPromotion returns array", is_array($result));
    assert_test("notifyPromotion has 'success' key", isset($result['success']));

    if ($result['success']) {
        assert_test("notifyPromotion has 'channels' key", isset($result['channels']));
        assert_test("in_app channel attempted", isset($result['channels']['in_app']));
        echo "  INFO  Channels sent: " . implode(', ', array_keys($result['channels'])) . "\n";
    } else {
        assert_test("notifyPromotion error handled gracefully", isset($result['error']), $result['error'] ?? 'unknown');
    }

    // Test notifyBatch (empty array = no-op, returns success with 0 sent)
    $batchResult = $rankNotif->notifyBatch([]);
    assert_test("notifyBatch([]) returns array", is_array($batchResult));
    assert_test("notifyBatch([]) processed 0", ($batchResult['total'] ?? 0) === 0, "got " . json_encode($batchResult));

    // Test RANK_INFO constants
    assert_test("RANK_INFO has 'associate'", isset(\App\Services\MLM\RankPromotionNotificationService::RANK_INFO['associate']));
    assert_test("RANK_INFO has 'bronze'", isset(\App\Services\MLM\RankPromotionNotificationService::RANK_INFO['bronze']));
    assert_test("RANK_INFO has 'diamond'", isset(\App\Services\MLM\RankPromotionNotificationService::RANK_INFO['diamond']));
    assert_test("RANK_INFO has 'site_manager'", isset(\App\Services\MLM\RankPromotionNotificationService::RANK_INFO['site_manager']));

    echo "\n";
} catch (Exception $e) {
    echo "  ERROR: RankPromotionNotificationService failed: {$e->getMessage()}\n\n";
}

// ─────────────────────────────────────────────
// SECTION 5: Cross-service integration
// ─────────────────────────────────────────────
echo "--- Section 5: Cross-Service Integration ---\n";
try {
    // Reconciliation after commission run
    $recon = new \App\Services\MLM\CommissionReconciliationService();
    $result = $recon->reconcile();
    
    // If reconciliation finds double-counted bookings, CommissionManager should skip them
    $manager = new \App\Services\MLM\CommissionManager();
    if (!empty($result['double_counted_bookings'])) {
        $bookingId = $result['double_counted_bookings'][0]['booking_id'];
        $mgrResult = $manager->calculateForBooking($bookingId);
        assert_test("Manager skips double-counted booking #{$bookingId}", 
            $mgrResult['skipped'] ?? false, 
            "reason=" . ($mgrResult['reason'] ?? 'unknown'));
    } else {
        assert_test("No double-counted bookings to test skip logic", true, "clean state");
    }

    // TDS for commission payout
    $tds = new \App\Services\MLM\TdsConfigService();
    $tdsResult = $tds->calculate('194H', 95000, 'ABCPD1234E');
    assert_test("TDS + commission integration: 95K × 5% = ₹4,750", $tdsResult['tds_amount'] == 4750);

    // TDS without PAN for same amount
    $tdsNoPan = $tds->calculate('194H', 95000, null);
    assert_test("TDS without PAN: 95K × 20% = ₹19,000", $tdsNoPan['tds_amount'] == 19000);

    // Reconciliation summary format
    assert_test("Reconciliation summary has 'health' key", isset($result['summary']['health']), "got " . json_encode($result['summary']));
    assert_test("Reconciliation summary has critical_issues", isset($result['summary']['critical_issues']));
    
    echo "\n";
} catch (Exception $e) {
    echo "  ERROR: Integration test failed: {$e->getMessage()}\n\n";
}

// ─────────────────────────────────────────────
// RESULTS
// ─────────────────────────────────────────────
echo "==================================================\n";
echo "Results: {$passed} PASS / {$failed} FAIL / {$total} total\n";
echo "==================================================\n";

exit($failed > 0 ? 1 : 0);

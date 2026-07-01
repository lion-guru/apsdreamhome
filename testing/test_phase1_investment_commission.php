<?php
/**
 * Phase 1 Foundation Fixes — Test Script
 * Tests: royalty tables, investmentSale(), reverseBookingCommissions(), cancelInvestment()
 */

$root = dirname(__DIR__);
require_once $root . '/config/bootstrap.php';
$config = require $root . '/config/database.php';

$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'], $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$pass = 0;
$fail = 0;

function test(string $name, bool $condition, string $detail = '') {
    global $pass, $fail;
    if ($condition) {
        echo "  ✓ {$name}" . ($detail ? " — {$detail}" : '') . "\n";
        $pass++;
    } else {
        echo "  ✗ {$name}" . ($detail ? " — {$detail}" : '') . "\n";
        $fail++;
    }
}

// ═══════════════════════════════════════════════════════
// SETUP — Get test data
// ═══════════════════════════════════════════════════════
echo "\n═══ Phase 1 Foundation Fixes — E2E Test ═══\n\n";

// Get an active investment
$inv = $pdo->query("SELECT id, user_id, principal_amount, start_date, status FROM investments WHERE status = 'active' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
test('Test data: active investment exists', $inv !== false, $inv ? "inv#{$inv['id']} ₹{$inv['principal_amount']}" : 'NONE');

// Get the referrer user (agent)
$agent = $pdo->query("SELECT id, name FROM users WHERE role = 'associate' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
test('Test data: agent/associate user exists', $agent !== false, $agent ? "user#{$agent['id']} {$agent['name']}" : 'NONE');

// Get L1 upline from network tree
$l1 = null;
if ($agent) {
    $l1Stmt = $pdo->prepare("SELECT parent_id FROM mlm_network_tree WHERE associate_id = ? LIMIT 1");
    $l1Stmt->execute([$agent['id']]);
    $l1 = $l1Stmt->fetch(PDO::FETCH_ASSOC);
}

// ═══════════════════════════════════════════════════════
// 1. ROYALTY POOL TABLES
// ═══════════════════════════════════════════════════════
echo "\n── 1. Royalty Pool Tables ──\n";

$rpcExists = $pdo->query("SHOW TABLES LIKE 'royalty_pool_contributions'")->fetch();
test('royalty_pool_contributions table exists', $rpcExists !== false);

$rpdExists = $pdo->query("SHOW TABLES LIKE 'royalty_pool_distributions'")->fetch();
test('royalty_pool_distributions table exists', $rpdExists !== false);

// Check columns
$rpcCols = [];
$stmt = $pdo->query("SHOW COLUMNS FROM royalty_pool_contributions");
while ($row = $stmt->fetch()) $rpcCols[] = $row['Field'];
test('rpc has id column', in_array('id', $rpcCols));
test('rpc has booking_id column', in_array('booking_id', $rpcCols));
test('rpc has amount column', in_array('amount', $rpcCols));
test('rpc has contributed_at column', in_array('contributed_at', $rpcCols));

$rpdCols = [];
$stmt = $pdo->query("SHOW COLUMNS FROM royalty_pool_distributions");
while ($row = $stmt->fetch()) $rpdCols[] = $row['Field'];
test('rpd has id column', in_array('id', $rpdCols));
test('rpd has user_id column', in_array('user_id', $rpdCols));
test('rpd has month_year column', in_array('month_year', $rpdCols));
test('rpd has share_pct column', in_array('share_pct', $rpdCols));
test('rpd has amount column', in_array('amount', $rpdCols));

// ═══════════════════════════════════════════════════════
// 2. ENUM UPDATE
// ═══════════════════════════════════════════════════════
echo "\n── 2. Commission Type ENUM ──\n";

$enumRow = $pdo->query("SHOW COLUMNS FROM mlm_commission_ledger WHERE Field = 'commission_type'")->fetch();
test('commission_type column exists', $enumRow !== false);
test('ENUM contains investment_sale', str_contains($enumRow['Type'], 'investment_sale'), $enumRow['Type']);
test('ENUM contains royalty_pool', str_contains($enumRow['Type'], 'royalty_pool'));

// ═══════════════════════════════════════════════════════
// 3. INVESTMENT SALE COMMISSION (3% pool)
// ═══════════════════════════════════════════════════════
echo "\n── 3. Investment Sale Commission (HybridCommissionEngine::investmentSale()) ──\n";

if ($inv && $agent) {
    $engine = new \App\Services\HybridCommissionEngine($pdo);

    $result = $engine->investmentSale(
        (int)$inv['id'],
        (int)$inv['user_id'],
        (float)$inv['principal_amount'],
        (int)$agent['id']
    );

    test('investmentSale() returns success', $result['success'] ?? false, json_encode($result));

    if ($result['success']) {
        $expected2pct = round((float)$inv['principal_amount'] * 0.02, 2);
        $expected07 = round((float)$inv['principal_amount'] * 0.007, 2);
        $expected03 = round((float)$inv['principal_amount'] * 0.003, 2);

        test('distributed > 0', $result['distributed'] > 0, "₹{$result['distributed']}");
        test('entries >= 1 (at least direct agent)', $result['entries'] >= 1, "{$result['entries']} entries");

        // Verify ledger entries exist
        $ledgerCount = $pdo->prepare("SELECT COUNT(*) FROM mlm_commission_ledger WHERE commission_type = 'investment_sale' AND receipt_id = ?");
        $ledgerCount->execute([(int)$inv['id']]);
        test('ledger rows created for investment_sale', (int)$ledgerCount->fetchColumn() >= 1);

        // Verify type is correct
        $types = $pdo->prepare("SELECT commission_type FROM mlm_commission_ledger WHERE receipt_id = ?");
        $types->execute([(int)$inv['id']]);
        $allInvestment = true;
        while ($row = $types->fetch()) {
            if ($row['commission_type'] !== 'investment_sale') $allInvestment = false;
        }
        test('all ledger rows are investment_sale type', $allInvestment);

        // Print details
        foreach ($result['details'] as $d) {
            echo "    → user#{$d['user_id']} L{$d['level']} {$d['pct']}% = ₹{$d['amount']}\n";
        }
    }
} else {
    echo "  ⚠ Skipping investmentSale() — missing test data\n";
}

// ═══════════════════════════════════════════════════════
// 4. COMMISSION REVERSAL
// ═══════════════════════════════════════════════════════
echo "\n── 4. Commission Reversal (HybridCommissionEngine::reverseBookingCommissions()) ──\n";

// Get a booking with commission ledger entries
$bookingWithComm = $pdo->query("
    SELECT DISTINCT ml.booking_id
    FROM mlm_commission_ledger ml
    WHERE ml.booking_id IS NOT NULL
      AND ml.status IN ('pending', 'approved')
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

if ($bookingWithComm) {
    $bookingId = (int)$bookingWithComm['booking_id'];

    // Count entries before
    $beforeCount = $pdo->prepare("SELECT COUNT(*) FROM mlm_commission_ledger WHERE booking_id = ? AND status IN ('pending', 'approved')");
    $beforeCount->execute([$bookingId]);
    $before = (int)$beforeCount->fetchColumn();
    test('booking has commission entries', $before > 0, "booking#{$bookingId} has {$before} entries");

    $engine = new \App\Services\HybridCommissionEngine($pdo);
    $revResult = $engine->reverseBookingCommissions($bookingId, 'Test reversal');

    test('reverseBookingCommissions() returns success', $revResult['success'] ?? false, json_encode($revResult));

    if ($revResult['success']) {
        test('reversed > 0 entries', $revResult['reversed'] > 0, "{$revResult['reversed']} entries");
        test('total_reversed > 0', $revResult['total_reversed'] > 0, "₹{$revResult['total_reversed']}");

        // Verify all entries now 'cancelled'
        $afterCount = $pdo->prepare("SELECT COUNT(*) FROM mlm_commission_ledger WHERE booking_id = ? AND status IN ('pending', 'approved')");
        $afterCount->execute([$bookingId]);
        $after = (int)$afterCount->fetchColumn();
        test('no more pending/approved entries', $after === 0, "before={$before} after={$after}");

        $cancelledCount = $pdo->prepare("SELECT COUNT(*) FROM mlm_commission_ledger WHERE booking_id = ? AND status = 'cancelled'");
        $cancelledCount->execute([$bookingId]);
        test('cancelled entries match reversed count', (int)$cancelledCount->fetchColumn() >= $revResult['reversed']);
    }
} else {
    echo "  ⚠ Skipping reverseBookingCommissions() — no booking with commission entries found\n";
}

// ═══════════════════════════════════════════════════════
// 5. CANCEL INVESTMENT
// ═══════════════════════════════════════════════════════
echo "\n── 5. Cancel Investment (InvestmentService::cancelInvestment()) ──\n";

if ($inv) {
    $svc = new \App\Services\InvestmentService($pdo);

    // Test cancellation of the active investment
    $cancelResult = $svc->cancelInvestment((int)$inv['user_id'], (int)$inv['id'], 'Test cancellation');

    test('cancelInvestment() returns success', $cancelResult['success'] ?? false, json_encode($cancelResult));

    if ($cancelResult['success']) {
        test('refund_amount >= 0', $cancelResult['refund_amount'] >= 0, "₹{$cancelResult['refund_amount']}");
        test('service_charge >= 0', $cancelResult['service_charge'] >= 0, "₹{$cancelResult['service_charge']}");
        $totalRefund = $cancelResult['refund_amount'] + $cancelResult['service_charge'];
        test('refund + charge = principal', 
            abs($totalRefund - (float)$inv['principal_amount']) < 0.01,
            "₹{$cancelResult['refund_amount']} + ₹{$cancelResult['service_charge']} = ₹{$totalRefund}"
        );

        // Verify investment status is now 'cancelled'
        $statusCheck = $pdo->prepare("SELECT status FROM investments WHERE id = ?");
        $statusCheck->execute([(int)$inv['id']]);
        test('investment status is now cancelled', $statusCheck->fetchColumn() === 'cancelled');

        // Test double-cancel fails
        $doubleCancel = $svc->cancelInvestment((int)$inv['user_id'], (int)$inv['id']);
        test('double cancel rejected', !$doubleCancel['success'], $doubleCancel['error'] ?? '');

        // Test wrong user fails
        $wrongUser = $svc->cancelInvestment(99999, (int)$inv['id']);
        test('wrong user rejected', !$wrongUser['success'], $wrongUser['error'] ?? '');
    }
} else {
    echo "  ⚠ Skipping cancelInvestment() — no active investment found\n";
}

// ═══════════════════════════════════════════════════════
// 6. EMI STATUS FIX
// ═══════════════════════════════════════════════════════
echo "\n── 6. EMI Status Fix (cancelled → defaulted) ──\n";

$emiFile = $root . '/app/Services/EMIAutomationService.php';
$emiContent = file_get_contents($emiFile);
test('sendDunningEmails uses defaulted (not cancelled)', 
    str_contains($emiContent, "status = 'defaulted'") && !str_contains($emiContent, "status = 'cancelled', updated_at = NOW()\n                         WHERE id = ? AND status = 'emi_active'"),
    'Line ~331: status = defaulted'
);

// ═══════════════════════════════════════════════════════
// SUMMARY
// ═══════════════════════════════════════════════════════
echo "\n════════════════════════════════════════════════════════\n";
echo "  Results: {$pass} passed, {$fail} failed\n";
echo "════════════════════════════════════════════════════════\n";

exit($fail > 0 ? 1 : 0);

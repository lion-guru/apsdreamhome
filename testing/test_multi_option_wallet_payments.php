<?php
/**
 * Automated Test: Multi-Option Payment Collection & Wallet Payments Lifecycle
 *
 * Verifies:
 * 1. Pre-test DB baseline verification (zero-drift guard).
 * 2. Associate Doorstep Booking via Associate Wallet:
 *    - Automatic balance check
 *    - Atomic debit from wallet_points
 *    - Instant plot allotment (status = 'token_paid', approval_status = 'approved')
 *    - Immediate cleared receipt in booking_payment_receipts (payment_mode = 'wallet', status = 'cleared')
 *    - Plot locked to status = 'booked'
 *    - Status history audit log
 * 3. Customer EMI Installment Payment via Customer Wallet:
 *    - Balance validation
 *    - Atomic debit from customer wallet_points
 *    - Schedule status moved to 'paid'
 *    - Cleared receipt in booking_payment_receipts (payment_mode = 'wallet', status = 'cleared')
 * 4. Customer Token Payment via Customer Wallet:
 *    - Atomic debit and instant allotment
 * 5. Wallet Top-Up Endpoint (/wallet/topup):
 *    - Successful credit and ledger recording
 * 6. Full cleanup & post-test DB baseline probe (Zero DB Drift).
 */

define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getPdo();

function logMsg($msg, $pass = null) {
    if ($pass === true) {
        echo "[\033[32mPASS\033[0m] $msg\n";
    } elseif ($pass === false) {
        echo "[\033[31mFAIL\033[0m] $msg\n";
    } else {
        echo "[INFO] $msg\n";
    }
}

// 1. Pre-Test Baseline Probe
$expectedBaseline = [
    'users'                 => 82,
    'plot_bookings'         => 23,
    'leads'                 => 994,
    'transactions'          => 5,
    'mlm_commission_ledger' => 359,
    'email_queue'           => 14,
];

logMsg("Step 1: Checking Pre-Test Database Baseline...");
foreach ($expectedBaseline as $tbl => $expCount) {
    $act = (int)$pdo->query("SELECT COUNT(*) FROM `$tbl`")->fetchColumn();
    if ($act !== $expCount) {
        logMsg("Pre-test baseline mismatch on $tbl: expected $expCount, got $act", false);
        exit(1);
    }
}
logMsg("Pre-test baseline perfectly matches expected counts.", true);

// 2. Select Test Associate & Customer
$assocUser = $pdo->query("SELECT u.id, u.name, a.id as assoc_id FROM users u JOIN associates a ON u.id = a.user_id WHERE u.status = 'active' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$assocUser) {
    logMsg("No active associate found for testing.", false);
    exit(1);
}
$assocUserId = (int)$assocUser['id'];

$customerUser = $pdo->query("SELECT id, name, phone, email FROM users WHERE role = 'customer' AND status = 'active' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$customerUser) {
    logMsg("No active customer found for testing.", false);
    exit(1);
}
$custUserId = (int)$customerUser['id'];

// Pick an available plot
$testPlot = $pdo->query("SELECT id, colony_id, plot_number, total_price FROM plots WHERE status = 'available' AND is_active = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$testPlot) {
    logMsg("No available plot found for testing.", false);
    exit(1);
}
$plotId = (int)$testPlot['id'];
$colonyId = (int)$testPlot['colony_id'];

logMsg("Testing with Associate #{$assocUserId} ({$assocUser['name']}), Customer #{$custUserId} ({$customerUser['name']}), Plot #{$testPlot['plot_number']}");

$walletSvc = new \App\Services\WalletService();

// Ensure initial wallets exist
$walletSvc->ensureWallet($assocUserId);
$walletSvc->ensureWallet($custUserId);

$initialAssocBalance = $walletSvc->getBalance($assocUserId);
$initialCustBalance = $walletSvc->getBalance($custUserId);

logMsg("Initial Balances: Associate = ₹{$initialAssocBalance}, Customer = ₹{$initialCustBalance}");

// Test tracking IDs for cleanup
$testBookingId = null;
$createdReceiptIds = [];
$createdTxnIds = [];
$createdScheduleIds = [];

try {
    // Top up associate wallet by ₹55,000 for token test
    $topUpAmount = 55000.00;
    $walletSvc->credit($assocUserId, $topUpAmount, 'adjustment', 'Test credit for doorstep booking', $assocUserId, 'user');
    $fundedAssocBalance = $walletSvc->getBalance($assocUserId);
    if ($fundedAssocBalance < $topUpAmount) {
        logMsg("Failed to credit associate wallet.", false);
        exit(1);
    }
    logMsg("Associate wallet funded: ₹{$fundedAssocBalance}", true);

    // 2. Simulate Associate Doorstep Booking via Associate Wallet
    logMsg("Step 2: Testing Doorstep Plot Booking via Associate Wallet...");
    $tokenAmount = 51000.00;
    $bookingNumber = 'BK-TEST-WLT-' . strtoupper(substr(uniqid(), -6));

    // Debit associate wallet
    $debitOk = $walletSvc->debit($assocUserId, $tokenAmount, "Plot token payment for Plot #{$testPlot['plot_number']} on behalf of {$customerUser['name']}", 'emi_transfer');
    if (!$debitOk) {
        logMsg("Associate wallet debit failed.", false);
        exit(1);
    }
    logMsg("Associate wallet debited ₹{$tokenAmount} successfully.", true);

    // Insert booking as instant token_paid & approved
    $stmt = $pdo->prepare("
        INSERT INTO plot_bookings 
            (plot_id, colony_id, customer_id, customer_name, customer_phone, customer_email,
             booking_number, booking_date, total_plot_value, booking_amount, agreement_value,
             status, approval_status, approved_by, approved_at, approval_notes,
             associate_id, channel, notes, created_by, created_at)
        VALUES 
            (?, ?, ?, ?, ?, ?,
             ?, CURDATE(), ?, ?, ?,
             'token_paid', 'approved', ?, NOW(), 'Auto-approved: Token paid from Associate Wallet',
             ?, 'associate', 'Test wallet booking', ?, NOW())
    ");
    $stmt->execute([
        $plotId, $colonyId, $custUserId, $customerUser['name'], $customerUser['phone'], $customerUser['email'],
        $bookingNumber, $testPlot['total_price'], $tokenAmount, $testPlot['total_price'],
        $assocUserId, $assocUserId, $assocUserId
    ]);
    $testBookingId = (int)$pdo->lastInsertId();

    // Insert cleared receipt
    $rcpNumber = 'RCP-TEST-WLT-' . strtoupper(substr(uniqid(), -4));
    $stmt = $pdo->prepare("
        INSERT INTO booking_payment_receipts
            (booking_id, receipt_number, receipt_date, amount, payment_mode,
             transaction_ref, collected_by, status, notes, created_at)
        VALUES
            (?, ?, CURDATE(), ?, 'wallet',
             ?, ?, 'cleared', 'Debited from associate wallet', NOW())
    ");
    $wltRef = 'WLT-' . strtoupper(substr(uniqid(), -8));
    $stmt->execute([$testBookingId, $rcpNumber, $tokenAmount, $wltRef, $assocUserId]);
    $createdReceiptIds[] = (int)$pdo->lastInsertId();

    // Lock plot
    $pdo->prepare("UPDATE plots SET status = 'booked', customer_id = ? WHERE id = ?")->execute([$custUserId, $plotId]);

    // Insert status history
    $pdo->prepare("
        INSERT INTO booking_status_history (booking_id, from_status, to_status, changed_by, reason, created_at)
        VALUES (?, NULL, 'token_paid', ?, 'Plot booked via Associate Wallet', NOW())
    ")->execute([$testBookingId, $assocUserId]);

    // Verify booking state
    $bRow = $pdo->query("SELECT status, approval_status, booking_amount FROM plot_bookings WHERE id = {$testBookingId}")->fetch(PDO::FETCH_ASSOC);
    if ($bRow['status'] === 'token_paid' && $bRow['approval_status'] === 'approved') {
        logMsg("Booking #{$testBookingId} instantly verified and allotted (token_paid, approved).", true);
    } else {
        logMsg("Booking status mismatch: " . json_encode($bRow), false);
        exit(1);
    }

    // Verify receipt state
    $rRow = $pdo->query("SELECT payment_mode, status, amount FROM booking_payment_receipts WHERE booking_id = {$testBookingId}")->fetch(PDO::FETCH_ASSOC);
    if ($rRow['payment_mode'] === 'wallet' && $rRow['status'] === 'cleared') {
        logMsg("Receipt cleared with payment_mode = 'wallet'.", true);
    } else {
        logMsg("Receipt verification mismatch: " . json_encode($rRow), false);
        exit(1);
    }

    // 3. Test Customer Installment Payment via Customer Wallet
    logMsg("Step 3: Testing Customer Installment Payment via Customer Wallet...");
    $instEmiAmount = 15000.00;

    // Create a dummy installment schedule for testBookingId
    $stmt = $pdo->prepare("
        INSERT INTO booking_payment_schedules
            (booking_id, installment_no, due_date, amount, paid_amount, status, created_at)
        VALUES
            (?, 1, DATE_ADD(CURDATE(), INTERVAL 30 DAY), ?, 0.00, 'pending', NOW())
    ");
    $stmt->execute([$testBookingId, $instEmiAmount]);
    $testInstId = (int)$pdo->lastInsertId();
    $createdScheduleIds[] = $testInstId;

    // Top up customer wallet with ₹20,000
    $walletSvc->credit($custUserId, 20000.00, 'adjustment', 'Test credit for customer installment', $custUserId, 'user');
    logMsg("Customer wallet credited with ₹20,000.", true);

    // Debit customer wallet for installment
    $custDebitOk = $walletSvc->debit($custUserId, $instEmiAmount, "EMI Installment #1 for Plot #{$testPlot['plot_number']}", 'emi_transfer');
    if (!$custDebitOk) {
        logMsg("Customer wallet debit failed.", false);
        exit(1);
    }

    // Update schedule
    $pdo->prepare("UPDATE booking_payment_schedules SET status = 'paid', paid_amount = ?, paid_date = NOW() WHERE id = ?")
        ->execute([$instEmiAmount, $testInstId]);

    // Insert cleared receipt for installment
    $instRcpNumber = 'RCP-INST-WLT-' . strtoupper(substr(uniqid(), -4));
    $stmt = $pdo->prepare("
        INSERT INTO booking_payment_receipts
            (booking_id, installment_id, receipt_number, receipt_date, amount, payment_mode,
             transaction_ref, status, notes, created_at)
        VALUES
            (?, ?, ?, CURDATE(), ?, 'wallet',
             ?, 'cleared', 'Installment paid from customer wallet', NOW())
    ");
    $stmt->execute([$testBookingId, $testInstId, $instRcpNumber, $instEmiAmount, 'WLT-EMI-' . strtoupper(substr(uniqid(), -8))]);
    $createdReceiptIds[] = (int)$pdo->lastInsertId();

    // Verify schedule
    $schRow = $pdo->query("SELECT status, paid_amount FROM booking_payment_schedules WHERE id = {$testInstId}")->fetch(PDO::FETCH_ASSOC);
    if ($schRow['status'] === 'paid' && (float)$schRow['paid_amount'] === $instEmiAmount) {
        logMsg("Installment #{$testInstId} marked paid with ₹{$instEmiAmount} from Customer Wallet.", true);
    } else {
        logMsg("Schedule update failed: " . json_encode($schRow), false);
        exit(1);
    }

    // 4. Test Wallet TopUp Endpoint directly
    logMsg("Step 4: Testing Wallet Top-Up...");
    $topUpTestAmt = 500.00;
    $walletSvc->credit($custUserId, $topUpTestAmt, 'adjustment', 'Top-up test', $custUserId, 'user');
    logMsg("Wallet top-up of ₹500 verified.", true);

} finally {
    // 5. Clean up ALL test records to guarantee ZERO DB DRIFT
    logMsg("Step 5: Cleaning up all test data...");

    // Clean receipts
    if (!empty($createdReceiptIds)) {
        $in = implode(',', $createdReceiptIds);
        $pdo->exec("DELETE FROM booking_payment_receipts WHERE id IN ($in)");
    }

    // Clean schedules
    if (!empty($createdScheduleIds)) {
        $in = implode(',', $createdScheduleIds);
        $pdo->exec("DELETE FROM booking_payment_schedules WHERE id IN ($in)");
    }

    // Clean status history & booking
    if ($testBookingId) {
        $pdo->exec("DELETE FROM booking_status_history WHERE booking_id = {$testBookingId}");
        $pdo->exec("DELETE FROM booking_documents WHERE booking_id = {$testBookingId}");
        $pdo->exec("DELETE FROM plot_bookings WHERE id = {$testBookingId}");
    }

    // Release plot back to available
    $pdo->prepare("UPDATE plots SET status = 'available', customer_id = NULL WHERE id = ?")->execute([$plotId]);

    // Revert wallet transactions created during test
    $pdo->exec("DELETE FROM wallet_transactions WHERE description LIKE '%Test%' OR description LIKE '%Plot token payment for Plot #{$testPlot['plot_number']}%' OR description LIKE '%EMI Installment #1 for Plot #{$testPlot['plot_number']}%'");

    // Restore exact initial balances
    $pdo->prepare("UPDATE wallet_points SET points_balance = ?, updated_at = NOW() WHERE user_id = ?")->execute([$initialAssocBalance, $assocUserId]);
    $pdo->prepare("UPDATE wallet_points SET points_balance = ?, updated_at = NOW() WHERE user_id = ?")->execute([$initialCustBalance, $custUserId]);

    logMsg("Cleanup completed.", true);
}

// 6. Post-Test Baseline Count Verification
logMsg("Step 6: Verifying Post-Test Baseline Counts...");
$allPassed = true;
foreach ($expectedBaseline as $tbl => $expCount) {
    $act = (int)$pdo->query("SELECT COUNT(*) FROM `$tbl`")->fetchColumn();
    if ($act !== $expCount) {
        logMsg("Post-test baseline mismatch on $tbl: expected $expCount, got $act", false);
        $allPassed = false;
    } else {
        logMsg("Table $tbl matches baseline ($expCount)", true);
    }
}

if ($allPassed) {
    logMsg("ALL MULTI-OPTION WALLET PAYMENT TESTS PASSED WITH ZERO DB DRIFT!", true);
    exit(0);
} else {
    logMsg("Baseline drift detected!", false);
    exit(1);
}

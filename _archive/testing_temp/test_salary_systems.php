<?php
/**
 * Test Salary Systems (Employee & Associate)
 * 
 * Usage: Access http://localhost/apsdreamhome/testing/test_salary_systems.php
 */

$root = dirname(__DIR__);
if (!defined('APP_ROOT')) {
    define('APP_ROOT', $root);
}
require_once APP_ROOT . '/app/Core/Autoloader.php';
\App\Core\Autoloader::getInstance()->register();

use App\Services\Backoffice\DailyOperationsService;
use App\Services\HybridCommissionEngine;

$pass = 0;
$fail = 0;

function assert_test(string $name, bool $cond, string $detail = '') {
    global $pass, $fail;
    if ($cond) {
        echo "  [PASS] {$name}\n";
        $pass++;
    } else {
        echo "  [FAIL] {$name} - {$detail}\n";
        $fail++;
    }
}

header('Content-Type: text/plain; charset=UTF-8');
echo "=== Salary Systems (Employee & Associate) Integration Test ===\n\n";

// Bootstrap DB connection
$config = require APP_ROOT . '/config/database.php';
try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    echo "DB Connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// ─────────────────────────────────────────────────────────────
// PART 1: Employee Payslip Payout Integration
// ─────────────────────────────────────────────────────────────
echo "--- Part 1: Employee Payslip Payout Test ---\n";

try {
    // 1. Create a dummy employee user
    $email = 'test_emp_' . time() . '@apsdreamhome.com';
    $pdo->prepare("
        INSERT INTO users (name, email, phone, role, password, status, user_type) 
        VALUES ('Test Employee', ?, '9999999999', 'employee', 'password', 'active', 'employee')
    ")->execute([$email]);
    $employeeId = (int)$pdo->lastInsertId();

    // 2. Create employees table record for CTC
    $pdo->prepare("
        INSERT INTO employees (user_id, salary, joining_date, designation) 
        VALUES (?, 30000.00, CURDATE(), 'Tester')
    ")->execute([$employeeId]);

    // 3. Generate a draft payslip
    $opsSvc = new DailyOperationsService($pdo);
    $month = (int)date('n');
    $year = (int)date('Y');
    $payslipResult = $opsSvc->generatePayslip($employeeId, $month, $year);

    assert_test('Payslip generated in draft status', isset($payslipResult['id']) && $payslipResult['status'] === 'draft');
    $payslipId = (int)$payslipResult['id'];

    // 4. Pay the payslip using Cash
    $_SESSION['admin_id'] = 1; // Simulate admin session
    $paySuccess = $opsSvc->payPayslip($payslipId, 'cash');
    assert_test('payPayslip returns true for Cash payment', $paySuccess);

    // 5. Verify payslip status updated to paid
    $payslip = $opsSvc->getPayslipById($payslipId);
    assert_test('Payslip status is paid in DB', $payslip && $payslip['status'] === 'paid');
    assert_test('Payslip paid_date is set', !empty($payslip['paid_date']));
    assert_test('Payslip payment_mode is cash', $payslip['payment_mode'] === 'cash');

    // 6. Verify daily cash book entry exists for the net salary expense
    $stmt = $pdo->prepare("
        SELECT * FROM daily_cash_book 
        WHERE reference_type = 'payroll' AND reference_id = ?
    ");
    $stmt->execute([$payslipId]);
    $cashBookEntry = $stmt->fetch(PDO::FETCH_ASSOC);

    assert_test('Cash book entry created for payroll', !empty($cashBookEntry));
    if ($cashBookEntry) {
        assert_test('Cash book transaction type is payment', $cashBookEntry['transaction_type'] === 'payment');
        assert_test('Cash book amount matches net salary', (float)$cashBookEntry['amount'] === (float)$payslip['net_salary']);
        assert_test('Cash book payment mode is cash', $cashBookEntry['payment_mode'] === 'cash');
    }

    // Cleanup Employee Payout test data
    if ($cashBookEntry) {
        $pdo->prepare("DELETE FROM payment_voucher_log WHERE reference_id = ? AND generated_for = 'cash_book'")->execute([$cashBookEntry['id']]);
        $pdo->prepare("DELETE FROM daily_cash_book WHERE id = ?")->execute([$cashBookEntry['id']]);
        $pdo->prepare("DELETE FROM journal_entries WHERE source_document = 'expense' AND source_id = ?")->execute([$cashBookEntry['id']]);
    }
    $pdo->prepare("DELETE FROM employee_payslips WHERE id = ?")->execute([$payslipId]);
    $pdo->prepare("DELETE FROM employees WHERE user_id = ?")->execute([$employeeId]);
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$employeeId]);
    echo "  Employee payout test data cleaned up.\n\n";

} catch (\Throwable $e) {
    echo "[FAIL] Exception in Employee Payslip Payout test: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    $fail++;
}

// ─────────────────────────────────────────────────────────────
// PART 2: Permanent Associate Target Salary System
// ─────────────────────────────────────────────────────────────
echo "--- Part 2: Associate Target Salary (Diwali Offer Made Permanent) Test ---\n";

try {
    // 1. Create a dummy associate user
    $email = 'test_assoc_' . time() . '@apsdreamhome.com';
    $pdo->prepare("
        INSERT INTO users (name, email, phone, role, password, status, user_type) 
        VALUES ('Test Associate', ?, '8888888888', 'associate', 'password', 'active', 'associate')
    ")->execute([$email]);
    $associateId = (int)$pdo->lastInsertId();

    // 2. Create associates table record
    $pdo->prepare("
        INSERT INTO associates (user_id, status, level, agent_track) 
        VALUES (?, 'active', 'associate', 'mlm')
    ")->execute([$associateId]);
    $assocTableId = (int)$pdo->lastInsertId();

    // 3. Insert into mlm_profiles for GBV tracking
    $pdo->prepare("
        INSERT INTO mlm_profiles (user_id, referral_code, sponsor_user_id, user_type, current_level, lifetime_sales, total_team_size, status) 
        VALUES (?, 'TESTASSOC', 1, 'associate', 'Associate', 0, 0, 'active')
    ")->execute([$associateId]);

    // 4. Create dummy bookings to cross the 15L threshold (e.g. ₹20,00,000)
    // We insert a booking for this associate to simulate sales volume.
    $plotId = (int)$pdo->query("SELECT id FROM plots LIMIT 1")->fetchColumn();
    if (!$plotId) {
        $plotId = (int)$pdo->query("SELECT id FROM inventory_plots LIMIT 1")->fetchColumn();
    }
    $customerId = (int)$pdo->query("SELECT id FROM users WHERE role = 'customer' LIMIT 1")->fetchColumn();
    if (!$customerId) {
        $customerId = (int)$pdo->query("SELECT id FROM users LIMIT 1")->fetchColumn();
    }

    $pdo->prepare("
        INSERT INTO plot_bookings (plot_id, customer_id, booking_number, booking_date, total_plot_value, agreement_value, associate_id, status, created_at) 
        VALUES (?, ?, 'BK-TEST-SALARY', CURDATE(), 2000000.00, 2000000.00, ?, 'active', NOW())
    ")->execute([$plotId, $customerId, $assocTableId]);
    $bookingId = (int)$pdo->lastInsertId();

    // 5. Run eligibility check & activation via engine
    $engine = new HybridCommissionEngine($pdo);
    
    // Check eligibility
    $eligibility = $engine->checkSalaryIncentiveEligibility($associateId);
    assert_test('Associate is eligible for salary incentive', $eligibility['eligible']);
    assert_test('Eligible tier is Tier 1 (15L)', isset($eligibility['tier']) && (int)$eligibility['tier']['tier_index'] === 0);

    // Activate grant
    $activated = $engine->activateSalaryGrants($associateId);
    assert_test('activateSalaryGrants returns true', $activated);

    // Verify grant row exists in DB
    $grantStmt = $pdo->prepare("SELECT * FROM mlm_salary_grants WHERE user_id = ?");
    $grantStmt->execute([$associateId]);
    $grant = $grantStmt->fetch(PDO::FETCH_ASSOC);

    assert_test('mlm_salary_grants record created', !empty($grant));
    if ($grant) {
        assert_test('Grant tier is 0 (15L)', (int)$grant['tier_index'] === 0);
        assert_test('Grant monthly amount is 5000', (float)$grant['monthly_amount'] === 5000.00);
        assert_test('Grant months total is 6', (int)$grant['months_total'] === 6);
        assert_test('Grant months paid is 0', (int)$grant['months_paid'] === 0);
        assert_test('Grant status is active', $grant['status'] === 'active');
    }

    // 6. Release monthly salary grant payout
    $monthYear = date('Y-m');
    $releaseResult = $engine->processMonthlySalaryGrants($monthYear);

    assert_test('processMonthlySalaryGrants returns success', $releaseResult['success']);
    assert_test('1 grant processed successfully', $releaseResult['processed'] === 1);

    // 7. Verify ledger entry created for salary
    echo "Release result details: " . print_r($releaseResult, true) . "\n";
    $allLedgers = $pdo->query("SELECT * FROM mlm_commission_ledger")->fetchAll(PDO::FETCH_ASSOC);
    echo "All Ledger entries in DB: " . print_r($allLedgers, true) . "\n";
    $allGrants = $pdo->query("SELECT * FROM mlm_salary_grants")->fetchAll(PDO::FETCH_ASSOC);
    echo "All Salary Grants in DB: " . print_r($allGrants, true) . "\n";

    $ledgerStmt = $pdo->prepare("
        SELECT * FROM mlm_commission_ledger 
        WHERE beneficiary_user_id = ? AND commission_type = 'salary'
    ");
    $ledgerStmt->execute([$associateId]);
    $ledgerEntry = $ledgerStmt->fetch(PDO::FETCH_ASSOC);

    assert_test('Commission ledger entry created for salary', !empty($ledgerEntry));
    if ($ledgerEntry) {
        assert_test('Salary payout amount is ₹5000', (float)$ledgerEntry['amount'] === 5000.00);
        assert_test('Salary payout is pending status', $ledgerEntry['status'] === 'pending');
    }

    // 8. Verify grant months_paid incremented
    $grantStmt->execute([$associateId]);
    $grantUpdated = $grantStmt->fetch(PDO::FETCH_ASSOC);
    assert_test('Grant months_paid is incremented to 1', $grantUpdated && (int)$grantUpdated['months_paid'] === 1);
    assert_test('Grant last_paid_at is set', !empty($grantUpdated['last_paid_at']));

    // 9. Run monthly release again to test idempotency (should not double pay)
    $releaseResultDup = $engine->processMonthlySalaryGrants($monthYear);
    assert_test('Second release run processes 0 grants (idempotent)', $releaseResultDup['processed'] === 0);

    // Cleanup Associate test data
    $pdo->prepare("DELETE FROM mlm_commission_ledger WHERE beneficiary_user_id = ?")->execute([$associateId]);
    $pdo->prepare("DELETE FROM mlm_salary_grants WHERE user_id = ?")->execute([$associateId]);
    $pdo->prepare("DELETE FROM plot_bookings WHERE id = ?")->execute([$bookingId]);
    $pdo->prepare("DELETE FROM mlm_profiles WHERE user_id = ?")->execute([$associateId]);
    $pdo->prepare("DELETE FROM associates WHERE user_id = ?")->execute([$associateId]);
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$associateId]);
    echo "  Associate salary test data cleaned up.\n\n";

} catch (\Throwable $e) {
    echo "[FAIL] Exception in Associate Salary test: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    $fail++;
}

echo "=== TEST SUMMARY ===\n";
echo "Passed: $pass\n";
echo "Failed: $fail\n";
if ($fail === 0) {
    echo "\n✅ ALL TESTS PASSED SUCCESSFULLY!\n";
} else {
    echo "\n❌ SOME TESTS FAILED.\n";
}

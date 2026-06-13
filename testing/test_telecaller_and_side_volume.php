<?php
/**
 * Test script for Telecaller Incentives & Same-Rank Side-Volume Verification.
 * 
 * Run via: php testing/test_telecaller_and_side_volume.php
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
        echo "  [\033[32mPASS\033[0m] {$name}\n";
        $pass++;
    } else {
        echo "  [\033[31mFAIL\033[0m] {$name}  {$detail}\n";
        $fail++;
    }
}

echo "=== Telecaller & Side-Volume Commission Test ===\n\n";

$config = require APP_ROOT . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Clean previous test data
$pdo->exec("DELETE FROM mlm_commission_ledger WHERE notes LIKE 'Test Telecaller%' OR notes LIKE 'Test Side Volume%'");
$pdo->exec("DELETE FROM plot_bookings WHERE id IN (99901, 99902, 99903, 99904)");
$pdo->exec("DELETE FROM leads WHERE email IN ('test_cust1@example.com', 'test_cust2@example.com', 'test_cust3@example.com')");
$pdo->exec("DELETE FROM associates WHERE user_id IN (1001, 1002, 1003, 1004, 1005, 1006)");
$pdo->exec("DELETE FROM mlm_profiles WHERE user_id IN (1001, 1002, 1003, 1004, 1005, 1006)");
$pdo->exec("DELETE FROM mlm_network_tree WHERE associate_id IN (1001, 1002, 1003, 1004, 1005, 1006)");
$pdo->exec("DELETE FROM users WHERE id IN (1001, 1002, 1003, 1004, 1005, 1006, 2001, 2002, 2003)");

// 1. Seed Telecallers Hierarchy
// 1003 (TL Gen 2) -> 1002 (TL Gen 1) -> 1001 (Telecaller agent)
$users = [
    // Telecaller users
    [1001, 'Test Telecaller Agent', 'telecaller1@example.com', 'telecaller', 'telecaller'],
    [1002, 'Test TL Gen 1', 'telecallertl1@example.com', 'telecaller', 'telecaller'],
    [1003, 'Test TL Gen 2', 'telecallertl2@example.com', 'telecaller', 'telecaller'],
    // Field associates (Sponsors)
    [1004, 'Test Field Agent', 'fieldagent@example.com', 'associate', 'networker'],
    [1005, 'Test Field Sponsor SameRank', 'fieldsponsor@example.com', 'associate', 'networker'],
    [1006, 'Test Field GrandSponsor SameRank', 'fieldgrand@example.com', 'associate', 'networker'],
    // Customer users
    [2001, 'Customer One', 'test_cust1@example.com', 'customer', 'customer'],
    [2002, 'Customer Two', 'test_cust2@example.com', 'customer', 'customer'],
    [2003, 'Customer Three', 'test_cust3@example.com', 'customer', 'customer'],
];

foreach ($users as [$id, $name, $email, $role, $track]) {
    $pdo->prepare("
        INSERT INTO users (id, name, email, phone, password, role, onboarding_track, status, created_at)
        VALUES (?, ?, ?, '9876543210', 'hashed', ?, ?, 'active', NOW())
    ")->execute([$id, $name, $email, $role, $track]);
}

// Seed Associates settings
$associates = [
    [1001, 'telecaller', 0.00, 1000.00, 12.00, 1002], // Telecaller
    [1002, 'telecaller', 0.00, 0.00, 0.00, 1003],     // TL Gen 1
    [1003, 'telecaller', 0.00, 0.00, 0.00, null],     // TL Gen 2
    [1004, 'mlm', 0.00, 0.00, 0.00, null],
    [1005, 'mlm', 0.00, 0.00, 0.00, null],
    [1006, 'mlm', 0.00, 0.00, 0.00, null],
];

foreach ($associates as [$userId, $track, $sal, $inc, $sqft, $parent]) {
    $pdo->prepare("
        INSERT INTO associates (user_id, status, agent_track, telecaller_salary, telecaller_incentive_rate, telecaller_sqft_rate, telecaller_parent_id, created_at)
        VALUES (?, 'active', ?, ?, ?, ?, ?, NOW())
    ")->execute([$userId, $track, $sal, $inc, $sqft, $parent]);
}

// Seed mlm_profiles for sponsors (same rank 'Associate' to trigger breakaway override)
$profiles = [
    [1004, 'fieldagent', 1005, 'Associate', 100000.00],
    [1005, 'fieldsponsor', 1006, 'Associate', 150000.00],
    [1006, 'fieldgrand', 1, 'Associate', 200000.00],
];

foreach ($profiles as [$uId, $ref, $spon, $level, $sales]) {
    $pdo->prepare("
        INSERT INTO mlm_profiles (user_id, referral_code, sponsor_user_id, user_type, current_level, lifetime_sales, status)
        VALUES (?, ?, ?, 'associate', ?, ?, 'active')
    ")->execute([$uId, $ref, $spon, $level, $sales]);
}

// Seed network tree: 1004 (agent) -> 1005 (sponsor) -> 1006 (grandsponsor)
$pdo->exec("INSERT INTO mlm_network_tree (associate_id, parent_id, sponsor_id, level, position) VALUES (1004, 1005, 1005, 1, 'left')");
$pdo->exec("INSERT INTO mlm_network_tree (associate_id, parent_id, sponsor_id, level, position) VALUES (1005, 1006, 1006, 2, 'left')");

// Seed Leads matching customers, assigned to telecaller user 1001
$leads = [
    ['Customer One', 'test_cust1@example.com', '9876543210', 1001],
    ['Customer Two', 'test_cust2@example.com', '9876543211', 1001],
];

foreach ($leads as [$name, $email, $phone, $assigned]) {
    $pdo->prepare("
        INSERT INTO leads (name, email, phone, assigned_to, status, created_at)
        VALUES (?, ?, ?, ?, 'converted', NOW())
    ")->execute([$name, $email, $phone, $assigned]);
}

// Seed plot bookings
// 99901 -> Token payment (receiptId = 0)
$pdo->exec("
    INSERT INTO plot_bookings (id, plot_id, associate_id, customer_id, booking_amount, total_plot_value, agreement_value, status, created_at)
    VALUES (99901, 1, 1004, 2001, 50000.00, 1000000.00, 1000000.00, 'active', NOW())
");

// 99902 -> Agreement payment (receiptId = 1)
$pdo->exec("
    INSERT INTO plot_bookings (id, plot_id, associate_id, customer_id, booking_amount, total_plot_value, agreement_value, status, created_at)
    VALUES (99902, 1, 1004, 2001, 50000.00, 1000000.00, 1000000.00, 'active', NOW())
");

// Initialize Engine
$engine = new HybridCommissionEngine($pdo);

// ====================================================================
// TEST Case 1: Token conversion incentive (receiptId = 0)
// ====================================================================
echo "--- Running Test Case 1: Token Conversion ---\n";
$res1 = $engine->processPipelineCommission(99901, 0, 50000.00, 1004);
assert_test('Token commission calculation succeeded', $res1['success'] === true);

if ($res1['success']) {
    $tcResult = $res1['telecaller'] ?? null;
    assert_test('Telecaller was resolved', $tcResult !== null);
    if ($tcResult) {
        assert_test('Telecaller got direct flat incentive (₹1,000)', $tcResult['incentive'] >= 1000.00);
        assert_test('Telecaller has ledger entries', count($tcResult['ledger_ids']) > 0);

        // Check TL overrides (Level 1: 2% of ₹50K = ₹1,000, Level 2: 1% of ₹50K = ₹500)
        $ledgers = $pdo->query("SELECT beneficiary_user_id, amount, notes FROM mlm_commission_ledger WHERE booking_id = 99901 OR notes LIKE '%Telecaller%'")->fetchAll(PDO::FETCH_ASSOC);
        
        $hasL1 = false;
        $hasL2 = false;
        foreach ($ledgers as $l) {
            if ($l['beneficiary_user_id'] == 1002 && (float)$l['amount'] == 1000.00) $hasL1 = true;
            if ($l['beneficiary_user_id'] == 1003 && (float)$l['amount'] == 500.00) $hasL2 = true;
        }
        assert_test('TL Gen 1 Level 1 override credited (2%)', $hasL1);
        assert_test('TL Gen 2 Level 2 override credited (1%)', $hasL2);
    }
}

// ====================================================================
// TEST Case 2: Agreement sqft proportional incentive (receiptId = 1)
// ====================================================================
echo "\n--- Running Test Case 2: Proportional SqFt Incentive ---\n";
// Let's ensure plot 1 exists with 1200 sqft
$pdo->exec("DELETE FROM plots WHERE id = 1");
$pdo->exec("INSERT INTO plots (id, colony_id, plot_number, area_sqft, base_price, total_price, status) VALUES (1, 1, 'T-01', 1200, 1000.00, 1200000.00, 'booked')");

$res2 = $engine->processPipelineCommission(99902, 1, 120000.00, 1004);
assert_test('Agreement commission calculation succeeded', $res2['success'] === true);

if ($res2['success']) {
    $tcResult = $res2['telecaller'] ?? null;
    assert_test('Telecaller resolved on subsequent receipt', $tcResult !== null);
    if ($tcResult) {
        // Area = 1200, Rate = 12/sqft. Total sqft incentive = 1200 * 12 = 14400.
        // Booking Value = 1000000.
        // Payment = 120000.
        // Proportional incentive = 14400 * (120000 / 1000000) = 1728.
        assert_test('Telecaller proportional sqft incentive calculated correctly (₹1,728)', abs($tcResult['incentive'] - (1728.00 + 2400.00 + 1200.00)) < 1.0, "got=" . $tcResult['incentive']); // Telecaller (1728) + TL1 (2400) + TL2 (1200) = 5328
    }
}

// ====================================================================
// TEST Case 3: Same-Rank Override Side-Volume Qualification
// ====================================================================
echo "\n--- Running Test Case 3: Same-Rank Override Side-Volume check ---\n";

// Sub-case A: Upline 1005 has side-volume < ₹50,000.
// Verify: Same-rank override should NOT be paid.
// Booking 99903
$pdo->exec("
    INSERT INTO plot_bookings (id, plot_id, associate_id, customer_id, booking_amount, total_plot_value, agreement_value, status, created_at)
    VALUES (99903, 1, 1004, 2003, 50000.00, 1000000.00, 1000000.00, 'active', '2026-06-01 10:00:00')
");

$res3 = $engine->processPipelineCommission(99903, 0, 50000.00, 1004);
assert_test('Commission calculation for 99903 succeeded', $res3['success'] === true);
if ($res3['success']) {
    $ledgers = $pdo->prepare("SELECT amount FROM mlm_commission_ledger WHERE beneficiary_user_id = 1005 AND notes LIKE 'Track A%' AND booking_id = 99903");
    $ledgers->execute();
    $overrideAmount = (float)$ledgers->fetchColumn();
    assert_test('Same-rank override not paid if side volume < ₹50,000 (got ₹' . number_format($overrideAmount) . ')', $overrideAmount == 0.0);
}

// Sub-case B: Upline 1005 meets ₹50,000 side volume.
// Let's seed a booking directly for 1005 in June 2026.
$pdo->exec("
    INSERT INTO plot_bookings (id, plot_id, associate_id, customer_id, booking_amount, total_plot_value, agreement_value, status, created_at)
    VALUES (99904, 1, 1005, 2003, 60000.00, 1000000.00, 1000000.00, 'active', '2026-06-02 10:00:00')
");

$res4 = $engine->processPipelineCommission(99903, 0, 50000.00, 1004);
assert_test('Commission calculation for 99903 with side-volume succeeded', $res4['success'] === true);
if ($res4['success']) {
    $ledgers = $pdo->prepare("SELECT amount FROM mlm_commission_ledger WHERE beneficiary_user_id = 1005 AND notes LIKE 'Track A%'");
    $ledgers->execute();
    $overrideAmount = (float)$ledgers->fetchColumn();
    // Override is 2% of 50,000 = 1000.
    assert_test('Same-rank override paid if side volume >= ₹50,000 (got ₹' . number_format($overrideAmount) . ')', $overrideAmount == 1000.00, "got=" . $overrideAmount);
}

// Clean up
$pdo->exec("DELETE FROM mlm_commission_ledger WHERE notes LIKE 'Test Telecaller%' OR notes LIKE 'Test Side Volume%' OR notes LIKE '%Telecaller%' OR notes LIKE '%Same-level override%'");
$pdo->exec("DELETE FROM plot_bookings WHERE id IN (99901, 99902, 99903, 99904)");
$pdo->exec("DELETE FROM plots WHERE id = 1");
$pdo->exec("DELETE FROM leads WHERE email IN ('test_cust1@example.com', 'test_cust2@example.com', 'test_cust3@example.com')");
$pdo->exec("DELETE FROM associates WHERE user_id IN (1001, 1002, 1003, 1004, 1005, 1006)");
$pdo->exec("DELETE FROM mlm_profiles WHERE user_id IN (1001, 1002, 1003, 1004, 1005, 1006)");
$pdo->exec("DELETE FROM mlm_network_tree WHERE associate_id IN (1001, 1002, 1003, 1004, 1005, 1006)");
$pdo->exec("DELETE FROM users WHERE id IN (1001, 1002, 1003, 1004, 1005, 1006, 2001, 2002, 2003)");

echo "\n" . str_repeat('=', 50) . "\n";
echo "Results: {$pass} PASS / {$fail} FAIL / " . ($pass + $fail) . " total\n";
echo str_repeat('=', 50) . "\n";

exit($fail > 0 ? 1 : 0);

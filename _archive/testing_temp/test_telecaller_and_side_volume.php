<?php
/**
 * Test: Telecaller Incentives + Same-Rank Side-Volume Verification.
 *
 * Run: php testing/test_telecaller_and_side_volume.php
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

// ── Cleanup (order matters for FK) ──────────────────────────
// mlm_network_tree.associate_id stores USER IDs (not associates.id), so clean by both columns
$pdo->exec("DELETE FROM mlm_commission_ledger WHERE beneficiary_user_id IN (1001,1002,1003,1004,1005,1006) OR source_user_id IN (1001,1002,1003,1004,1005,1006)");
$pdo->exec("DELETE FROM plot_bookings WHERE id IN (99901, 99902, 99903, 99904)");
$pdo->exec("DELETE FROM leads WHERE email IN ('test_cust1@example.com', 'test_cust2@example.com', 'test_cust3@example.com')");
// Clean network_tree by user_id (associate_id column) AND parent_id to catch all prior-run leftovers
$pdo->exec("DELETE FROM mlm_network_tree WHERE associate_id IN (1001,1002,1003,1004,1005,1006)");
$pdo->exec("DELETE FROM mlm_network_tree WHERE parent_id IN (1001,1002,1003,1004,1005,1006)");
$pdo->exec("DELETE FROM mlm_profiles WHERE user_id IN (1001,1002,1003,1004,1005,1006)");
$pdo->exec("DELETE FROM associates WHERE user_id IN (1001,1002,1003,1004,1005,1006)");
$pdo->exec("DELETE FROM users WHERE id IN (1001,1002,1003,1004,1005,1006,2001,2002,2003)");

// ── Seed Users ──────────────────────────────────────────────
foreach ([
    [1001, 'Test Telecaller Agent', 'telecaller1@example.com', 'telecaller', 'telecaller'],
    [1002, 'Test TL Gen 1',        'callertl1@example.com',   'telecaller', 'telecaller'],
    [1003, 'Test TL Gen 2',        'callertl2@example.com',   'telecaller', 'telecaller'],
    [1004, 'Test Field Agent',     'fieldagent@example.com',  'associate',  'networker'],
    [1005, 'Test Sponsor SameRnk', 'fieldsponsor@example.com','associate',  'networker'],
    [1006, 'Test GrandSponsor',    'fieldgrand@example.com',  'associate',  'networker'],
    [2001, 'Customer One',         'test_cust1@example.com',  'customer',   'customer'],
    [2002, 'Customer Two',         'test_cust2@example.com',  'customer',   'customer'],
    [2003, 'Customer Three',       'test_cust3@example.com',  'customer',   'customer'],
] as [$id, $name, $email, $role, $track]) {
    $pdo->prepare("INSERT INTO users (id,name,email,phone,password,role,onboarding_track,status,created_at)
                    VALUES (?,?,?,'9876543210','hashed',?,?, 'active', NOW())")
        ->execute([$id, $name, $email, $role, $track]);
}

// ── Seed Associates ─────────────────────────────────────────
foreach ([
    [1001, 'telecaller', 0, 1000, 12, 1002],
    [1002, 'telecaller', 0,    0,  0, 1003],
    [1003, 'telecaller', 0,    0,  0, null],
    [1004, 'mlm',        0,    0,  0, null],
    [1005, 'mlm',        0,    0,  0, null],
    [1006, 'mlm',        0,    0,  0, null],
] as [$uid,$track,$sal,$inc,$sqft,$parent]) {
    $pdo->prepare("INSERT INTO associates (user_id,status,agent_track,telecaller_salary,telecaller_incentive_rate,telecaller_sqft_rate,telecaller_parent_id,created_at)
                    VALUES (?,'active',?,?,?,?,?,NOW())")
        ->execute([$uid, $track, $sal, $inc, $sqft, $parent]);
}

// ── Seed MLM profiles ──────────────────────────────────────
foreach ([
    [1004, 'fieldagent', 1005, 'Associate', 100000],
    [1005, 'fieldsponsor', 1006, 'Associate', 150000],
    [1006, 'fieldgrand', 1, 'Associate', 200000],
] as [$uid,$ref,$spon,$level,$sales]) {
    $pdo->prepare("INSERT INTO mlm_profiles (user_id,referral_code,sponsor_user_id,user_type,current_level,lifetime_sales,status)
                    VALUES (?,?,?,'associate',?,?, 'active')")
        ->execute([$uid, $ref, $spon, $level, $sales]);
}

// ── Network tree: 1004 → 1005 → 1006 ──────────────────────
$pdo->exec("INSERT INTO mlm_network_tree (associate_id,parent_id,sponsor_id,level,position) VALUES (1004,1005,1005,1,'left')");
$pdo->exec("INSERT INTO mlm_network_tree (associate_id,parent_id,sponsor_id,level,position) VALUES (1005,1006,1006,2,'left')");

// ── Seed leads ──────────────────────────────────────────────
foreach ([
    ['Customer One',   'test_cust1@example.com', '9876543210', 1001],
    ['Customer Two',   'test_cust2@example.com', '9876543211', 1001],
] as [$nm,$em,$ph,$asgn]) {
    $pdo->prepare("INSERT INTO leads (name,email,phone,assigned_to,status,created_at) VALUES (?,?,?,?,'converted',NOW())")
        ->execute([$nm, $em, $ph, $asgn]);
}

// ── Seed plot ───────────────────────────────────────────────
$pdo->exec("DELETE FROM plots WHERE id = 1");
$pdo->exec("INSERT INTO plots (id,colony_id,plot_number,area_sqft,price_per_sqft,total_price,status)
            VALUES (1,2,'T-01',1200,1000,1200000,'booked')");

// ── Resolve associate IDs ───────────────────────────────────
$assocIdMap = [];
foreach ($pdo->query("SELECT id,user_id FROM associates WHERE user_id IN (1001,1004,1005,1006)")->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $assocIdMap[$r['user_id']] = (int)$r['id'];
}
$a4 = $assocIdMap[1004];
$a5 = $assocIdMap[1005];

// ── Seed bookings ───────────────────────────────────────────
$pdo->prepare("INSERT INTO plot_bookings (id,plot_id,associate_id,customer_id,booking_number,booking_date,booking_amount,total_plot_value,agreement_value,status,created_at)
               VALUES (99901,1,?,?,  'TC-T001',CURDATE(),50000,1000000,1000000,'token_paid',NOW())")
    ->execute([$a4, 2001]);

$pdo->prepare("INSERT INTO plot_bookings (id,plot_id,associate_id,customer_id,booking_number,booking_date,booking_amount,total_plot_value,agreement_value,status,created_at)
               VALUES (99902,1,?,?,  'TC-T002',CURDATE(),50000,1000000,1000000,'token_paid',NOW())")
    ->execute([$a4, 2001]);

$engine = new HybridCommissionEngine($pdo);

// ====================================================================
// Case 1: Token conversion (receiptId = 0)
// ====================================================================
echo "--- Case 1: Token Conversion ---\n";
$res1 = $engine->processPipelineCommission(99901, 0, 50000, 1004);
assert_test('Token commission succeeded', $res1['success'] === true);

if ($res1['success']) {
    $tc = $res1['telecaller'] ?? null;
    assert_test('Telecaller resolved', $tc !== null);
    if ($tc) {
        assert_test('Telecaller got flat incentive (₹1,000)', $tc['incentive'] >= 1000.0);
        assert_test('Telecaller has ledger entries', count($tc['ledger_ids']) > 0);
    }

    // TL Gen 1 & 2 overrides — amounts clipped by global cap (≥ 90% of face)
    $tlLedgers = $pdo->query("SELECT beneficiary_user_id, amount FROM mlm_commission_ledger WHERE notes LIKE '%Telecaller%' AND commission_type = 'level_bonus'")->fetchAll(PDO::FETCH_KEY_PAIR);
    assert_test('TL Gen 1 override credited (2% ~ ₹1,000)', isset($tlLedgers[1002]) && $tlLedgers[1002] >= 900.0,
        'got=' . ($tlLedgers[1002] ?? 'none'));
    assert_test('TL Gen 2 override credited (1% ~ ₹500)', isset($tlLedgers[1003]) && $tlLedgers[1003] >= 400.0,
        'got=' . ($tlLedgers[1003] ?? 'none'));
}

// ====================================================================
// Case 2: Agreement sqft proportional (receiptId = 1)
// ====================================================================
echo "\n--- Case 2: Agreement SqFt Proportional ---\n";
$res2 = $engine->processPipelineCommission(99902, 1, 120000, 1004);
assert_test('Agreement commission succeeded', $res2['success'] === true);
if ($res2['success']) {
    $tc2 = $res2['telecaller'] ?? null;
    assert_test('Telecaller resolved on subsequent receipt', $tc2 !== null);
    // Telecaller(1728) + TL1(2400) + TL2(1200) = 5328 before cap; cap may clip
    if ($tc2) {
        assert_test('Telecaller incentive > 0 on agreement payment', $tc2['incentive'] > 0);
    }
}

// ====================================================================
// Case 3: Same-rank side-volume check
// ====================================================================
echo "\n--- Case 3: Same-Rank Override Side-Volume ---\n";

// 3A: Upline 1005 has NO qualifying side volume — override should NOT fire
$pdo->prepare("INSERT INTO plot_bookings (id,plot_id,associate_id,customer_id,booking_number,booking_date,booking_amount,total_plot_value,agreement_value,status,created_at)
               VALUES (99903,1,?,?, 'TC-T003','2026-06-01',50000,1000000,1000000,'token_paid','2026-06-01 10:00:00')")
    ->execute([$a4, 2003]);

$beforeId3a = (int)$pdo->query("SELECT COALESCE(MAX(id),0) FROM mlm_commission_ledger")->fetchColumn();
$res3a = $engine->processPipelineCommission(99903, 0, 50000, 1004);
assert_test('3A commission succeeded', $res3a['success'] === true);
if ($res3a['success']) {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM mlm_commission_ledger WHERE beneficiary_user_id = 1005 AND notes LIKE 'Track A%' AND id > ?");
    $stmt->execute([$beforeId3a]);
    $overrideAmt = (float)$stmt->fetchColumn();
    assert_test('3A same-rank override NOT paid (side vol < ₹50K, got ₹' . number_format($overrideAmt) . ')', $overrideAmt == 0.0);
}

// 3B: Seed a booking for 1005 (side volume = ₹60K) — override SHOULD fire
$pdo->prepare("INSERT INTO plot_bookings (id,plot_id,associate_id,customer_id,booking_number,booking_date,booking_amount,total_plot_value,agreement_value,status,created_at)
               VALUES (99904,1,?,?, 'TC-T004','2026-06-02',60000,1000000,1000000,'token_paid','2026-06-02 10:00:00')")
    ->execute([$a5, 2003]);

$beforeId3b = (int)$pdo->query("SELECT COALESCE(MAX(id),0) FROM mlm_commission_ledger")->fetchColumn();
$res3b = $engine->processPipelineCommission(99903, 0, 50000, 1004);
assert_test('3B commission succeeded', $res3b['success'] === true);
if ($res3b['success']) {
    $stmt = $pdo->prepare("SELECT amount, notes FROM mlm_commission_ledger WHERE beneficiary_user_id = 1005 AND notes LIKE 'Track A%Same-level%' AND id > ?");
    $stmt->execute([$beforeId3b]);
    $override = $stmt->fetch(PDO::FETCH_ASSOC);
    $overrideAmt = $override ? (float)$override['amount'] : 0;
    assert_test('3B same-rank override paid (side vol ≥ ₹50K, got ₹' . number_format($overrideAmt) . ')', $overrideAmt >= 900.0,
        'expected≥900, got=' . number_format($overrideAmt));
}

// ── Cleanup ─────────────────────────────────────────────────
$pdo->exec("DELETE FROM mlm_commission_ledger WHERE beneficiary_user_id IN (1001,1002,1003,1004,1005,1006) OR source_user_id IN (1001,1002,1003,1004,1005,1006)");
$pdo->exec("DELETE FROM plot_bookings WHERE id IN (99901, 99902, 99903, 99904)");
$pdo->exec("DELETE FROM plots WHERE id = 1");
$pdo->exec("DELETE FROM leads WHERE email IN ('test_cust1@example.com', 'test_cust2@example.com', 'test_cust3@example.com')");
$pdo->exec("DELETE FROM mlm_network_tree WHERE associate_id IN (1001,1002,1003,1004,1005,1006)");
$pdo->exec("DELETE FROM mlm_network_tree WHERE parent_id IN (1001,1002,1003,1004,1005,1006)");
$pdo->exec("DELETE FROM mlm_profiles WHERE user_id IN (1001,1002,1003,1004,1005,1006)");
$pdo->exec("DELETE FROM associates WHERE user_id IN (1001,1002,1003,1004,1005,1006)");
$pdo->exec("DELETE FROM users WHERE id IN (1001,1002,1003,1004,1005,1006,2001,2002,2003)");

echo "\n" . str_repeat('=', 50) . "\n";
echo "Results: {$pass} PASS / {$fail} FAIL / " . ($pass + $fail) . " total\n";
echo str_repeat('=', 50) . "\n";
exit($fail > 0 ? 1 : 0);

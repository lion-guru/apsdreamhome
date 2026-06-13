<?php
/**
 * Run Commission Engine on Motiram Test Bookings
 * ─────────────────────────────────────────────────
 * Exercises processPipelineCommission on the 6 test bookings
 * to verify Track A (slab differential), Track B (performance rollup),
 * and Track C (milestone escrow) all fire correctly.
 *
 * Usage: php database/seeder/run_commission_engine.php
 */

$root   = dirname(__DIR__, 2);
$config = require $root . '/config/database.php';
$pdo    = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

define('APP_ROOT', $root);
require_once $root . '/app/Core/Autoloader.php';
$autoloader = \App\Core\Autoloader::getInstance();
$autoloader->register();

// Load dotenv if available
if (file_exists($root . '/.env')) {
    $lines = file($root . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (trim($line) === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if (!isset($_ENV[$key])) $_ENV[$key] = $value;
    }
}

use App\Services\HybridCommissionEngine;

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║  Commission Engine — Motiram Test Bookings             ║\n";
echo "║  APS Dream Homes Pvt. Ltd.                              ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

$engine = new HybridCommissionEngine($pdo);

// Get the test bookings (Motiram only)
$stmt = $pdo->prepare("
    SELECT pb.id AS booking_id, pb.plot_id, pb.booking_amount, pb.agreement_value,
           pb.status, pb.booking_date, pb.associate_id,
           p.plot_number, p.block, p.total_price
    FROM plot_bookings pb
    JOIN plots p ON p.id = pb.plot_id
    WHERE p.colony_id = 7
    ORDER BY pb.booking_date ASC, pb.id ASC
");
$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$agentUserId = 9;
$associateId = null;

// Resolve associate_id for user 9
$assocRow = $pdo->query("SELECT id FROM associates WHERE user_id = {$agentUserId} LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if ($assocRow) {
    $associateId = (int) $assocRow['id'];
}

echo "Configuration:\n";
echo "  Agent user_id    : {$agentUserId}\n";
echo "  associate_id     : {$associateId}\n";
echo "  Bookings to process: " . count($bookings) . "\n\n";

// Track cumulative commission impact
$totalTrackA = 0;
$totalTrackB = 0;
$totalTrackC = 0;
$totalDistributed = 0;

echo str_repeat("─", 80) . "\n";
echo sprintf("  %-8s  %-12s  %-8s  %-12s  %-10s  %-8s\n", "Booking", "Plot", "Amount", "Track A", "Track B", "Track C");
echo str_repeat("─", 80) . "\n";

foreach ($bookings as $bk) {
    $bookingId  = (int) $bk['booking_id'];
    $plotNumber = $bk['plot_number'];
    $amount     = (float) $bk['booking_amount'];  // token paid — commission runs on actual payment

    // Process commission on each booking token amount
    $result = $engine->processPipelineCommission(
        $bookingId,
        0,              // receiptId (placeholder)
        $amount,        // amountReceived
        $agentUserId    // executingAgentId
    );

    $trackA = $result['track_a']['distributed'] ?? 0;
    $trackB = $result['track_b']['distributed'] ?? 0;
    $trackC = $result['track_c']['distributed'] ?? 0;
    $total  = $result['total_distributed'] ?? 0;

    $totalTrackA += $trackA;
    $totalTrackB += $trackB;
    $totalTrackC += $trackC;
    $totalDistributed += $total;

    $success = ($result['success'] ?? false) ? '✓' : '✗';
    $reason  = $result['error'] ?? '';

    echo sprintf("  %s %-5d  %-12s  ₹%-10s  ₹%-10s  ₹%-8s  ₹%-8s",
        $success, $bookingId, $plotNumber, number_format($amount),
        number_format($trackA, 2), number_format($trackB, 2), number_format($trackC, 2));

    if ($reason) echo "  [{$reason}]";
    echo "\n";
}

echo str_repeat("─", 80) . "\n";
echo sprintf("  %-8s  %-12s  %-8s  ₹%-10s  ₹%-8s  ₹%-8s\n",
    "TOTAL", "", "", "", number_format($totalTrackA, 2),
    number_format($totalTrackB, 2), number_format($totalTrackC, 2));
echo str_repeat("─", 80) . "\n\n";

/* ─── LEDGER VERIFICATION ──────────────────────────────────── */
echo "── Ledger Entries ──\n";
$ledger = $pdo->prepare("
    SELECT commission_type, COUNT(*) AS cnt, SUM(amount) AS total
    FROM mlm_commission_ledger
    WHERE beneficiary_user_id = ?
    GROUP BY commission_type
    ORDER BY commission_type
");
$ledger->execute([$agentUserId]);
$ledgerRows = $ledger->fetchAll(PDO::FETCH_ASSOC);

foreach ($ledgerRows as $lr) {
    echo sprintf("  %-20s  %d entries  ₹%s\n",
        $lr['commission_type'], $lr['cnt'], number_format((float)$lr['total'], 2));
}

/* ─── GBV VERIFICATION ─────────────────────────────────────── */
echo "\n── Agent GBV ──\n";
$gbvStmt = $pdo->prepare("SELECT lifetime_sales FROM mlm_profiles WHERE user_id = ?");
$gbvStmt->execute([$agentUserId]);
$gbvRow = $gbvStmt->fetch(PDO::FETCH_ASSOC);
$gbv = $gbvRow ? (float) $gbvRow['lifetime_sales'] : 0;
echo "  Cumulative GBV: ₹" . number_format($gbv, 2) . "\n";

$rankStmt = $pdo->prepare("SELECT current_level FROM mlm_profiles WHERE user_id = ?");
$rankStmt->execute([$agentUserId]);
$rankRow = $rankStmt->fetch(PDO::FETCH_ASSOC);
echo "  Current Level  : " . ($rankRow['current_level'] ?? 'unknown') . "\n";

/* ─── TRACK B CONSECUTIVE MONTHS ───────────────────────────── */
echo "\n── Track B Consecutive Qualifying Months ──\n";
$months = $pdo->prepare("
    SELECT DATE_FORMAT(pb.created_at, '%Y-%m') AS ym,
           SUM(COALESCE(pb.agreement_value, pb.total_plot_value, 0)) AS month_total
    FROM plot_bookings pb
    WHERE pb.associate_id = ?
      AND pb.status NOT IN ('cancelled')
    GROUP BY ym
    ORDER BY ym DESC
    LIMIT 12
");
$months->execute([$associateId]);
$monthRows = $months->fetchAll(PDO::FETCH_ASSOC);

foreach ($monthRows as $mr) {
    $qualifies = $mr['month_total'] >= 50000 ? '✓' : '✗';
    echo "  {$mr['ym']}: ₹" . number_format((float)$mr['month_total'], 2) . " {$qualifies}\n";
}

/* ─── ESCROW BALANCE ───────────────────────────────────────── */
echo "\n── Track C Escrow Balance ──\n";
$escrow = $engine->getAgentEscrowBalance($agentUserId);
echo "  Cumulative Escrow: ₹" . number_format($escrow, 2) . "\n";

/* ─── SALARY INCENTIVE ELIGIBILITY ─────────────────────────── */
echo "\n── Salary Incentive Eligibility ──\n";
$salary = $engine->checkSalaryIncentiveEligibility($agentUserId);
if ($salary['eligible']) {
    echo "  ✓ ELIGIBLE — Tier {$salary['tier']}: ₹" . number_format($salary['bonus'], 2) . "/month\n";
} else {
    echo "  ✗ Not eligible (need ≥₹15L cumulative sales + ≥60 days active)\n";
    echo "    Current GBV: ₹" . number_format($gbv, 2) . "\n";
}

echo "\n╔══════════════════════════════════════════════════════════╗\n";
echo "║  Commission engine run complete!                       ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n";

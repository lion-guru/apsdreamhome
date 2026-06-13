<?php
/**
 * Seed Test Bookings — Motiram Township + Commission Pipeline
 * ───────────────────────────────────────────────────────────────
 * Creates test bookings on Motiram Township plots to exercise:
 *   1. plot_bookings (token_paid / emi_active)
 *   2. booking_payment_schedules (EMI installments)
 *   3. Plot status updates (available → booked)
 *   4. Track B qualifying months (consecutive ≥₹50K)
 *
 * Agent 9 (Test Emp) → associates.id (created if missing)
 * Customer 3 (Customer One) is the buyer
 *
 * Safety: Idempotent — skips if bookings already exist for Motiram plots.
 * Usage:  php database/seeder/seed_test_bookings.php
 */

$root   = dirname(__DIR__, 2);
$config = require $root . '/config/database.php';
$pdo    = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$now       = date('Y-m-d H:i:s');
$adminId   = 1;
$customerId = 3;   // Customer One
$agentUserId = 9;  // Test Emp

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║  Test Bookings — Motiram Township Seeder               ║\n";
echo "║  APS Dream Homes Pvt. Ltd.                              ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

/* ─── IDEMPOTENCY CHECK ─────────────────────────────────────── */
$existingCount = $pdo->query("SELECT COUNT(*) FROM plot_bookings WHERE plot_id IN (SELECT id FROM plots WHERE colony_id = 7)")->fetchColumn();
if ($existingCount > 0) {
    echo "⚠  {$existingCount} Motiram bookings already exist. Skipping.\n";
    exit(0);
}

echo "[1/5] Idempotency check … PASS\n\n";

$pdo->beginTransaction();
echo "[TX]  Transaction started\n\n";

try {

    /* ─── ENSURE ASSOCIATE RECORD FOR USER 9 ──────────────────── */
    echo "[2/5] Ensuring associate record for user {$agentUserId} …\n";

    $assocRow = $pdo->query("SELECT id FROM associates WHERE user_id = {$agentUserId} LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if ($assocRow) {
        $associateId = (int) $assocRow['id'];
        echo "       associate_id = {$associateId} (existing)\n";
    } else {
        $pdo->prepare('INSERT INTO associates (user_id, status, created_at) VALUES (?, ?, ?)')->execute([$agentUserId, 'active', $now]);
        $associateId = (int) $pdo->lastInsertId();
        echo "       associate_id = {$associateId} (created)\n";
    }

    // Also ensure the mlm_profiles link exists
    $mlmRow = $pdo->query("SELECT id FROM mlm_profiles WHERE user_id = {$agentUserId}")->fetch();
    if (!$mlmRow) {
        $refCode = 'TEST' . str_pad($agentUserId, 4, '0', STR_PAD_LEFT);
        $pdo->prepare('INSERT INTO mlm_profiles (user_id, referral_code, sponsor_user_id, user_type, current_level, lifetime_sales, total_team_size, direct_referrals, total_commission, pending_commission, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([$agentUserId, $refCode, 2, 'agent', 0, 0, 0, 0, 0, 0, 'active', $now]);
        echo "       mlm_profiles created for user {$agentUserId}\n";
    }

    // Ensure network_tree link
    $treeRow = $pdo->query("SELECT id FROM mlm_network_tree WHERE associate_id = {$agentUserId}")->fetch();
    if (!$treeRow) {
        $pdo->prepare('INSERT INTO mlm_network_tree (associate_id, sponsor_id, parent_id, level, position, created_at) VALUES (?, ?, ?, ?, ?, ?)')
            ->execute([$agentUserId, 2, 1, 1, 'right', $now]);
        echo "       network_tree link created: {$agentUserId} → 2 → 1\n";
    }

    echo "\n";

    /* ─── BOOKING DATA ──────────────────────────────────────────
       6 bookings across 3 months (April, May, June 2026)
       Each month ≥ ₹50K → 3 consecutive qualifying months for Track B
       ───────────────────────────────────────────────────────── */

    echo "[3/5] Creating 6 bookings on Motiram plots …\n";

    // Get Motiram plot IDs (block, plot_number, price_per_sqft, total_price)
    $plots = $pdo->query("
        SELECT id, plot_number, block, price_per_sqft, total_price
        FROM plots
        WHERE colony_id = 7 AND status = 'available'
        ORDER BY plot_number
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);

    if (count($plots) < 6) {
        throw new \RuntimeException('Need at least 6 available Motiram plots, found ' . count($plots));
    }

    // Booking definitions:  [plot_index, month (Y-m-d), channel, booking_amount, agreement_value, status]
    $bookings = [
        // April 2026 — 2 bookings, total ≈ ₹30L → qualifies
        [0, '2026-04-05', 'associate', 51000,  $plots[0]['total_price'], 'emi_active'],
        [1, '2026-04-12', 'agent',     51000,  $plots[1]['total_price'], 'emi_active'],

        // May 2026 — 2 bookings, total ≈ ₹30L → qualifies
        [2, '2026-05-08', 'associate', 51000,  $plots[2]['total_price'], 'emi_active'],
        [3, '2026-05-20', 'agent',     51000,  $plots[3]['total_price'], 'emi_active'],

        // June 2026 (current) — 2 bookings, total ≈ ₹30L → qualifies
        [4, '2026-06-01', 'associate', 51000,  $plots[4]['total_price'], 'token_paid'],
        [5, '2026-06-10', 'agent',     51000,  $plots[5]['total_price'], 'token_paid'],
    ];

    $bookingInsert = $pdo->prepare('
        INSERT INTO plot_bookings
            (plot_id, customer_id, booking_number, booking_date,
             total_plot_value, booking_amount, agreement_value,
             status, channel, associate_id, commission_pct, notes, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    $scheduleInsert = $pdo->prepare('
        INSERT INTO booking_payment_schedules
            (booking_id, installment_no, due_date, amount, principal, interest,
             opening_balance, closing_balance, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    $plotUpdate = $pdo->prepare('UPDATE plots SET status = ? WHERE id = ?');

    $bookedIds = [];

    foreach ($bookings as [$plotIdx, $bookDate, $channel, $tokenAmt, $agreementVal, $status]) {
        $plot = $plots[$plotIdx];
        $bookingNum = 'APS-BK-' . date('Ymd', strtotime($bookDate)) . '-' . str_pad($plotIdx + 1, 4, '0', STR_PAD_LEFT);

        $bookingInsert->execute([
            $plot['id'],
            $customerId,
            $bookingNum,
            $bookDate,
            $plot['total_price'],
            $tokenAmt,
            $agreementVal,
            $status,
            $channel,
            $associateId,
            2.00,  // commission_pct
            "Test booking — {$plot['plot_number']} ({$plot['block']} block) via {$channel}",
            $bookDate . ' 10:00:00',
        ]);

        $bookingId = (int) $pdo->lastInsertId();
        $bookedIds[] = $bookingId;

        // Update plot status
        $plotUpdate->execute([$status === 'cancelled' ? 'available' : 'booked', $plot['id']]);

        // Create EMI schedule (6 monthly installments)
        $remaining = $agreementVal - $tokenAmt;
        $emiAmount = round($remaining / 6, 2);
        $dueDate = new \DateTime($bookDate);

        for ($inst = 1; $inst <= 6; $inst++) {
            $dueDate->modify('+1 month');
            $instStatus = ($inst === 1) ? 'pending' : 'pending';
            $principal = round($emiAmount * 0.7, 2);
            $interest  = round($emiAmount * 0.3, 2);

            $scheduleInsert->execute([
                $bookingId,
                $inst,
                $dueDate->format('Y-m-d'),
                $emiAmount,
                $principal,
                $interest,
                $remaining,
                max(0, $remaining - $emiAmount),
                $instStatus,
                $now,
            ]);

            $remaining -= $emiAmount;
        }

        $month = date('M Y', strtotime($bookDate));
        echo "       {$bookingNum}: {$plot['plot_number']} ({$plot['block']}) — ₹" . number_format($plot['total_price']) . " [{$status}] — {$month}\n";
    }

    echo "\n";

    /* ─── PAYMENT SCHEDULE SUMMARY ────────────────────────────── */
    echo "[4/5] Payment schedules created …\n";

    $totalSchedules = $pdo->query("SELECT COUNT(*) FROM booking_payment_schedules WHERE booking_id IN (" . implode(',', $bookedIds) . ")")->fetchColumn();
    echo "       {$totalSchedules} installments across " . count($bookedIds) . " bookings\n";

    // Show monthly totals for Track B verification
    echo "\n       Monthly booking totals (for Track B):\n";
    $monthlyTotals = $pdo->query("
        SELECT DATE_FORMAT(booking_date, '%Y-%m') AS ym,
               COUNT(*) AS cnt,
               SUM(booking_amount) AS total
        FROM plot_bookings
        WHERE associate_id = {$associateId}
          AND status NOT IN ('cancelled')
        GROUP BY ym
        ORDER BY ym ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($monthlyTotals as $mt) {
        $qualifies = $mt['total'] >= 50000 ? '✓ QUALIFIES' : '✗ below threshold';
        echo "       {$mt['ym']}: {$mt['cnt']} bookings = ₹" . number_format($mt['total']) . " {$qualifies}\n";
    }

    echo "\n";

    /* ─── COLONY PLOT SUMMARY ─────────────────────────────────── */
    echo "[5/5] Colony summary after bookings …\n";

    $plotStats = $pdo->query("
        SELECT status, COUNT(*) AS cnt
        FROM plots
        WHERE colony_id = 7
        GROUP BY status
    ")->fetchAll(PDO::FETCH_KEY_PAIR);

    foreach ($plotStats as $status => $cnt) {
        echo "       {$status}: {$cnt}\n";
    }

    /* ─── COMMIT ──────────────────────────────────────────────── */
    $pdo->commit();

    echo "\n╔══════════════════════════════════════════════════════════╗\n";
    echo "║  ✅  Test bookings seeded successfully!                ║\n";
    echo "╚══════════════════════════════════════════════════════════╝\n\n";

    echo "Summary:\n";
    echo "  Agent         : user {$agentUserId} → associate_id {$associateId}\n";
    echo "  Customer      : user {$customerId}\n";
    echo "  Bookings      : " . count($bookedIds) . " (IDs: " . implode(', ', $bookedIds) . ")\n";
    echo "  Plot IDs      : " . implode(', ', array_map(fn($p) => $p['id'], array_slice($plots, 0, 6))) . "\n";
    echo "  Track B ready : 3 consecutive months ≥₹50K\n";
    echo "\n";
    echo "Next: Run commission engine on these bookings to see Track B fire!\n";

} catch (\Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();

    echo "\n╔══════════════════════════════════════════════════════════╗\n";
    echo "║  ❌  SEED FAILED — Transaction rolled back             ║\n";
    echo "╚══════════════════════════════════════════════════════════╝\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File:  " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}

<?php
/**
 * Migration S522: Backfill EMI schedules for the 20 historical bookings.
 *
 * Triggered by explicit owner approval: "Backfill EMI schedules for the 20 historical bookings"
 * — Is dev-mode test data from 2026-11-05 E2E journey run. This migration ports the 20
 * historical rows from the legacy `bookings` table into `plot_bookings` and seeds a
 * full 12-month EMI schedule for each in `booking_payment_schedules`.
 *
 * Plan per historical booking (9592-9611, all created 2026-11-05):
 *   1. Insert a `plot_bookings` row mirroring the legacy booking:
 *        plot_id, customer_id=users.id, customer_name, total_plot_value=plots.total_price,
 *        status='emi_active', booking_date='2026-07-01', booking_number='TEST-HIST-{n}',
 *        booking_amount = 20% token, notes='TEST SEED (historical backfill)', created_by=1
 *   2. Seed 12 EMIs in `booking_payment_schedules`: 20% down + 12 instalments of 80% total.
 *      EMI #1 due 2026-12-05; monthly thereafter. Tag each with 'TEST SEED (dev)' in remarks.
 *
 * Idempotency: each run first purges any existing plot_bookings rows whose booking_number
 * matches the production cone (`TEST-HIST-%`) and their dependent schedules, then re-inserts.
 *
 * Safety:
 *   - Pre-flight dump verified: backups/db/pre_historical_backfill.sql
 *   - Runs inside a single transaction with rollback on any error.
 */
require_once __DIR__ . '/../../config/bootstrap.php';

use App\Core\Database\Database;

try {
    $pdo = \App\Core\Database\Database::getInstance()->getConnection();
} catch (\Throwable $e) {
    die("DB connection failed: " . $e->getMessage() . "\n");
}

echo "=== MIGRATION S522: Historical backfill of 20 bookings + EMI schedules ===\n";

$historicalIds = range(9592, 9611);
echo "Historical bookings to backfill: " . implode(',', $historicalIds) . "\n";

// Read plot_ids straight from legacy bookings joined to plots for price source-of-truth.
$stmt = $pdo->query(
    "SELECT b.id AS legacy_booking_id, b.plot_id, b.customer_id,
            p.total_price AS plot_total_price, u.name AS customer_name,
            p.plot_number AS plot_number
     FROM bookings b
     JOIN plots p ON p.id = b.plot_id
     JOIN users u ON u.id = b.customer_id
     WHERE b.id IN (" . implode(',', $historicalIds) . ")
     ORDER BY b.id ASC"
);
$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

$found = array_column($rows, 'legacy_booking_id');
$missing = array_diff($historicalIds, $found);
if ($missing) {
    echo "ERROR: missing historical bookings: " . implode(',', $missing) . "\n";
    exit(1);
}

$pdo->beginTransaction();
try {
    // Step 1: purge any pre-existing TEST-HIST seeds so the migration is fully idempotent.
    $delHistRecv = $pdo->prepare(
        "DELETE FROM booking_payment_receipts
         WHERE booking_id IN (SELECT id FROM plot_bookings WHERE booking_number LIKE 'TEST-HIST-%')"
    );
    $delHistRecv->execute();

    $delHistSched = $pdo->prepare(
        "DELETE FROM booking_payment_schedules
         WHERE booking_id IN (SELECT id FROM plot_bookings WHERE booking_number LIKE 'TEST-HIST-%')"
    );
    $delHistSched->execute();

    $delHist = $pdo->prepare("DELETE FROM plot_bookings WHERE booking_number LIKE 'TEST-HIST-%'");
    $delHist->execute();
    echo "[S522-0] PURGED prior TEST-HIST rows (idempotent re-run prep).\n";

    // Step 2: insert the 20 mirror plot_bookings rows.
    $bookNumStmt = $pdo->prepare(
        "INSERT INTO plot_bookings
         (tenant_id, plot_id, colony_id, customer_id, customer_name,
          booking_number, booking_date, booking_amount, total_plot_value,
          agreement_value, status, channel, notes, created_by,
          created_at, updated_at)
         VALUES (?, ?, 1, ?, ?,
                 ?, ?, ?, ?,
                 ?, 'emi_active', 'walk_in',
                 'TEST SEED (dev) historical backfill S522', 1,
                 NOW(), NOW())"
    );

    $insertedBookingIds = [];
    $n = 0;
    foreach ($rows as $r) {
        $n++;
        $tokenAmt = round((float)$r['plot_total_price'] * 0.20, 2);
        $bookingNumber = sprintf('TEST-HIST-%04d', $n);
        $bookNumStmt->execute([
            1,                         // tenant_id
            $r['plot_id'],
            $r['customer_id'],
            $r['customer_name'],
            $bookingNumber,
            '2026-07-01',              // booking date in seed universe
            $tokenAmt,                 // 20% down payment
            $r['plot_total_price'],
            $r['plot_total_price'],   // agreement_value mirrors full value
        ]);
        $insertedBookingIds[] = [
            'id' => (int)$pdo->lastInsertId(),
            'number' => $bookingNumber,
            'customer_name' => $r['customer_name'],
            'total_price' => (float)$r['plot_total_price'],
            'plot_id' => (int)$r['plot_id'],
        ];
    }
    echo "[S522-1] INSERTED 20 plot_bookings rows.\n";

    // Step 3: seed 12 EMIs per new booking — 20% received up front, 80% over 12 months.
    $schedStmt = $pdo->prepare(
        "INSERT INTO booking_payment_schedules
         (id, tenant_id, booking_id, installment_no, due_date, due_date_year,
          amount, principal, interest, opening_balance, closing_balance,
          status, paid_amount, paid_date, remarks, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?,
                 ?, ?, 0.00, ?, ?,
                 'pending', 0.00, NULL, 'TEST SEED (dev) historical backfill S522', NOW(), NOW())"
    );

    $maxId = (int)$pdo->query(
        "SELECT COALESCE(MAX(id), 0) FROM booking_payment_schedules"
    )->fetchColumn();

    $installmentTotal = 0;
    foreach ($insertedBookingIds as $b) {
        $down = round($b['total_price'] * 0.20, 2);
        $emi = round(($b['total_price'] - $down) / 12.0, 2);
        for ($i = 1; $i <= 12; $i++) {
            $maxId++;
            $dueDate = date('Y-m-d', strtotime("2026-12-05 +" . ($i - 1) . " months"));
            $dueYear = (int)date('Y', strtotime($dueDate));
            $opening = $emi * (12 - $i + 1);
            $closing = $emi * (12 - $i);
            $schedStmt->execute([
                $maxId, 1, $b['id'], $i, $dueDate, $dueYear,
                $emi, $emi, $opening, $closing,
            ]);
            $installmentTotal++;
        }
    }
    echo "[S522-2] INSERTED {$installmentTotal} EMI schedule rows for 20 bookings.\n";

    $pdo->commit();
    echo "[S522-3] Transaction committed.\n";
} catch (\Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "[S522-ERROR] {$e->getMessage()} — rolled back, zero rows committed.\n";
    exit(1);
}

echo "=== S522 migration complete ===\n";

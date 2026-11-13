<?php
/**
 * Migration S507: Reconcile booking_payment_schedules — orphan rows, ghost FK,
 * and seed proper EMI schedules for the 3 live test bookings (dev mode).
 *
 * Repair (idempotent):
 *   1. Delete orphan schedule rows (booking_id NOT present in plot_bookings).
 *   2. Delete ghost receipts + demand letters (dead booking_id 9005 etc.).
 *   3. Drop the stale FK `fk_bps_booking` (→ legacy bookings.id) and add the
 *      correct one → plot_bookings(id) ON DELETE CASCADE.
 *   4. Seed a proper EMI schedule for the 3 live test bookings
 *      (plot_bookings ids 1, 5, 12): 20% down at booking, remaining 80% over
 *      12 monthly EMIs beginning 2026-12-05. Booking 1's first 2 EMIs are
 *      marked PAID with cleared receipts to exercise the paid-flow rendering.
 *
 * Idempotent: re-running deletes nothing new (orphans already gone), keeps the
 * plot_bookings FK, and skips the seed if schedules already exist.
 * Pre-flight safety dump was taken BEFORE this migration:
 *   backups/db/bps_pre_orphan_cleanup_2026-11-14_*.sql
 */
require_once __DIR__ . '/../../config/bootstrap.php';

use App\Core\Database\Database;

try {
    $pdo = \App\Core\Database\Database::getInstance()->getConnection();
} catch (\Throwable $e) {
    die("DB connection failed: " . $e->getMessage() . "\n");
}

echo "=== MIGRATION S507: booking_payment_schedules orphan reconcile + seed ===\n";

// ---------------------------------------------------------------------------
// STEP 1: Count + delete orphan schedule rows
// ---------------------------------------------------------------------------
try {
    $orphanCount = (int)$pdo->query(
        "SELECT COUNT(*) FROM booking_payment_schedules
         WHERE booking_id NOT IN (SELECT id FROM plot_bookings)"
    )->fetchColumn();

    if ($orphanCount > 0) {
        $stmt = $pdo->prepare(
            "DELETE FROM booking_payment_schedules
             WHERE booking_id NOT IN (SELECT id FROM plot_bookings)"
        );
        $stmt->execute();
        echo "[S507-1] DELETED {$orphanCount} orphan schedule rows (booking_id not in plot_bookings).\n";
    } else {
        echo "[S507-1] OK: no orphan schedule rows.\n";
    }
} catch (\Throwable $e) {
    echo "[S507-1] ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// ---------------------------------------------------------------------------
// STEP 2: Count + delete ghost receipts + demand letters
// ---------------------------------------------------------------------------
foreach (['booking_payment_receipts', 'booking_demand_letters'] as $table) {
    try {
        $ghost = (int)$pdo->query(
            "SELECT COUNT(*) FROM `{$table}`
             WHERE booking_id NOT IN (SELECT id FROM plot_bookings)"
        )->fetchColumn();
        if ($ghost > 0) {
            $stmt = $pdo->prepare(
                "DELETE FROM `{$table}`
                 WHERE booking_id NOT IN (SELECT id FROM plot_bookings)"
            );
            $stmt->execute();
            echo "[S507-2] DELETED {$ghost} ghost rows from `{$table}` (dead booking_id).\n";
        } else {
            echo "[S507-2] OK: no ghost rows in `{$table}`.\n";
        }
    } catch (\Throwable $e) {
        echo "[S507-2] ERROR {$table}: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// ---------------------------------------------------------------------------
// STEP 3: Swap stale FK for the correct one (plot_bookings)
// ---------------------------------------------------------------------------
try {
    $fkExists = (bool)$pdo->query(
        "SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
         WHERE CONSTRAINT_SCHEMA = DATABASE()
           AND TABLE_NAME = 'booking_payment_schedules'
           AND CONSTRAINT_NAME = 'fk_bps_booking'"
    )->fetchColumn();

    $fkTarget = $pdo->query(
        "SELECT REFERENCED_TABLE_NAME
         FROM information_schema.KEY_COLUMN_USAGE
         WHERE CONSTRAINT_SCHEMA = DATABASE()
           AND TABLE_NAME = 'booking_payment_schedules'
           AND CONSTRAINT_NAME = 'fk_bps_booking'"
    )->fetchColumn();

    if ($fkExists) {
        if ($fkTarget === 'plot_bookings') {
            echo "[S507-3] SKIP: fk_bps_booking already references plot_bookings.\n";
        } else {
            $pdo->exec("ALTER TABLE `booking_payment_schedules` DROP FOREIGN KEY `fk_bps_booking`");
            $pdo->exec(
                "ALTER TABLE `booking_payment_schedules`
                 ADD CONSTRAINT `fk_bps_booking`
                 FOREIGN KEY (`booking_id`) REFERENCES `plot_bookings` (`id`)
                 ON DELETE CASCADE ON UPDATE CASCADE"
            );
            echo "[S507-3] FIXED: fk_bps_booking re-pointed (bookings.id -> plot_bookings.id, ON DELETE CASCADE).\n";
        }
    } else {
        $pdo->exec(
            "ALTER TABLE `booking_payment_schedules`
             ADD CONSTRAINT `fk_bps_booking`
             FOREIGN KEY (`booking_id`) REFERENCES `plot_bookings` (`id`)
             ON DELETE CASCADE ON UPDATE CASCADE"
        );
        echo "[S507-3] CREATED: fk_bps_booking (plot_bookings.id, ON DELETE CASCADE).\n";
    }
} catch (\Throwable $e) {
    echo "[S507-3] ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// ---------------------------------------------------------------------------
// STEP 3b: Same stale FK fix for booking_payment_receipts + booking_demand_letters
// (fk_*_booking -> legacy `bookings` table, rows 9592-9611, none of which are
// referenced by the live test data; re-point at plot_bookings like the app code).
// ---------------------------------------------------------------------------
$siblingRepoints = [
    'booking_payment_receipts' => 'fk_bpr_booking',
    'booking_demand_letters'   => 'fk_bdl_booking',
];
foreach ($siblingRepoints as $table => $fk) {
    try {
        $exists = (bool)$pdo->query(
            "SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = DATABASE()
               AND TABLE_NAME = '{$table}'
               AND CONSTRAINT_NAME = '{$fk}'"
        )->fetchColumn();

        $target = $pdo->query(
            "SELECT REFERENCED_TABLE_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE CONSTRAINT_SCHEMA = DATABASE()
               AND TABLE_NAME = '{$table}'
               AND CONSTRAINT_NAME = '{$fk}'"
        )->fetchColumn();

        if ($exists && $target === 'plot_bookings') {
            echo "[S507-3b] SKIP: `{$table}`.`{$fk}` already references plot_bookings.\n";
            continue;
        }
        if ($exists) {
            $pdo->exec("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fk}`");
        }
        $pdo->exec(
            "ALTER TABLE `{$table}`
             ADD CONSTRAINT `{$fk}`
             FOREIGN KEY (`booking_id`) REFERENCES `plot_bookings` (`id`)
             ON DELETE CASCADE ON UPDATE CASCADE"
        );
        echo "[S507-3b] FIXED: `{$table}`.`{$fk}` re-pointed (bookings.id -> plot_bookings.id, ON DELETE CASCADE).\n";
    } catch (\Throwable $e) {
        echo "[S507-3b] ERROR `{$table}`: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// ---------------------------------------------------------------------------
// STEP 4: Seed 12-month EMI schedules for the 3 live test bookings
// ---------------------------------------------------------------------------
try {
    $already = (int)$pdo->query(
        "SELECT COUNT(*) FROM booking_payment_schedules WHERE booking_id IN (1, 5, 12)"
    )->fetchColumn();

    if ($already > 0) {
        echo "[S507-4] SKIP: EMI schedules already exist for bookings 1/5/12 ({$already} rows).\n";
    } else {
        $seeds = [
            1  => 3000000.00,  // admin test booking, plot 52, RN-A-002
            5  => 3600000.00,  // admin test booking, plot 53, RN-A-003
            12 => 1098900.00,  // customer1 test booking, plot 11, A-001
        ];

        $conn = $pdo;
        $conn->beginTransaction();

        foreach ($seeds as $bookingId => $price) {
            $down = round($price * 0.20, 2);            // 20% down at booking time
            $emi = round(($price - $down) / 12.0, 2);   // flat test EMI
            $paidInsts = ($bookingId === 1) ? [1, 2] : [];  // booking 1: 2 EMIs pre-paid

            for ($n = 1; $n <= 12; $n++) {
                $dueDate = date('Y-m-d', strtotime("2026-12-05 +" . ($n - 1) . " months"));
                $year = (int)date('Y', strtotime($dueDate));
                $isPaid = in_array($n, $paidInsts, true);
                $status = $isPaid ? 'paid' : 'pending';
                $paidDate = $isPaid ? date('Y-m-d') : null;

                // `id` has NO auto_increment (composite PK id,due_date_year,tenant_id)
                // so supply the id explicitly inside the transaction.
                $nextId = (int)$conn->query(
                    "SELECT COALESCE(MAX(id), 0) + 1 FROM booking_payment_schedules"
                )->fetchColumn();

                $opening = $emi * (12 - $n + 1);
                $closing = $emi * (12 - $n);

                $stmt = $conn->prepare(
                    "INSERT INTO `booking_payment_schedules`
                     (`id`, `tenant_id`, `booking_id`, `installment_no`, `due_date`, `due_date_year`,
                      `amount`, `principal`, `interest`, `opening_balance`, `closing_balance`,
                      `status`, `paid_amount`, `paid_date`, `remarks`, `created_at`, `updated_at`)
                     VALUES (?,?,?,?,?,?, ?,?,?, ?,?, ?,?,?, 'TEST SEED (dev)', NOW(), NOW())"
                );
                $stmt->execute([
                    $nextId, 1, $bookingId, $n, $dueDate, $year,
                    $emi, $emi, 0.00, $opening, $closing,
                    $status, $isPaid ? $emi : 0.00, $paidDate,
                ]);

                if ($isPaid) {
                    // Create a cleared receipt for each pre-paid test EMI
                    $receiptNo = 'APS-RCP-S507-' . str_pad((string)(($bookingId * 100) + $n), 6, '0', STR_PAD_LEFT);
                    $r = $conn->prepare(
                        "INSERT INTO `booking_payment_receipts`
                         (`tenant_id`, `booking_id`, `installment_id`, `receipt_number`,
                          `receipt_date`, `amount`, `payment_mode`, `status`, `notes`,
                          `created_at`, `updated_at`)
                         VALUES (?,?,?,?, ?,?,'neft','cleared','TEST SEED (dev)', NOW(), NOW())"
                    );
                    $r->execute([1, $bookingId, $nextId, $receiptNo, $paidDate, $emi]);
                }
            }
            echo "[S507-4] SEEDED 12 EMIs for plot_bookings #{$bookingId}: down ₹{$down}, EMI ₹{$emi}\n";
        }

        $conn->commit();
        echo "[S507-4] Seed committed.\n";
    }
} catch (\Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "[S507-4] ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// ---------------------------------------------------------------------------
// STEP 5: Verify counts after migration
// ---------------------------------------------------------------------------
try {
    $total = (int)$pdo->query("SELECT COUNT(*) FROM booking_payment_schedules")->fetchColumn();
    $live  = (int)$pdo->query(
        "SELECT COUNT(*) FROM booking_payment_schedules
         WHERE booking_id IN (SELECT id FROM plot_bookings)"
    )->fetchColumn();
    $orphans = $total - $live;
    echo "[S507-5] VERIFY: {$total} schedule rows live, {$live} linked to plot_bookings, {$orphans} orphans remaining.\n";
} catch (\Throwable $e) {
    echo "[S507-5] ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

echo "=== S507 migration complete ===\n";

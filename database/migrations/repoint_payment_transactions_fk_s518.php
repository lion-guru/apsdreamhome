<?php
/**
 * Migration S518: Re-point `payment_transactions.booking_id` FK to `plot_bookings`.
 *
 * Why: every payment-flow INSERT failed with errno 1452 because this one FK
 * referenced the LEGACY `bookings` table, while every live code path writes
 * booking_id referencing `plot_bookings.id`. Same class as Session 507's
 * re-point of booking_payment_schedules / receipts / demand_letters from
 * `bookings` -> `plot_bookings`.
 *
 * Safe to re-run: if the FK already points at plot_bookings, SKIP.
 */
require_once __DIR__ . '/../../config/bootstrap.php';

use App\Core\Database\Database;

try {
    $pdo = \App\Core\Database\Database::getInstance()->getConnection();
} catch (\Throwable $e) {
    die("DB connection failed: " . $e->getMessage() . "\n");
}

echo "=== MIGRATION S518: payment_transactions FK re-point ===\n";
$exists = (bool)$pdo->query(
    "SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
     WHERE CONSTRAINT_SCHEMA = DATABASE()
       AND TABLE_NAME = 'payment_transactions'
       AND CONSTRAINT_NAME = 'fk_payment_transactions_booking_id'"
)->fetchColumn();
$target = $pdo->query(
    "SELECT REFERENCED_TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE
     WHERE CONSTRAINT_SCHEMA = DATABASE()
       AND TABLE_NAME = 'payment_transactions'
       AND CONSTRAINT_NAME = 'fk_payment_transactions_booking_id'"
)->fetchColumn();

if ($exists && $target === 'plot_bookings') {
    echo "[S518] SKIP: fk_payment_transactions_booking_id already references plot_bookings.\n";
    exit(0);
}
if ($exists) {
    $pdo->exec("ALTER TABLE payment_transactions DROP FOREIGN KEY fk_payment_transactions_booking_id");
    echo "[S518-1] DROPPED stale FK (was: {$target}.id).\n";
}
$pdo->exec(
    "ALTER TABLE payment_transactions
     ADD CONSTRAINT fk_payment_transactions_booking_id
     FOREIGN KEY (booking_id) REFERENCES plot_bookings (id)
     ON DELETE SET NULL ON UPDATE CASCADE"
);
echo "[S518-2] ADDED fk_payment_transactions_booking_id -> plot_bookings(id) ON DELETE SET NULL ON UPDATE CASCADE.\n";
echo "=== S518 migration complete ===\n";

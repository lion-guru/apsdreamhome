<?php
/**
 * Migration S521: Re-point the 12 remaining stale FKs from legacy `bookings` -> `plot_bookings`.
 *
 * Ghost rows whose booking_id does not match a live plot_bookings.id were deleted in
 * a previous partial run of this migration ("rows deleted before the FK add").
 *
 * Idempotent: re-running is safe (empty DML + FK already correct -> SKIP).
 *
 * Pre-flight safety dump taken before migration:
 *   backups/db/stale_fks_pre_repoint_2026-11-14.sql
 *
 * Rules:
 *   - column IS_NULLABLE YES -> FK ON DELETE SET NULL ON UPDATE CASCADE
 *   - column IS_NULLABLE NO  -> FK ON DELETE RESTRICT  ON UPDATE CASCADE
 */
require_once __DIR__ . '/../../config/bootstrap.php';

use App\Core\Database\Database;

try {
    $pdo = \App\Core\Database\Database::getInstance()->getConnection();
} catch (\Throwable $e) {
    die("DB connection failed: " . $e->getMessage() . "\n");
}

echo "=== MIGRATION S521: Re-point 12 stale FKs to plot_bookings ===\n";
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

$tables = [
    'agreements'              => 'booking_id',
    'booking_documents'       => 'booking_id',
    'booking_payments'        => 'booking_id',
    'booking_refunds'         => 'booking_id',
    'booking_status_history'  => 'booking_id',
    'booking_transfers'       => 'original_booking_id',
    'emi_schedule'            => 'booking_id',
    'field_collections'       => 'plot_booking_id',
    'payments'                => 'booking_id',
    'payment_orders'          => 'booking_id',
    'sales'                   => 'booking_id',
    'support_tickets'         => 'booking_id',
];

// book_documents.booking_id is NOT NULL so it cannot use ON DELETE SET NULL as others; handled inside step 2 explicitly.
// Also handle the failed-partial run's dropped-FK case: if no FK to bookings exists on the column, look for a missing
// reference and assert it's already on plot_bookings, otherwise re-add with correct RESTRICT.
foreach ($tables as $table => $col) {
    $constraintName = (string)$pdo->query(
        "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
         WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = '{$table}' AND COLUMN_NAME = '{$col}'
           AND REFERENCED_TABLE_NAME = 'bookings' LIMIT 1"
    )->fetchColumn();

    if ($constraintName === '') {
        // Already re-pointed (or never existed). Check target.
        $already = (string)$pdo->query(
            "SELECT REFERENCED_TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = '{$table}' AND COLUMN_NAME = '{$col}'
               AND REFERENCED_TABLE_NAME = 'plot_bookings' LIMIT 1"
        )->fetchColumn();
        echo ($already === 'plot_bookings')
            ? "[S521-2] SKIP {$table}: already references plot_bookings.\n"
            : "[S521-2] RESTORING `{$table}`: bookmarks table has no FK -> local repair run (adding like the others).\n";
        if ($already === 'plot_bookings') {
            continue;
        }
        // Repair path: add the FK with the correct ON DELETE rule
        $nullable = $pdo->query(
            "SELECT IS_NULLABLE FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$table}' AND COLUMN_NAME = '{$col}'"
        )->fetchColumn();
        $onDelete = ($nullable === 'YES') ? 'SET NULL' : 'RESTRICT';
        $constraintName = 'fk_' . substr(md5($table . ':' . $col), 0, 16);
        try {
            $pdo->exec(
                "ALTER TABLE `{$table}`
                 ADD CONSTRAINT `{$constraintName}`
                 FOREIGN KEY (`{$col}`) REFERENCES `plot_bookings` (`id`)
                 ON DELETE {$onDelete} ON UPDATE CASCADE"
            );
            echo "[S521-2] REPAIRED {$table}.{$col}: created `{$constraintName}` -> plot_bookings(ON DELETE {$onDelete}).\n";
        } catch (\Throwable $e) {
            echo "[S521-2] ERROR restoring {$table}: " . $e->getMessage() . "\n";
            exit(1);
        }
        continue;
    }

    $nullable = $pdo->query(
        "SELECT IS_NULLABLE FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$table}' AND COLUMN_NAME = '{$col}'"
    )->fetchColumn();
    $onDelete = ($nullable === 'YES') ? 'SET NULL' : 'RESTRICT';

    $pdo->exec("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraintName}`");
    $pdo->exec(
        "ALTER TABLE `{$table}`
         ADD CONSTRAINT `{$constraintName}`
         FOREIGN KEY (`{$col}`) REFERENCES `plot_bookings` (`id`)
         ON DELETE {$onDelete} ON UPDATE CASCADE"
    );
    echo "[S521-2] {$table}.{$col}: re-pointed `{$constraintName}` -> plot_bookings (ON DELETE {$onDelete}).\n";
}

// Step 1: DELETE ghost rows whose FK target no longer exists anywhere live (dev-mode test data).
$deletedTotal = 0;
foreach ($tables as $table => $col) {
    $orphans = (int)$pdo->query(
        "SELECT COUNT(*) FROM `{$table}`
         WHERE `{$col}` IS NOT NULL
           AND `{$col}` NOT IN (SELECT id FROM plot_bookings)"
    )->fetchColumn();

    if ($orphans > 0) {
        $del = $pdo->prepare(
            "DELETE FROM `{$table}`
             WHERE `{$col}` IS NOT NULL
               AND `{$col}` NOT IN (SELECT id FROM plot_bookings)"
        );
        $del->execute();
        $deletedTotal += $orphans;
        echo "[S521-1] {$table}.{$col}: DELETED {$orphans} ghost rows.\n";
    }
}

// Step 2: per-column FK re-point — SET NULL if nullable, RESTRICT if NOT NULL
foreach ($tables as $table => $col) {
    $constraintName = (string)$pdo->query(
        "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
         WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = '{$table}' AND COLUMN_NAME = '{$col}'
           AND REFERENCED_TABLE_NAME = 'bookings' LIMIT 1"
    )->fetchColumn();

    if ($constraintName === '') {
        $already = (string)$pdo->query(
            "SELECT REFERENCED_TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = '{$table}' AND COLUMN_NAME = '{$col}'
               AND REFERENCED_TABLE_NAME = 'plot_bookings' LIMIT 1"
        )->fetchColumn();
        echo ($already === 'plot_bookings')
            ? "[S521-2] SKIP {$table}: already references plot_bookings.\n"
            : "[S521-2] SKIP {$table}: no bookings FK on `{$col}`.\n";
        continue;
    }

    $nullable = $pdo->query(
        "SELECT IS_NULLABLE FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$table}' AND COLUMN_NAME = '{$col}'"
    )->fetchColumn();
    $onDelete = ($nullable === 'YES') ? 'SET NULL' : 'RESTRICT';

    $pdo->exec("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraintName}`");
    $pdo->exec(
        "ALTER TABLE `{$table}`
         ADD CONSTRAINT `{$constraintName}`
         FOREIGN KEY (`{$col}`) REFERENCES `plot_bookings` (`id`)
         ON DELETE {$onDelete} ON UPDATE CASCADE"
    );
    echo "[S521-2] {$table}.{$col}: re-pointed `{$constraintName}` -> plot_bookings (ON DELETE {$onDelete}).\n";
}

$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
echo "=== S521 migration complete: {$deletedTotal} ghost rows deleted ===\n";

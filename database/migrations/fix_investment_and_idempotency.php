<?php
/**
 * Migration: Investment Plan Liability Tracking + Ledger Idempotency
 *
 * Fixes:
 *   1. Adds liability tracking columns to `investments` table
 *      (company_contribution, plot_promised_sqft, plot_promised_value, maturity_status, plot_id)
 *   2. Adds 'defaulted' to plot_bookings status ENUM
 *   3. Adds unique index on mlm_commission_ledger to prevent double-dipping
 *
 * Run once: php database/migrations/fix_investment_and_idempotency.php
 */

$root   = dirname(__DIR__, 2);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "Connected.\n";

    // ── 1. Add investment liability columns ───────────────────────────
    $investmentCols = [
        'company_contribution'  => "DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Amount company owes toward the plot'",
        'plot_promised_sqft'    => "INT NOT NULL DEFAULT 0 COMMENT 'Plot area promised to investor after maturity'",
        'plot_promised_value'   => "DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Total plot value promised'",
        'plot_id'               => "INT NULL DEFAULT NULL COMMENT 'Specific plot reserved for this investor'",
        'maturity_alert_sent'   => "TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = 90-day maturity alert sent to admin'",
        'maturity_status'       => "ENUM('pending','land_reserved','plot_allotted','registry_done') NOT NULL DEFAULT 'pending'",
        'plot_reserved_at'      => "DATE NULL DEFAULT NULL",
        'registry_date'         => "DATE NULL DEFAULT NULL",
    ];

    foreach ($investmentCols as $col => $definition) {
        $exists = $pdo->query("
            SELECT COUNT(*) FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'investments'
              AND COLUMN_NAME = '$col'
        ")->fetchColumn();

        if (!$exists) {
            $pdo->exec("ALTER TABLE investments ADD COLUMN $col $definition");
            echo "✅ investments.$col — added\n";
        } else {
            echo "ℹ️  investments.$col — already exists\n";
        }
    }

    // ── 2. Add 'defaulted' to plot_bookings.status ENUM ──────────────
    $statusInfo = $pdo->query("
        SELECT COLUMN_TYPE FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'plot_bookings'
          AND COLUMN_NAME = 'status'
    ")->fetchColumn();

    if ($statusInfo && strpos($statusInfo, 'defaulted') === false) {
        // Insert 'defaulted' into ENUM
        $newType = str_replace("'cancelled'", "'cancelled','defaulted'", $statusInfo);
        $pdo->exec("ALTER TABLE plot_bookings MODIFY status $newType");
        echo "✅ plot_bookings.status — added 'defaulted' value\n";
    } else {
        echo "ℹ️  plot_bookings.status — 'defaulted' already present\n";
    }

    // ── 3. Add idempotency index on mlm_commission_ledger ────────────
    // Prevents double-commission from duplicate payment records
    $indexExists = $pdo->query("
        SELECT COUNT(*) FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'mlm_commission_ledger'
          AND INDEX_NAME = 'uq_no_double_commission'
    ")->fetchColumn();

    if (!$indexExists) {
        // First check if booking_id and receipt_id columns exist
        $hasBookingId = $pdo->query("
            SELECT COUNT(*) FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'mlm_commission_ledger'
              AND COLUMN_NAME = 'booking_id'
        ")->fetchColumn();

        if (!$hasBookingId) {
            // Add booking_id and receipt_id columns if missing
            $pdo->exec("ALTER TABLE mlm_commission_ledger 
                ADD COLUMN booking_id BIGINT NULL DEFAULT NULL,
                ADD COLUMN receipt_id BIGINT NULL DEFAULT NULL");
            echo "✅ mlm_commission_ledger.booking_id + receipt_id — added\n";
        }

        // Add the unique index (allow NULL — NULLs don't conflict in unique indexes)
        $pdo->exec("
            CREATE UNIQUE INDEX uq_no_double_commission 
            ON mlm_commission_ledger (booking_id, receipt_id, beneficiary_user_id, commission_type)
        ");
        echo "✅ mlm_commission_ledger — idempotency index added\n";
    } else {
        echo "ℹ️  mlm_commission_ledger — idempotency index already exists\n";
    }

    // ── 4. Add hold_until column to mlm_commission_ledger ────────────
    // Commissions held for 90 days before payout to allow for cancellation reversals
    $hasHold = $pdo->query("
        SELECT COUNT(*) FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'mlm_commission_ledger'
          AND COLUMN_NAME = 'hold_until'
    ")->fetchColumn();

    if (!$hasHold) {
        $pdo->exec("ALTER TABLE mlm_commission_ledger 
            ADD COLUMN hold_until DATE NULL DEFAULT NULL 
            COMMENT '90-day hold before payout allowed'");
        echo "✅ mlm_commission_ledger.hold_until — added\n";
    } else {
        echo "ℹ️  mlm_commission_ledger.hold_until — already exists\n";
    }

    echo "\n✅ All migrations complete.\n";
    echo "\nSummary of business fixes applied:\n";
    echo "  1. Investment table now tracks company liability (₹ owed per investor)\n";
    echo "  2. Plot promised sqft/value tracked for maturity delivery\n";
    echo "  3. Booking status 'defaulted' added (stops auto-cancel at 90 days)\n";
    echo "  4. Commission double-dipping prevented by unique index\n";
    echo "  5. 90-day commission hold column added\n";

} catch (Exception $e) {
    echo "❌ Migration FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

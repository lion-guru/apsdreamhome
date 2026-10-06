<?php
/**
 * Migration: align employee_advances with AdvanceRecoveryService contract.
 * Adds EMI schedule columns + 'active' status, idempotency key on recovery log.
 * Idempotent. Run: php scripts/migrate_advance_recovery_fix.php
 */

require_once __DIR__ . '/../config/bootstrap.php';

function colExists($pdo, $table, $col) {
    $rows = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$col'")->fetchAll();
    return !empty($rows);
}

try {
    $db = \App\Core\Database\Database::getInstance();
    $pdo = $db->getPdo();

    $adds = [
        "ADD COLUMN advance_no VARCHAR(50) NULL AFTER employee_id",
        "ADD COLUMN repay_months INT NOT NULL DEFAULT 0 AFTER reason",
        "ADD COLUMN emi_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER repay_months",
        "ADD COLUMN interest_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER emi_amount",
        "ADD COLUMN start_month TINYINT UNSIGNED NULL AFTER interest_rate",
        "ADD COLUMN start_year SMALLINT UNSIGNED NULL AFTER start_month",
    ];
    foreach ($adds as $def) {
        preg_match('/ADD COLUMN (\w+)/', $def, $m);
        if (!colExists($pdo, 'employee_advances', $m[1])) {
            $pdo->exec("ALTER TABLE employee_advances $def");
            echo "added employee_advances.{$m[1]}\n";
        } else {
            echo "exists employee_advances.{$m[1]}\n";
        }
    }

    // Backfill advance_no for legacy rows
    $n = $pdo->exec("UPDATE employee_advances SET advance_no = CONCAT('ADV-', DATE_FORMAT(COALESCE(requested_at, created_at), '%Y%m%d'), '-', LPAD(id, 4, '0')) WHERE advance_no IS NULL OR advance_no = ''");
    echo "backfilled advance_no on $n row(s)\n";

    // Extend status enum with active/closed (preserve existing values)
    $row = $pdo->query("SHOW COLUMNS FROM employee_advances LIKE 'status'")->fetch(PDO::FETCH_ASSOC);
    if (stripos($row['Type'], "'active'") === false) {
        $pdo->exec("ALTER TABLE employee_advances MODIFY COLUMN status ENUM('pending','approved','rejected','active','closed') NULL DEFAULT 'pending'");
        echo "extended status enum with active/closed\n";
    } else {
        echo "status enum already has active\n";
    }

    // Idempotency: one recovery log row per advance per month
    $idx = $pdo->query("SHOW INDEX FROM advance_recovery_log WHERE Key_name = 'uk_advance_month'")->fetchAll();
    if (empty($idx)) {
        $pdo->exec("ALTER TABLE advance_recovery_log ADD UNIQUE KEY uk_advance_month (advance_id, recovery_month, recovery_year)");
        echo "added uk_advance_month\n";
    } else {
        echo "uk_advance_month exists\n";
    }

    echo "\nDone.\n";
} catch (\Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

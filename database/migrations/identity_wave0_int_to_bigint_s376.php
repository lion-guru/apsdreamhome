<?php
/**
 * Migration: Identity Wave 0 — INT -> BIGINT UNSIGNED for associate_id (Session 376)
 * Phase 4/7: Identity Unification Wave 0 (Blockers)
 * Converts 5 associate_id columns to BIGINT UNSIGNED to match users.id.
 * Fake/test data mode — value-preserving, MAX verified before/after.
 *
 * Idempotent: checks INFORMATION_SCHEMA before ALTER, re-run = SKIP.
 * Run via: php database/migrations/identity_wave0_int_to_bigint_s376.php
 * Safety: mysqldump was taken before (backups/db/apsdreamhome_pre-restore_2026-11-09_wave0.sql, 90MB)
 */
require_once dirname(__DIR__, 2) . '/config/bootstrap_cli.php';

use App\Core\Database\Database;

$pdo = Database::getInstance()->getConnection();
echo "✅ Database connected\n\n";

$targets = [
    ['table' => 'plot_bookings', 'nullable' => 'YES'],
    ['table' => 'commission_advances', 'nullable' => 'NO'],
    ['table' => 'mlm_payouts', 'nullable' => 'NO'],
    ['table' => 'mlm_rank_history', 'nullable' => 'NO'],
    ['table' => 'salary_payments', 'nullable' => 'YES'],
];

foreach ($targets as $t) {
    $table = $t['table'];
    // Current type
    $stmt = $pdo->prepare("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME='associate_id'");
    $stmt->execute([$table]);
    $colType = $stmt->fetchColumn();
    if ($colType === 'bigint(20) unsigned') {
        echo "SKIP $table.associate_id already bigint(20) unsigned\n";
        continue;
    }
    echo "--- $table.associate_id ($colType) ---\n";
    $maxBefore = $pdo->query("SELECT MAX(associate_id) FROM $table")->fetchColumn();
    $countBefore = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
    echo "  Before: MAX=" . var_export($maxBefore, true) . " count=$countBefore\n";

    // Drop FK if exists (mlm_payouts, mlm_rank_history point to associates INT)
    $fk = $pdo->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='$table' AND COLUMN_NAME='associate_id' AND REFERENCED_TABLE_NAME IS NOT NULL LIMIT 1")->fetchColumn();
    if ($fk) {
        echo "  Dropping FK $fk (legacy associates INT parent — deprecated, not re-added; child becomes plain BIGINT)\n";
        $pdo->exec("ALTER TABLE $table DROP FOREIGN KEY $fk");
    }

    $nullClause = ($t['nullable'] === 'YES') ? 'NULL' : 'NOT NULL';
    $pdo->exec("ALTER TABLE $table MODIFY associate_id BIGINT UNSIGNED $nullClause");

    $stmt = $pdo->prepare("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME='associate_id'");
    $stmt->execute([$table]);
    $after = $stmt->fetchColumn();
    $maxAfter = $pdo->query("SELECT MAX(associate_id) FROM $table")->fetchColumn();
    $countAfter = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
    echo "  After: type=$after MAX=" . var_export($maxAfter, true) . " count=$countAfter\n";
    if ((string)$maxBefore !== (string)$maxAfter) {
        echo "  ERROR: MAX changed! Rolling back needed.\n";
        exit(1);
    }
    echo "  OK ($maxAfter)\n\n";
}

// Note: 3 missing profiles (employees 7/8 user_id 121023/121025, associates 465 user_id 121237)
// have NO users row (orphan test data, dev mode). Skipped — owner to confirm mapping or ignore.
// Profiles cannot be created with user_id FK to users(id) that doesn't exist.
echo "Wave 0 INT->BIGINT DONE. Missing profiles skipped (orphan test rows, no users mapping).\n";

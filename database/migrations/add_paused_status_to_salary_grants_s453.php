<?php
/**
 * Migration S453: add the 'paused' status to the salary-grant enums.
 *
 * WHY (S453):
 *   App\Services\Payroll\SalaryGrantService pauses a grant when the monthly
 *   activity maintenance check fails:
 *       UPDATE mlm_salary_grants SET status = 'paused', ...
 *   and then looks for it again with
 *       WHERE status IN ('active', 'paused')
 *   and resumes it with
 *       if ($grant['status'] === 'paused') { ... }
 *
 *   But BOTH tables were created with
 *       status ENUM('active', 'completed', 'cancelled')
 *   - there is no 'paused' member.  MySQL therefore TRUNCATED the value to
 *   '' (with warning 1265 "Data truncated for column 'status'"), and because
 *   the app's PDO runs with ERRMODE_EXCEPTION the write raised instead:
 *   the grant never reached 'paused', dropped out of
 *   `WHERE status IN ('active','paused')` entirely, and so was never resumed,
 *   never cancelled and never paid again.  A single missed month froze the
 *   whole salary grant permanently.
 *
 *   The S457 migration added the companion columns (maintenance_mode,
 *   consecutive_inactive_months, paused_reason, last_activity_verified_at)
 *   but not the enum member, so the pause path was never executable.
 *
 * WHAT IT DOES:
 *   Adds 'paused' to the `status` enum of mlm_salary_grants and
 *   salary_tracker - appending it at the END so the existing enum indexes
 *   (and therefore every existing row's stored value) are untouched.
 *
 * 100% Idempotent. Re-run skips safely.
 */

require_once __DIR__ . '/../../config/bootstrap.php';

try {
    $pdo = \App\Core\Database\Database::getInstance()->getPdo();
} catch (\Throwable $e) {
    die("DB connection failed: " . $e->getMessage() . "\n");
}

echo "=== MIGRATION S453: add 'paused' to the salary-grant status enums ===\n";

if (!function_exists('s453_tableExists')) {
    function s453_tableExists(PDO $pdo, string $table): bool
    {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
        ");
        $stmt->execute([$table]);
        return (int) $stmt->fetchColumn() > 0;
    }
}

if (!function_exists('s453_statusType')) {
    function s453_statusType(PDO $pdo, string $table): string
    {
        $stmt = $pdo->prepare("
            SELECT COLUMN_TYPE
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = 'status'
        ");
        $stmt->execute([$table]);
        return (string) $stmt->fetchColumn();
    }
}

foreach (['mlm_salary_grants', 'salary_tracker'] as $table) {
    if (!s453_tableExists($pdo, $table)) {
        echo "  [-] {$table} does not exist - skipped\n";
        continue;
    }

    $type = s453_statusType($pdo, $table);
    if ($type === '') {
        echo "  [-] {$table} has no status column - skipped\n";
        continue;
    }

    if (stripos($type, "'paused'") !== false) {
        echo "  [=] {$table}.status already has 'paused' - skipped\n";
        continue;
    }

    // Rebuild the enum with 'paused' appended at the END so the existing
    // enum indexes - and therefore every stored value - are preserved.
    preg_match_all("/'([^']+)'/", $type, $m);
    $members = $m[1];
    if (in_array('cancelled', $members, true)) {
        // keep 'cancelled' last so the index of every earlier member is stable
        $members = array_values(array_diff($members, ['cancelled']));
        $members[] = 'cancelled';
    }
    $members[] = 'paused';
    $newType = "enum('" . implode("','", $members) . "')";

    $sql = "ALTER TABLE `{$table}` MODIFY COLUMN `status` {$newType} NULL DEFAULT 'active'";
    $pdo->exec($sql);
    echo "  [+] {$table}.status -> {$newType}\n";
}

echo "=== MIGRATION S453 COMPLETE ===\n";

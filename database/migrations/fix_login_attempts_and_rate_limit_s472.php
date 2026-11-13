<?php
/**
 * Migration S472: the schema the login lockout is written against.
 *
 * WHY (S472):
 *   Three different implementations of the brute-force protection exist in
 *   this codebase, and two of them were written against a schema that was
 *   never created:
 *
 *   1. App\Http\Controllers\Auth\AuthController (the LIVE one) uses the
 *      `login_attempts` table - identifier + success + ip + ua + created_at.
 *      That table exists and works.
 *
 *   2. App\Services\Auth\AuthenticationService counts
 *          SELECT COUNT(*) FROM activity_logs_unified
 *          WHERE email = ? AND ... AND success = 0
 *      but activity_logs_unified has NO `email` and NO `success` column -
 *      so the query died with 1054 on every login and the throttle never
 *      engaged.  Its INSERT had the same two phantom columns.
 *
 *   3. App\Services\Auth\AuthService reads and writes a table named
 *      `failed_activity_logs_unified`, which does not exist at all - so
 *      isAccountLocked() always returned false and the ACCOUNT LOCKOUT never
 *      happened.
 *
 *   The fix is NOT a second pair of tables.  `login_attempts` already holds
 *   exactly the columns all three implementations need, so this migration
 *   gives that one table the one column it lacks (user_id, so a lockout can
 *   be counted per ACCOUNT as well as per identifier) and creates the one
 *   genuinely missing table (`rate_limit`, used by
 *   App\Services\Security\MonitorService::checkRateLimit, which deletes,
 *   counts and inserts rows there).
 *
 * 100% Idempotent. Re-run skips safely.
 */

require_once __DIR__ . '/../../config/bootstrap.php';

try {
    $pdo = \App\Core\Database\Database::getInstance()->getPdo();
} catch (\Throwable $e) {
    die("DB connection failed: " . $e->getMessage() . "\n");
}

echo "=== MIGRATION S472: the login lockout's schema ===\n";

if (!function_exists('s472_columnExists')) {
    function s472_columnExists(PDO $pdo, string $table, string $column): bool
    {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
        ");
        $stmt->execute([$table, $column]);
        return (int) $stmt->fetchColumn() > 0;
    }
}

if (!function_exists('s472_tableExists')) {
    function s472_tableExists(PDO $pdo, string $table): bool
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

if (!function_exists('s472_indexExists')) {
    function s472_indexExists(PDO $pdo, string $table, string $index): bool
    {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND INDEX_NAME = ?
        ");
        $stmt->execute([$table, $index]);
        return (int) $stmt->fetchColumn() > 0;
    }
}

// ---- 1. login_attempts.user_id ------------------------------------------
// The lockout has to be countable per ACCOUNT (isAccountLocked($userId)),
// not only per identifier (the email/phone).
if (!s472_tableExists($pdo, 'login_attempts')) {
    echo "  [-] login_attempts does not exist - skipped\n";
} else {
    if (s472_columnExists($pdo, 'login_attempts', 'user_id')) {
        echo "  [=] login_attempts.user_id already exists - skipped\n";
    } else {
        $pdo->exec("ALTER TABLE `login_attempts` ADD COLUMN `user_id` BIGINT UNSIGNED NULL DEFAULT NULL");
        echo "  [+] login_attempts.user_id added\n";
    }
    if (s472_indexExists($pdo, 'login_attempts', 'idx_login_attempts_user')) {
        echo "  [=] login_attempts.idx_login_attempts_user already exists - skipped\n";
    } else {
        $pdo->exec("ALTER TABLE `login_attempts` ADD INDEX `idx_login_attempts_user` (`user_id`, `success`, `created_at`)");
        echo "  [+] login_attempts.idx_login_attempts_user added\n";
    }
}

// ---- 2. the rate_limit table --------------------------------------------
// App\Services\Security\MonitorService::checkRateLimit DELETEs, COUNTs and
// INSERTs here.  The table was never created, so the monitor's rate limiter
// died on every call (and the catch swallowed it).
if (s472_tableExists($pdo, 'rate_limit')) {
    echo "  [=] rate_limit already exists - skipped\n";
} else {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `rate_limit` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
            `identifier` VARCHAR(255) NOT NULL DEFAULT '',
            `ip_address` VARCHAR(45) NOT NULL DEFAULT '',
            `timestamp` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_rate_limit_identifier` (`identifier`, `ip_address`, `timestamp`),
            KEY `idx_rate_limit_timestamp` (`timestamp`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "  [+] rate_limit created\n";
}

echo "=== MIGRATION S472 COMPLETE ===\n";

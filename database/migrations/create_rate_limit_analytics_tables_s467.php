<?php
/**
 * Migration S467: create the two rate-limit analytics tables the code writes.
 *
 * WHY (S467): App\Services\Analytics\Reports\RateLimitAnalytics is a
 * complete service - log(), recordEvent(), recordViolation(),
 * getRateLimitTrends(), getEndpointMetrics(), getUserMetrics(),
 * getViolationHistory(), getRealTimeMetrics(), cleanupOldData() - and every
 * one of its statements hit a table that does not exist:
 *
 *     INSERT INTO rate_limit_events (api_key_id, endpoint, requests,
 *                                     limit_value, window_size)
 *     INSERT INTO rate_limit_violations (api_key_id, endpoint, requests,
 *                                        limit_value, ip_address)
 *     ... plus SELECT/UPDATE/DELETE on both.
 *
 * So all of it died with 1146 "Table doesn't exist".  These are NOT the
 * existing `rate_limit_logs` table (that one logs individual requests:
 * request_key / ip_address / user_agent / expires_at) - they are per-API-key
 * rate-limit METRICS, a different domain, so they get their own tables.
 *
 * The column names are exactly the ones the service reads and writes.
 *
 * 100% Idempotent.  Re-run skips safely.
 */

require_once __DIR__ . '/../../config/bootstrap.php';

try {
    $pdo = \App\Core\Database\Database::getInstance()->getPdo();
} catch (\Throwable $e) {
    die("DB connection failed: " . $e->getMessage() . "\n");
}

echo "=== MIGRATION S467: the rate-limit analytics tables ===\n";

if (!function_exists('s467_tableExists')) {
    function s467_tableExists(PDO $pdo, string $table): bool
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

$tables = [
    'rate_limit_events' => "
        CREATE TABLE IF NOT EXISTS `rate_limit_events` (
            `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `tenant_id`     INT UNSIGNED NOT NULL DEFAULT 1,
            `api_key_id`    INT NULL,
            `endpoint`      VARCHAR(255) NOT NULL,
            `requests`      INT NOT NULL DEFAULT 0,
            `limit_value`   INT NOT NULL DEFAULT 0,
            `window_size`   VARCHAR(50) NOT NULL DEFAULT '60',
            `timestamp`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_rle_tenant_time` (`tenant_id`, `timestamp`),
            KEY `idx_rle_api_key`   (`api_key_id`),
            KEY `idx_rle_endpoint`  (`endpoint`(100))
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
    'rate_limit_violations' => "
        CREATE TABLE IF NOT EXISTS `rate_limit_violations` (
            `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `tenant_id`        INT UNSIGNED NOT NULL DEFAULT 1,
            `api_key_id`       INT NULL,
            `endpoint`         VARCHAR(255) NOT NULL,
            `requests`         INT NOT NULL DEFAULT 0,
            `limit_value`      INT NOT NULL DEFAULT 0,
            `ip_address`       VARCHAR(45) NULL,
            `action_taken`     VARCHAR(50) NULL,
            `resolved`         TINYINT(1) NOT NULL DEFAULT 0,
            `resolution_notes` TEXT NULL,
            `timestamp`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_rlv_tenant_time` (`tenant_id`, `timestamp`),
            KEY `idx_rlv_api_key`    (`api_key_id`),
            KEY `idx_rlv_resolved`   (`resolved`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
];

foreach ($tables as $name => $sql) {
    if (s467_tableExists($pdo, $name)) {
        echo "  [=] {$name} already exists - skipped\n";
        continue;
    }
    try {
        $pdo->exec($sql);
        echo "  [+] {$name} CREATED\n";
    } catch (\Throwable $e) {
        echo "  [!] {$name} FAILED: " . $e->getMessage() . "\n";
    }
}

// ---- one more column the code writes and the schema does not have ----
// ApiAnalytics inserts (endpoint, method, api_key_id, response_time,
// status_code, ip_address, tenant_id, timestamp) into `api_requests`, and
// that table has no `api_key_id` -> 1054 on every API request log.
if (!function_exists('s467_columnExists')) {
    function s467_columnExists(PDO $pdo, string $table, string $column): bool
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

if (s467_tableExists($pdo, 'api_requests')) {
    if (s467_columnExists($pdo, 'api_requests', 'api_key_id')) {
        echo "  [=] api_requests.api_key_id already exists - skipped\n";
    } else {
        try {
            $pdo->exec("ALTER TABLE `api_requests` ADD COLUMN `api_key_id` INT NULL AFTER `method`");
            echo "  [+] api_requests.api_key_id ADDED\n";
        } catch (\Throwable $e) {
            echo "  [!] api_requests.api_key_id FAILED: " . $e->getMessage() . "\n";
        }
    }
} else {
    echo "  [-] api_requests does not exist - skipped\n";
}

echo "=== MIGRATION S467 COMPLETE ===\n";

<?php
/**
 * Migration: Create monitoring_errors + monitoring_alerts tables
 *
 * Idempotent: skips if tables already exist.
 * Run: php scripts/add_monitoring_tables.php
 */

use App\Core\Database\Database;

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';
    if (strpos($class, $prefix) !== 0) return;
    $relative = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) require_once $file;
});

chdir(__DIR__ . '/..');

$db = Database::getInstance();
$pdo = $db->getPdo();

$statements = [
    'monitoring_errors' => "CREATE TABLE IF NOT EXISTS `monitoring_errors` (
        `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `level` VARCHAR(20) NOT NULL DEFAULT 'error',
        `message` TEXT NOT NULL,
        `file` VARCHAR(500) NULL,
        `line` INT NULL,
        `context` JSON NULL,
        `user_id` INT NULL,
        `trace` MEDIUMTEXT NULL,
        `environment` VARCHAR(32) NOT NULL DEFAULT 'production',
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_level` (`level`),
        INDEX `idx_created` (`created_at`),
        INDEX `idx_user` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'monitoring_alerts' => "CREATE TABLE IF NOT EXISTS `monitoring_alerts` (
        `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `severity` ENUM('info','warning','critical') NOT NULL DEFAULT 'warning',
        `source` VARCHAR(64) NOT NULL,
        `message` TEXT NOT NULL,
        `metadata` JSON NULL,
        `notified_email` TINYINT(1) NOT NULL DEFAULT 0,
        `notified_sms` TINYINT(1) NOT NULL DEFAULT 0,
        `resolved_at` DATETIME NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_severity` (`severity`),
        INDEX `idx_source` (`source`),
        INDEX `idx_created` (`created_at`),
        INDEX `idx_resolved` (`resolved_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];

$created = 0;
$skipped = 0;
foreach ($statements as $table => $sql) {
    $check = $pdo->query("SHOW TABLES LIKE '{$table}'")->fetchColumn();
    if ($check) {
        echo "[SKIP] Table '{$table}' already exists" . PHP_EOL;
        $skipped++;
        continue;
    }
    try {
        $pdo->exec($sql);
        echo "[OK]   Created table '{$table}'" . PHP_EOL;
        $created++;
    } catch (Throwable $e) {
        echo "[FAIL] '{$table}': " . $e->getMessage() . PHP_EOL;
        exit(1);
    }
}

echo PHP_EOL . "Migration complete. Created: {$created}, Skipped: {$skipped}" . PHP_EOL;
exit(0);?>
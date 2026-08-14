<?php
/**
 * Migration: Create A/B Testing tables
 *
 * Tables created:
 *   - ab_experiments: experiment definitions (name, variants, traffic, status)
 *   - ab_events:      per-user event log (assignments + conversions)
 *
 * Idempotent: skips if tables already exist.
 * Run: php scripts/add_ab_testing_tables.php
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
    'ab_experiments' => "CREATE TABLE IF NOT EXISTS `ab_experiments` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) UNIQUE NOT NULL,
        `description` TEXT NULL,
        `variants` JSON NOT NULL,
        `traffic_allocation` INT NOT NULL DEFAULT 100,
        `status` ENUM('draft','running','ended') NOT NULL DEFAULT 'draft',
        `winner` VARCHAR(50) NULL,
        `started_at` DATETIME NULL,
        `ended_at` DATETIME NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_status` (`status`),
        INDEX `idx_name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'ab_events' => "CREATE TABLE IF NOT EXISTS `ab_events` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `experiment_id` INT NOT NULL,
        `user_id` INT NULL,
        `variant` VARCHAR(50) NOT NULL,
        `event_type` VARCHAR(50) NOT NULL,
        `metadata` JSON NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_exp` (`experiment_id`),
        INDEX `idx_user` (`user_id`),
        INDEX `idx_event` (`event_type`),
        INDEX `idx_exp_user` (`experiment_id`, `user_id`),
        INDEX `idx_exp_event` (`experiment_id`, `event_type`)
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
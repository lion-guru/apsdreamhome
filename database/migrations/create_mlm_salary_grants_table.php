<?php
/**
 * Migration: Create mlm_salary_grants table
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

    // Drop table if exists to ensure a clean slate
    $pdo->exec("DROP TABLE IF EXISTS `mlm_salary_grants`;");

    $sql = "
    CREATE TABLE `mlm_salary_grants` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` BIGINT(20) UNSIGNED NOT NULL,
        `tier_index` INT NOT NULL,
        `volume_threshold` DECIMAL(15, 2) NOT NULL,
        `monthly_amount` DECIMAL(15, 2) NOT NULL,
        `months_total` INT NOT NULL,
        `months_paid` INT DEFAULT 0,
        `status` ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
        `activated_at` DATETIME NOT NULL,
        `last_paid_at` DATETIME DEFAULT NULL,
        `created_at` DATETIME NOT NULL,
        `updated_at` DATETIME NOT NULL,
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
        UNIQUE KEY `uq_user_tier` (`user_id`, `tier_index`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;
    ";

    $pdo->exec($sql);
    echo "✅ Table mlm_salary_grants created successfully.\n";

} catch (Exception $e) {
    echo "❌ Migration FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

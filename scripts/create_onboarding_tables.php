<?php
/**
 * Create onboarding tables
 * Run: php scripts/create_onboarding_tables.php
 */
$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$pass = '';
$db   = 'apsdreamhome';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, "DB connection failed: " . $e->getMessage() . "\n");
    exit(1);
}

$sql = "
CREATE TABLE IF NOT EXISTS `company_settings` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `tenant_id` int(10) unsigned NOT NULL,
    `company_name` varchar(200) NOT NULL,
    `gstin` varchar(20) DEFAULT NULL,
    `address` text DEFAULT NULL,
    `city` varchar(100) DEFAULT NULL,
    `state` varchar(100) DEFAULT NULL,
    `pincode` varchar(20) DEFAULT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `email` varchar(100) DEFAULT NULL,
    `logo_url` varchar(500) DEFAULT NULL,
    `primary_color` varchar(7) DEFAULT '#0a192f',
    `secondary_color` varchar(7) DEFAULT '#d4af37',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `onboarding_progress` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `tenant_id` int(10) unsigned NOT NULL,
    `step_key` varchar(50) NOT NULL,
    `completed_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_tenant_step` (`tenant_id`, `step_key`),
    KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    $pdo->exec($sql);
    echo "Tables created/verified\n";
} catch (PDOException $e) {
    fwrite(STDERR, "Create table failed: " . $e->getMessage() . "\n");
    exit(1);
}

echo "DONE\n";
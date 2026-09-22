<?php
/**
 * Create journey_stage_log table for audit trail
 * Run: php scripts/create_journey_stage_log.php
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
CREATE TABLE IF NOT EXISTS `journey_stage_log` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `tenant_id` int(10) unsigned NOT NULL DEFAULT 1,
    `booking_id` int(10) unsigned NOT NULL,
    `stage_key` varchar(50) NOT NULL,
    `stage_label` varchar(100) NOT NULL,
    `extra_data` longtext NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_tenant_booking` (`tenant_id`, `booking_id`),
    KEY `idx_stage` (`stage_key`),
    KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    $pdo->exec($sql);
    echo "Table journey_stage_log created/verified\n";
} catch (PDOException $e) {
    fwrite(STDERR, "Create table failed: " . $e->getMessage() . "\n");
    exit(1);
}

echo "DONE\n";
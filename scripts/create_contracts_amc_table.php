<?php
/**
 * Create contracts_amc table for AMC/Contract renewal tracking
 * Run: php scripts/create_contracts_amc_table.php
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
CREATE TABLE IF NOT EXISTS `contracts_amc` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `tenant_id` int(10) unsigned NOT NULL DEFAULT 1,
    `contract_number` varchar(50) NOT NULL,
    `type` enum('amc','rental','service','maintenance','warranty','other') NOT NULL DEFAULT 'amc',
    `party_name` varchar(200) NOT NULL,
    `party_type` enum('customer','vendor','supplier','partner') NOT NULL DEFAULT 'customer',
    `party_id` int(10) unsigned NULL COMMENT 'FK to users/leads/vendors',
    `description` text NULL,
    `start_date` date NOT NULL,
    `end_date` date NOT NULL,
    `renewal_date` date NULL COMMENT 'Auto-calculated if null',
    `amount` decimal(15,2) NOT NULL DEFAULT 0,
    `currency` varchar(3) NOT NULL DEFAULT 'INR',
    `billing_cycle` enum('monthly','quarterly','half_yearly','yearly','one_time') NOT NULL DEFAULT 'yearly',
    `status` enum('draft','active','expiring_soon','expired','renewed','cancelled') NOT NULL DEFAULT 'draft',
    `auto_renew` tinyint(1) NOT NULL DEFAULT 0,
    `renewal_notice_days` int(10) unsigned NOT NULL DEFAULT 30 COMMENT 'Days before expiry to send reminder',
    `last_reminder_at` datetime NULL,
    `reminder_count` int(10) unsigned NOT NULL DEFAULT 0,
    `notes` text NULL,
    `created_by` bigint(20) unsigned NULL,
    `updated_by` bigint(20) unsigned NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_contract_number` (`contract_number`, `tenant_id`),
    KEY `idx_tenant_status` (`tenant_id`, `status`),
    KEY `idx_end_date` (`end_date`),
    KEY `idx_renewal_date` (`renewal_date`),
    KEY `idx_party` (`party_type`, `party_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    $pdo->exec($sql);
    echo "Table contracts_amc created/verified\n";
} catch (PDOException $e) {
    fwrite(STDERR, "Create table failed: " . $e->getMessage() . "\n");
    exit(1);
}

// Add foreign key to lead_activities if not exists (for activity logging)
try {
    $pdo->exec("ALTER TABLE `contracts_amc` ADD CONSTRAINT `fk_contracts_amc_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL");
    echo "FK created_by added\n";
} catch (PDOException $e) {
    // FK may already exist or users table different
}

echo "DONE\n";
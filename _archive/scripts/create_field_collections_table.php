<?php
/**
 * Create field_collections table
 * Run: php scripts/create_field_collections_table.php
 * Stores cash/cheque collections recorded in the field by associates/agents
 */
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';
try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'], $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $pdo->exec("CREATE TABLE IF NOT EXISTS `field_collections` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `user_id` bigint(20) unsigned NOT NULL,
        `user_role` enum('associate','agent') NOT NULL,
        `collection_date` date NOT NULL,
        `customer_name` varchar(255) NOT NULL,
        `customer_phone` varchar(20) DEFAULT NULL,
        `plot_booking_id` bigint(20) unsigned DEFAULT NULL,
        `amount` decimal(15,2) NOT NULL,
        `payment_mode` enum('cash','cheque','online','bank_transfer','other') NOT NULL DEFAULT 'cash',
        `cheque_number` varchar(50) DEFAULT NULL,
        `cheque_date` date DEFAULT NULL,
        `cheque_bank` varchar(255) DEFAULT NULL,
        `remarks` text DEFAULT NULL,
        `receipt_generated` tinyint(1) NOT NULL DEFAULT 0,
        `receipt_number` varchar(50) DEFAULT NULL,
        `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
        `verified_by` bigint(20) unsigned DEFAULT NULL,
        `verified_at` datetime DEFAULT NULL,
        `rejection_reason` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_fc_user` (`user_id`,`user_role`),
        KEY `idx_fc_date` (`collection_date`),
        KEY `idx_fc_status` (`status`),
        KEY `idx_fc_booking` (`plot_booking_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "✓ field_collections table created successfully!\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

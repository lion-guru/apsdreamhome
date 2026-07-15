<?php
/**
 * Migration: Create mlm_rank_slabs + mlm_royalty_pool tables
 * Blueprint: 7-tier differential commission rank system + 2% global royalty pool
 *
 * Run: php scripts/migrate_mlm_rank_slabs.php
 */

$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // ── 1. mlm_rank_slabs ──────────────────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS mlm_rank_slabs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        rank_slug VARCHAR(30) UNIQUE NOT NULL,
        rank_name VARCHAR(50) NOT NULL,
        min_gbv DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        max_gbv DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        commission_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        reward_name VARCHAR(100) DEFAULT NULL,
        reward_value DECIMAL(10,2) DEFAULT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_active (is_active),
        KEY idx_gbv_range (min_gbv, max_gbv)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Seed the 7-tier rank slabs from blueprint
    $pdo->exec("INSERT INTO mlm_rank_slabs (rank_slug, rank_name, min_gbv, max_gbv, commission_rate, reward_name, reward_value) VALUES
        ('associate',      'Associate',      0.00,        1000000.00,   5.00, 'Mobile Phone',    15000.00),
        ('sr_associate',   'Sr. Associate',   1000000.00,  3500000.00,   7.00, 'Tablet',          30000.00),
        ('bdm',            'BDM',             3500000.00,  7000000.00,  10.00, 'Laptop',          60000.00),
        ('sr_bdm',         'Sr. BDM',         7000000.00,  15000000.00, 12.00, 'Tour Package',   100000.00),
        ('vice_president', 'Vice President',  15000000.00, 30000000.00, 15.00, 'Pulsar Bike',    150000.00),
        ('president',      'President',       30000000.00, 50000000.00, 18.00, 'Bullet Bike',    220000.00),
        ('site_manager',   'Site Manager',    50000000.00, 9999999999.00, 20.00, 'Hatchback Car', 600000.00)
        ON DUPLICATE KEY UPDATE
            commission_rate = VALUES(commission_rate),
            reward_name = VALUES(reward_name),
            reward_value = VALUES(reward_value)
    ");

    echo "✓ mlm_rank_slabs created and seeded (7 tiers)\n";

    // ── 2. mlm_royalty_pool ────────────────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS mlm_royalty_pool (
        id INT AUTO_INCREMENT PRIMARY KEY,
        month_year VARCHAR(7) NOT NULL COMMENT 'Format: YYYY-MM',
        total_pool_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        total_qualified_managers INT NOT NULL DEFAULT 0,
        per_manager_share DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        distributed_status ENUM('accumulating','distributed','cancelled') DEFAULT 'accumulating',
        distributed_at TIMESTAMP NULL,
        notes TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_month (month_year),
        KEY idx_status (distributed_status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    echo "✓ mlm_royalty_pool created\n";

    // ── 3. mlm_royalty_contributions (audit trail) ──────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS mlm_royalty_contributions (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        month_year VARCHAR(7) NOT NULL,
        booking_id BIGINT UNSIGNED DEFAULT NULL,
        payment_amount DECIMAL(15,2) NOT NULL,
        contribution_amount DECIMAL(15,2) NOT NULL,
        contributed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_month (month_year),
        KEY idx_booking (booking_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    echo "✓ mlm_royalty_contributions created (audit trail)\n";

    // ── 4. mlm_career_rewards ──────────────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS mlm_career_rewards (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        rank_slug VARCHAR(30) NOT NULL,
        reward_name VARCHAR(100) NOT NULL,
        reward_value DECIMAL(10,2) DEFAULT NULL,
        gbv_at_award DECIMAL(15,2) NOT NULL,
        status ENUM('pending','awarded','cancelled') DEFAULT 'pending',
        awarded_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_user (user_id),
        KEY idx_rank (rank_slug),
        KEY idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    echo "✓ mlm_career_rewards created\n";

    // ── Summary ─────────────────────────────────────────────
    echo "\nMigration complete. Tables created:\n";
    echo "  1. mlm_rank_slabs (7 tiers seeded)\n";
    echo "  2. mlm_royalty_pool (monthly accumulator)\n";
    echo "  3. mlm_royalty_contributions (audit trail)\n";
    echo "  4. mlm_career_rewards (achievement tracking)\n";

} catch (Exception $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

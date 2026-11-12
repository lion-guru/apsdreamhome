<?php
/**
 * Migration: Update MLM Rank Benefits to Authentic Business Targets & Create Leader Allowances Table
 *
 * Implements:
 * 1. Updates mlm_rank_benefits with user's authentic qualification matrix:
 *    - Sr. Ass. : ₹10 Lakhs volume + 5 Direct Associates (Reward: Tablet)
 *    - BDM      : ₹35 Lakhs volume + 3 Sr. Associates in downline (Reward: Laptop)
 *    - Sr. BDM  : ₹70 Lakhs volume + 3 BDMs in downline (Reward: Tour)
 *    - V.P.     : ₹1.5 Crore volume + 3 Sr. BDMs in downline (Reward: Pulsar Bike)
 *    - President: ₹3.0 Crore volume + 3 V.P.s in downline (Reward: Bullet Bike)
 *    - Site Mgr : ₹5.0 Crore volume + 3 Presidents in downline (Reward: Car)
 *
 * 2. Creates mlm_leader_allowances table for Office Support & Site Visit Expense Allowances
 *
 * Safe & Idempotent: re-runs are non-destructive and skip existing structures.
 */

define('APS_ROOT', dirname(__DIR__, 2));
require_once APS_ROOT . '/config/bootstrap.php';

use App\Core\Database\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    echo "=== Starting Migration: MLM Plan & Leader Allowances (S456) ===\n\n";

    // 1. Update mlm_rank_benefits table rows
    echo "Step 1: Updating mlm_rank_benefits with authentic business targets...\n";
    $ranks = [
        [
            'rank_name'             => 'Ass.',
            'rank_order'            => 1,
            'min_leg_count'         => 0,
            'min_qualifying_volume' => 0.00,
            'direct_sale_pct'       => 5.00,
            'reward_item'           => 'Mobile Phone'
        ],
        [
            'rank_name'             => 'Sr. Ass.',
            'rank_order'            => 2,
            'min_leg_count'         => 5, // 5 direct associates
            'min_qualifying_volume' => 1000000.00, // ₹10 Lakhs
            'direct_sale_pct'       => 7.00,
            'reward_item'           => 'Tablet'
        ],
        [
            'rank_name'             => 'BDM',
            'rank_order'            => 3,
            'min_leg_count'         => 3, // 3 Sr. Associates
            'min_qualifying_volume' => 3500000.00, // ₹35 Lakhs
            'direct_sale_pct'       => 10.00,
            'reward_item'           => 'Laptop'
        ],
        [
            'rank_name'             => 'Sr. BDM',
            'rank_order'            => 4,
            'min_leg_count'         => 3, // 3 BDMs
            'min_qualifying_volume' => 7000000.00, // ₹70 Lakhs
            'direct_sale_pct'       => 12.00,
            'reward_item'           => 'Domestic / Foreign Tour'
        ],
        [
            'rank_name'             => 'V.P.',
            'rank_order'            => 5,
            'min_leg_count'         => 3, // 3 Sr. BDMs
            'min_qualifying_volume' => 15000000.00, // ₹1.5 Crore
            'direct_sale_pct'       => 15.00,
            'reward_item'           => 'Pulsar Bike'
        ],
        [
            'rank_name'             => 'President',
            'rank_order'            => 6,
            'min_leg_count'         => 3, // 3 V.P.s
            'min_qualifying_volume' => 30000000.00, // ₹3.0 Crore
            'direct_sale_pct'       => 18.00,
            'reward_item'           => 'Bullet Bike'
        ],
        [
            'rank_name'             => 'Site Manager',
            'rank_order'            => 7,
            'min_leg_count'         => 3, // 3 Presidents
            'min_qualifying_volume' => 50000000.00, // ₹5.0 Crore
            'direct_sale_pct'       => 20.00,
            'reward_item'           => 'Luxury Car'
        ],
    ];

    $stmt = $pdo->prepare("
        UPDATE mlm_rank_benefits 
        SET min_leg_count = ?, min_qualifying_volume = ?, direct_sale_pct = ?, reward_item = ?, updated_at = NOW()
        WHERE rank_name = ?
    ");

    foreach ($ranks as $r) {
        $stmt->execute([
            $r['min_leg_count'],
            $r['min_qualifying_volume'],
            $r['direct_sale_pct'],
            $r['reward_item'],
            $r['rank_name']
        ]);
        echo "  ✓ Updated {$r['rank_name']}: Target ₹" . number_format($r['min_qualifying_volume']) . ", Legs: {$r['min_leg_count']}, Rate: {$r['direct_sale_pct']}%, Reward: {$r['reward_item']}\n";
    }

    // 2. Create mlm_leader_allowances table for Office Support & Site Visit Allowances
    echo "\nStep 2: Ensuring mlm_leader_allowances table exists...\n";
    $tableExists = (bool)$pdo->query("SHOW TABLES LIKE 'mlm_leader_allowances'")->fetchColumn();
    if (!$tableExists) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `mlm_leader_allowances` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `tenant_id` int(10) unsigned NOT NULL DEFAULT 1,
                `user_id` bigint(20) unsigned NOT NULL,
                `rank_name` varchar(50) NOT NULL,
                `allowance_type` enum('office_support','site_visit_fuel','vehicle_hiring','branch_rent','meeting_support') NOT NULL,
                `month_year` varchar(7) NOT NULL COMMENT 'Format YYYY-MM',
                `target_volume` decimal(15,2) NOT NULL DEFAULT 0.00,
                `achieved_volume` decimal(15,2) NOT NULL DEFAULT 0.00,
                `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
                `site_visit_id` int(11) DEFAULT NULL,
                `proof_document` varchar(255) DEFAULT NULL,
                `status` enum('qualified','claimed','approved','paid','rejected') NOT NULL DEFAULT 'qualified',
                `notes` text DEFAULT NULL,
                `approved_by` int(11) DEFAULT NULL,
                `approved_at` datetime DEFAULT NULL,
                `paid_at` datetime DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_user_month` (`user_id`, `month_year`),
                KEY `idx_allowance_type` (`allowance_type`),
                KEY `idx_status` (`status`),
                KEY `idx_tenant_id` (`tenant_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        echo "  ✓ Created mlm_leader_allowances table.\n";
    } else {
        echo "  ✓ mlm_leader_allowances table already exists (skipping creation).\n";
    }

    echo "\n=== Migration S456 Completed Successfully! ===\n";
    exit(0);
} catch (\Throwable $e) {
    echo "❌ Migration Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}


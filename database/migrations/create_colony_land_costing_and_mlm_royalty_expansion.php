<?php
/**
 * Migration: Colony Land Costing System
 * ──────────────────────────────────────
 * Creates `colony_land_costing` table for full land acquisition → plot price calculation.
 *
 * Formula:
 *   1. Wastage → Net Sellable SqFt
 *   2. Raw land cost per sellable SqFt
 *   3. + Development cost, Legal, Admin overhead
 *   4. + Marketing/MLM commission budget
 *   5. + Profit margin → Final Selling Price
 */

$root   = dirname(__DIR__, 2);
$config = require_once $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "Connected.\n";

    // ─────────────────────────────────────────────────────────────────────────
    // TABLE: colony_land_costing
    // ─────────────────────────────────────────────────────────────────────────
    $pdo->exec("DROP TABLE IF EXISTS `colony_land_costing`");
    $pdo->exec("
        CREATE TABLE `colony_land_costing` (
            `id`                        INT AUTO_INCREMENT PRIMARY KEY,
            `colony_id`                 INT NOT NULL COMMENT 'colonies.id',
            `costing_label`             VARCHAR(255) NOT NULL DEFAULT 'Initial Costing'
                COMMENT 'Label for this costing version (e.g. Phase 1, Revised Jul-2026)',

            -- ── LAND ACQUISITION ──────────────────────────────────────────
            `total_land_sqft`           DECIMAL(16,4) NOT NULL DEFAULT 0.0000
                COMMENT 'Total raw land purchased in SqFt',
            `land_purchase_rate`        DECIMAL(12,4) NOT NULL DEFAULT 0.0000
                COMMENT 'Acquisition cost per SqFt (₹/SqFt)',
            `land_registry_cost`        DECIMAL(14,2) NOT NULL DEFAULT 0.00
                COMMENT 'Flat registry/stamp duty amount (₹ total)',

            -- ── WASTAGE DEDUCTIONS ────────────────────────────────────────
            `road_wastage_pct`          DECIMAL(6,2) NOT NULL DEFAULT 15.00
                COMMENT '% of land used for roads',
            `drainage_wastage_pct`      DECIMAL(6,2) NOT NULL DEFAULT 5.00
                COMMENT '% of land used for drainage / nali',
            `park_wastage_pct`          DECIMAL(6,2) NOT NULL DEFAULT 5.00
                COMMENT '% of land used for parks / green areas',
            `other_wastage_pct`         DECIMAL(6,2) NOT NULL DEFAULT 0.00
                COMMENT 'Other wastage: religious/utility/buffer areas',

            -- ── DEVELOPMENT COSTS (per sellable SqFt) ────────────────────
            `road_dev_cost_sqft`        DECIMAL(10,4) NOT NULL DEFAULT 0.0000
                COMMENT 'Road construction cost per SqFt (₹/SqFt)',
            `drainage_dev_cost_sqft`    DECIMAL(10,4) NOT NULL DEFAULT 0.0000
                COMMENT 'Drainage/nali construction per SqFt',
            `electricity_cost_sqft`     DECIMAL(10,4) NOT NULL DEFAULT 0.0000
                COMMENT 'Electrical infrastructure per SqFt',
            `water_pipeline_cost_sqft`  DECIMAL(10,4) NOT NULL DEFAULT 0.0000
                COMMENT 'Water pipeline per SqFt',
            `boundary_wall_cost_sqft`   DECIMAL(10,4) NOT NULL DEFAULT 0.0000
                COMMENT 'Boundary wall/security per SqFt',
            `other_dev_cost_sqft`       DECIMAL(10,4) NOT NULL DEFAULT 0.0000
                COMMENT 'Other development (park landscaping, streetlights, etc)',

            -- ── OVERHEAD COSTS ────────────────────────────────────────────
            `legal_approval_cost`       DECIMAL(14,2) NOT NULL DEFAULT 0.00
                COMMENT 'RERA/town planning/NOC flat total cost',
            `admin_overhead_pct`        DECIMAL(5,2) NOT NULL DEFAULT 5.00
                COMMENT 'Admin/management overhead % of pre-marketing cost',
            `marketing_commission_pct`  DECIMAL(5,2) NOT NULL DEFAULT 20.00
                COMMENT 'MLM/sales commission budget % (from selling price)',

            -- ── PROFIT MARGIN ─────────────────────────────────────────────
            `target_profit_pct`         DECIMAL(5,2) NOT NULL DEFAULT 20.00
                COMMENT 'Desired company profit % on final selling price',

            -- ── CALCULATED FIELDS (auto-filled by service) ───────────────
            `net_sellable_sqft`         DECIMAL(16,4) GENERATED ALWAYS AS (
                `total_land_sqft` * (
                    1 - (`road_wastage_pct` + `drainage_wastage_pct` + `park_wastage_pct` + `other_wastage_pct`) / 100
                )
            ) STORED COMMENT 'Auto-calculated: sellable SqFt after all wastage deductions',

            `landing_cost_sqft`         DECIMAL(12,4) NOT NULL DEFAULT 0.0000
                COMMENT 'Total cost per sellable SqFt (service-calculated & stored)',
            `suggested_price_sqft`      DECIMAL(12,4) NOT NULL DEFAULT 0.0000
                COMMENT 'landing_cost ÷ (1 - profit%) — system suggested price',
            `final_price_sqft`          DECIMAL(12,4) NOT NULL DEFAULT 0.0000
                COMMENT 'Admin-approved final selling price per SqFt',

            -- ── META ──────────────────────────────────────────────────────
            `is_approved`               TINYINT(1) NOT NULL DEFAULT 0,
            `approved_by`               INT NULL COMMENT 'users.id of approving admin',
            `approved_at`               DATETIME NULL,
            `notes`                     TEXT NULL,
            `version`                   INT NOT NULL DEFAULT 1
                COMMENT 'Increment when costing is revised',
            `tenant_id`                 INT NOT NULL DEFAULT 1,
            `created_by`                INT NOT NULL DEFAULT 1,
            `created_at`                DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`                DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            INDEX `idx_colony_id` (`colony_id`),
            INDEX `idx_tenant` (`tenant_id`),
            INDEX `idx_approved` (`is_approved`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC
        COMMENT='Full land costing & plot pricing calculator per colony';
    ");
    echo "✅ colony_land_costing table created.\n";

    // ─────────────────────────────────────────────────────────────────────────
    // TABLE: colony_land_costing_items (breakdown line items for audit trail)
    // ─────────────────────────────────────────────────────────────────────────
    $pdo->exec("DROP TABLE IF EXISTS `colony_land_costing_items`");
    $pdo->exec("
        CREATE TABLE `colony_land_costing_items` (
            `id`                INT AUTO_INCREMENT PRIMARY KEY,
            `costing_id`        INT NOT NULL COMMENT 'colony_land_costing.id',
            `item_type`         ENUM(
                'land_purchase',
                'land_registry',
                'road_wastage',
                'drainage_wastage',
                'park_wastage',
                'other_wastage',
                'road_development',
                'drainage_development',
                'electricity',
                'water_pipeline',
                'boundary_wall',
                'other_development',
                'legal_approval',
                'admin_overhead',
                'marketing_commission',
                'profit_margin'
            ) NOT NULL,
            `item_label`        VARCHAR(255) NOT NULL,
            `quantity`          DECIMAL(16,4) NOT NULL DEFAULT 0.0000
                COMMENT 'SqFt or units involved',
            `unit`              VARCHAR(50) NOT NULL DEFAULT 'SqFt',
            `rate`              DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
            `amount`            DECIMAL(14,2) NOT NULL DEFAULT 0.00,
            `is_deduction`      TINYINT(1) NOT NULL DEFAULT 0
                COMMENT '1 = reduces sellable area, 0 = cost addition',
            `tenant_id`         INT NOT NULL DEFAULT 1,
            `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_costing_id` (`costing_id`),
            INDEX `idx_tenant` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC
        COMMENT='Line-item audit trail for colony land costing calculations';
    ");
    echo "✅ colony_land_costing_items table created.\n";

    // ─────────────────────────────────────────────────────────────────────────
    // Expand mlm_rank_slabs: add royalty_eligible + leadership_bonus columns
    // ─────────────────────────────────────────────────────────────────────────
    $cols = $pdo->query("SHOW COLUMNS FROM `mlm_rank_slabs` LIKE 'royalty_eligible'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("
            ALTER TABLE `mlm_rank_slabs`
            ADD COLUMN `royalty_eligible`        TINYINT(1)    NOT NULL DEFAULT 0
                COMMENT '1 = this rank receives monthly royalty pool distribution',
            ADD COLUMN `royalty_pool_share_pct`  DECIMAL(6,4)  NOT NULL DEFAULT 0.0000
                COMMENT 'What % of royalty pool this rank class gets (shared equally among all members at this rank)',
            ADD COLUMN `leadership_bonus_pct`    DECIMAL(5,2)  NOT NULL DEFAULT 0.00
                COMMENT 'Additional leadership override bonus %',
            ADD COLUMN `profit_share_eligible`   TINYINT(1)    NOT NULL DEFAULT 0
                COMMENT '1 = shareholder level — eligible for company profit sharing',
            ADD COLUMN `reward_description`      VARCHAR(500)  NULL
                COMMENT 'Human-readable reward description shown on dashboard (e.g. Bike, Car, International Tour)',
            ADD COLUMN `sort_order`              INT           NOT NULL DEFAULT 0
                COMMENT 'Display order in admin panel and associate dashboard'
        ");
        echo "✅ mlm_rank_slabs: royalty/leadership columns added.\n";
    } else {
        echo "⏭  mlm_rank_slabs: royalty columns already exist, skipping.\n";
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Seed expanded ranks (9-level) into mlm_rank_slabs if empty
    // ─────────────────────────────────────────────────────────────────────────
    $count = (int)$pdo->query("SELECT COUNT(*) FROM mlm_rank_slabs")->fetchColumn();
    if ($count === 0) {
        echo "Seeding 9 default ranks into mlm_rank_slabs...\n";
        $ranks = [
            ['associate',        'Associate',         0,         10000000,  5,  0, 0.00, 0, 0, 'Welcome Kit',                                   1],
            ['sr_associate',     'Sr. Associate',     10000000,  35000000,  7,  0, 0.00, 0, 0, null,                                             2],
            ['bdm',              'BDM',               35000000,  70000000, 10,  0, 0.00, 0, 0, null,                                             3],
            ['sr_bdm',           'Sr. BDM',           70000000, 150000000, 12,  0, 0.00, 0, 0, null,                                             4],
            ['vice_president',   'Vice President',   150000000, 300000000, 15,  0, 0.00, 0, 0, 'Domestic Tour Package',                          5],
            ['president',        'President',        300000000, 500000000, 18,  0, 0.00, 0, 0, 'Car Bonus / International Tour',                 6],
            ['site_manager',     'Site Manager',     500000000,          0, 20,  0, 0.00, 0, 0, 'Luxury Car Bonus',                              7],
            ['royalty_director', 'Royalty Director', 1000000000,         0, 20,  1, 2.00, 0, 0, 'Monthly Royalty Pool + Villa Fund',              8],
            ['shareholder',      'Shareholder',      2500000000,         0, 20,  1, 3.00, 1, 0, 'Company Profit Sharing + Equity Participation',  9],
        ];

        $stmt = $pdo->prepare("
            INSERT INTO mlm_rank_slabs
                (rank_slug, rank_name, min_gbv, max_gbv, commission_rate,
                 royalty_eligible, royalty_pool_share_pct, profit_share_eligible,
                 leadership_bonus_pct, reward_description, sort_order, is_active)
            VALUES (?,?,?,?,?, ?,?,?, ?,?,?, 1)
        ");
        foreach ($ranks as $r) {
            $stmt->execute($r);
        }
        echo "✅ mlm_rank_slabs: 9 default ranks seeded.\n";
    } else {
        echo "⏭  mlm_rank_slabs: already has {$count} rows, skipping seed.\n";
    }

    echo "\n✅ All migrations completed successfully.\n";

} catch (Exception $e) {
    echo "❌ Migration FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

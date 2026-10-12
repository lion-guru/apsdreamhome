<?php
/**
 * Dynamic Pricing Engine - Database Migration (Session 218).
 * Idempotent: each CREATE is guarded; safe to re-run.
 *
 * Notes vs first cut:
 *  - model_id is NULLABLE (suggestions can exist before any model is trained;
 *    the original NOT NULL + FK forced model_id=0 which violates the FK).
 *  - models: one row per colony is kept by the service via UPDATE-or-INSERT;
 *    the UNIQUE(colony_id, is_active) key is an INDEX (a unique there would
 *    also block multiple inactive model versions of the same colony).
 */

require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

echo "=== Creating Dynamic Pricing Tables ===\n\n";

$sqls = [];

// 1. dynamic_pricing_models - trained pricing models per colony
$sqls['dynamic_pricing_models'] = "CREATE TABLE IF NOT EXISTS dynamic_pricing_models (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
    colony_id INT UNSIGNED NOT NULL,
    model_name VARCHAR(100) NOT NULL,
    model_type ENUM('linear_regression','xgboost','random_forest','ensemble') NOT NULL DEFAULT 'ensemble',
    model_version VARCHAR(20) NOT NULL DEFAULT '1.0',
    model_data LONGTEXT NOT NULL COMMENT 'Serialized model weights/parameters (JSON)',
    feature_columns JSON NOT NULL COMMENT 'List of feature column names used by model',
    target_column VARCHAR(50) NOT NULL DEFAULT 'final_price_per_sqft',
    training_metrics JSON COMMENT 'RMSE, R2, samples, trained_at',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_production TINYINT(1) NOT NULL DEFAULT 0,
    trained_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_colony_active (colony_id, is_active),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// 2. dynamic_pricing_suggestions - AI-generated price recommendations
$sqls['dynamic_pricing_suggestions'] = "CREATE TABLE IF NOT EXISTS dynamic_pricing_suggestions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
    colony_id INT UNSIGNED NOT NULL,
    model_id INT NULL DEFAULT NULL COMMENT 'Trained model used (NULL = heuristic-only suggestion)',
    plot_id INT UNSIGNED NULL COMMENT 'Specific plot suggestion (NULL = colony-wide base rate)',
    current_base_pps DECIMAL(10,2) NOT NULL,
    suggested_base_pps DECIMAL(10,2) NOT NULL,
    price_change_pct DECIMAL(6,2) NOT NULL,
    confidence_score DECIMAL(4,3) NOT NULL COMMENT '0.000-1.000',
    reasoning TEXT COMMENT 'Human-readable explanation of factors',
    feature_snapshot JSON NOT NULL COMMENT 'Feature values at suggestion time',
    status ENUM('pending','approved','rejected','applied') NOT NULL DEFAULT 'pending',
    approved_by INT UNSIGNED NULL,
    approved_at TIMESTAMP NULL DEFAULT NULL,
    applied_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_colony (colony_id),
    INDEX idx_model (model_id),
    INDEX idx_status (status),
    INDEX idx_plot (plot_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// 3. pricing_optimization_logs - audit trail of applied changes
$sqls['pricing_optimization_logs'] = "CREATE TABLE IF NOT EXISTS pricing_optimization_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
    colony_id INT UNSIGNED NOT NULL,
    suggestion_id INT UNSIGNED NULL,
    action ENUM('suggestion_generated','approved','rejected','applied','rolled_back') NOT NULL,
    old_base_pps DECIMAL(10,2) NULL,
    new_base_pps DECIMAL(10,2) NULL,
    affected_plots INT NOT NULL DEFAULT 0,
    performed_by INT UNSIGNED NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_colony (colony_id),
    INDEX idx_suggestion (suggestion_id),
    INDEX idx_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// 4. pricing_experiments - A/B testing framework for pricing strategies
$sqls['pricing_experiments'] = "CREATE TABLE IF NOT EXISTS pricing_experiments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
    colony_id INT UNSIGNED NOT NULL,
    experiment_name VARCHAR(100) NOT NULL,
    experiment_type ENUM('ab_test','multi_armed_bandit','sequential') NOT NULL DEFAULT 'ab_test',
    control_base_pps DECIMAL(10,2) NOT NULL,
    variant_configs JSON NOT NULL COMMENT 'Array of variant configs with base_pps, traffic_split',
    traffic_allocation JSON NOT NULL COMMENT 'Plot assignment logic',
    status ENUM('draft','running','paused','completed','archived') NOT NULL DEFAULT 'draft',
    start_date DATE NULL,
    end_date DATE NULL,
    min_sample_size INT NOT NULL DEFAULT 30,
    confidence_level DECIMAL(4,3) NOT NULL DEFAULT 0.95,
    results JSON NULL COMMENT 'Statistical results when completed',
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_colony (colony_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// 5. pricing_experiment_assignments - tracks which plot got which variant
$sqls['pricing_experiment_assignments'] = "CREATE TABLE IF NOT EXISTS pricing_experiment_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
    experiment_id INT UNSIGNED NOT NULL,
    plot_id INT UNSIGNED NOT NULL,
    variant_key VARCHAR(50) NOT NULL COMMENT 'e.g., control, variant_a, variant_b',
    assigned_base_pps DECIMAL(10,2) NOT NULL,
    original_base_pps DECIMAL(10,2) NULL DEFAULT NULL COMMENT 'pre-experiment base (NULL = legacy no-base row)',
    original_pps DECIMAL(10,2) NULL DEFAULT NULL COMMENT 'pre-experiment price_per_sqft (exact restore source)',
    original_total DECIMAL(14,2) NULL DEFAULT NULL COMMENT 'pre-experiment total_price (exact restore source)',
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    converted_at TIMESTAMP NULL DEFAULT NULL,
    converted_pps DECIMAL(10,2) NULL,
    UNIQUE KEY uniq_experiment_plot (experiment_id, plot_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_experiment (experiment_id),
    INDEX idx_variant (variant_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

foreach ($sqls as $name => $sql) {
    try {
        $pdo->exec($sql);
        echo "OK  $name\n";
    } catch (Throwable $e) {
        echo "ERR $name: " . $e->getMessage() . "\n";
    }
}

// Runtime config defaults are seeded by DynamicPricingService::ensureConfigRows()
// (called from the controller) - no service_configs inserts here.

// Idempotent upgrades for tables created by earlier partial rounds (S218 live DB
// predates the original-price capture columns and was missing the plot unique key).
$upgrades = [
    "ALTER TABLE pricing_experiment_assignments ADD COLUMN original_base_pps DECIMAL(10,2) NULL DEFAULT NULL AFTER assigned_base_pps",
    "ALTER TABLE pricing_experiment_assignments ADD COLUMN original_pps DECIMAL(10,2) NULL DEFAULT NULL AFTER original_base_pps",
    "ALTER TABLE pricing_experiment_assignments ADD COLUMN original_total DECIMAL(14,2) NULL DEFAULT NULL AFTER original_pps",
    "ALTER TABLE pricing_experiment_assignments ADD UNIQUE KEY uniq_experiment_plot (experiment_id, plot_id)",
];
foreach ($upgrades as $sql) {
    try {
        $pdo->exec($sql);
        echo "OK  upgrade: " . substr($sql, 0, 70) . "...\n";
    } catch (Throwable $e) {
        // 1060 duplicate column / 1061 duplicate key = already applied
        $code = $e->getCode();
        echo ($code == '42S21' || $code == '42000' || strpos($e->getMessage(), 'Duplicate') !== false ? "SKIP" : "ERR ") . " upgrade: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Dynamic Pricing Migration Complete ===\n";

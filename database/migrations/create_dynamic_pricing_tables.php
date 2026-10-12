<?php
/**
 * Dynamic Pricing Engine - Database Migration
 * Creates tables for AI-driven dynamic pricing models and suggestions
 */

require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

echo "=== Creating Dynamic Pricing Tables ===\n\n";

// 1. dynamic_pricing_models - stores trained pricing models per colony/segment
$sql1 = "CREATE TABLE IF NOT EXISTS dynamic_pricing_models (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
    colony_id INT UNSIGNED NOT NULL,
    model_name VARCHAR(100) NOT NULL,
    model_type ENUM('linear_regression','xgboost','random_forest','ensemble') NOT NULL DEFAULT 'ensemble',
    model_version VARCHAR(20) NOT NULL DEFAULT '1.0',
    model_data LONGTEXT NOT NULL COMMENT 'Serialized model weights/parameters (JSON)',
    feature_columns JSON NOT NULL COMMENT 'List of feature column names used by model',
    target_column VARCHAR(50) NOT NULL DEFAULT 'final_price_per_sqft',
    training_metrics JSON COMMENT 'MAE, RMSE, R2, training_date, sample_count',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_production TINYINT(1) NOT NULL DEFAULT 0,
    trained_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_colony_active (colony_id, is_active),
    INDEX idx_tenant (tenant_id),
    INDEX idx_colony (colony_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

$pdo->exec($sql1);
echo "✓ dynamic_pricing_models created\n";

// 2. dynamic_pricing_suggestions - AI-generated price recommendations
$sql2 = "CREATE TABLE IF NOT EXISTS dynamic_pricing_suggestions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
    colony_id INT UNSIGNED NOT NULL,
    model_id INT UNSIGNED NOT NULL,
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
    INDEX idx_plot (plot_id),
    FOREIGN KEY (model_id) REFERENCES dynamic_pricing_models(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

$pdo->exec($sql2);
echo "✓ dynamic_pricing_suggestions created\n";

// 3. pricing_optimization_logs - audit trail of applied changes
$sql3 = "CREATE TABLE IF NOT EXISTS pricing_optimization_logs (
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

$pdo->exec($sql3);
echo "✓ pricing_optimization_logs created\n";

// 4. pricing_experiments - A/B testing framework for pricing strategies
$sql4 = "CREATE TABLE IF NOT EXISTS pricing_experiments (
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

$pdo->exec($sql4);
echo "✓ pricing_experiments created\n";

// 5. pricing_experiment_assignments - tracks which plot got which variant
$sql5 = "CREATE TABLE IF NOT EXISTS pricing_experiment_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
    experiment_id INT UNSIGNED NOT NULL,
    plot_id INT UNSIGNED NOT NULL,
    variant_key VARCHAR(50) NOT NULL COMMENT 'e.g., control, variant_a, variant_b',
    assigned_base_pps DECIMAL(10,2) NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    converted_at TIMESTAMP NULL DEFAULT NULL,
    converted_pps DECIMAL(10,2) NULL,
    UNIQUE KEY uniq_experiment_plot (experiment_id, plot_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_experiment (experiment_id),
    INDEX idx_variant (variant_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

$pdo->exec($sql5);
echo "✓ pricing_experiment_assignments created\n";

// 6. Seed default service configs for dynamic pricing
$defaults = [
    'dynamic_pricing_enabled' => ['0', 'Master switch for dynamic pricing engine'],
    'dynamic_pricing_min_confidence' => ['0.75', 'Minimum confidence score to auto-apply suggestions'],
    'dynamic_pricing_max_change_pct' => ['15', 'Maximum % price change per suggestion'],
    'dynamic_pricing_retrain_days' => ['30', 'Days between model retraining'],
    'dynamic_pricing_lookback_days' => ['180', 'Days of historical data for training'],
    'dynamic_pricing_min_samples' => ['50', 'Minimum booking samples to train'],
    'dynamic_pricing_features' => ['["sales_velocity","inventory_age_days","corner_ratio","park_ratio","road_width_avg","seasonality_index","competitor_price_index"]', 'Feature columns for ML model'],
];

try {
    foreach ($defaults as $key => [$val, $desc]) {
        $exists = $pdo->prepare("SELECT id FROM service_configs WHERE service_name = 'dynamic_pricing' AND config_key = ? LIMIT 1");
        $exists->execute([$key]);
        if (!$exists->fetchColumn()) {
            $pdo->prepare("INSERT INTO service_configs (service_name, config_key, config_value, config_type, description, group_name) VALUES ('dynamic_pricing', ?, ?, 'json', ?, 'dynamic_pricing')")->execute([$key, $val, $desc]);
        }
    }
    echo "✓ Default service configs seeded\n";
} catch (Throwable $e) {
    echo "Note: Service configs seeding skipped: " . $e->getMessage() . "\n";
}

echo "\n=== Dynamic Pricing Migration Complete ===\n";
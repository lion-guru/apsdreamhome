<?php
/**
 * Migration: Create colony_pricing_feasibility audit table
 *
 * Logs every feasibility calculation when a colony is priced,
 * capturing all input parameters and the recommended vs applied price.
 *
 * Run: php scripts/migrate_colony_feasibility.php
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

    echo "=== Migration: Colony Pricing Feasibility Table ===\n\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS colony_pricing_feasibility (
            id INT AUTO_INCREMENT PRIMARY KEY,
            colony_id INT NOT NULL,

            -- Input: Area
            total_raw_area_sqft DECIMAL(15,2) NOT NULL DEFAULT 0,
            saleable_area_yield_pct DECIMAL(5,2) NOT NULL DEFAULT 60.00,
            saleable_area_sqft DECIMAL(15,2) NOT NULL DEFAULT 0,

            -- Input: Cost Components
            land_cost_total DECIMAL(15,2) NOT NULL DEFAULT 0,
            registry_cost_total DECIMAL(15,2) NOT NULL DEFAULT 0,
            development_cost_total DECIMAL(15,2) NOT NULL DEFAULT 0,
            approvals_cost_total DECIMAL(15,2) NOT NULL DEFAULT 0,

            -- Calculated: Cost-Basis
            raw_cost_basis_ppsf DECIMAL(10,2) NOT NULL DEFAULT 0,

            -- Input: Overhead Percentages
            target_profit_pct DECIMAL(5,2) NOT NULL DEFAULT 20.00,
            office_overhead_pct DECIMAL(5,2) NOT NULL DEFAULT 5.00,
            mlm_budget_pct DECIMAL(5,2) NOT NULL DEFAULT 25.00,

            -- Calculated: Pricing
            markup_factor DECIMAL(8,4) NOT NULL DEFAULT 2.0000,
            recommended_price_ppsf DECIMAL(10,2) NOT NULL DEFAULT 0,
            applied_price_ppsf DECIMAL(10,2) NOT NULL DEFAULT 0,

            -- Context
            notes TEXT DEFAULT NULL,
            created_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            KEY idx_cpf_colony (colony_id),
            KEY idx_cpf_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    echo "Table created successfully.\n";

    // Verify
    $stmt = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'apsdreamhome' AND TABLE_NAME = 'colony_pricing_feasibility'");
    echo "Verified: " . ($stmt->fetchColumn() ? 'EXISTS' : 'MISSING') . "\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}?>
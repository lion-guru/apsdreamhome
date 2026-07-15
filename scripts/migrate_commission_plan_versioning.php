<?php
$config = require dirname(__DIR__) . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "=== Step 1: Add versioning columns to mlm_commission_plans ===\n";

$colsToAdd = [
    'version INT UNSIGNED NOT NULL DEFAULT 1',
    'effective_date DATE NULL',
    'expiry_date DATE NULL',
    'updated_by INT UNSIGNED NULL',
    'global_cap_pct DECIMAL(5,2) NOT NULL DEFAULT 20.00',
    'track_a_pct DECIMAL(5,2) NOT NULL DEFAULT 15.00',
    'track_b_pct DECIMAL(5,2) NOT NULL DEFAULT 3.00',
    'track_c_pct DECIMAL(5,2) NOT NULL DEFAULT 2.00',
    'royalty_pool_pct DECIMAL(5,2) NOT NULL DEFAULT 2.00',
    'same_level_override_gen1 DECIMAL(4,2) NOT NULL DEFAULT 2.00',
    'same_level_override_gen2 DECIMAL(4,2) NOT NULL DEFAULT 1.00',
];

// Check existing columns
$existing = [];
$r = $pdo->query("DESCRIBE mlm_commission_plans");
while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
    $existing[] = $row['Field'];
}

foreach ($colsToAdd as $colDef) {
    $colName = explode(' ', $colDef)[0];
    if (!in_array($colName, $existing)) {
        try {
            $pdo->exec("ALTER TABLE mlm_commission_plans ADD COLUMN $colDef");
            echo "  ADD $colName: OK\n";
        } catch (Exception $e) {
            echo "  ADD $colName: ERROR - " . $e->getMessage() . "\n";
        }
    } else {
        echo "  $colName: already exists\n";
    }
}

// Add index on version
try {
    $pdo->exec("ALTER TABLE mlm_commission_plans ADD INDEX idx_version (version)");
    echo "  idx_version: OK\n";
} catch (Exception $e) {
    echo "  idx_version: already exists or error\n";
}

echo "\n=== Step 2: Create commission_plan_audit table ===\n";
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS commission_plan_audit (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        plan_id INT UNSIGNED NOT NULL,
        plan_name VARCHAR(100) NOT NULL,
        plan_code VARCHAR(20) NOT NULL,
        version INT UNSIGNED NOT NULL,
        action ENUM('create','update','activate','deactivate','delete','clone') NOT NULL,
        changed_fields JSON NULL,
        old_values JSON NULL,
        new_values JSON NULL,
        changed_by INT UNSIGNED NOT NULL,
        changed_by_name VARCHAR(100) NULL,
        ip_address VARCHAR(45) NULL,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_plan_id (plan_id),
        INDEX idx_version (version),
        INDEX idx_action (action),
        INDEX idx_changed_by (changed_by),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "  commission_plan_audit: OK\n";
} catch (Exception $e) {
    echo "  commission_plan_audit: ERROR - " . $e->getMessage() . "\n";
}

echo "\n=== Step 3: Seed version=1 for existing plans ===\n";
try {
    $pdo->exec("UPDATE mlm_commission_plans SET version = 1 WHERE version IS NULL OR version = 0");
    echo "  Updated plans to version 1\n";
} catch (Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== DONE ===\n";
echo "\nFinal mlm_commission_plans schema:\n";
$r = $pdo->query("DESCRIBE mlm_commission_plans");
while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$row['Field']} {$row['Type']} " . ($row['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . " DEFAULT " . ($row['Default'] ?? 'NONE') . "\n";
}

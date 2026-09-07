<?php
/**
 * Migration: Add Telecaller settings and overrides to associates table.
 * MySQL 8.0 compatible (no ADD COLUMN IF NOT EXISTS).
 *
 * Run via: php database/migrations/update_associates_telecallers_overrides.php
 */

$root = dirname(__DIR__, 2);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "Running migration: update_associates_telecallers_overrides...\n";

    // Get existing columns
    $cols = $pdo->query("SHOW COLUMNS FROM associates")->fetchAll(\PDO::FETCH_COLUMN);
    $has = fn(string $c) => in_array($c, $cols, true);

    // 1. telecaller_salary
    if (!$has('telecaller_salary')) {
        $pdo->exec("ALTER TABLE associates ADD COLUMN telecaller_salary DECIMAL(10,2) DEFAULT 0.00 AFTER brokerage_rate");
        echo "  + Added telecaller_salary\n";
    } else {
        echo "  = telecaller_salary already exists\n";
    }

    // 2. telecaller_incentive_rate
    if (!$has('telecaller_incentive_rate')) {
        $pdo->exec("ALTER TABLE associates ADD COLUMN telecaller_incentive_rate DECIMAL(10,2) DEFAULT 0.00 AFTER telecaller_salary");
        echo "  + Added telecaller_incentive_rate\n";
    } else {
        echo "  = telecaller_incentive_rate already exists\n";
    }

    // 3. telecaller_sqft_rate
    if (!$has('telecaller_sqft_rate')) {
        $pdo->exec("ALTER TABLE associates ADD COLUMN telecaller_sqft_rate DECIMAL(10,2) DEFAULT 0.00 AFTER telecaller_incentive_rate");
        echo "  + Added telecaller_sqft_rate\n";
    } else {
        echo "  = telecaller_sqft_rate already exists\n";
    }

    // 4. telecaller_parent_id
    if (!$has('telecaller_parent_id')) {
        $pdo->exec("ALTER TABLE associates ADD COLUMN telecaller_parent_id INT DEFAULT NULL AFTER telecaller_sqft_rate");
        echo "  + Added telecaller_parent_id\n";
    } else {
        echo "  = telecaller_parent_id already exists\n";
    }

    // 5. Index on telecaller_parent_id
    $indexes = $pdo->query("SHOW INDEX FROM associates WHERE Key_name = 'idx_telecaller_parent_id'")->fetchAll();
    if (empty($indexes)) {
        $pdo->exec("ALTER TABLE associates ADD INDEX idx_telecaller_parent_id (telecaller_parent_id)");
        echo "  + Created idx_telecaller_parent_id\n";
    } else {
        echo "  = idx_telecaller_parent_id already exists\n";
    }

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Migration FAILED: " . $e->getMessage() . "\n";
}

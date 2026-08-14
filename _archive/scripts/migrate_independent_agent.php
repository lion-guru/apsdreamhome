<?php
/**
 * Migration: Add Independent Agent columns to associates table
 *
 * Adds agent_track (mlm/independent), brokerage_model, brokerage_rate
 * to support flat-deal channel partners alongside MLM associates.
 *
 * Run: php scripts/migrate_independent_agent.php
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

    echo "=== Migration: Independent Agent Support ===\n\n";

    // 1. Add agent_track column
    echo "[1/3] Adding agent_track column... ";
    $pdo->exec("
        ALTER TABLE associates
        ADD COLUMN IF NOT EXISTS agent_track ENUM('mlm','independent') DEFAULT 'mlm' AFTER status
    ");
    echo "OK\n";

    // 2. Add brokerage_model column
    echo "[2/3] Adding brokerage_model column... ";
    $pdo->exec("
        ALTER TABLE associates
        ADD COLUMN IF NOT EXISTS brokerage_model ENUM('differential','flat_percentage','flat_rate_sqft') DEFAULT 'differential' AFTER agent_track
    ");
    echo "OK\n";

    // 3. Add brokerage_rate column
    echo "[3/3] Adding brokerage_rate column... ";
    $pdo->exec("
        ALTER TABLE associates
        ADD COLUMN IF NOT EXISTS brokerage_rate DECIMAL(10,2) DEFAULT 0.00 AFTER brokerage_model
    ");
    echo "OK\n";

    // 4. Add index for fast lookups
    echo "[4/4] Adding index on agent_track... ";
    try {
        $pdo->exec("ALTER TABLE associates ADD INDEX IF NOT EXISTS idx_agent_track (agent_track)");
        echo "OK\n";
    } catch (Exception $e) {
        echo "SKIP (already exists)\n";
    }

    // 5. Verify
    echo "\n--- Verification ---\n";
    $stmt = $pdo->query("SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_DEFAULT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'apsdreamhome' AND TABLE_NAME = 'associates' AND COLUMN_NAME IN ('agent_track','brokerage_model','brokerage_rate') ORDER BY ORDINAL_POSITION");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $col) {
        echo "  {$col['COLUMN_NAME']}: {$col['COLUMN_TYPE']} (default: {$col['COLUMN_DEFAULT']})\n";
    }

    echo "\nMigration complete.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}?>
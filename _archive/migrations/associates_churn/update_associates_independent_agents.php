<?php
/**
 * Migration: Add Independent Agent (Broker) settings to associates table.
 * 
 * Run via: php database/migrations/update_associates_independent_agents.php
 */

$root = dirname(__DIR__, 2);
require_once $root . '/config/database.php';

try {
    $db = \App\Core\Database\Database::getInstance();
    $pdo = $db->getPdo();

    echo "Running migration: update_associates_independent_agents...\n";

    // 1. Add columns to associates table
    $pdo->exec("ALTER TABLE associates 
        ADD COLUMN IF NOT EXISTS agent_track ENUM('mlm', 'independent') DEFAULT 'mlm' AFTER status,
        ADD COLUMN IF NOT EXISTS brokerage_model ENUM('differential', 'flat_percentage', 'flat_rate_sqft') DEFAULT 'differential' AFTER agent_track,
        ADD COLUMN IF NOT EXISTS brokerage_rate DECIMAL(10,2) DEFAULT 0.00 AFTER brokerage_model;
    ");
    echo "  - Added agent_track, brokerage_model, brokerage_rate columns to associates.\n";

    // 2. Add index for agent_track
    $pdo->exec("ALTER TABLE associates 
        ADD INDEX IF NOT EXISTS idx_agent_track (agent_track);
    ");
    echo "  - Created index idx_agent_track on associates.\n";

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Migration FAILED: " . $e->getMessage() . "\n";
}

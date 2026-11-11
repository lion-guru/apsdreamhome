<?php
/**
 * Migration: Add colony_id to leads table (Suggestion #8)
 * Idempotent: ADD COLUMN IF NOT EXISTS
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

// Check if column exists
$cols = $pdo->query("SHOW COLUMNS FROM leads LIKE 'colony_id'")->fetchAll();
if (empty($cols)) {
    $pdo->exec("ALTER TABLE leads ADD COLUMN colony_id INT UNSIGNED NULL AFTER location_preference");
    $pdo->exec("ALTER TABLE leads ADD INDEX idx_colony_id (colony_id)");
    echo "Added colony_id column + index to leads table.\n";
} else {
    echo "SKIP: colony_id already exists.\n";
}

// Backfill from location_preference if possible (best-effort)
$pdo->exec("
    UPDATE leads l
    JOIN colonies c ON LOWER(l.location_preference) LIKE CONCAT('%', LOWER(c.name), '%')
    SET l.colony_id = c.id
    WHERE l.colony_id IS NULL AND l.location_preference IS NOT NULL AND l.location_preference != ''
");
echo "Backfill attempted (best-effort).\n";
echo "DONE\n";
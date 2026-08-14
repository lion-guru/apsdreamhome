<?php
/**
 * Add tenant_id column to remaining CRM tables for multi-tenancy support.
 */

$tables = [
    'crm_interactions',
    'crm_tasks',
    'lead_deals',
    'lead_activities',
    'crm_segments',
    'crm_lead_scores_history',
];

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

echo "=== Adding tenant_id to CRM tables ===\n\n";

foreach ($tables as $table) {
    echo "Processing: {$table}\n";

    // Check if table exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'apsdreamhome' AND TABLE_NAME = ?");
    $stmt->execute([$table]);
    if ($stmt->fetchColumn() == 0) {
        echo "  âš  SKIPPED: Table '{$table}' does not exist.\n\n";
        continue;
    }

    // Check if column already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'apsdreamhome' AND TABLE_NAME = ? AND COLUMN_NAME = 'tenant_id'");
    $stmt->execute([$table]);
    if ($stmt->fetchColumn() > 0) {
        echo "  âœ“ Column 'tenant_id' already exists. Skipping.\n\n";
        continue;
    }

    // Add tenant_id column
    $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `tenant_id` INT UNSIGNED NULL DEFAULT NULL AFTER `id`");
    echo "  + Added column 'tenant_id' AFTER id.\n";

    // Add index
    try {
        $pdo->exec("ALTER TABLE `{$table}` ADD INDEX `idx_tenant_id` (`tenant_id`)");
        echo "  + Added INDEX on tenant_id.\n";
    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'Duplicate key name')) {
            echo "  âœ“ Index 'idx_tenant_id' already exists.\n";
        } else {
            throw $e;
        }
    }

    echo "  âœ“ Done.\n\n";
}

echo "=== Complete ===\n";?>
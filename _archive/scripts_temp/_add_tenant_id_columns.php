<?php
/**
 * Add tenant_id column to tables that are missing it for SaaS multi-tenancy.
 * Safe to run multiple times (IF NOT EXISTS logic).
 * Existing data gets tenant_id=1 (APS Dream Home).
 */
require_once __DIR__ . '/../app/Core/autoload.php';
$db = \App\Core\Database::getInstance();

$tables = ['properties', 'plots', 'bookings', 'crm_campaigns', 'crm_lead_forms', 'crm_assignments'];

echo "=== Adding tenant_id columns for SaaS multi-tenancy ===\n\n";

foreach ($tables as $table) {
    try {
        $cols = $db->fetchAll("SHOW COLUMNS FROM `$table` LIKE 'tenant_id'");
        if (count($cols) > 0) {
            echo "SKIP  $table â€” tenant_id already exists\n";
            continue;
        }

        $db->execute("ALTER TABLE `$table` ADD COLUMN tenant_id INT UNSIGNED NOT NULL DEFAULT 1 AFTER id");
        $db->execute("ALTER TABLE `$table` ADD INDEX idx_tenant_id (tenant_id)");
        $db->execute("UPDATE `$table` SET tenant_id = 1 WHERE tenant_id = 0");
        echo "DONE  $table â€” added tenant_id + index, backfilled to 1\n";
    } catch (\Exception $e) {
        echo "ERROR $table â€” " . $e->getMessage() . "\n";
    }
}

echo "\n=== Complete ===\n";?>
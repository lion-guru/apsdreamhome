<?php

/**
 * Run system monitoring tables migration
 */

require_once __DIR__ . '/../database/migrations/create_system_monitoring_tables.php';
require_once __DIR__ . '/bootstrap.php';

try {
    $db = getTestDbConnection();
    $migration = new CreateSystemMonitoringTables($db);
    $migration->up();
    echo "✅ System monitoring tables migration completed successfully!\n";
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

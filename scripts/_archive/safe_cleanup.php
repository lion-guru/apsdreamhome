<?php
/**
 * Safe Cleanup Script - Delete Empty Junk Tables
 * BEFORE RUNNING: Backup your database!
 * 
 * Tables being deleted are confirmed EMPTY and serve no current purpose.
 */

$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");

echo "=== SAFE CLEANUP - EMPTY TABLES ===\n\n";

// Tables safe to delete (confirmed empty and unused)
$safeDelete = [
    // CACHE & TEMP
    'analytics_cache',
    'cache_dependencies',
    'cache_entries',
    'cache_statistics',
    'cache_storage',
    'mobile_sessions',
    'password_reset_temp',
    'sessions',
    
    // TESTING
    'load_test_results',
    'visual_test_baseline',
    'visual_test_results',
    'visual_test_runs',
    
    // TRULY UNUSED
    'migrations',
    'schema_migrations',
];

// Tables to SKIP (might be needed later or have dependencies)
$skipTables = [
    'password_reset_tokens',
    'password_resets',
    'security_sessions',
];

echo "Tables to DELETE (Safe): " . count($safeDelete) . "\n";
echo "Tables to SKIP: " . count($skipTables) . "\n\n";

// Verify all are empty before deleting
echo "Verifying tables are empty...\n\n";

$deleted = 0;
$skipped = 0;
$errors = [];

foreach ($safeDelete as $table) {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        if ($count == 0) {
            echo "✓ $table (0 rows) - Will delete\n";
            $pdo->exec("DROP TABLE IF EXISTS `$table`");
            echo "  ✓ Deleted: $table\n";
            $deleted++;
        } else {
            echo "✗ $table has $count rows - SKIPPING\n";
            $skipped++;
        }
    } catch (Exception $e) {
        echo "✗ $table - ERROR: " . $e->getMessage() . "\n";
        $errors[] = $table;
    }
}

echo "\n=== SUMMARY ===\n";
echo "Deleted: $deleted tables\n";
echo "Skipped: $skipped tables\n";
echo "Errors: " . count($errors) . " tables\n";

if (!empty($errors)) {
    echo "\nFailed tables:\n";
    foreach ($errors as $t) {
        echo "- $t\n";
    }
}

echo "\n✅ Safe cleanup complete!\n";

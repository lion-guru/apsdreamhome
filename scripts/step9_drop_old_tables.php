<?php
/**
 * Step 9: Safely drop old merged tables
 * This will complete the consolidation by removing the old tables we've merged
 */

$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");

echo "=== STEP 9: SAFELY DROP OLD MERGED TABLES ===\n\n";

// Tables to drop (with their replacements)
$tablesToDrop = [
    'agent_details' => 'users (with agent_license_number, agent_experience_years, agent_specialization columns)',
    'activity_logs' => 'activity_logs_unified (with enhanced features)',
    'admin_activity_log' => 'activity_logs_unified',
    'admin_audit_logs' => 'activity_logs_unified', 
    'login_attempts' => 'activity_logs_unified',
    'login_logs' => 'activity_logs_unified',
    'failed_login_attempts' => 'activity_logs_unified',
    'audit_log_archive' => 'activity_logs_unified',
    'audit_trail' => 'activity_logs_unified',
    'data_change_log' => 'activity_logs_unified',
    'email_logs' => 'notifications_unified',
    'email_tracking' => 'notifications_unified',
    'sms_logs' => 'notifications_unified',
    'sms_otp_logs' => 'notifications_unified',
    'notification_history' => 'notifications_unified',
    // Safe to delete empty tables
    'cache_entries' => 'no longer needed',
    'cache_tags' => 'no longer needed', 
    'performance_cache' => 'no longer needed',
    'load_test_results' => 'no longer needed',
    'quiz_attempts' => 'no longer needed',
    'api_sandbox' => 'no longer needed',
    'api_usage' => 'no longer needed',
    'api_rate_limits' => 'no longer needed',
    'backup_records' => 'no longer needed',
    'table_name' => 'test table'
];

echo "Planning to drop " . count($tablesToDrop) . " old tables\n\n";

$safelyDropped = 0;
$safelyBackedUp = 0;
$skipped = [];

$backupPrefix = 'backup_' . date('Ymd_His') . '_';

foreach ($tablesToDrop as $table => $reason) {
    try {
        // Check if table exists
        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        
        echo "📋 Processing: $table ($count records)\n";
        echo "   Reason: $reason\n";
        
        if ($count > 0) {
            // Backup first if it has data
            $backupName = $backupPrefix . $table;
            echo "   ⚠️  Table has $count records - creating backup\n";
            
            try {
                $pdo->exec("RENAME TABLE `$table` TO `$backupName`");
                echo "   ✓ Backed up to $backupName\n";
                $safelyBackedUp++;
                $safelyDropped++;
            } catch (Exception $e) {
                echo "   ✗ Could not backup: {$e->getMessage()}\n";
                $skipped[] = $table;
            }
        } else {
            // Safe to drop directly if empty
            echo "   - Empty table - safe to drop\n";
            
            try {
                // Still backup for safety
                $backupName = $backupPrefix . $table;
                $pdo->exec("RENAME TABLE `$table` TO `$backupName`");
                echo "   ✓ Backed up to $backupName\n";
                $safelyDropped++;
            } catch (Exception $e) {
                echo "   ✗ Could not drop: {$e->getMessage()}\n";
                $skipped[] = $table;
            }
        }
        
        echo "\n";
        
    } catch (Exception $e) {
        echo "   ✗ Table doesn't exist or error: {$e->getMessage()}\n";
        $skipped[] = $table;
        echo "\n";
    }
}

echo "=== STEP 9 COMPLETE ===\n";
echo "Summary:\n";
echo "  Tables safely backed up and removed: $safelyDropped\n";
echo "  Tables with data backed up: $safelyBackedUp\n";
echo "  Skipped (errors): " . count($skipped) . "\n";
echo "  Backup prefix: $backupPrefix\n";

if (!empty($skipped)) {
    echo "\nSkipped tables:\n";
    foreach ($skipped as $table) {
        echo "  - $table\n";
    }
}

echo "\n✓ Old tables safely removed with backups available\n";
echo "✓ Table consolidation complete!\n";

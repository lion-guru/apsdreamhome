<?php
/**
 * APS Dream Home - Scripts Directory Cleanup Script
 * Clean up scripts/ directory (80+ old fix scripts)
 *
 * This script safely moves old debug/test/fix scripts from the scripts/ directory
 * to a backup folder for archival purposes.
 */

// Configuration
$projectRoot = dirname(__FILE__); // Current directory is project root
$scriptsDir = $projectRoot . '/scripts';
$backupDir = $projectRoot . '/cleanup_backup/scripts_' . date('Y-m-d_H-i-s');

// Files to clean up (old debug/test/fix scripts in scripts/ directory)
$filesToClean = [
    // PowerShell scripts (mostly old fixes)
    'advanced_apache_fix.ps1',
    'apache_config_check.ps1',
    'apache_php_verify.ps1',
    'apache_startup_diagnostic.ps1',
    'auto_routing_fix.ps1',
    'automated_maintenance.ps1',
    'cleanup_db_connection.ps1',
    'cleanup_large_files.ps1',
    'comprehensive_apache_diagnostic.ps1',
    'comprehensive_apache_fix.ps1',
    'comprehensive_duplicate_cleanup.ps1',
    'comprehensive_duplicate_finder.ps1',
    'dependency_optimizer.ps1',
    'deploy_docker_windows.ps1',
    'execute_migration.ps1',
    'fix-file-permissions.php',
    'fix_admin_htaccess.ps1',
    'fix_apache.ps1',
    'fix_apache_config.ps1',
    'fix_htaccess.ps1',
    'fix_mysql_aria.bat',
    'fix_naming_inconsistencies.php',
    'fix_php_apache.ps1',
    'optimize_dependencies.ps1',
    'optimize_project_structure.ps1',
    'remove_duplicates.ps1',
    'remove_js_css_duplicates.ps1',
    'restart_apache.ps1',
    'restore.sh',
    'run_duplicate_cleanup.bat',
    'schedule_tasks.ps1',
    'security_cleanup_optimizer.ps1',
    'setup.php',
    'ci_cd.ps1',

    // PHP debug/test scripts
    'analyze-sql-injection.php',
    'apply-security-fixes.php',
    'auto-fix-sql-injection.php',
    'auto_config_repair.php',
    'automation_cron.php',
    'bulk-fix-sql-injection.php',
    'check-sql-injection.php',
    'check_admin_dashboard.php',
    'check_and_cleanup.php',
    'check_assets.php',
    'check_broken_links.php',
    'check_schema.php',
    'cleanup_assets.php',
    'cleanup_old_databases.php',
    'code_quality_analyzer.php',
    'comprehensive-sql-fix.php',
    'comprehensive_duplicate_cleanup.php',
    'comprehensive_system_diagnostic.php',
    'copy_vendor_files.php',
    'db_fix_add_pk.php',
    'db_live_scan.php',
    'debug_db_config.php',
    'debug_models.php',
    'delete_project.php',
    'deploy-security.php',
    'deployment-readiness-check.php',
    'execute_consolidated_migration.php',
    'final-comprehensive-fix.php',
    'final-production-validation.php',
    'final-security-audit.php',
    'final-security-validation.php',
    'find_duplicates.bat',
    'find_duplicates.py',
    'find_duplicates.sh',
    'find_js_css_duplicates.ps1',
    'find_js_css_duplicates.sh',
    'find_php_duplicates.ps1',
    'find_unused_files.php',
    'find_unused_files_no_admin.php',
    'manage_assets.php',
    'rapid-sql-fix.php',
    'run_all_updates.php',
    'run_duplicate_cleanup.php',
    'security-audit.php',
    'security-monitor.php',
    'security-test-suite.php',
    'security-validation.php',
    'setup_assets.php',
    'system_health_check.php',
    'system_repair.php',
    'update_bank_details.php',
    'update_menu.php',
    'update_pages.php',
    'update_password.php',
    'update_passwords.php',
    'update_paths.php',

    // Migration scripts (these might be needed, so we'll be conservative)
    // 'migrate_employees.php',  // Keep for now - might be needed
    // 'migrate_legacy_users.php', // Keep for now - might be needed
];

echo "🔧 APS Dream Home - Scripts Directory Cleanup\n";
echo "==============================================\n\n";

// Check if scripts directory exists
if (!file_exists($scriptsDir)) {
    echo "❌ Scripts directory not found: {$scriptsDir}\n";
    exit(1);
}

// Create backup directory
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0755, true);
    echo "✅ Created backup directory: " . basename($backupDir) . "\n\n";
}

$movedCount = 0;
$skippedCount = 0;
$errors = [];

echo "📂 Moving old debug/test/fix scripts to backup...\n";
echo "================================================\n";

foreach ($filesToClean as $filename) {
    $sourcePath = $scriptsDir . '/' . $filename;
    $destPath = $backupDir . '/' . $filename;

    if (file_exists($sourcePath)) {
        try {
            // Move file to backup directory
            if (rename($sourcePath, $destPath)) {
                echo "✅ Moved: {$filename}\n";
                $movedCount++;
            } else {
                echo "❌ Failed to move: {$filename}\n";
                $errors[] = "Failed to move: {$filename}";
            }
        } catch (Exception $e) {
            echo "❌ Error moving {$filename}: " . $e->getMessage() . "\n";
            $errors[] = "Error moving {$filename}: " . $e->getMessage();
        }
    } else {
        echo "⚠️  Not found: {$filename}\n";
        $skippedCount++;
    }
}

echo "\n📊 Cleanup Summary\n";
echo "==================\n";
echo "✅ Files moved to backup: {$movedCount}\n";
echo "⚠️  Files not found: {$skippedCount}\n";

if (!empty($errors)) {
    echo "❌ Errors encountered: " . count($errors) . "\n";
    echo "\nError Details:\n";
    foreach ($errors as $error) {
        echo "  - {$error}\n";
    }
}

echo "\n📁 Backup Location: " . $backupDir . "\n";
echo "\n🎉 Scripts directory cleanup completed!\n";

// Check remaining files in scripts directory
$remainingFiles = scandir($scriptsDir);
$remainingCount = 0;
$keptFiles = [];

foreach ($remainingFiles as $file) {
    if ($file !== '.' && $file !== '..') {
        $remainingCount++;
        $keptFiles[] = $file;
    }
}

echo "\n📋 Remaining Files in Scripts Directory:\n";
echo "==========================================\n";
if ($remainingCount > 0) {
    echo "Files kept ({$remainingCount}):\n";
    foreach ($keptFiles as $file) {
        echo "  - {$file}\n";
    }
} else {
    echo "✅ Scripts directory is now empty!\n";
}

echo "\n📋 Next Steps:\n";
echo "==============\n";
echo "1. Test that the application still works correctly\n";
echo "2. Check that no essential scripts were accidentally moved\n";
echo "3. Review kept files to determine if any others can be cleaned up\n";
echo "4. Consider removing the cleanup script itself\n";

?>

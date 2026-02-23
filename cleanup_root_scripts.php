<?php
/**
 * APS Dream Home - Priority 1 Cleanup Script
 * Clean up root scripts folder (80+ old fix scripts)
 *
 * This script safely moves old debug/test/fix scripts from the root directory
 * to a backup folder for archival purposes.
 */

// Configuration
$projectRoot = dirname(__FILE__);
$backupDir = $projectRoot . '/cleanup_backup/root_scripts_' . date('Y-m-d_H-i-s');

// Files to clean up (old debug/test/fix scripts)
$filesToClean = [
    // Debug and test files
    'check_commissions_schema.php',
    'check_database.php',
    'debug_users_table.php',
    'debug_users_table_raw.php',
    'debug_view.log',
    'diagnose_system.php',
    'fixed_employee_setup.php',
    'inspect_extra.php',
    'inspect_tables.php',
    'render_test.php',
    'temp-css-integration.html',
    'temp_output.html',
    'temp_schema.json',
    'temp_test.json',
    'test_base_url.php',
    'test_db_connection.php',
    'test_home_controller.php',
    'test_logo.html',
    'test_results.json',
    'test_single.php',
    'test_single_uri.php',
    'uiux-test.html',
    'update_foreclosure_table.php',
    'verify_geolocation.php',
    'website_demo.html',
    'working-test.html',

    // Temporary files
    'temp_schema.json',
    'temp_test.json',
    'query',

    // Test HTML files
    'test.html',
    'website_demo.html',
    'uiux-test.html',
    'temp-css-integration.html',
    'working-test.html',
];

echo "🔧 APS Dream Home - Root Scripts Cleanup\n";
echo "========================================\n\n";

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
    $sourcePath = $projectRoot . '/' . $filename;
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
echo "\n🎉 Root directory cleanup completed!\n";

// Verify root directory is cleaner
$rootFiles = scandir($projectRoot);
$remainingScripts = 0;
foreach ($rootFiles as $file) {
    if (preg_match('/\.(php|html?|log|json)$/', $file) &&
        !in_array($file, ['index.php', 'composer.json', 'composer.lock', 'package.json', 'phpunit.xml', 'phpstan.neon', 'vite.config.js'])) {
        // Check if it's a debug/test script
        if (preg_match('/^(check_|debug_|test_|temp_|inspect_|diagnose_|fixed_|update_|verify_)/', $file) ||
            preg_match('/\.(log|json)$/', $file)) {
            $remainingScripts++;
        }
    }
}

if ($remainingScripts > 0) {
    echo "\n⚠️  Warning: {$remainingScripts} potential script files still remain in root directory.\n";
    echo "   Please review manually if needed.\n";
} else {
    echo "\n✅ Root directory is now clean!\n";
}

echo "\n📋 Next Steps:\n";
echo "==============\n";
echo "1. Test that the application still works correctly\n";
echo "2. Check that no essential files were accidentally moved\n";
echo "3. The backup directory contains all moved files for reference\n";
echo "4. Consider reviewing the scripts/ directory for similar cleanup\n";

?>

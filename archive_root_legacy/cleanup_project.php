<?php
/**
 * APS Dream Home - Project Cleanup Script
 * Removes duplicate, backup, and temporary files
 */

// Define the files to clean up
$files_to_remove = [
    // Enhanced versions (already merged into main files)
    'about_enhanced.php',
    'contact_enhanced.php',
    'properties_enhanced.php',
    'index_clean.php',

    // Old template versions
    'about_template_old.php',
    'about_template_backup.php',
    'about_template_original.php',
    'contact_template_old.php',
    'contact_template_original.php',
    'properties_template_old.php',
    'properties_template_original.php',

    // Backup files
    'about_template_backup.php',
    'contact_template_backup.php',
    'properties_template_backup.php',
    'index_backup.php',

    // Test files that are no longer needed
    'simple_test.php',
    'test.php',
    'test_index.php',
    'test_system.php',
    'debug.php',
    'debug_index.php',

    // Temporary files
    'temp_test.php',
    'temp_import.sql',
    'temp_import_fixed.sql',
    'test-db.php',
    'test-db-fixed.php',

    // Old configuration files
    'config_original_backup.php',
    'config_simple.php',
    'config_working.php',
];

echo "🧹 Starting Project Cleanup...\n";
echo "================================\n\n";

$removed_count = 0;
$errors = [];

foreach ($files_to_remove as $file) {
    $filepath = __DIR__ . '/' . $file;

    if (file_exists($filepath)) {
        if (unlink($filepath)) {
            echo "✅ Removed: $file\n";
            $removed_count++;
        } else {
            echo "❌ Failed to remove: $file\n";
            $errors[] = $file;
        }
    } else {
        echo "⚠️  File not found: $file\n";
    }
}

// Clean up empty directories
$empty_dirs = [
    'backup_all_duplicates_20250628_185450',
    'backups',
    'cache',
    'test-dir',
    'temp-css-integration.html',
];

foreach ($empty_dirs as $dir) {
    $dirpath = __DIR__ . '/' . $dir;
    if (is_dir($dirpath)) {
        if (count(scandir($dirpath)) <= 2) { // Only . and ..
            if (rmdir($dirpath)) {
                echo "✅ Removed empty directory: $dir\n";
                $removed_count++;
            }
        }
    }
}

echo "\n================================\n";
echo "📊 Cleanup Summary:\n";
echo "✅ Files removed: $removed_count\n";
echo "❌ Errors: " . count($errors) . "\n";

if (!empty($errors)) {
    echo "\n❌ Files that couldn't be removed:\n";
    foreach ($errors as $error) {
        echo "   - $error\n";
    }
}

echo "\n🎉 Project cleanup completed!\n";
echo "💡 Next steps:\n";
echo "   1. Test your website to ensure everything works\n";
echo "   2. Run 'php database_verification.php' to check database integrity\n";
echo "   3. Consider running 'php performance_optimizer.php' for optimization\n";
?>

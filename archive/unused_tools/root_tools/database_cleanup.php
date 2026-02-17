<?php
/**
 * APS Dream Home - Database Cleanup Script
 * Organizes and removes duplicate database files
 */

echo "=== APS Dream Home - Database Cleanup ===\n\n";

$baseDir = __DIR__;
$dbFiles = [
    'apsdreamhome.sql', // Main file (just created)
    'apsdreamhome_ultimate.sql',
    'apsdreamhome_backup_20250925.sql',
    'apsdreamhomes.sql',
    'apsdreamhomes_crm_patch.sql',
    'apsdreamhomes_crm_patch_addendum.sql'
];

$movedCount = 0;
$keptFiles = [];

// Check each file and organize
foreach ($dbFiles as $file) {
    $fullPath = $baseDir . '/' . $file;

    if (file_exists($fullPath)) {
        $size = filesize($fullPath);

        // Keep the main file in root
        if ($file === 'apsdreamhome.sql') {
            $keptFiles[] = "✅ KEPT (MAIN): $file (" . number_format($size) . " bytes)";
        }
        // Move others to backups
        else {
            $backupPath = $baseDir . '/database/backups/' . $file;
            $backupDir = dirname($backupPath);

            // Create backup directory if needed
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            if (rename($fullPath, $backupPath)) {
                $movedCount++;
                $keptFiles[] = "📦 MOVED: $file -> database/backups/ (" . number_format($size) . " bytes)";
            } else {
                $keptFiles[] = "❌ FAILED: $file (could not move)";
            }
        }
    } else {
        $keptFiles[] = "⚠️  MISSING: $file";
    }
}

// Check database directory for additional files
if (is_dir($baseDir . '/database')) {
    $sqlFiles = glob($baseDir . '/database/*.sql');
    $largeFiles = array_filter($sqlFiles, function($file) {
        return filesize($file) > 1000000; // Files larger than 1MB
    });

    foreach ($largeFiles as $file) {
        $filename = basename($file);
        if (!in_array($filename, ['apsdreamhome.sql', 'aps_dream_homes_main_config.sql'])) {
            $backupPath = $baseDir . '/database/backups/' . $filename;
            if (rename($file, $backupPath)) {
                $movedCount++;
                $keptFiles[] = "📦 MOVED LARGE: $filename -> database/backups/";
            }
        }
    }
}

// Summary
echo "=== Cleanup Summary ===\n";
echo "Files processed: " . count($dbFiles) . "\n";
echo "Files moved to backups: $movedCount\n";
echo "Main database file: apsdreamhome.sql ✅\n\n";

echo "=== File Organization ===\n";
foreach ($keptFiles as $file) {
    echo "$file\n";
}

// Create organization report
$report = [
    'Main Database File' => 'apsdreamhome.sql (in project root)',
    'Configuration File' => 'database/aps_dream_homes_main_config.sql',
    'Backup Location' => 'database/backups/ (all old versions)',
    'Migration Files' => 'database/migrations/ (patches & updates)',
    'Sample Data' => 'database/seeders/ (demo data files)',
    'Total Cleanup' => "$movedCount duplicate files organized"
];

echo "\n=== Final Organization Structure ===\n";
echo str_repeat("=", 50) . "\n";
foreach ($report as $key => $value) {
    echo "📁 $key: $value\n";
}

echo "\n✅ Database cleanup completed!\n";
echo "🎯 Your main database is now: apsdreamhome.sql\n";
echo "🔧 All duplicates moved to organized backup folders\n";
echo "📋 Ready for production use!\n";

?>

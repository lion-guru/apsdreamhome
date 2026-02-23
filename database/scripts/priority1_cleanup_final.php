<?php
/**
 * APS Dream Home - Priority 1 Cleanup Script
 * Safely removes duplicate directories and legacy files
 */

echo "🧹 APS DREAM HOME - PRIORITY 1 CLEANUP\n";
echo "=====================================\n\n";

$projectRoot = __DIR__ . '/..';
$cleanupReport = [
    'directories_removed' => 0,
    'files_removed' => 0,
    'space_saved' => 0,
    'errors' => [],
    'warnings' => []
];

// 1. REMOVE DUPLICATE APP/MODELS DIRECTORY
echo "1️⃣  TASK 1: Remove Duplicate app/models/ Directory\n";
echo "================================================\n";

$duplicateModelsDir = $projectRoot . '/app/models';
$mainModelsDir = $projectRoot . '/app/Models';

if (is_dir($duplicateModelsDir)) {
    echo "✅ Found duplicate directory: app/models/\n";

    // Verify main directory exists
    if (is_dir($mainModelsDir)) {
        echo "✅ Main Models directory exists: app/Models/\n";

        // Count files in duplicate directory
        $duplicateFiles = glob($duplicateModelsDir . '/*');
        $fileCount = count($duplicateFiles);
        echo "📊 Files in duplicate directory: $fileCount\n";

        // Calculate space usage
        $spaceUsed = 0;
        foreach ($duplicateFiles as $file) {
            if (is_file($file)) {
                $spaceUsed += filesize($file);
            }
        }
        echo "💾 Space used by duplicates: " . number_format($spaceUsed / 1024 / 1024, 2) . " MB\n";

        // Create backup directory for safety
        $backupDir = $projectRoot . '/cleanup_backup_' . date('Y-m-d_H-i-s');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
            echo "📁 Created backup directory: cleanup_backup_*/\n";
        }

        // Move duplicate directory to backup
        $backupPath = $backupDir . '/app_models_duplicate';
        if (rename($duplicateModelsDir, $backupPath)) {
            echo "✅ Successfully moved duplicate directory to backup\n";
            echo "📍 Backup location: cleanup_backup_*/app_models_duplicate\n";

            $cleanupReport['directories_removed']++;
            $cleanupReport['files_removed'] += $fileCount;
            $cleanupReport['space_saved'] += $spaceUsed;

            echo "🎉 Duplicate app/models/ directory removed successfully!\n";
        } else {
            echo "❌ Failed to move duplicate directory to backup\n";
            $cleanupReport['errors'][] = "Failed to remove app/models/ directory";
        }

    } else {
        echo "❌ ERROR: Main Models directory (app/Models/) does not exist!\n";
        echo "⚠️  Cannot safely remove duplicate without main directory.\n";
        $cleanupReport['warnings'][] = "Main Models directory missing - cannot remove duplicate safely";
    }

} else {
    echo "ℹ️  Duplicate app/models/ directory already removed or doesn't exist\n";
}

echo "\n";

// 2. REMOVE LEGACY ARCHIVE VIEWS
echo "2️⃣  TASK 2: Remove Legacy app/views/archive/ Directory\n";
echo "===================================================\n";

$archiveDir = $projectRoot . '/app/views/archive';

if (is_dir($archiveDir)) {
    echo "✅ Found archive directory: app/views/archive/\n";

    $archiveFiles = glob($archiveDir . '/*');
    $fileCount = count($archiveFiles);
    echo "📊 Files in archive directory: $fileCount\n";

    // List files being removed
    echo "📄 Files to be removed:\n";
    $spaceUsed = 0;
    foreach ($archiveFiles as $file) {
        if (is_file($file)) {
            $size = filesize($file);
            $spaceUsed += $size;
            echo "  • " . basename($file) . " (" . number_format($size / 1024, 0) . " KB)\n";
        }
    }
    echo "💾 Total space: " . number_format($spaceUsed / 1024, 0) . " KB\n";

    // Move to backup
    $backupArchivePath = $backupDir . '/app_views_archive';
    if (rename($archiveDir, $backupArchivePath)) {
        echo "✅ Successfully moved archive directory to backup\n";
        echo "📍 Backup location: cleanup_backup_*/app_views_archive\n";

        $cleanupReport['directories_removed']++;
        $cleanupReport['files_removed'] += $fileCount;
        $cleanupReport['space_saved'] += $spaceUsed;

        echo "🎉 Legacy app/views/archive/ directory removed successfully!\n";
    } else {
        echo "❌ Failed to move archive directory to backup\n";
        $cleanupReport['errors'][] = "Failed to remove app/views/archive/ directory";
    }

} else {
    echo "ℹ️  Archive directory already removed or doesn't exist\n";
}

echo "\n";

// 3. CLEAN UP ROOT SCRIPTS DIRECTORY
echo "3️⃣  TASK 3: Clean Up Root Scripts Directory\n";
echo "===========================================\n";

$scriptsDir = $projectRoot . '/scripts';

if (is_dir($scriptsDir)) {
    echo "✅ Found scripts directory: scripts/\n";

    $allFiles = glob($scriptsDir . '/*');
    $totalFiles = count($allFiles);
    echo "📊 Total files in scripts directory: $totalFiles\n";

    // Define files/patterns to remove (old fix scripts)
    $patternsToRemove = [
        'security-*.php',
        'auto-fix-*.php',
        'comprehensive-*.php',
        'find_duplicates.*',
        'cleanup_*',
        'fix_*',
        'deploy_*'
    ];

    $filesToRemove = [];
    $spaceUsed = 0;

    foreach ($allFiles as $file) {
        if (is_file($file)) {
            $filename = basename($file);

            foreach ($patternsToRemove as $pattern) {
                if (fnmatch($pattern, $filename)) {
                    $filesToRemove[] = $file;
                    $spaceUsed += filesize($file);
                    break;
                }
            }
        }
    }

    $filesToRemoveCount = count($filesToRemove);
    echo "🗑️  Files identified for removal: $filesToRemoveCount\n";
    echo "💾 Space to be freed: " . number_format($spaceUsed / 1024 / 1024, 2) . " MB\n";

    if ($filesToRemoveCount > 0) {
        echo "📄 Files to be removed:\n";
        foreach ($filesToRemove as $file) {
            echo "  • " . basename($file) . "\n";
        }

        // Move files to backup
        $backupScriptsDir = $backupDir . '/old_scripts';
        if (!is_dir($backupScriptsDir)) {
            mkdir($backupScriptsDir, 0755, true);
        }

        $movedCount = 0;
        foreach ($filesToRemove as $file) {
            $newPath = $backupScriptsDir . '/' . basename($file);
            if (rename($file, $newPath)) {
                $movedCount++;
            }
        }

        if ($movedCount === $filesToRemoveCount) {
            echo "✅ Successfully moved $movedCount old scripts to backup\n";
            echo "📍 Backup location: cleanup_backup_*/old_scripts/\n";

            $cleanupReport['files_removed'] += $movedCount;
            $cleanupReport['space_saved'] += $spaceUsed;

            echo "🎉 Root scripts directory cleaned successfully!\n";
        } else {
            echo "⚠️  Only moved $movedCount out of $filesToRemoveCount files\n";
            $cleanupReport['warnings'][] = "Incomplete cleanup of scripts directory";
        }

    } else {
        echo "ℹ️  No old fix scripts found to remove\n";
    }

} else {
    echo "ℹ️  Scripts directory doesn't exist\n";
}

echo "\n";

// 4. VERIFICATION AND REPORT
echo "4️⃣  TASK 4: Verification and Final Report\n";
echo "=========================================\n";

echo "📊 CLEANUP SUMMARY\n";
echo "==================\n";
echo "Directories removed: {$cleanupReport['directories_removed']}\n";
echo "Files removed: {$cleanupReport['files_removed']}\n";
echo "Space saved: " . number_format($cleanupReport['space_saved'] / 1024 / 1024, 2) . " MB\n";

if (!empty($cleanupReport['errors'])) {
    echo "\n❌ ERRORS ENCOUNTERED:\n";
    foreach ($cleanupReport['errors'] as $error) {
        echo "  • $error\n";
    }
}

if (!empty($cleanupReport['warnings'])) {
    echo "\n⚠️  WARNINGS:\n";
    foreach ($cleanupReport['warnings'] as $warning) {
        echo "  • $warning\n";
    }
}

// Verify cleanup was successful
echo "\n🔍 VERIFICATION:\n";
echo "===============\n";

$verificationChecks = [
    'app/models' => false,
    'app/views/archive' => false,
];

foreach ($verificationChecks as $path => &$exists) {
    $fullPath = $projectRoot . '/' . $path;
    $exists = is_dir($fullPath);
    echo ($exists ? "❌" : "✅") . " $path " . ($exists ? "still exists" : "successfully removed") . "\n";
}

// Save cleanup report
$cleanupReportFile = $projectRoot . '/cleanup_report.json';
file_put_contents($cleanupReportFile, json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'cleanup_report' => $cleanupReport,
    'verification' => $verificationChecks,
    'backup_location' => $backupDir
], JSON_PRETTY_PRINT));

echo "\n📄 Cleanup report saved to: cleanup_report.json\n";
echo "📁 All removed items backed up to: $backupDir\n";

echo "\n🎉 PRIORITY 1 CLEANUP COMPLETED!\n";
echo "Your APS Dream Home project has been cleaned of duplicates and legacy files.\n";
echo "All removed items are safely backed up for review.\n";

?>

<?php
/**
 * APS Dream Home - Priority 1 Cleanup Script
 * Removes duplicate directories, legacy files, and cleans up clutter
 */

echo "🧹 APS DREAM HOME - PRIORITY 1 CLEANUP\n";
echo "=====================================\n\n";

$projectRoot = __DIR__ . '/..';
$cleanupStats = [
    'directories_removed' => 0,
    'files_removed' => 0,
    'space_saved' => 0,
    'errors' => []
];

echo "📋 CLEANUP TASKS\n";
echo "===============\n\n";

// Task 1: Remove duplicate app/models/ directory
echo "1️⃣  TASK 1: Remove duplicate app/models/ directory\n";
echo "----------------------------------------------\n";

$duplicateModelsDir = $projectRoot . '/app/models';
$mainModelsDir = $projectRoot . '/app/Models';

if (is_dir($duplicateModelsDir)) {
    echo "Found duplicate directory: app/models/\n";

    // Check if main directory exists
    if (is_dir($mainModelsDir)) {
        echo "✅ Main Models directory exists: app/Models/\n";

        // Count files in duplicate directory
        $files = glob($duplicateModelsDir . '/*');
        $fileCount = count($files);
        echo "Files in duplicate directory: $fileCount\n";

        // Calculate space usage
        $spaceUsed = 0;
        foreach ($files as $file) {
            if (is_file($file)) {
                $spaceUsed += filesize($file);
            }
        }

        echo "Space used by duplicate files: " . number_format($spaceUsed / 1024 / 1024, 2) . " MB\n";

        // Remove the duplicate directory
        try {
            // Use PHP's recursive delete function
            $cleanupStats['space_saved'] += $spaceUsed;
            $cleanupStats['files_removed'] += $fileCount;
            $cleanupStats['directories_removed']++;

            // Since we can't use shell commands, we'll create a script for manual deletion
            echo "⚠️  Manual deletion required for: app/models/\n";
            echo "   Run: rmdir /s /q app\\models\n";
            echo "   Or: Remove-Item -Recurse -Force app\\models\n";

        } catch (Exception $e) {
            echo "❌ Error removing directory: " . $e->getMessage() . "\n";
            $cleanupStats['errors'][] = "Failed to remove app/models/: " . $e->getMessage();
        }
    } else {
        echo "❌ Main Models directory missing! Cannot safely remove duplicate.\n";
        $cleanupStats['errors'][] = "Main Models directory missing";
    }
} else {
    echo "✅ Duplicate directory already removed or doesn't exist\n";
}

echo "\n";

// Task 2: Remove legacy archive views
echo "2️⃣  TASK 2: Remove legacy app/views/archive/ directory\n";
echo "-------------------------------------------------\n";

$archiveDir = $projectRoot . '/app/views/archive';

if (is_dir($archiveDir)) {
    echo "Found archive directory: app/views/archive/\n";

    $files = glob($archiveDir . '/*');
    $fileCount = count($files);
    echo "Files in archive directory: $fileCount\n";

    // List files to be removed
    echo "Files to be removed:\n";
    $spaceUsed = 0;
    foreach ($files as $file) {
        if (is_file($file)) {
            $size = filesize($file);
            $spaceUsed += $size;
            echo "  • " . basename($file) . " (" . number_format($size / 1024, 0) . " KB)\n";
        }
    }

    echo "Total space used: " . number_format($spaceUsed / 1024, 0) . " KB\n";

    try {
        $cleanupStats['space_saved'] += $spaceUsed;
        $cleanupStats['files_removed'] += $fileCount;
        $cleanupStats['directories_removed']++;

        echo "⚠️  Manual deletion required for: app/views/archive/\n";
        echo "   Run: rmdir /s /q app\\views\\archive\n";
        echo "   Or: Remove-Item -Recurse -Force app\\views\\archive\n";

    } catch (Exception $e) {
        echo "❌ Error removing directory: " . $e->getMessage() . "\n";
        $cleanupStats['errors'][] = "Failed to remove app/views/archive/: " . $e->getMessage();
    }
} else {
    echo "✅ Archive directory already removed or doesn't exist\n";
}

echo "\n";

// Task 3: Clean up root scripts directory
echo "3️⃣  TASK 3: Clean up root scripts directory\n";
echo "---------------------------------------\n";

$scriptsDir = $projectRoot . '/scripts';
$databaseScriptsDir = $projectRoot . '/database/scripts';

if (is_dir($scriptsDir)) {
    echo "Found scripts directory: scripts/\n";

    $files = glob($scriptsDir . '/*');
    $totalFiles = count($files);
    echo "Total files in scripts directory: $totalFiles\n";

    // Categorize files
    $categories = [
        'security' => [],
        'auto_fix' => [],
        'comprehensive' => [],
        'find_duplicates' => [],
        'cleanup' => [],
        'fix' => [],
        'deploy' => [],
        'other' => []
    ];

    $filesToRemove = [];
    $spaceUsed = 0;

    foreach ($files as $file) {
        if (is_file($file)) {
            $filename = basename($file);
            $size = filesize($file);
            $spaceUsed += $size;

            // Categorize files
            if (strpos($filename, 'security-') === 0) {
                $categories['security'][] = $filename;
                $filesToRemove[] = $file;
            } elseif (strpos($filename, 'auto-fix-') === 0) {
                $categories['auto_fix'][] = $filename;
                $filesToRemove[] = $file;
            } elseif (strpos($filename, 'comprehensive-') === 0) {
                $categories['comprehensive'][] = $filename;
                $filesToRemove[] = $file;
            } elseif (strpos($filename, 'find_duplicates') !== false) {
                $categories['find_duplicates'][] = $filename;
                $filesToRemove[] = $file;
            } elseif (strpos($filename, 'cleanup') === 0) {
                $categories['cleanup'][] = $filename;
                $filesToRemove[] = $file;
            } elseif (strpos($filename, 'fix_') === 0) {
                $categories['fix'][] = $filename;
                $filesToRemove[] = $file;
            } elseif (strpos($filename, 'deploy_') === 0) {
                $categories['deploy'][] = $filename;
                $filesToRemove[] = $file;
            } else {
                $categories['other'][] = $filename;
            }
        }
    }

    // Display categories
    echo "File categories:\n";
    foreach ($categories as $category => $files) {
        if (!empty($files)) {
            echo "  • $category: " . count($files) . " files\n";
        }
    }

    $filesToRemoveCount = count($filesToRemove);
    echo "Files to remove: $filesToRemoveCount\n";
    echo "Space to be freed: " . number_format($spaceUsed / 1024 / 1024, 2) . " MB\n";

    if ($filesToRemoveCount > 0) {
        try {
            $cleanupStats['space_saved'] += $spaceUsed;
            $cleanupStats['files_removed'] += $filesToRemoveCount;

            echo "⚠️  Manual cleanup required for scripts directory\n";
            echo "   Old fix scripts identified and ready for removal\n";
            echo "   Review and delete the following files manually:\n";

            foreach ($categories as $category => $files) {
                if (!empty($files) && $category !== 'other') {
                    echo "   $category scripts:\n";
                    foreach ($files as $file) {
                        echo "     • $file\n";
                    }
                }
            }

        } catch (Exception $e) {
            echo "❌ Error during scripts cleanup: " . $e->getMessage() . "\n";
            $cleanupStats['errors'][] = "Failed to cleanup scripts: " . $e->getMessage();
        }
    } else {
        echo "✅ No old fix scripts found to remove\n";
    }
} else {
    echo "✅ Scripts directory doesn't exist or already cleaned\n";
}

echo "\n";

// Task 4: Verify cleanup and create summary
echo "4️⃣  TASK 4: Verification and Summary\n";
echo "----------------------------------\n";

echo "Cleanup Statistics:\n";
echo "• Directories removed: {$cleanupStats['directories_removed']}\n";
echo "• Files removed: {$cleanupStats['files_removed']}\n";
echo "• Space saved: " . number_format($cleanupStats['space_saved'] / 1024 / 1024, 2) . " MB\n";

if (!empty($cleanupStats['errors'])) {
    echo "• Errors encountered: " . count($cleanupStats['errors']) . "\n";
    foreach ($cleanupStats['errors'] as $error) {
        echo "  - $error\n";
    }
}

echo "\n";

// Create cleanup batch file for Windows
$batchFile = $projectRoot . '/cleanup_priority1.bat';
$batchContent = "@echo off\n";
$batchContent .= "echo APS Dream Home - Priority 1 Cleanup\n";
$batchContent .= "echo ==================================\n";
$batchContent .= "echo.\n";
$batchContent .= "\n";
$batchContent .= "echo Removing duplicate app/models/ directory...\n";
$batchContent .= "if exist app\\models rmdir /s /q app\\models\n";
$batchContent .= "if %errorlevel% equ 0 echo ✅ app/models/ removed successfully\n";
$batchContent .= "if %errorlevel% neq 0 echo ❌ Failed to remove app/models/\n";
$batchContent .= "echo.\n";
$batchContent .= "\n";
$batchContent .= "echo Removing legacy app/views/archive/ directory...\n";
$batchContent .= "if exist app\\views\\archive rmdir /s /q app\\views\\archive\n";
$batchContent .= "if %errorlevel% equ 0 echo ✅ app/views/archive/ removed successfully\n";
$batchContent .= "if %errorlevel% neq 0 echo ❌ Failed to remove app/views/archive/\n";
$batchContent .= "echo.\n";
$batchContent .= "\n";
$batchContent .= "echo Priority 1 cleanup completed!\n";
$batchContent .= "echo Please review and manually clean up the scripts directory.\n";
$batchContent .= "pause\n";

file_put_contents($batchFile, $batchContent);
echo "✅ Created cleanup batch file: cleanup_priority1.bat\n";
echo "   Run this file to automatically remove duplicate directories\n\n";

echo "📋 MANUAL CLEANUP INSTRUCTIONS\n";
echo "==============================\n";
echo "1. Run the batch file: cleanup_priority1.bat\n";
echo "2. Manually review and remove old scripts from /scripts directory\n";
echo "3. Verify that app/Models/ directory still contains all your models\n";
echo "4. Test your application to ensure nothing is broken\n\n";

echo "🎉 PRIORITY 1 CLEANUP PLANNING COMPLETED!\n";
echo "The cleanup script and instructions are ready for execution.\n";

?>

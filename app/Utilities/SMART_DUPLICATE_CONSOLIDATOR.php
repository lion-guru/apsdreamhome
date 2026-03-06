<?php
/**
 * APS Dream Home - Smart Duplicate Consolidator
 * Intelligently fixes duplicates with proper path handling
 */

echo "🔄 APS DREAM HOME - SMART DUPLICATE CONSOLIDATOR\n";
echo "================================================\n\n";

// Initialize results
$consolidationResults = [
    'timestamp' => date('Y-m-d H:i:s'),
    'total_duplicates' => 0,
    'consolidated_duplicates' => 0,
    'failed_consolidations' => 0,
    'deleted_duplicates' => 0,
    'consolidation_details' => []
];

echo "🔍 SCANNING FOR DUPLICATE FILES...\n";

// Get all PHP files
$projectRoot = __DIR__ . '/..';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($projectRoot));
$allFiles = [];

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filePath = $file->getPathname();
        $relativePath = str_replace($projectRoot . '/', '', $filePath);
        
        // Skip vendor and node_modules
        if (strpos($relativePath, 'vendor/') !== false || strpos($relativePath, 'node_modules/') !== false) {
            continue;
        }
        
        $allFiles[] = [
            'path' => $relativePath,
            'absolute_path' => $filePath,
            'size' => filesize($filePath),
            'lines' => substr_count(file_get_contents($filePath), "\n")
        ];
    }
}

echo "   Total PHP files: " . count($allFiles) . "\n";

// Group files by base name
$fileGroups = [];
foreach ($allFiles as $file) {
    $baseName = basename($file['path'], '.php');
    
    // Clean blade files - treat .blade.php as .php
    if (strpos($baseName, '.blade') !== false) {
        $baseName = str_replace('.blade', '', $baseName);
    }
    
    if (!isset($fileGroups[$baseName])) {
        $fileGroups[$baseName] = [];
    }
    $fileGroups[$baseName][] = $file;
}

// Find duplicates
$duplicateGroups = [];
foreach ($fileGroups as $baseName => $files) {
    if (count($files) > 1) {
        $duplicateGroups[$baseName] = $files;
    }
}

$consolidationResults['total_duplicates'] = count($duplicateGroups);
echo "   Duplicate groups found: " . count($duplicateGroups) . "\n\n";

// Process each duplicate group
foreach ($duplicateGroups as $baseName => $files) {
    echo "📁 PROCESSING GROUP: $baseName\n";
    echo "   Files: " . count($files) . "\n";
    
    // Sort by size (largest first) to find main file
    usort($files, function($a, $b) {
        return $b['size'] - $a['size'];
    });
    
    $mainFile = $files[0];
    $duplicates = array_slice($files, 1);
    
    echo "   Main file: {$mainFile['path']}\n";
    echo "   Duplicates to process: " . count($duplicates) . "\n";
    
    // Process each duplicate
    foreach ($duplicates as $duplicate) {
        $result = processDuplicate($mainFile, $duplicate, $consolidationResults);
        
        if ($result['success']) {
            $consolidationResults['consolidated_duplicates']++;
            echo "   ✅ Processed: {$duplicate['path']}\n";
        } else {
            $consolidationResults['failed_consolidations']++;
            echo "   ❌ Failed: {$duplicate['path']} - {$result['reason']}\n";
        }
    }
    
    echo "\n";
}

// Generate final report
generateFinalReport($consolidationResults);

echo "\n✅ SMART DUPLICATE CONSOLIDATION COMPLETE!\n";
echo "   Total Groups: {$consolidationResults['total_duplicates']}\n";
echo "   Processed: {$consolidationResults['consolidated_duplicates']}\n";
echo "   Deleted: {$consolidationResults['deleted_duplicates']}\n";
echo "   Failed: {$consolidationResults['failed_consolidations']}\n";

/**
 * Process duplicate file
 */
function processDuplicate($mainFile, $duplicate, &$results) {
    // Check if main file exists
    if (!file_exists($mainFile['absolute_path'])) {
        return ['success' => false, 'reason' => 'Main file not found'];
    }
    
    // Check if duplicate exists
    if (!file_exists($duplicate['absolute_path'])) {
        return ['success' => false, 'reason' => 'Duplicate file not found'];
    }
    
    // Special handling for .blade files - delete them as per APS rules
    if (strpos($duplicate['path'], '.blade') !== false) {
        if (unlink($duplicate['absolute_path'])) {
            $results['deleted_duplicates']++;
            $results['consolidation_details'][] = [
                'action' => 'deleted_blade_file',
                'main_file' => $mainFile['path'],
                'duplicate_file' => $duplicate['path'],
                'reason' => 'Blade files not allowed per APS rules'
            ];
            return ['success' => true, 'reason' => 'Blade file deleted'];
        }
        return ['success' => false, 'reason' => 'Failed to delete blade file'];
    }
    
    // Check if files are actually duplicates (same content)
    $mainContent = file_get_contents($mainFile['absolute_path']);
    $duplicateContent = file_get_contents($duplicate['absolute_path']);
    
    if ($mainContent === $duplicateContent) {
        // Exact duplicate - delete it
        if (unlink($duplicate['absolute_path'])) {
            $results['deleted_duplicates']++;
            $results['consolidation_details'][] = [
                'action' => 'deleted_exact_duplicate',
                'main_file' => $mainFile['path'],
                'duplicate_file' => $duplicate['path'],
                'reason' => 'Exact duplicate content'
            ];
            return ['success' => true, 'reason' => 'Exact duplicate deleted'];
        }
        return ['success' => false, 'reason' => 'Failed to delete duplicate'];
    }
    
    // Different content - try to merge
    $uniqueContent = getUniqueContent($mainContent, $duplicateContent);
    
    if (!empty($uniqueContent)) {
        // Add unique content to main file
        $enhancedContent = $mainContent . "\n\n// Merged from: {$duplicate['path']}\n" . $uniqueContent;
        
        if (file_put_contents($mainFile['absolute_path'], $enhancedContent)) {
            // Delete duplicate after successful merge
            if (unlink($duplicate['absolute_path'])) {
                $results['consolidation_details'][] = [
                    'action' => 'merged_and_deleted',
                    'main_file' => $mainFile['path'],
                    'duplicate_file' => $duplicate['path'],
                    'unique_content_size' => strlen($uniqueContent),
                    'reason' => 'Unique content merged and duplicate deleted'
                ];
                return ['success' => true, 'reason' => 'Merged and deleted'];
            }
        }
        return ['success' => false, 'reason' => 'Failed to merge content'];
    }
    
    // No unique content to merge, just delete duplicate
    if (unlink($duplicate['absolute_path'])) {
        $results['deleted_duplicates']++;
        $results['consolidation_details'][] = [
            'action' => 'deleted_no_unique_content',
            'main_file' => $mainFile['path'],
            'duplicate_file' => $duplicate['path'],
            'reason' => 'No unique content to merge'
        ];
        return ['success' => true, 'reason' => 'Deleted (no unique content)'];
    }
    
    return ['success' => false, 'reason' => 'Failed to process'];
}

/**
 * Get unique content from duplicate
 */
function getUniqueContent($mainContent, $duplicateContent) {
    // Extract functions from duplicate
    preg_match_all('/function\s+(\w+)\s*\([^)]*\)\s*{[^}]*}/s', $duplicateContent, $duplicateFunctions);
    
    $uniqueContent = '';
    
    foreach ($duplicateFunctions[0] as $function) {
        $functionName = '';
        if (preg_match('/function\s+(\w+)/', $function, $matches)) {
            $functionName = $matches[1];
            
            // Check if function exists in main content
            if (strpos($mainContent, "function $functionName") === false) {
                $uniqueContent .= "\n" . $function;
            }
        }
    }
    
    // Extract classes from duplicate
    preg_match_all('/class\s+(\w+)[^{]*{[^}]*}/s', $duplicateContent, $duplicateClasses);
    
    foreach ($duplicateClasses[0] as $class) {
        $className = '';
        if (preg_match('/class\s+(\w+)/', $class, $matches)) {
            $className = $matches[1];
            
            // Check if class exists in main content
            if (strpos($mainContent, "class $className") === false) {
                $uniqueContent .= "\n" . $class;
            }
        }
    }
    
    return $uniqueContent;
}

/**
 * Generate final report
 */
function generateFinalReport($results) {
    $report = [
        'timestamp' => $results['timestamp'],
        'consolidation_summary' => [
            'total_duplicate_groups' => $results['total_duplicates'],
            'total_files_processed' => $results['consolidated_duplicates'],
            'total_files_deleted' => $results['deleted_duplicates'],
            'failed_operations' => $results['failed_consolidations'],
            'success_rate' => $results['total_duplicates'] > 0 ? 
                round((($results['consolidated_duplicates'] + $results['deleted_duplicates']) / 
                ($results['total_duplicates'] * 2)) * 100, 2) . '%' : '100%'
        ],
        'operations_performed' => [
            'blade_files_deleted' => 0,
            'exact_duplicates_deleted' => 0,
            'content_merged' => 0,
            'no_unique_content_deleted' => 0
        ],
        'consolidation_details' => array_slice($results['consolidation_details'], 0, 50), // Limit to first 50
        'recommendations' => [
            'verify_functionality' => 'Test all merged functions to ensure they work correctly',
            'update_documentation' => 'Update documentation to reflect consolidated structure',
            'run_tests' => 'Run comprehensive tests to verify no functionality was lost',
            'backup_verification' => 'Verify all backups are properly maintained'
        ]
    ];
    
    // Count operation types
    foreach ($results['consolidation_details'] as $detail) {
        if (isset($detail['action'])) {
            switch ($detail['action']) {
                case 'deleted_blade_file':
                    $report['operations_performed']['blade_files_deleted']++;
                    break;
                case 'deleted_exact_duplicate':
                    $report['operations_performed']['exact_duplicates_deleted']++;
                    break;
                case 'merged_and_deleted':
                    $report['operations_performed']['content_merged']++;
                    break;
                case 'deleted_no_unique_content':
                    $report['operations_performed']['no_unique_content_deleted']++;
                    break;
            }
        }
    }
    
    file_put_contents(__DIR__ . '/../smart_duplicate_consolidation_report.json', json_encode($report, JSON_PRETTY_PRINT));
    
    echo "   Report saved to: smart_duplicate_consolidation_report.json\n";
    
    // Display operation summary
    echo "\n📊 OPERATIONS SUMMARY:\n";
    echo "   Blade Files Deleted: " . $report['operations_performed']['blade_files_deleted'] . "\n";
    echo "   Exact Duplicates Deleted: " . $report['operations_performed']['exact_duplicates_deleted'] . "\n";
    echo "   Content Merged: " . $report['operations_performed']['content_merged'] . "\n";
    echo "   No Unique Content Deleted: " . $report['operations_performed']['no_unique_content_deleted'] . "\n";
}

?>

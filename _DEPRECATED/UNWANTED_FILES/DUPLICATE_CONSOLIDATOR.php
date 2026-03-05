<?php

// TODO: Add proper error handling with try-catch blocks

**
 * APS Dream Home - Duplicate File Consolidator
 * Systematically fixes all identified duplicates
 */

echo "🔄 APS DREAM HOME - DUPLICATE FILE CONSOLIDATOR\n";
echo "===============================================\n\n";

// Load analysis results
$analysisFile = __DIR__ . '/../deep_project_analysis.json';
if (!file_exists($analysisFile)) {
    echo "❌ Analysis file not found. Run deep scanner first.\n";
    exit(1);
}

$analysis = json_decode(file_get_contents($analysisFile), true);
$consolidationResults = [
    'timestamp' => date('Y-m-d H:i:s'),
    'total_duplicates' => 0,
    'consolidated_duplicates' => 0,
    'failed_consolidations' => 0,
    'consolidation_details' => []
];

echo "🔍 LOADING DUPLICATE ANALYSIS...\n";

// Check if duplicate_analysis exists
if (!isset($analysis['duplicate_analysis']) || empty($analysis['duplicate_analysis'])) {
    echo "ℹ️ No duplicates found in analysis.\n";
    echo "✅ DUPLICATE CONSOLIDATION COMPLETE!\n";
    exit(0);
}

$consolidationResults['total_duplicates'] = count($analysis['duplicate_analysis']);
echo "   Found {$consolidationResults['total_duplicates']} duplicate groups\n\n";

// Process each duplicate group
foreach ($analysis['duplicate_analysis'] as $duplicateGroup) {
    echo "📁 PROCESSING GROUP: {$duplicateGroup['base_name']}\n";
    echo "   Files: {$duplicateGroup['count']}\n";
    
    // Find main file (largest or most recent)
    $mainFile = findMainFile($duplicateGroup['files']);
    $duplicateFiles = array_filter($duplicateGroup['files'], function($file) use ($mainFile) {
        return $file['path'] !== $mainFile['path'];
    });
    
    echo "   Main file: {$mainFile['path']}\n";
    echo "   Duplicates to merge: " . count($duplicateFiles) . "\n";
    
    // Consolidate each duplicate
    foreach ($duplicateFiles as $duplicate) {
        if (consolidateDuplicate($mainFile, $duplicate, $consolidationResults)) {
            $consolidationResults['consolidated_duplicates']++;
            echo "   ✅ Consolidated: {$duplicate['path']}\n";
        } else {
            $consolidationResults['failed_consolidations']++;
            echo "   ❌ Failed to consolidate: {$duplicate['path']}\n";
        }
    }
    
    echo "\n";
}

// Generate consolidation report
generateConsolidationReport($consolidationResults);

echo "✅ DUPLICATE CONSOLIDATION COMPLETE!\n";
echo "   Total Groups: {$consolidationResults['total_duplicates']}\n";
echo "   Consolidated: {$consolidationResults['consolidated_duplicates']}\n";
echo "   Failed: {$consolidationResults['failed_consolidations']}\n";

/**
 * Find main file from duplicate group
 */
function findMainFile($files) {
    // Sort by size (largest first) then by modification time (newest first)
    usort($files, function($a, $b) {
        if ($a['size'] === $b['size']) {
            return 0;
        }
        return ($a['size'] > $b['size']) ? -1 : 1;
    });
    
    return $files[0];
}

/**
 * Consolidate duplicate into main file
 */
function consolidateDuplicate($mainFile, $duplicateFile, &$consolidationResults) {
    $mainPath = __DIR__ . '/../' . str_replace('\\', '/', $mainFile['path']);
    $duplicatePath = __DIR__ . '/../' . str_replace('\\', '/', $duplicateFile['path']);
    
    // Check if files exist
    if (!file_exists($mainPath)) {
        echo "   ⚠️ Main file not found: {$mainPath}\n";
        return false;
    }
    
    if (!file_exists($duplicatePath)) {
        echo "   ⚠️ Duplicate file not found: {$duplicatePath}\n";
        return false;
    }
    
    // Read file contents
    $mainContent = file_get_contents($mainPath);
    $duplicateContent = file_get_contents($duplicatePath);
    
    // Extract unique functions from duplicate
    $uniqueFunctions = extractUniqueFunctions($mainContent, $duplicateContent);
    $uniqueClasses = extractUniqueClasses($mainContent, $duplicateContent);
    
    // Merge into main file
    $mergedContent = $mainContent;
    
    // Add unique functions
    if (!empty($uniqueFunctions)) {
        $mergedContent .= "\n\n// Consolidated functions from: {$duplicateFile['path']}\n";
        foreach ($uniqueFunctions as $function) {
            $mergedContent .= "\n" . $function;
        }
    }
    
    // Add unique classes
    if (!empty($uniqueClasses)) {
        $mergedContent .= "\n\n// Consolidated classes from: {$duplicateFile['path']}\n";
        foreach ($uniqueClasses as $class) {
            $mergedContent .= "\n" . $class;
        }
    }
    
    // Save enhanced main file
    if (file_put_contents($mainPath, $mergedContent)) {
        // Delete duplicate file
        if (unlink($duplicatePath)) {
            $consolidationResults['consolidation_details'][] = [
                'main_file' => $mainFile['path'],
                'duplicate_file' => $duplicateFile['path'],
                'functions_added' => count($uniqueFunctions),
                'classes_added' => count($uniqueClasses),
                'action' => 'Consolidated and deleted duplicate'
            ];
            
            return true;
        } else {
            echo "   ⚠️ Failed to delete duplicate file: {$duplicatePath}\n";
        }
    } else {
        echo "   ⚠️ Failed to save enhanced main file: {$mainPath}\n";
    }
    
    return false;
}

/**
 * Extract unique functions from duplicate content
 */
function extractUniqueFunctions($mainContent, $duplicateContent) {
    $uniqueFunctions = [];
    
    // Extract function signatures from duplicate
    preg_match_all('/function\s+(\w+)\s*\([^)]*\)\s*{[^}]*}/s', $duplicateContent, $duplicateFunctions);
    
    foreach ($duplicateFunctions[0] as $function) {
        // Check if function already exists in main content
        $functionName = '';
        if (preg_match('/function\s+(\w+)/', $function, $matches)) {
            $functionName = $matches[1];
            
            if (strpos($mainContent, "function $functionName") === false) {
                $uniqueFunctions[] = $function;
            }
        }
    }
    
    return $uniqueFunctions;
}

/**
 * Extract unique classes from duplicate content
 */
function extractUniqueClasses($mainContent, $duplicateContent) {
    $uniqueClasses = [];
    
    // Extract class definitions from duplicate
    preg_match_all('/class\s+(\w+)[^{]*{[^}]*}/s', $duplicateContent, $duplicateClasses);
    
    foreach ($duplicateClasses[0] as $class) {
        // Check if class already exists in main content
        $className = '';
        if (preg_match('/class\s+(\w+)/', $class, $matches)) {
            $className = $matches[1];
            
            if (strpos($mainContent, "class $className") === false) {
                $uniqueClasses[] = $class;
            }
        }
    }
    
    return $uniqueClasses;
}

/**
 * Generate consolidation report
 */
function generateConsolidationReport($results) {
    $report = [
        'timestamp' => $results['timestamp'],
        'consolidation_summary' => [
            'total_duplicate_groups' => $results['total_duplicates'],
            'total_files_consolidated' => $results['consolidated_duplicates'],
            'failed_consolidations' => $results['failed_consolidations'],
            'success_rate' => $results['total_duplicates'] > 0 ? round(($results['consolidated_duplicates'] / $results['total_duplicates']) * 100, 2) . '%' : '100%'
        ],
        'consolidation_details' => $results['consolidation_details'],
        'recommendations' => [
            'verify_functionality' => 'Test all consolidated functions to ensure they work correctly',
            'update_documentation' => 'Update documentation to reflect consolidated structure',
            'run_tests' => 'Run comprehensive tests to verify no functionality was lost',
            'backup_verification' => 'Verify all backups are properly maintained'
        ]
    ];
    
    file_put_contents(__DIR__ . '/../duplicate_consolidation_report.json', json_encode($report, JSON_PRETTY_PRINT));
    
    echo "   Report saved to: duplicate_consolidation_report.json\n";
}

?>

<?php
/**
 * APS Dream Home - Complete Duplicate Analysis & Fix System
 * Maximum level duplicate detection and automatic fixing
 */

echo "🔍 APS DREAM HOME - COMPLETE DUPLICATE ANALYSIS & FIX SYSTEM\n";
echo "======================================================\n\n";

// Initialize comprehensive results
$analysisResults = [
    'timestamp' => date('Y-m-d H:i:s'),
    'total_files_scanned' => 0,
    'duplicate_types_found' => [
        'exact_duplicates' => 0,
        'near_duplicates' => 0,
        'folder_duplicates' => 0,
        'empty_duplicate_files' => 0,
        'implementation_duplicates' => 0
    ],
    'duplicate_groups' => [],
    'files_fixed' => 0,
    'files_deleted' => 0,
    'files_merged' => 0
];

echo "🔍 STARTING COMPLETE DUPLICATE ANALYSIS...\n";

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
        
        $content = file_get_contents($filePath);
        
        $allFiles[] = [
            'path' => $relativePath,
            'absolute_path' => $filePath,
            'size' => filesize($filePath),
            'lines' => substr_count($content, "\n"),
            'content' => $content,
            'content_hash' => md5($content),
            'normalized_content' => normalizeContent($content),
            'is_empty' => trim($content) === '' || substr_count($content, "\n") < 3
        ];
        
        $analysisResults['total_files_scanned']++;
    }
}

echo "   Total PHP files scanned: " . count($allFiles) . "\n\n";

echo "🔍 LEVEL 1: EXACT DUPLICATE ANALYSIS...\n";
// Level 1: Exact duplicates (same content)
$contentGroups = [];
foreach ($allFiles as $file) {
    $hash = $file['content_hash'];
    if (!isset($contentGroups[$hash])) {
        $contentGroups[$hash] = [];
    }
    $contentGroups[$hash][] = $file;
}

$exactDuplicates = [];
foreach ($contentGroups as $hash => $files) {
    if (count($files) > 1) {
        $exactDuplicates[] = [
            'type' => 'exact_duplicate',
            'hash' => $hash,
            'files' => $files,
            'count' => count($files),
            'severity' => 'CRITICAL',
            'action' => 'delete_duplicates_keep_one'
        ];
        $analysisResults['duplicate_types_found']['exact_duplicates'] += count($files) - 1;
    }
}

echo "   Exact duplicate groups: " . count($exactDuplicates) . "\n";

echo "\n🔍 LEVEL 2: FOLDER STRUCTURE ANALYSIS...\n";
// Level 2: Folder structure duplicates
$folderGroups = [];
foreach ($allFiles as $file) {
    $dirPath = dirname($file['path']);
    $baseName = basename($file['path'], '.php');
    
    // Clean blade files
    if (strpos($baseName, '.blade') !== false) {
        $baseName = str_replace('.blade', '', $baseName);
    }
    
    $folderKey = $dirPath . '/' . $baseName;
    if (!isset($folderGroups[$folderKey])) {
        $folderGroups[$folderKey] = [];
    }
    $folderGroups[$folderKey][] = $file;
}

$folderDuplicates = [];
foreach ($folderGroups as $key => $files) {
    if (count($files) > 1) {
        // Check if they are in different folders
        $dirs = array_unique(array_map(function($f) { return dirname($f['path']); }, $files));
        if (count($dirs) > 1) {
            $folderDuplicates[] = [
                'type' => 'folder_duplicate',
                'folder_key' => $key,
                'files' => $files,
                'count' => count($files),
                'directories' => $dirs,
                'severity' => 'HIGH',
                'action' => 'consolidate_to_main_folder'
            ];
            $analysisResults['duplicate_types_found']['folder_duplicates'] += count($files) - 1;
        }
    }
}

echo "   Folder duplicate groups: " . count($folderDuplicates) . "\n";

echo "\n🔍 LEVEL 3: EMPTY DUPLICATE FILES ANALYSIS...\n";
// Level 3: Empty duplicate files
$emptyFiles = array_filter($allFiles, function($file) {
    return $file['is_empty'];
});

if (!empty($emptyFiles)) {
    $emptyDuplicates = [
        'type' => 'empty_duplicate',
        'files' => $emptyFiles,
        'count' => count($emptyFiles),
        'severity' => 'MEDIUM',
        'action' => 'delete_empty_files'
    ];
    $analysisResults['duplicate_types_found']['empty_duplicate_files'] = count($emptyFiles);
}

echo "   Empty duplicate files: " . count($emptyFiles) . "\n";

echo "\n🔍 LEVEL 4: IMPLEMENTATION DUPLICATES ANALYSIS...\n";
// Level 4: Implementation duplicates (same functionality in different files)
$implementationGroups = [];
$functionSignatures = [];

foreach ($allFiles as $file) {
    if ($file['is_empty']) continue;
    
    $functions = extractFunctions($file['content']);
    foreach ($functions as $function) {
        $signature = $function['name'] . '(' . implode(',', $function['params']) . ')';
        if (!isset($functionSignatures[$signature])) {
            $functionSignatures[$signature] = [];
        }
        $functionSignatures[$signature][] = [
            'file' => $file,
            'function' => $function
        ];
    }
}

foreach ($functionSignatures as $signature => $functions) {
    if (count($functions) > 1) {
        // Check if these are already identified as other duplicates
        $alreadyIdentified = false;
        foreach ($functions as $func) {
            foreach (array_merge($exactDuplicates, $folderDuplicates) as $dup) {
                if (in_array($func['file']['path'], array_column($dup['files'], 'path'))) {
                    $alreadyIdentified = true;
                    break 2;
                }
            }
        }
        
        if (!$alreadyIdentified) {
            $implementationDuplicates[] = [
                'type' => 'implementation_duplicate',
                'signature' => $signature,
                'functions' => $functions,
                'count' => count($functions),
                'severity' => 'LOW',
                'action' => 'review_and_consolidate'
            ];
            $analysisResults['duplicate_types_found']['implementation_duplicates'] += count($functions) - 1;
        }
    }
}

echo "   Implementation duplicate groups: " . count($implementationDuplicates) . "\n";

// Combine all duplicate groups
$allDuplicateGroups = array_merge($exactDuplicates, $folderDuplicates, $implementationDuplicates);
$analysisResults['duplicate_groups'] = $allDuplicateGroups;

echo "\n🔧 STARTING AUTOMATIC DUPLICATE FIXING...\n\n";

// Process each duplicate group
foreach ($allDuplicateGroups as $group) {
    echo "🔍 PROCESSING: " . strtoupper(str_replace('_', ' ', $group['type'])) . "\n";
    
    switch ($group['type']) {
        case 'exact_duplicate':
            $result = fixExactDuplicates($group, $projectRoot);
            break;
            
        case 'folder_duplicate':
            $result = fixFolderDuplicates($group, $projectRoot);
            break;
            
        case 'empty_duplicate':
            $result = fixEmptyDuplicates($group, $projectRoot);
            break;
            
        case 'implementation_duplicate':
            $result = fixImplementationDuplicates($group, $projectRoot);
            break;
            
        default:
            $result = ['status' => 'skipped', 'message' => 'Unknown duplicate type'];
    }
    
    if ($result['status'] === 'success') {
        $analysisResults['files_fixed'] += $result['files_fixed'] ?? 0;
        $analysisResults['files_deleted'] += $result['files_deleted'] ?? 0;
        $analysisResults['files_merged'] += $result['files_merged'] ?? 0;
        
        echo "   ✅ " . $result['message'] . "\n";
        if (isset($result['details'])) {
            foreach ($result['details'] as $detail) {
                echo "      - $detail\n";
            }
        }
    } else {
        echo "   ⚠️ " . $result['message'] . "\n";
    }
    
    echo "\n";
}

// Generate comprehensive report
$comprehensiveReport = [
    'timestamp' => $analysisResults['timestamp'],
    'scan_summary' => [
        'total_files_scanned' => $analysisResults['total_files_scanned'],
        'duplicate_types_found' => $analysisResults['duplicate_types_found'],
        'total_duplicate_groups' => count($allDuplicateGroups),
        'files_fixed' => $analysisResults['files_fixed'],
        'files_deleted' => $analysisResults['files_deleted'],
        'files_merged' => $analysisResults['files_merged'],
        'project_clean' => empty($allDuplicateGroups)
    ],
    'duplicate_analysis' => [
        'exact_duplicates' => $exactDuplicates,
        'folder_duplicates' => $folderDuplicates,
        'empty_duplicates' => $emptyDuplicates,
        'implementation_duplicates' => $implementationDuplicates
    ],
    'fixes_applied' => $analysisResults['duplicate_groups'],
    'recommendations' => [
        'review_remaining_files' => 'Review any remaining files for potential improvements',
        'test_functionality' => 'Test all fixed functionality to ensure it works correctly',
        'update_documentation' => 'Update project documentation after changes',
        'backup_before_changes' => 'Always backup before making changes'
    ]
];

file_put_contents(__DIR__ . '/../complete_duplicate_analysis.json', json_encode($comprehensiveReport, JSON_PRETTY_PRINT));

echo "📊 COMPLETE DUPLICATE ANALYSIS RESULTS:\n";
echo "====================================\n";
echo "📁 Total Files Scanned: " . $analysisResults['total_files_scanned'] . "\n";
echo "🔄 Exact Duplicates: " . $analysisResults['duplicate_types_found']['exact_duplicates'] . "\n";
echo "📁 Folder Duplicates: " . $analysisResults['duplicate_types_found']['folder_duplicates'] . "\n";
echo "📝 Empty Duplicates: " . $analysisResults['duplicate_types_found']['empty_duplicate_files'] . "\n";
echo "⚙️ Implementation Duplicates: " . $analysisResults['duplicate_types_found']['implementation_duplicates'] . "\n";
echo "📊 Total Duplicate Groups: " . count($allDuplicateGroups) . "\n";
echo "🔧 Files Fixed: " . $analysisResults['files_fixed'] . "\n";
echo "🗑️ Files Deleted: " . $analysisResults['files_deleted'] . "\n";
echo "🔀 Files Merged: " . $analysisResults['files_merged'] . "\n";

if (empty($allDuplicateGroups)) {
    echo "\n🎉 STATUS: PERFECT - No duplicates found!\n";
} else {
    echo "\n⚠️ STATUS: ACTION TAKEN - Duplicates found and fixed!\n";
}

echo "\n✅ COMPLETE DUPLICATE ANALYSIS & FIX COMPLETE!\n";
echo "📄 Report saved to: complete_duplicate_analysis.json\n";

// Helper functions
function normalizeContent($content) {
    $content = preg_replace('/\/\*[\s\S]*?\*\//', '', $content);
    $content = preg_replace('/\/\/.*$/m', '', $content);
    $content = preg_replace('/\s+/', ' ', $content);
    return strtolower(trim($content));
}

function extractFunctions($content) {
    $functions = [];
    preg_match_all('/function\s+(\w+)\s*\(([^)]*)\)/', $content, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        $params = array_map('trim', explode(',', $match[2]));
        $params = array_map(function($p) { return preg_replace('/\s.*$/', '', $p); }, $params);
        $functions[] = [
            'name' => $match[1],
            'params' => $params
        ];
    }
    return $functions;
}

function fixExactDuplicates($group, $projectRoot) {
    $files = $group['files'];
    $keepFile = $files[0]; // Keep first file
    $deletedFiles = [];
    
    for ($i = 1; $i < count($files); $i++) {
        $filePath = $projectRoot . '/' . $files[$i]['path'];
        if (unlink($filePath)) {
            $deletedFiles[] = $files[$i]['path'];
        }
    }
    
    return [
        'status' => 'success',
        'message' => 'Kept ' . basename($keepFile['path']) . ', deleted ' . count($deletedFiles) . ' exact duplicates',
        'files_deleted' => count($deletedFiles),
        'details' => array_map(function($f) { return 'Deleted: ' . $f; }, $deletedFiles)
    ];
}

function fixFolderDuplicates($group, $projectRoot) {
    $files = $group['files'];
    $mainFolder = $projectRoot . '/app/Http/Controllers';
    $movedFiles = [];
    
    foreach ($files as $file) {
        $dirPath = dirname($file['path']);
        
        // If not in main controllers folder, move it there
        if (strpos($dirPath, 'Http/Controllers') === false) {
            $sourcePath = $projectRoot . '/' . $file['path'];
            $targetDir = $mainFolder . '/' . basename($dirPath);
            
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            
            $targetPath = $targetDir . '/' . basename($file['path']);
            
            if (rename($sourcePath, $targetPath)) {
                $movedFiles[] = $file['path'] . ' → ' . str_replace($projectRoot . '/', '', $targetPath);
            }
        }
    }
    
    return [
        'status' => 'success',
        'message' => 'Moved ' . count($movedFiles) . ' files to main controllers folder',
        'files_fixed' => count($movedFiles),
        'details' => $movedFiles
    ];
}

function fixEmptyDuplicates($group, $projectRoot) {
    $files = $group['files'];
    $deletedFiles = [];
    
    foreach ($files as $file) {
        $filePath = $projectRoot . '/' . $file['path'];
        if (unlink($filePath)) {
            $deletedFiles[] = $file['path'];
        }
    }
    
    return [
        'status' => 'success',
        'message' => 'Deleted ' . count($deletedFiles) . ' empty duplicate files',
        'files_deleted' => count($deletedFiles),
        'details' => array_map(function($f) { return 'Deleted: ' . $f; }, $deletedFiles)
    ];
}

function fixImplementationDuplicates($group, $projectRoot) {
    $files = $group['files'];
    $mainFile = $files[0]; // Keep first file as main
    $mergedFiles = [];
    
    for ($i = 1; $i < count($files); $i++) {
        $sourcePath = $projectRoot . '/' . $files[$i]['path'];
        $targetPath = $projectRoot . '/' . $mainFile['path'];
        
        // Read both files and merge
        $mainContent = file_get_contents($targetPath);
        $sourceContent = file_get_contents($sourcePath);
        
        // Extract unique functions from source
        $sourceFunctions = extractFunctions($sourceContent);
        $uniqueFunctions = [];
        
        foreach ($sourceFunctions as $function) {
            $functionCode = extractFunctionCode($sourceContent, $function['name']);
            if ($functionCode && strpos($mainContent, 'function ' . $function['name']) === false) {
                $mainContent .= "\n\n" . $functionCode;
                $uniqueFunctions[] = $function['name'];
            }
        }
        
        // Update main file
        file_put_contents($targetPath, $mainContent);
        
        // Delete source file
        if (unlink($sourcePath)) {
            $mergedFiles[] = $files[$i]['path'] . ' → ' . $mainFile['path'] . ' (merged functions: ' . implode(', ', $uniqueFunctions) . ')';
        }
    }
    
    return [
        'status' => 'success',
        'message' => 'Merged ' . count($mergedFiles) . ' implementation duplicates',
        'files_merged' => count($mergedFiles),
        'details' => $mergedFiles
    ];
}

function extractFunctionCode($content, $functionName) {
    preg_match('/function\s+' . $functionName . '\s*\([^)]*\)\s*\{([^}]*)\}/', $content, $matches);
    return isset($matches[0]) ? $matches[0] : null;
}

?>

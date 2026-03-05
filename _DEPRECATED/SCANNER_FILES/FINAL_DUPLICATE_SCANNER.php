<?php
/**
 * APS Dream Home - Final Duplicate Scanner
 * Deep scan for any remaining duplicates in the entire project
 */

echo "🔍 APS DREAM HOME - FINAL DUPLICATE SCANNER\n";
echo "==========================================\n\n";

// Initialize results
$scanResults = [
    'timestamp' => date('Y-m-d H:i:s'),
    'total_files_scanned' => 0,
    'duplicate_groups_found' => 0,
    'exact_duplicates' => 0,
    'similar_files' => 0,
    'duplicate_details' => []
];

echo "📁 SCANNING ENTIRE PROJECT FOR DUPLICATES...\n";

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
            'content_hash' => md5($content),
            'content' => $content
        ];
        
        $scanResults['total_files_scanned']++;
    }
}

echo "   Total PHP files scanned: " . count($allFiles) . "\n\n";

echo "🔍 ANALYZING FOR DUPLICATES...\n";

// Group files by content hash (exact duplicates)
$contentGroups = [];
foreach ($allFiles as $file) {
    $hash = $file['content_hash'];
    if (!isset($contentGroups[$hash])) {
        $contentGroups[$hash] = [];
    }
    $contentGroups[$hash][] = $file;
}

// Find exact duplicates
$exactDuplicates = [];
foreach ($contentGroups as $hash => $files) {
    if (count($files) > 1) {
        $exactDuplicates[] = [
            'hash' => $hash,
            'files' => $files,
            'count' => count($files)
        ];
        $scanResults['exact_duplicates'] += count($files) - 1; // Subtract 1 for the original
    }
}

echo "   Exact duplicate groups: " . count($exactDuplicates) . "\n";

// Group files by base name (potential duplicates)
$fileGroups = [];
foreach ($allFiles as $file) {
    $baseName = basename($file['path'], '.php');
    
    // Clean blade files - treat .blade as .php
    if (strpos($baseName, '.blade') !== false) {
        $baseName = str_replace('.blade', '', $baseName);
    }
    
    if (!isset($fileGroups[$baseName])) {
        $fileGroups[$baseName] = [];
    }
    $fileGroups[$baseName][] = $file;
}

// Find potential duplicates by name
$nameDuplicates = [];
foreach ($fileGroups as $baseName => $files) {
    if (count($files) > 1) {
        // Check if they are actually different (not exact duplicates)
        $uniqueHashes = array_unique(array_column($files, 'content_hash'));
        if (count($uniqueHashes) > 1) {
            $nameDuplicates[] = [
                'base_name' => $baseName,
                'files' => $files,
                'count' => count($files),
                'unique_content' => count($uniqueHashes)
            ];
            $scanResults['similar_files'] += count($files) - 1;
        }
    }
}

echo "   Similar file groups: " . count($nameDuplicates) . "\n\n";

$scanResults['duplicate_groups_found'] = count($exactDuplicates) + count($nameDuplicates);

// Display results
if (!empty($exactDuplicates)) {
    echo "❌ EXACT DUPLICATES FOUND:\n";
    echo "========================\n";
    
    foreach ($exactDuplicates as $duplicate) {
        echo "📁 Content Hash: " . substr($duplicate['hash'], 0, 8) . "...\n";
        echo "   Files: " . $duplicate['count'] . "\n";
        
        foreach ($duplicate['files'] as $file) {
            echo "   - " . $file['path'] . " (" . $file['lines'] . " lines)\n";
        }
        echo "\n";
    }
}

if (!empty($nameDuplicates)) {
    echo "⚠️ SIMILAR FILES (SAME NAME, DIFFERENT CONTENT):\n";
    echo "===============================================\n";
    
    foreach ($nameDuplicates as $duplicate) {
        echo "📁 Base Name: " . $duplicate['base_name'] . "\n";
        echo "   Files: " . $duplicate['count'] . " (Unique content: " . $duplicate['unique_content'] . ")\n";
        
        foreach ($duplicate['files'] as $file) {
            echo "   - " . $file['path'] . " (" . $file['lines'] . " lines, " . $file['size'] . " bytes)\n";
        }
        echo "\n";
    }
}

if (empty($exactDuplicates) && empty($nameDuplicates)) {
    echo "✅ NO DUPLICATES FOUND!\n";
    echo "========================\n";
    echo "   All files are unique and properly organized.\n\n";
}

// Generate detailed report
$duplicateReport = [
    'timestamp' => $scanResults['timestamp'],
    'scan_summary' => [
        'total_files_scanned' => $scanResults['total_files_scanned'],
        'exact_duplicate_groups' => count($exactDuplicates),
        'similar_file_groups' => count($nameDuplicates),
        'total_duplicate_files' => $scanResults['exact_duplicates'] + $scanResults['similar_files'],
        'duplicate_free' => empty($exactDuplicates) && empty($nameDuplicates)
    ],
    'exact_duplicates' => $exactDuplicates,
    'similar_files' => $nameDuplicates,
    'recommendations' => []
];

// Add recommendations
if (!empty($exactDuplicates)) {
    $duplicateReport['recommendations'][] = 'Delete exact duplicate files and keep only one copy';
}

if (!empty($nameDuplicates)) {
    $duplicateReport['recommendations'][] = 'Review similar files and consider merging unique functionality';
}

if (empty($exactDuplicates) && empty($nameDuplicates)) {
    $duplicateReport['recommendations'][] = 'Project is duplicate-free and well organized';
}

file_put_contents(__DIR__ . '/../final_duplicate_scan_report.json', json_encode($duplicateReport, JSON_PRETTY_PRINT));

echo "📊 FINAL DUPLICATE SCAN RESULTS:\n";
echo "===============================\n";
echo "📁 Total Files Scanned: " . $scanResults['total_files_scanned'] . "\n";
echo "🔄 Exact Duplicate Groups: " . count($exactDuplicates) . "\n";
echo "📝 Similar File Groups: " . count($nameDuplicates) . "\n";
echo "📋 Total Duplicate Files: " . ($scanResults['exact_duplicates'] + $scanResults['similar_files']) . "\n";

if (empty($exactDuplicates) && empty($nameDuplicates)) {
    echo "\n🎉 STATUS: EXCELLENT - Project is duplicate-free!\n";
} else {
    echo "\n⚠️ STATUS: ACTION NEEDED - Duplicates found!\n";
}

echo "\n✅ FINAL DUPLICATE SCAN COMPLETE!\n";
echo "📄 Report saved to: final_duplicate_scan_report.json\n";

?>

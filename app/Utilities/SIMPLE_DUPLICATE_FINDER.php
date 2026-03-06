<?php
/**
 * APS Dream Home - Simple Duplicate Finder
 * Find and list all duplicates in the project
 */

echo "🔍 APS DREAM HOME - SIMPLE DUPLICATE FINDER\n";
echo "==========================================\n\n";

// Get all PHP files
$projectRoot = __DIR__ . '/..';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($projectRoot));
$allFiles = [];

echo "📁 SCANNING ALL PHP FILES...\n";

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filePath = $file->getPathname();
        $relativePath = str_replace($projectRoot . '/', '', $filePath);
        
        // Skip vendor and node_modules
        if (strpos($relativePath, 'vendor/') !== false || strpos($relativePath, 'node_modules/') !== false) {
            continue;
        }
        
        $content = file_get_contents($filePath);
        $baseName = basename($relativePath, '.php');
        
        // Clean blade files
        if (strpos($baseName, '.blade') !== false) {
            $baseName = str_replace('.blade', '', $baseName);
        }
        
        $allFiles[] = [
            'path' => $relativePath,
            'base_name' => $baseName,
            'content_hash' => md5($content),
            'size' => filesize($filePath),
            'lines' => substr_count($content, "\n")
        ];
    }
}

echo "   Total files found: " . count($allFiles) . "\n\n";

echo "🔍 GROUPING FILES BY NAME...\n";

// Group files by base name
$fileGroups = [];
foreach ($allFiles as $file) {
    $baseName = $file['base_name'];
    if (!isset($fileGroups[$baseName])) {
        $fileGroups[$baseName] = [];
    }
    $fileGroups[$baseName][] = $file;
}

echo "   Unique file names: " . count($fileGroups) . "\n\n";

echo "🔍 FINDING DUPLICATES...\n";

// Find duplicates
$duplicates = [];
foreach ($fileGroups as $baseName => $files) {
    if (count($files) > 1) {
        $duplicates[$baseName] = $files;
    }
}

if (empty($duplicates)) {
    echo "✅ NO DUPLICATES FOUND!\n";
    echo "======================\n";
    echo "   All files are unique and properly organized.\n\n";
} else {
    echo "⚠️ DUPLICATES FOUND!\n";
    echo "===================\n";
    echo "   Duplicate groups: " . count($duplicates) . "\n\n";
    
    foreach ($duplicates as $baseName => $files) {
        echo "📁 FILE NAME: $baseName\n";
        echo "   Files found: " . count($files) . "\n";
        
        foreach ($files as $file) {
            echo "   - " . $file['path'] . " (" . $file['lines'] . " lines, " . $file['size'] . " bytes)\n";
        }
        echo "\n";
    }
}

// Save report
$report = [
    'timestamp' => date('Y-m-d H:i:s'),
    'total_files_scanned' => count($allFiles),
    'unique_file_names' => count($fileGroups),
    'duplicate_groups' => count($duplicates),
    'duplicates_found' => !empty($duplicates),
    'duplicate_details' => $duplicates
];

file_put_contents(__DIR__ . '/../simple_duplicate_finder.json', json_encode($report, JSON_PRETTY_PRINT));

echo "📊 FINAL RESULTS:\n";
echo "================\n";
echo "📁 Total Files Scanned: " . count($allFiles) . "\n";
echo "🔍 Unique File Names: " . count($fileGroups) . "\n";
echo "🔄 Duplicate Groups: " . count($duplicates) . "\n";

if (empty($duplicates)) {
    echo "🎉 STATUS: EXCELLENT - No duplicates found!\n";
} else {
    echo "⚠️ STATUS: ACTION NEEDED - " . count($duplicates) . " duplicate groups found!\n";
}

echo "\n✅ DUPLICATE SCAN COMPLETE!\n";
echo "📄 Report saved to: simple_duplicate_finder.json\n";

?>

<?php
/**
 * APS Dream Home - Complete Duplicate Analysis & Deduplication Scan
 * Maximum level analysis to identify and eliminate all duplicates
 */

echo "🔍 APS DREAM HOME - COMPLETE DUPLICATE ANALYSIS\n";
echo "==========================================\n\n";

// Project root directory
$projectRoot = __DIR__ . '/..';
$projectName = 'APS Dream Home';

echo "📁 DUPLICATE ANALYSIS OVERVIEW\n";
echo "---------------------------\n";
echo "Project: $projectName\n";
echo "Scan Date: " . date('Y-m-d H:i:s') . "\n";
echo "Scan Level: Maximum Deep Analysis\n";
echo "Focus: Duplicate Detection & Elimination\n\n";

// 1. File Structure Analysis
echo "📂 FILE STRUCTURE ANALYSIS\n";
echo "------------------------\n";

function getAllFiles($dir, $excludePatterns = []) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $relativePath = str_replace($dir . '/', '', $file->getPathname());
            
            // Check if file should be excluded
            $shouldExclude = false;
            foreach ($excludePatterns as $pattern) {
                if (fnmatch($pattern, $relativePath)) {
                    $shouldExclude = true;
                    break;
                }
            }
            
            if (!$shouldExclude) {
                $files[] = [
                    'path' => $relativePath,
                    'full_path' => $file->getPathname(),
                    'size' => $file->getSize(),
                    'modified' => $file->getMTime(),
                    'hash' => md5_file($file->getPathname())
                ];
            }
        }
    }
    
    return $files;
}

// Exclude common directories and files
$excludePatterns = [
    'vendor/*',
    'node_modules/*',
    '.git/*',
    '*.log',
    '*.tmp',
    'cache/*',
    'tmp/*'
];

$allFiles = getAllFiles($projectRoot, $excludePatterns);
echo "Total Files Scanned: " . count($allFiles) . "\n";

// 2. Exact Duplicate Detection
echo "\n🔍 EXACT DUPLICATE DETECTION\n";
echo "--------------------------\n";

$hashGroups = [];
$exactDuplicates = [];

foreach ($allFiles as $file) {
    $hash = $file['hash'];
    if (!isset($hashGroups[$hash])) {
        $hashGroups[$hash] = [];
    }
    $hashGroups[$hash][] = $file;
}

foreach ($hashGroups as $hash => $files) {
    if (count($files) > 1) {
        $exactDuplicates[$hash] = $files;
    }
}

echo "Exact Duplicate Groups: " . count($exactDuplicates) . "\n";
$totalExactDuplicates = 0;

foreach ($exactDuplicates as $hash => $files) {
    echo "\nDuplicate Group (Hash: " . substr($hash, 0, 8) . "):\n";
    foreach ($files as $file) {
        echo "  • {$file['path']} ({$file['size']} bytes)\n";
        $totalExactDuplicates++;
    }
}

echo "\nTotal Exact Duplicate Files: $totalExactDuplicates\n";

// 3. Similar Content Detection
echo "\n🔍 SIMILAR CONTENT DETECTION\n";
echo "---------------------------\n";

$similarFiles = [];
$processedFiles = [];

foreach ($allFiles as $file) {
    if (in_array($file['path'], $processedFiles)) {
        continue;
    }
    
    $similarGroup = [$file];
    $fileContent = file_get_contents($file['full_path']);
    
    foreach ($allFiles as $otherFile) {
        if ($file['path'] === $otherFile['path']) {
            continue;
        }
        
        if (in_array($otherFile['path'], $processedFiles)) {
            continue;
        }
        
        // Skip files that are too different in size
        if (abs($file['size'] - $otherFile['size']) > ($file['size'] * 0.1)) {
            continue;
        }
        
        $otherContent = file_get_contents($otherFile['full_path']);
        
        // Calculate similarity using similar_text
        similar_text($fileContent, $otherContent, $percent);
        
        if ($percent > 90) { // 90% similarity threshold
            $similarGroup[] = $otherFile;
            $processedFiles[] = $otherFile['path'];
        }
    }
    
    if (count($similarGroup) > 1) {
        $similarFiles[] = $similarGroup;
    }
    
    $processedFiles[] = $file['path'];
}

echo "Similar Content Groups: " . count($similarFiles) . "\n";
$totalSimilarFiles = 0;

foreach ($similarFiles as $group) {
    echo "\nSimilar Group (" . count($group) . " files):\n";
    foreach ($group as $file) {
        echo "  • {$file['path']}\n";
        $totalSimilarFiles++;
    }
}

echo "\nTotal Similar Files: $totalSimilarFiles\n";

// 4. Naming Pattern Duplication
echo "\n🔍 NAMING PATTERN DUPLICATION\n";
echo "-----------------------------\n";

$patternGroups = [];
$namingDuplicates = [];

foreach ($allFiles as $file) {
    $pathParts = pathinfo($file['path']);
    $filename = $pathParts['filename'];
    $extension = $pathParts['extension'] ?? '';
    
    // Group by filename (without extension)
    if (!isset($patternGroups[$filename])) {
        $patternGroups[$filename] = [];
    }
    $patternGroups[$filename][] = $file;
}

foreach ($patternGroups as $filename => $files) {
    if (count($files) > 1) {
        $namingDuplicates[$filename] = $files;
    }
}

echo "Naming Duplicate Groups: " . count($namingDuplicates) . "\n";

foreach ($namingDuplicates as $filename => $files) {
    echo "\nDuplicate Name: $filename\n";
    foreach ($files as $file) {
        echo "  • {$file['path']}\n";
    }
}

// 5. Directory Structure Duplication
echo "\n🔍 DIRECTORY STRUCTURE DUPLICATION\n";
echo "---------------------------------\n";

function getDirectoryStructure($dir) {
    $structure = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $relativePath = str_replace($dir . '/', '', $item->getPathname());
        $structure[] = $item->isDir() ? $relativePath . '/' : $relativePath;
    }
    
    sort($structure);
    return $structure;
}

$mainDirs = ['app', 'config', 'public', 'database', 'tools', 'tests'];
$directoryStructures = [];

foreach ($mainDirs as $dir) {
    $dirPath = $projectRoot . '/' . $dir;
    if (is_dir($dirPath)) {
        $directoryStructures[$dir] = getDirectoryStructure($dirPath);
        echo "$dir: " . count($directoryStructures[$dir]) . " items\n";
    }
}

// Check for similar directory structures
$structureHashes = [];
foreach ($directoryStructures as $dir => $structure) {
    $hash = md5(implode("\n", $structure));
    $structureHashes[$hash] = $structureHashes[$hash] ?? [];
    $structureHashes[$hash][] = $dir;
}

foreach ($structureHashes as $hash => $dirs) {
    if (count($dirs) > 1) {
        echo "\nSimilar Directory Structure:\n";
        foreach ($dirs as $dir) {
            echo "  • $dir\n";
        }
    }
}

// 6. Code Duplication Analysis
echo "\n🔍 CODE DUPLICATION ANALYSIS\n";
echo "--------------------------\n";

$phpFiles = array_filter($allFiles, function($file) {
    return pathinfo($file['path'], PATHINFO_EXTENSION) === 'php';
});

$codeDuplicates = [];
$functionSignatures = [];

foreach ($phpFiles as $file) {
    $content = file_get_contents($file['full_path']);
    
    // Extract function signatures
    preg_match_all('/function\s+(\w+)\s*\([^)]*\)/', $content, $matches);
    foreach ($matches[1] as $functionName) {
        if (!isset($functionSignatures[$functionName])) {
            $functionSignatures[$functionName] = [];
        }
        $functionSignatures[$functionName][] = $file['path'];
    }
    
    // Extract class names
    preg_match_all('/class\s+(\w+)/', $content, $matches);
    foreach ($matches[1] as $className) {
        if (!isset($functionSignatures[$className])) {
            $functionSignatures[$className] = [];
        }
        $functionSignatures[$className][] = $file['path'];
    }
}

echo "Duplicate Functions/Classes:\n";
$duplicateFunctions = 0;
foreach ($functionSignatures as $name => $files) {
    if (count($files) > 1) {
        echo "  • $name: " . implode(', ', $files) . "\n";
        $duplicateFunctions++;
    }
}

echo "Total Duplicate Functions/Classes: $duplicateFunctions\n";

// 7. Configuration Duplication
echo "\n🔍 CONFIGURATION DUPLICATION\n";
echo "---------------------------\n";

$configFiles = array_filter($allFiles, function($file) {
    return strpos($file['path'], 'config') !== false || 
           strpos($file['path'], '.env') !== false ||
           pathinfo($file['path'], PATHINFO_EXTENSION) === 'json';
});

$configDuplicates = [];
$configKeys = [];

foreach ($configFiles as $file) {
    $content = file_get_contents($file['full_path']);
    
    // Extract configuration keys
    if (pathinfo($file['path'], PATHINFO_EXTENSION) === 'json') {
        $json = json_decode($content, true);
        if ($json) {
            $keys = array_keys($json);
            foreach ($keys as $key) {
                if (!isset($configKeys[$key])) {
                    $configKeys[$key] = [];
                }
                $configKeys[$key][] = $file['path'];
            }
        }
    } else {
        // Extract array keys from PHP config
        preg_match_all('/\'([^\']+)\'\s*=>/', $content, $matches);
        foreach ($matches[1] as $key) {
            if (!isset($configKeys[$key])) {
                $configKeys[$key] = [];
            }
            $configKeys[$key][] = $file['path'];
        }
    }
}

echo "Duplicate Configuration Keys:\n";
$duplicateConfigKeys = 0;
foreach ($configKeys as $key => $files) {
    if (count($files) > 1) {
        echo "  • $key: " . implode(', ', $files) . "\n";
        $duplicateConfigKeys++;
    }
}

echo "Total Duplicate Config Keys: $duplicateConfigKeys\n";

// 8. Asset Duplication
echo "\n🔍 ASSET DUPLICATION\n";
echo "------------------\n";

$assetFiles = array_filter($allFiles, function($file) {
    $extension = pathinfo($file['path'], PATHINFO_EXTENSION);
    return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'css', 'js']);
});

$assetDuplicates = [];
$assetHashes = [];

foreach ($assetFiles as $file) {
    $hash = $file['hash'];
    if (!isset($assetHashes[$hash])) {
        $assetHashes[$hash] = [];
    }
    $assetHashes[$hash][] = $file;
}

foreach ($assetHashes as $hash => $files) {
    if (count($files) > 1) {
        $assetDuplicates[$hash] = $files;
    }
}

echo "Duplicate Asset Groups: " . count($assetDuplicates) . "\n";
$totalAssetDuplicates = 0;

foreach ($assetDuplicates as $hash => $files) {
    echo "\nDuplicate Asset Group:\n";
    foreach ($files as $file) {
        echo "  • {$file['path']} ({$file['size']} bytes)\n";
        $totalAssetDuplicates++;
    }
}

echo "\nTotal Duplicate Assets: $totalAssetDuplicates\n";

// 9. Database Script Duplication
echo "\n🔍 DATABASE SCRIPT DUPLICATION\n";
echo "------------------------------\n";

$dbFiles = array_filter($allFiles, function($file) {
    return strpos($file['path'], 'database') !== false ||
           pathinfo($file['path'], PATHINFO_EXTENSION) === 'sql';
});

$dbDuplicates = [];
$dbTableNames = [];

foreach ($dbFiles as $file) {
    $content = file_get_contents($file['full_path']);
    
    // Extract table names from SQL
    preg_match_all('/CREATE TABLE\s+(IF NOT EXISTS\s+)?`?(\w+)`?/', $content, $matches);
    foreach ($matches[2] as $tableName) {
        if (!isset($dbTableNames[$tableName])) {
            $dbTableNames[$tableName] = [];
        }
        $dbTableNames[$tableName][] = $file['path'];
    }
}

echo "Duplicate Table Definitions:\n";
$duplicateTables = 0;
foreach ($dbTableNames as $tableName => $files) {
    if (count($files) > 1) {
        echo "  • $tableName: " . implode(', ', $files) . "\n";
        $duplicateTables++;
    }
}

echo "Total Duplicate Table Definitions: $duplicateTables\n";

// 10. Summary and Recommendations
echo "\n📋 DUPLICATE ANALYSIS SUMMARY\n";
echo "==========================\n";

$summary = [
    'total_files' => count($allFiles),
    'exact_duplicates' => count($exactDuplicates),
    'similar_content' => count($similarFiles),
    'naming_duplicates' => count($namingDuplicates),
    'code_duplicates' => $duplicateFunctions,
    'config_duplicates' => $duplicateConfigKeys,
    'asset_duplicates' => count($assetDuplicates),
    'db_duplicates' => $duplicateTables
];

echo "Total Files Scanned: {$summary['total_files']}\n";
echo "Exact Duplicate Groups: {$summary['exact_duplicates']}\n";
echo "Similar Content Groups: {$summary['similar_content']}\n";
echo "Naming Duplicate Groups: {$summary['naming_duplicates']}\n";
echo "Code Duplicates: {$summary['code_duplicates']}\n";
echo "Configuration Duplicates: {$summary['config_duplicates']}\n";
echo "Asset Duplicates: {$summary['asset_duplicates']}\n";
echo "Database Duplicates: {$summary['db_duplicates']}\n\n";

// Calculate duplicate percentage
$totalDuplicates = $summary['exact_duplicates'] + $summary['similar_content'] + 
                   $summary['naming_duplicates'] + $summary['code_duplicates'] + 
                   $summary['config_duplicates'] + $summary['asset_duplicates'] + 
                   $summary['db_duplicates'];

$duplicatePercentage = $summary['total_files'] > 0 ? 
    round(($totalDuplicates / $summary['total_files']) * 100, 2) : 0;

echo "Duplicate Percentage: $duplicatePercentage%\n";

// Health Score
$healthScore = 100;
$healthScore -= $totalDuplicates * 2;
$healthScore = max(0, $healthScore);

echo "Duplicate Health Score: $healthScore/100\n";

if ($healthScore >= 90) {
    echo "🏆 EXCELLENT: Very few duplicates found\n";
} elseif ($healthScore >= 70) {
    echo "✅ GOOD: Some duplicates but manageable\n";
} elseif ($healthScore >= 50) {
    echo "⚠️  FAIR: Significant duplicates found\n";
} else {
    echo "🚨 POOR: Many duplicates need cleanup\n";
}

// 11. Cleanup Recommendations
echo "\n💡 CLEANUP RECOMMENDATIONS\n";
echo "========================\n";

$recommendations = [];

if ($summary['exact_duplicates'] > 0) {
    $recommendations[] = "Remove {$summary['exact_duplicates']} exact duplicate file groups";
}

if ($summary['similar_content'] > 0) {
    $recommendations[] = "Review {$summary['similar_content']} similar content groups";
}

if ($summary['naming_duplicates'] > 0) {
    $recommendations[] = "Consolidate {$summary['naming_duplicates']} naming duplicates";
}

if ($summary['code_duplicates'] > 0) {
    $recommendations[] = "Refactor {$summary['code_duplicates']} duplicate functions/classes";
}

if ($summary['config_duplicates'] > 0) {
    $recommendations[] = "Merge {$summary['config_duplicates']} duplicate configuration keys";
}

if ($summary['asset_duplicates'] > 0) {
    $recommendations[] = "Optimize {$summary['asset_duplicates']} duplicate assets";
}

if ($summary['db_duplicates'] > 0) {
    $recommendations[] = "Consolidate {$summary['db_duplicates']} duplicate database scripts";
}

echo "Priority Cleanup Actions:\n";
foreach ($recommendations as $i => $rec) {
    echo ($i + 1) . ". $rec\n";
}

// 12. Generate Cleanup Script
echo "\n🔧 GENERATING CLEANUP SCRIPT\n";
echo "---------------------------\n";

$cleanupScript = "<?php\n";
$cleanupScript .= "/**\n";
$cleanupScript .= " * APS Dream Home - Duplicate Cleanup Script\n";
$cleanupScript .= " * Generated on: " . date('Y-m-d H:i:s') . "\n";
$cleanupScript .= " */\n\n";

$cleanupScript .= "echo \"🧹 APS DREAM HOME - DUPLICATE CLEANUP\\n\";\n";
$cleanupScript .= "echo \"================================\\n\\n\";\n\n";

// Add exact duplicate cleanup
if (!empty($exactDuplicates)) {
    $cleanupScript .= "// Exact Duplicate Cleanup\n";
    $cleanupScript .= "echo \"🗑️  Removing exact duplicates...\\n\";\n";
    foreach ($exactDuplicates as $hash => $files) {
        if (count($files) > 1) {
            // Keep the first file, remove the rest
            $keepFile = array_shift($files);
            $cleanupScript .= "// Keeping: {$keepFile['path']}\\n";
            foreach ($files as $file) {
                $cleanupScript .= "if (file_exists('{$file['path']}')) {\n";
                $cleanupScript .= "    unlink('{$file['path']}');\n";
                $cleanupScript .= "    echo \"  Removed: {$file['path']}\\n\";\n";
                $cleanupScript .= "}\n";
            }
        }
    }
    $cleanupScript .= "\n";
}

// Add naming duplicate cleanup
if (!empty($namingDuplicates)) {
    $cleanupScript .= "// Naming Duplicate Cleanup\n";
    $cleanupScript .= "echo \"📝 Renaming naming duplicates...\\n\";\n";
    foreach ($namingDuplicates as $filename => $files) {
        if (count($files) > 1) {
            $cleanupScript .= "// Renaming duplicates for: $filename\\n";
            foreach ($files as $i => $file) {
                if ($i > 0) {
                    $pathParts = pathinfo($file['path']);
                    $newName = $pathParts['filename'] . "_duplicate_" . ($i + 1) . '.' . ($pathParts['extension'] ?? '');
                    $newPath = dirname($file['path']) . '/' . $newName;
                    $cleanupScript .= "if (file_exists('{$file['path']}')) {\n";
                    $cleanupScript .= "    rename('{$file['path']}', '$newPath');\n";
                    $cleanupScript .= "    echo \"  Renamed: {$file['path']} -> $newName\\n\";\n";
                    $cleanupScript .= "}\n";
                }
            }
        }
    }
    $cleanupScript .= "\n";
}

$cleanupScript .= "echo \"\\n🎉 Cleanup completed!\\n\";\n";
$cleanupScript .= "echo \"Review the changes and commit to version control.\\n\";\n";

file_put_contents($projectRoot . '/cleanup_duplicates.php', $cleanupScript);
echo "✅ Cleanup script generated: cleanup_duplicates.php\n";

// 13. Final Summary
echo "\n📊 FINAL ANALYSIS SUMMARY\n";
echo "========================\n";

$finalSummary = [
    'scan_date' => date('Y-m-d H:i:s'),
    'total_files' => count($allFiles),
    'exact_duplicate_groups' => count($exactDuplicates),
    'similar_content_groups' => count($similarFiles),
    'naming_duplicate_groups' => count($namingDuplicates),
    'code_duplicates' => $duplicateFunctions,
    'config_duplicates' => $duplicateConfigKeys,
    'asset_duplicate_groups' => count($assetDuplicates),
    'db_duplicate_groups' => $duplicateTables,
    'duplicate_percentage' => $duplicatePercentage,
    'health_score' => $healthScore,
    'cleanup_script_generated' => true
];

file_put_contents($projectRoot . '/duplicate_analysis_results.json', json_encode($finalSummary, JSON_PRETTY_PRINT));

echo "Analysis Results:\n";
foreach ($finalSummary as $key => $value) {
    if (is_bool($value)) {
        echo "$key: " . ($value ? 'Yes' : 'No') . "\n";
    } else {
        echo "$key: $value\n";
    }
}

echo "\n🎉 COMPLETE DUPLICATE ANALYSIS FINISHED!\n";
echo "Your APS Dream Home project has been thoroughly analyzed for duplicates.\n";
echo "Run 'php cleanup_duplicates.php' to clean up identified duplicates.\n";

?>

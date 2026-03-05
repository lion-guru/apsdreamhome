<?php
/**
 * APS Dream Home - Final Deep Scanner
 * Complete project analysis after consolidation
 */

echo "🔍 APS DREAM HOME - FINAL DEEP PROJECT SCAN\n";
echo "=============================================\n\n";

// Initialize results
$scanResults = [
    'timestamp' => date('Y-m-d H:i:s'),
    'total_files' => 0,
    'existing_files' => 0,
    'missing_files' => 0,
    'file_types' => [],
    'security_issues' => 0,
    'performance_issues' => 0,
    'large_files' => 0,
    'duplicate_groups' => 0,
    'scan_details' => []
];

echo "📁 SCANNING PROJECT STRUCTURE...\n";

// Get all PHP files
$projectRoot = __DIR__ . '/..';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($projectRoot));
$allFiles = [];
$existingFiles = [];
$missingFiles = [];

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
        
        $scanResults['total_files']++;
        
        // Track file types
        $directory = dirname($relativePath);
        if (!isset($scanResults['file_types'][$directory])) {
            $scanResults['file_types'][$directory] = 0;
        }
        $scanResults['file_types'][$directory]++;
    }
}

echo "   Total PHP files found: " . count($allFiles) . "\n";

// Check file existence and analyze
echo "\n🔍 ANALYZING FILES...\n";
$securityIssues = 0;
$performanceIssues = 0;
$largeFiles = 0;

foreach ($allFiles as $file) {
    if (file_exists($file['absolute_path'])) {
        $existingFiles[] = $file;
        $scanResults['existing_files']++;
        
        // Analyze file content
        $content = file_get_contents($file['absolute_path']);
        
        // Security analysis
        if (strpos($content, '$_POST') !== false && strpos($content, 'Security::sanitize') === false) {
            $securityIssues++;
        }
        
        // Performance analysis
        if ($file['lines'] > 500) {
            $performanceIssues++;
            $largeFiles++;
        }
        
        // Check for common issues
        if (strpos($content, 'mysql_query') !== false) {
            $securityIssues++;
        }
        
        if (strpos($content, 'eval(') !== false) {
            $securityIssues++;
        }
        
    } else {
        $missingFiles[] = $file;
        $scanResults['missing_files']++;
    }
}

$scanResults['security_issues'] = $securityIssues;
$scanResults['performance_issues'] = $performanceIssues;
$scanResults['large_files'] = $largeFiles;

echo "   Existing files: " . count($existingFiles) . "\n";
echo "   Missing files: " . count($missingFiles) . "\n";
echo "   Security issues: " . $securityIssues . "\n";
echo "   Performance issues: " . $performanceIssues . "\n";
echo "   Large files: " . $largeFiles . "\n";

// Check for duplicates
echo "\n🔍 CHECKING FOR REMAINING DUPLICATES...\n";
$fileGroups = [];
foreach ($existingFiles as $file) {
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

// Find duplicates
$duplicateGroups = [];
foreach ($fileGroups as $baseName => $files) {
    if (count($files) > 1) {
        $duplicateGroups[$baseName] = $files;
    }
}

$scanResults['duplicate_groups'] = count($duplicateGroups);
echo "   Duplicate groups found: " . count($duplicateGroups) . "\n";

// Show top duplicates if any
if (!empty($duplicateGroups)) {
    echo "\n⚠️ REMAINING DUPLICATES:\n";
    $count = 0;
    foreach ($duplicateGroups as $baseName => $files) {
        if ($count >= 10) break;
        echo "   📁 $baseName: " . count($files) . " files\n";
        $count++;
    }
}

// File type analysis
echo "\n📊 FILE TYPE ANALYSIS:\n";
arsort($scanResults['file_types']);
$count = 0;
foreach ($scanResults['file_types'] as $directory => $fileCount) {
    if ($count >= 10) break;
    echo "   📁 $directory: $fileCount files\n";
    $count++;
}

// Generate recommendations
echo "\n💡 RECOMMENDATIONS:\n";
$recommendations = [];

if ($scanResults['missing_files'] > 0) {
    $recommendations[] = "Clean up missing file references ($scanResults[missing_files] files)";
}

if ($scanResults['security_issues'] > 0) {
    $recommendations[] = "Fix security vulnerabilities ($scanResults[security_issues] issues)";
}

if ($scanResults['performance_issues'] > 0) {
    $recommendations[] = "Optimize large files ($scanResults[performance_issues] files)";
}

if ($scanResults['duplicate_groups'] > 0) {
    $recommendations[] = "Consolidate remaining duplicates ($scanResults[duplicate_groups] groups)";
}

if (empty($recommendations)) {
    echo "   ✅ System is optimized and clean!\n";
} else {
    foreach ($recommendations as $rec) {
        echo "   🔧 $rec\n";
    }
}

// Save detailed report
$detailedReport = [
    'timestamp' => $scanResults['timestamp'],
    'scan_summary' => [
        'total_files_scanned' => $scanResults['total_files'],
        'existing_files' => $scanResults['existing_files'],
        'missing_files' => $scanResults['missing_files'],
        'security_issues' => $scanResults['security_issues'],
        'performance_issues' => $scanResults['performance_issues'],
        'large_files' => $scanResults['large_files'],
        'duplicate_groups' => $scanResults['duplicate_groups']
    ],
    'file_type_distribution' => $scanResults['file_types'],
    'remaining_duplicates' => $duplicateGroups,
    'recommendations' => $recommendations,
    'health_score' => calculateHealthScore($scanResults)
];

file_put_contents(__DIR__ . '/../final_deep_scan_report.json', json_encode($detailedReport, JSON_PRETTY_PRINT));

echo "\n📊 FINAL SCAN RESULTS:\n";
echo "===================\n";
echo "📁 Total Files: " . $scanResults['total_files'] . "\n";
echo "✅ Existing Files: " . $scanResults['existing_files'] . "\n";
echo "❌ Missing Files: " . $scanResults['missing_files'] . "\n";
echo "🔒 Security Issues: " . $scanResults['security_issues'] . "\n";
echo "⚡ Performance Issues: " . $scanResults['performance_issues'] . "\n";
echo "📏 Large Files: " . $scanResults['large_files'] . "\n";
echo "🔄 Duplicate Groups: " . $scanResults['duplicate_groups'] . "\n";

$healthScore = calculateHealthScore($scanResults);
echo "\n🏆 SYSTEM HEALTH SCORE: " . $healthScore . "/100\n";

if ($healthScore >= 90) {
    echo "🎉 STATUS: EXCELLENT - System is optimized!\n";
} elseif ($healthScore >= 80) {
    echo "👍 STATUS: GOOD - System is healthy!\n";
} elseif ($healthScore >= 70) {
    echo "⚠️ STATUS: FAIR - Some improvements needed!\n";
} else {
    echo "❌ STATUS: POOR - Major improvements needed!\n";
}

echo "\n✅ FINAL DEEP SCAN COMPLETE!\n";
echo "📄 Report saved to: final_deep_scan_report.json\n";

/**
 * Calculate system health score
 */
function calculateHealthScore($results) {
    $score = 100;
    
    // Deduct for missing files
    $score -= min($results['missing_files'] * 2, 20);
    
    // Deduct for security issues
    $score -= min($results['security_issues'] * 1, 25);
    
    // Deduct for performance issues
    $score -= min($results['performance_issues'] * 0.5, 20);
    
    // Deduct for duplicates
    $score -= min($results['duplicate_groups'] * 3, 15);
    
    return max(0, round($score, 1));
}

?>

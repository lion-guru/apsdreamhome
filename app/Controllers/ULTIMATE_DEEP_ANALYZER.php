<?php
/**
 * APS Dream Home - Ultimate Deep Project Analyzer
 * Maximum level analysis for all files and duplicates
 */

echo "🔍 APS DREAM HOME - ULTIMATE DEEP PROJECT ANALYZER\n";
echo "==================================================\n\n";

// Initialize comprehensive results
$ultimateResults = [
    'timestamp' => date('Y-m-d H:i:s'),
    'scan_summary' => [
        'total_files_scanned' => 0,
        'php_files' => 0,
        'duplicate_files' => 0,
        'empty_files' => 0,
        'junk_files' => 0,
        'unwanted_files' => 0,
        'proper_files' => 0
    ],
    'duplicate_analysis' => [],
    'unwanted_files' => [],
    'file_categories' => [
        'controllers' => [],
        'models' => [],
        'views' => [],
        'utilities' => [],
        'junk' => [],
        'unwanted' => []
    ],
    'recommendations' => []
];

echo "🔍 STARTING ULTIMATE DEEP ANALYSIS...\n";

// Get all PHP files
$projectRoot = __DIR__ . '/..';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($projectRoot));
$allFiles = [];

echo "📁 SCANNING ALL FILES...\n";

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filePath = $file->getPathname();
        $relativePath = str_replace($projectRoot . '/', '', $filePath);
        
        // Skip vendor and node_modules for now
        if (strpos($relativePath, 'vendor/') !== false || strpos($relativePath, 'node_modules/') !== false) {
            continue;
        }
        
        $content = file_get_contents($filePath);
        $lineCount = substr_count($content, "\n");
        $baseName = basename($relativePath, '.php');
        
        // Clean blade files
        if (strpos($baseName, '.blade') !== false) {
            $baseName = str_replace('.blade', '', $baseName);
        }
        
        $allFiles[] = [
            'path' => $relativePath,
            'absolute_path' => $filePath,
            'base_name' => $baseName,
            'size' => filesize($filePath),
            'lines' => $lineCount,
            'content' => $content,
            'content_hash' => md5($content),
            'is_empty' => trim($content) === '' || $lineCount < 3,
            'is_junk' => isJunkFile($baseName, $content),
            'is_unwanted' => isUnwantedFile($baseName, $content),
            'category' => categorizeFile($relativePath, $content)
        ];
        
        $ultimateResults['scan_summary']['total_files_scanned']++;
        $ultimateResults['scan_summary']['php_files']++;
    }
}

echo "   Total PHP files found: " . count($allFiles) . "\n\n";

echo "🔍 ANALYZING FILE CATEGORIES...\n";

// Categorize files
foreach ($allFiles as $file) {
    $category = $file['category'];
    $ultimateResults['file_categories'][$category][] = $file;
    
    if ($file['is_junk']) {
        $ultimateResults['scan_summary']['junk_files']++;
        $ultimateResults['file_categories']['junk'][] = $file;
    }
    
    if ($file['is_unwanted']) {
        $ultimateResults['scan_summary']['unwanted_files']++;
        $ultimateResults['file_categories']['unwanted'][] = $file;
    }
    
    if ($file['is_empty']) {
        $ultimateResults['scan_summary']['empty_files']++;
    }
}

echo "   Controllers: " . count($ultimateResults['file_categories']['controllers']) . "\n";
echo "   Models: " . count($ultimateResults['file_categories']['models']) . "\n";
echo "   Views: " . count($ultimateResults['file_categories']['views']) . "\n";
echo "   Utilities: " . count($ultimateResults['file_categories']['utilities']) . "\n";
echo "   Junk Files: " . count($ultimateResults['file_categories']['junk']) . "\n";
echo "   Unwanted Files: " . count($ultimateResults['file_categories']['unwanted']) . "\n";
echo "   Empty Files: " . $ultimateResults['scan_summary']['empty_files'] . "\n\n";

echo "🔍 FINDING DUPLICATES...\n";

// Find duplicates by base name
$fileGroups = [];
foreach ($allFiles as $file) {
    $baseName = $file['base_name'];
    if (!isset($fileGroups[$baseName])) {
        $fileGroups[$baseName] = [];
    }
    $fileGroups[$baseName][] = $file;
}

$duplicateGroups = [];
foreach ($fileGroups as $baseName => $files) {
    if (count($files) > 1) {
        $duplicateGroups[] = [
            'base_name' => $baseName,
            'files' => $files,
            'count' => count($files),
            'type' => 'name_duplicate'
        ];
        $ultimateResults['scan_summary']['duplicate_files'] += count($files) - 1;
    }
}

$ultimateResults['duplicate_analysis'] = $duplicateGroups;

echo "   Duplicate groups found: " . count($duplicateGroups) . "\n\n";

echo "🔍 IDENTIFYING UNWANTED FILES...\n";

// Display detailed analysis
if (!empty($duplicateGroups)) {
    echo "⚠️ DUPLICATE FILES FOUND:\n";
    echo "========================\n";
    
    foreach ($duplicateGroups as $group) {
        echo "📁 FILE NAME: " . $group['base_name'] . "\n";
        echo "   Files found: " . $group['count'] . "\n";
        
        foreach ($group['files'] as $file) {
            echo "   - " . $file['path'] . " (" . $file['lines'] . " lines)\n";
        }
        echo "\n";
    }
}

if (!empty($ultimateResults['file_categories']['junk'])) {
    echo "🗑️ JUNK FILES FOUND:\n";
    echo "====================\n";
    
    foreach ($ultimateResults['file_categories']['junk'] as $file) {
        echo "🗑️ " . $file['path'] . "\n";
    }
    echo "\n";
}

if (!empty($ultimateResults['file_categories']['unwanted'])) {
    echo "❌ UNWANTED FILES FOUND:\n";
    echo "========================\n";
    
    foreach ($ultimateResults['file_categories']['unwanted'] as $file) {
        echo "❌ " . $file['path'] . "\n";
    }
    echo "\n";
}

// Generate recommendations
if (!empty($duplicateGroups)) {
    $ultimateResults['recommendations'][] = [
        'priority' => 'HIGH',
        'action' => 'DELETE_DUPLICATES',
        'message' => 'Delete duplicate files and keep only one version'
    ];
}

if (!empty($ultimateResults['file_categories']['junk'])) {
    $ultimateResults['recommendations'][] = [
        'priority' => 'HIGH',
        'action' => 'DELETE_JUNK',
        'message' => 'Delete all junk and test files'
    ];
}

if (!empty($ultimateResults['file_categories']['unwanted'])) {
    $ultimateResults['recommendations'][] = [
        'priority' => 'HIGH',
        'action' => 'DELETE_UNWANTED',
        'message' => 'Delete unwanted and backup files'
    ];
}

if (!empty($ultimateResults['file_categories']['utilities'])) {
    $ultimateResults['recommendations'][] = [
        'priority' => 'MEDIUM',
        'action' => 'ORGANIZE_UTILITIES',
        'message' => 'Organize utility files into proper structure'
    ];
}

// Generate comprehensive report
$ultimateReport = [
    'timestamp' => $ultimateResults['timestamp'],
    'scan_summary' => $ultimateResults['scan_summary'],
    'file_categories' => [
        'controllers' => array_map(function($f) { return $f['path']; }, $ultimateResults['file_categories']['controllers']),
        'models' => array_map(function($f) { return $f['path']; }, $ultimateResults['file_categories']['models']),
        'views' => array_map(function($f) { return $f['path']; }, $ultimateResults['file_categories']['views']),
        'utilities' => array_map(function($f) { return $f['path']; }, $ultimateResults['file_categories']['utilities']),
        'junk' => array_map(function($f) { return $f['path']; }, $ultimateResults['file_categories']['junk']),
        'unwanted' => array_map(function($f) { return $f['path']; }, $ultimateResults['file_categories']['unwanted'])
    ],
    'duplicate_analysis' => $ultimateResults['duplicate_analysis'],
    'recommendations' => $ultimateResults['recommendations'],
    'project_health' => [
        'total_files' => $ultimateResults['scan_summary']['total_files_scanned'],
        'has_duplicates' => !empty($duplicateGroups),
        'has_junk' => !empty($ultimateResults['file_categories']['junk']),
        'has_unwanted' => !empty($ultimateResults['file_categories']['unwanted']),
        'health_score' => calculateProjectHealth($ultimateResults)
    ]
];

file_put_contents(__DIR__ . '/../ultimate_deep_analysis.json', json_encode($ultimateReport, JSON_PRETTY_PRINT));

echo "📊 ULTIMATE ANALYSIS RESULTS:\n";
echo "==============================\n";
echo "📁 Total Files Scanned: " . $ultimateResults['scan_summary']['total_files_scanned'] . "\n";
echo "📂 Controllers: " . count($ultimateResults['file_categories']['controllers']) . "\n";
echo "📂 Models: " . count($ultimateResults['file_categories']['models']) . "\n";
echo "📂 Views: " . count($ultimateResults['file_categories']['views']) . "\n";
echo "📂 Utilities: " . count($ultimateResults['file_categories']['utilities']) . "\n";
echo "🗑️ Junk Files: " . count($ultimateResults['file_categories']['junk']) . "\n";
echo "❌ Unwanted Files: " . count($ultimateResults['file_categories']['unwanted']) . "\n";
echo "🔄 Duplicate Groups: " . count($duplicateGroups) . "\n";
echo "📝 Empty Files: " . $ultimateResults['scan_summary']['empty_files'] . "\n";

$healthScore = calculateProjectHealth($ultimateResults);
echo "🏆 Project Health Score: " . $healthScore . "/100\n";

if ($healthScore >= 90) {
    echo "🎉 STATUS: EXCELLENT - Project is very clean!\n";
} elseif ($healthScore >= 80) {
    echo "👍 STATUS: GOOD - Project is well organized!\n";
} elseif ($healthScore >= 70) {
    echo "⚠️ STATUS: FAIR - Some improvements needed!\n";
} else {
    echo "❌ STATUS: POOR - Major cleanup needed!\n";
}

echo "\n✅ ULTIMATE DEEP ANALYSIS COMPLETE!\n";
echo "📄 Report saved to: ultimate_deep_analysis.json\n";

// Helper functions
function categorizeFile($path, $content) {
    if (strpos($path, 'Controllers/') !== false) {
        return 'controllers';
    } elseif (strpos($path, 'Models/') !== false) {
        return 'models';
    } elseif (strpos($path, 'views/') !== false) {
        return 'views';
    } elseif (isJunkFile(basename($path), $content)) {
        return 'junk';
    } elseif (isUnwantedFile(basename($path), $content)) {
        return 'unwanted';
    } else {
        return 'utilities';
    }
}

function isJunkFile($fileName, $content) {
    $junkPatterns = [
        '/test/i',
        '/demo/i',
        '/sample/i',
        '/temp/i',
        '/backup/i',
        '/old/i',
        '/debug/i',
        '/trial/i'
    ];
    
    foreach ($junkPatterns as $pattern) {
        if (preg_match($pattern, $fileName) || preg_match($pattern, $content)) {
            return true;
        }
    }
    
    return false;
}

function isUnwantedFile($fileName, $content) {
    $unwantedPatterns = [
        '/mcp/i',
        '/workflow/i',
        '/beyond/i',
        '/eternal/i',
        '/infinite/i',
        '/cosmic/i',
        '/transcendence/i',
        '/autonomous/i',
        '/super_admin/i',
        '/complete_report/i',
        '/final_status/i'
    ];
    
    foreach ($unwantedPatterns as $pattern) {
        if (preg_match($pattern, $fileName) || preg_match($pattern, $content)) {
            return true;
        }
    }
    
    return false;
}

function calculateProjectHealth($results) {
    $score = 100;
    
    // Deduct for duplicates
    if (!empty($results['duplicate_analysis'])) {
        $score -= count($results['duplicate_analysis']) * 10;
    }
    
    // Deduct for junk files
    if (!empty($results['file_categories']['junk'])) {
        $score -= count($results['file_categories']['junk']) * 5;
    }
    
    // Deduct for unwanted files
    if (!empty($results['file_categories']['unwanted'])) {
        $score -= count($results['file_categories']['unwanted']) * 8;
    }
    
    // Deduct for empty files
    $score -= $results['scan_summary']['empty_files'] * 2;
    
    return max(0, $score);
}

?>

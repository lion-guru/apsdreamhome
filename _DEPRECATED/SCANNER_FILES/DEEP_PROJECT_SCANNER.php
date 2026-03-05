<?php
/**
 * APS Dream Home - Deep Project Scanner & Analyzer
 * Complete project analysis for intelligent optimization
 */

echo "🔍 APS DREAM HOME - DEEP PROJECT SCANNER & ANALYZER\n";
echo "================================================\n\n";

// Initialize analysis results
$analysisResults = [
    'timestamp' => date('Y-m-d H:i:s'),
    'total_files' => 0,
    'file_analysis' => [],
    'duplicate_analysis' => [],
    'code_quality_analysis' => [],
    'security_analysis' => [],
    'performance_analysis' => [],
    'recommendations' => []
];

echo "🔍 SCANNING ENTIRE PROJECT STRUCTURE...\n";

// 1. Deep scan all PHP files
$projectRoot = __DIR__ . '/..';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($projectRoot));

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filePath = $file->getPathname();
        $relativePath = str_replace($projectRoot . '/', '', $filePath);
        
        // Skip vendor and node_modules
        if (strpos($relativePath, 'vendor/') !== false || strpos($relativePath, 'node_modules/') !== false) {
            continue;
        }
        
        $analysisResults['total_files']++;
        
        // Analyze file
        analyzeFile($filePath, $relativePath, $analysisResults);
    }
}

echo "   Total PHP Files: {$analysisResults['total_files']}\n";

// 2. Analyze duplicates
echo "\n🔍 ANALYZING DUPLICATE FILES...\n";
analyzeDuplicates($analysisResults);

// 3. Analyze code quality
echo "\n📊 ANALYZING CODE QUALITY...\n";
analyzeCodeQuality($analysisResults);

// 4. Analyze security
echo "\n🔒 ANALYZING SECURITY...\n";
analyzeSecurity($analysisResults);

// 5. Analyze performance
echo "\n⚡ ANALYZING PERFORMANCE...\n";
analyzePerformance($analysisResults);

// 6. Generate comprehensive report
echo "\n📋 GENERATING COMPREHENSIVE ANALYSIS REPORT...\n";
generateComprehensiveReport($analysisResults);

echo "\n✅ DEEP PROJECT ANALYSIS COMPLETE!\n";

/**
 * Analyze individual file
 */
function analyzeFile($filePath, $relativePath, &$analysisResults) {
    $content = file_get_contents($filePath);
    $fileInfo = [
        'path' => $relativePath,
        'size' => filesize($filePath),
        'lines' => substr_count($content, "\n"),
        'functions' => [],
        'classes' => [],
        'security_issues' => [],
        'performance_issues' => []
    ];
    
    // Extract functions
    preg_match_all('/function\s+(\w+)\s*\(/', $content, $matches);
    $fileInfo['functions'] = $matches[1];
    
    // Extract classes
    preg_match_all('/class\s+(\w+)/', $content, $matches);
    $fileInfo['classes'] = $matches[1];
    
    // Check for security issues
    if (strpos($content, '$_GET') !== false) {
        $fileInfo['security_issues'][] = 'Direct $_GET usage';
    }
    if (strpos($content, '$_POST') !== false) {
        $fileInfo['security_issues'][] = 'Direct $_POST usage';
    }
    if (strpos($content, 'mysql_query') !== false) {
        $fileInfo['security_issues'][] = 'Direct mysql_query usage';
    }
    if (strpos($content, 'eval(') !== false) {
        $fileInfo['security_issues'][] = 'eval() function usage';
    }
    
    // Check for performance issues
    if (preg_match('/for\s*\([^)]+\).*mysql_query/', $content)) {
        $fileInfo['performance_issues'][] = 'Query in loop';
    }
    if (substr_count($content, "\n") > 500) {
        $fileInfo['performance_issues'][] = 'Large file (>500 lines)';
    }
    
    $analysisResults['file_analysis'][] = $fileInfo;
}

/**
 * Analyze duplicates
 */
function analyzeDuplicates(&$analysisResults) {
    $fileGroups = [];
    
    // Group files by name
    foreach ($analysisResults['file_analysis'] as $file) {
        $baseName = basename($file['path'], '.php');
        $directory = dirname($file['path']);
        
        if (!isset($fileGroups[$baseName])) {
            $fileGroups[$baseName] = [];
        }
        
        $fileGroups[$baseName][] = $file;
    }
    
    // Find duplicates
    foreach ($fileGroups as $baseName => $files) {
        if (count($files) > 1) {
            $analysisResults['duplicate_analysis'][] = [
                'base_name' => $baseName,
                'count' => count($files),
                'files' => $files,
                'recommendation' => 'Keep main file, merge functionality, delete duplicates'
            ];
        }
    }
    
    $duplicateCount = count(array_filter($fileGroups, function($group) {
        return count($group) > 1;
    }));
    
    echo "   Duplicate Groups: $duplicateCount\n";
}

/**
 * Analyze code quality
 */
function analyzeCodeQuality(&$analysisResults) {
    $totalFunctions = 0;
    $totalClasses = 0;
    $totalLines = 0;
    $largeFiles = 0;
    $filesWithoutDocs = 0;
    
    foreach ($analysisResults['file_analysis'] as $file) {
        $totalFunctions += count($file['functions']);
        $totalClasses += count($file['classes']);
        $totalLines += $file['lines'];
        
        if ($file['lines'] > 500) {
            $largeFiles++;
        }
        
        // Check for documentation
        $content = file_get_contents(__DIR__ . '/../' . $file['path']);
        if (strpos($content, '/**') === false && strpos($content, '/*') === false) {
            $filesWithoutDocs++;
        }
    }
    
    $analysisResults['code_quality_analysis'] = [
        'total_functions' => $totalFunctions,
        'total_classes' => $totalClasses,
        'total_lines' => $totalLines,
        'large_files' => $largeFiles,
        'files_without_documentation' => $filesWithoutDocs,
        'avg_lines_per_file' => $analysisResults['total_files'] > 0 ? round($totalLines / $analysisResults['total_files'], 2) : 0
    ];
    
    echo "   Total Functions: $totalFunctions\n";
    echo "   Total Classes: $totalClasses\n";
    echo "   Large Files: $largeFiles\n";
    echo "   Files Without Documentation: $filesWithoutDocs\n";
}

/**
 * Analyze security
 */
function analyzeSecurity(&$analysisResults) {
    $securityIssues = [];
    $vulnerableFiles = 0;
    
    foreach ($analysisResults['file_analysis'] as $file) {
        if (!empty($file['security_issues'])) {
            $vulnerableFiles++;
            foreach ($file['security_issues'] as $issue) {
                if (!isset($securityIssues[$issue])) {
                    $securityIssues[$issue] = 0;
                }
                $securityIssues[$issue]++;
            }
        }
    }
    
    $analysisResults['security_analysis'] = [
        'vulnerable_files' => $vulnerableFiles,
        'security_issues_distribution' => $securityIssues,
        'most_common_issue' => array_keys($securityIssues, max($securityIssues))[0] ?? 'None'
    ];
    
    echo "   Vulnerable Files: $vulnerableFiles\n";
    echo "   Most Common Issue: " . ($analysisResults['security_analysis']['most_common_issue'] ?? 'None') . "\n";
}

/**
 * Analyze performance
 */
function analyzePerformance(&$analysisResults) {
    $performanceIssues = [];
    $slowFiles = 0;
    
    foreach ($analysisResults['file_analysis'] as $file) {
        if (!empty($file['performance_issues'])) {
            $slowFiles++;
            foreach ($file['performance_issues'] as $issue) {
                if (!isset($performanceIssues[$issue])) {
                    $performanceIssues[$issue] = 0;
                }
                $performanceIssues[$issue]++;
            }
        }
    }
    
    $analysisResults['performance_analysis'] = [
        'slow_files' => $slowFiles,
        'performance_issues_distribution' => $performanceIssues,
        'most_common_performance_issue' => array_keys($performanceIssues, max($performanceIssues))[0] ?? 'None'
    ];
    
    echo "   Performance Issue Files: $slowFiles\n";
    echo "   Most Common Performance Issue: " . ($analysisResults['performance_analysis']['most_common_performance_issue'] ?? 'None') . "\n";
}

/**
 * Generate comprehensive report
 */
function generateComprehensiveReport($analysisResults) {
    // Generate recommendations
    $analysisResults['recommendations'] = generateRecommendations($analysisResults);
    
    // Save detailed analysis
    $reportFile = __DIR__ . '/../deep_project_analysis.json';
    file_put_contents($reportFile, json_encode($analysisResults, JSON_PRETTY_PRINT));
    
    echo "   Detailed analysis saved to: deep_project_analysis.json\n";
    
    // Display summary
    echo "\n📊 ANALYSIS SUMMARY:\n";
    echo "   Total Files: {$analysisResults['total_files']}\n";
    echo "   Duplicate Groups: " . count($analysisResults['duplicate_analysis']) . "\n";
    echo "   Security Issues: " . $analysisResults['security_analysis']['vulnerable_files'] . "\n";
    echo "   Performance Issues: " . $analysisResults['performance_analysis']['slow_files'] . "\n";
    echo "   Recommendations: " . count($analysisResults['recommendations']) . "\n";
}

/**
 * Generate recommendations
 */
function generateRecommendations($analysisResults) {
    $recommendations = [];
    
    // Security recommendations
    if ($analysisResults['security_analysis']['vulnerable_files'] > 0) {
        $recommendations[] = [
            'priority' => 'HIGH',
            'category' => 'Security',
            'description' => 'Fix security vulnerabilities in ' . $analysisResults['security_analysis']['vulnerable_files'] . ' files',
            'action' => 'Implement input sanitization and prepared statements'
        ];
    }
    
    // Performance recommendations
    if ($analysisResults['performance_analysis']['slow_files'] > 0) {
        $recommendations[] = [
            'priority' => 'MEDIUM',
            'category' => 'Performance',
            'description' => 'Optimize performance issues in ' . $analysisResults['performance_analysis']['slow_files'] . ' files',
            'action' => 'Remove queries from loops, implement caching, optimize large files'
        ];
    }
    
    // Duplicate recommendations
    if (!empty($analysisResults['duplicate_analysis'])) {
        $recommendations[] = [
            'priority' => 'HIGH',
            'category' => 'Code Organization',
            'description' => 'Consolidate ' . count($analysisResults['duplicate_analysis']) . ' duplicate file groups',
            'action' => 'Merge functionality into main files, delete duplicates'
        ];
    }
    
    // Documentation recommendations
    if ($analysisResults['code_quality_analysis']['files_without_documentation'] > 0) {
        $recommendations[] = [
            'priority' => 'LOW',
            'category' => 'Documentation',
            'description' => 'Add documentation to ' . $analysisResults['code_quality_analysis']['files_without_documentation'] . ' files',
            'action' => 'Add PHPDoc comments and documentation blocks'
        ];
    }
    
    return $recommendations;
}

?>

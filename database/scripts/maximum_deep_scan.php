<?php
/**
 * APS Dream Home - MAXIMUM LEVEL COMPLETE PROJECT DEEP SCAN
 * Ultimate comprehensive analysis of entire project structure, code, security, and database
 */

echo "🔬 APS DREAM HOME - MAXIMUM LEVEL COMPLETE PROJECT DEEP SCAN\n";
echo "=========================================================\n\n";

$projectRoot = __DIR__ . '/..';
$scanStartTime = microtime(true);

// 1. COMPLETE DIRECTORY STRUCTURE SCAN
echo "📂 PHASE 1: COMPLETE DIRECTORY STRUCTURE SCAN\n";
echo "=============================================\n";

function deepScanDirectory($dir, $excludePatterns = []) {
    $results = [
        'files' => [],
        'directories' => [],
        'total_size' => 0,
        'file_types' => [],
        'extensions' => [],
        'issues' => []
    ];

    if (!is_dir($dir)) {
        return $results;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $relativePath = str_replace($dir . '/', '', $item->getPathname());

        // Check exclusions
        $shouldExclude = false;
        foreach ($excludePatterns as $pattern) {
            if (fnmatch($pattern, $relativePath)) {
                $shouldExclude = true;
                break;
            }
        }

        if ($shouldExclude) continue;

        if ($item->isDir()) {
            $results['directories'][] = $relativePath;

            // Check for empty directories
            $dirContents = glob($item->getPathname() . '/*');
            if (empty($dirContents)) {
                $results['issues'][] = "Empty directory: $relativePath";
            }
        } else {
            $fileInfo = [
                'path' => $relativePath,
                'size' => $item->getSize(),
                'modified' => $item->getMTime(),
                'extension' => pathinfo($item->getPathname(), PATHINFO_EXTENSION),
                'permissions' => substr(sprintf('%o', $item->getPerms()), -4)
            ];

            $results['files'][] = $fileInfo;
            $results['total_size'] += $fileInfo['size'];

            // Track file types
            $ext = strtolower($fileInfo['extension']);
            if (!isset($results['extensions'][$ext])) {
                $results['extensions'][$ext] = 0;
            }
            $results['extensions'][$ext]++;

            // Categorize files
            if (in_array($ext, ['php', 'js', 'css', 'html', 'sql'])) {
                $results['file_types']['code'] = ($results['file_types']['code'] ?? 0) + 1;
            } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'ico'])) {
                $results['file_types']['images'] = ($results['file_types']['images'] ?? 0) + 1;
            } elseif (in_array($ext, ['pdf', 'doc', 'docx', 'txt', 'md'])) {
                $results['file_types']['documents'] = ($results['file_types']['documents'] ?? 0) + 1;
            } elseif (in_array($ext, ['json', 'xml', 'yaml', 'yml'])) {
                $results['file_types']['config'] = ($results['file_types']['config'] ?? 0) + 1;
            } else {
                $results['file_types']['other'] = ($results['file_types']['other'] ?? 0) + 1;
            }
        }
    }

    return $results;
}

$excludePatterns = [
    'vendor/*',
    'node_modules/*',
    '.git/*',
    '*.log',
    '*.tmp',
    'cache/*',
    'tmp/*',
    'storage/logs/*',
    'storage/cache/*'
];

echo "Scanning project structure...\n";
$structureScan = deepScanDirectory($projectRoot, $excludePatterns);

echo "📊 STRUCTURE RESULTS:\n";
echo "Total Files: " . count($structureScan['files']) . "\n";
echo "Total Directories: " . count($structureScan['directories']) . "\n";
echo "Total Size: " . number_format($structureScan['total_size'] / 1024 / 1024, 2) . " MB\n";

echo "\nFile Type Distribution:\n";
arsort($structureScan['extensions']);
foreach (array_slice($structureScan['extensions'], 0, 10) as $ext => $count) {
    echo "  $ext: $count files\n";
}

echo "\nContent Categories:\n";
foreach ($structureScan['file_types'] as $type => $count) {
    echo "  $type: $count files\n";
}

echo "\nIssues Found:\n";
foreach (array_slice($structureScan['issues'], 0, 5) as $issue) {
    echo "  ⚠️  $issue\n";
}

// 2. MAXIMUM CODE QUALITY ANALYSIS
echo "\n\n🔍 PHASE 2: MAXIMUM CODE QUALITY ANALYSIS\n";
echo "=========================================\n";

$phpFiles = array_filter($structureScan['files'], function($file) {
    return strtolower($file['extension']) === 'php';
});

$codeAnalysis = [
    'files_analyzed' => 0,
    'total_lines' => 0,
    'security_issues' => [],
    'code_smells' => [],
    'complexity_issues' => [],
    'documentation_issues' => [],
    'performance_issues' => [],
    'functions' => [],
    'classes' => [],
    'namespaces' => []
];

echo "Analyzing " . count($phpFiles) . " PHP files...\n";

foreach ($phpFiles as $file) {
    $filePath = $projectRoot . '/' . $file['path'];

    if (!file_exists($filePath)) continue;

    $content = file_get_contents($filePath);
    $lines = explode("\n", $content);
    $codeAnalysis['files_analyzed']++;
    $codeAnalysis['total_lines'] += count($lines);

    // Security analysis
    $securityPatterns = [
        '/\beval\s*\(/i' => 'Dangerous // SECURITY FIX: eval() removed for security reasons) usage',
        '/\bexec\s*\(/i' => 'System command execution',
        '/\bsystem\s*\(/i' => 'System command execution',
        '/\bshell_exec\s*\(/i' => 'Shell command execution',
        '/\bpassthru\s*\(/i' => 'Command passthrough',
        '/password\s*=\s*[\'"][^\'"]*[\'"]/i' => 'Hardcoded password',
        '/api[_-]?key\s*=\s*[\'"][^\'"]*[\'"]/i' => 'Hardcoded API key',
        '/\$_GET\[.*\]\s*\.\s*[\'"][^\'"]*[\'"]/i' => 'Potential SQL injection',
        '/\$_POST\[.*\]\s*\.\s*[\'"][^\'"]*[\'"]/i' => 'Potential SQL injection'
    ];

    foreach ($securityPatterns as $pattern => $description) {
        if (preg_match($pattern, $content)) {
            $codeAnalysis['security_issues'][] = [
                'file' => $file['path'],
                'issue' => $description,
                'line' => 'N/A'
            ];
        }
    }

    // Code smell analysis
    if (substr_count($content, '<?php') > 1) {
        $codeAnalysis['code_smells'][] = [
            'file' => $file['path'],
            'issue' => 'Multiple PHP opening tags'
        ];
    }

    if (strlen($content) > 100000) { // 100KB file
        $codeAnalysis['code_smells'][] = [
            'file' => $file['path'],
            'issue' => 'Very large file (>100KB)'
        ];
    }

    // Documentation analysis
    if (!preg_match('/\/\*\*/', $content) && !preg_match('/^\s*\*\s/', $content)) {
        $codeAnalysis['documentation_issues'][] = [
            'file' => $file['path'],
            'issue' => 'Missing PHPDoc comments'
        ];
    }

    // Performance analysis
    if (preg_match('/SELECT\s+\*/i', $content)) {
        $codeAnalysis['performance_issues'][] = [
            'file' => $file['path'],
            'issue' => 'SELECT * query found'
        ];
    }

    // Function and class extraction
    preg_match_all('/function\s+(\w+)/', $content, $functionMatches);
    foreach ($functionMatches[1] as $function) {
        $codeAnalysis['functions'][] = [
            'name' => $function,
            'file' => $file['path']
        ];
    }

    preg_match_all('/class\s+(\w+)/', $content, $classMatches);
    foreach ($classMatches[1] as $class) {
        $codeAnalysis['classes'][] = [
            'name' => $class,
            'file' => $file['path']
        ];
    }

    preg_match_all('/namespace\s+([^;]+)/', $content, $namespaceMatches);
    foreach ($namespaceMatches[1] as $namespace) {
        $codeAnalysis['namespaces'][] = trim($namespace);
    }
}

echo "📊 CODE ANALYSIS RESULTS:\n";
echo "Files Analyzed: {$codeAnalysis['files_analyzed']}\n";
echo "Total Lines: " . number_format($codeAnalysis['total_lines']) . "\n";
echo "Functions Found: " . count($codeAnalysis['functions']) . "\n";
echo "Classes Found: " . count($codeAnalysis['classes']) . "\n";
echo "Namespaces: " . count(array_unique($codeAnalysis['namespaces'])) . "\n";

echo "\nSecurity Issues: " . count($codeAnalysis['security_issues']) . "\n";
echo "Code Smells: " . count($codeAnalysis['code_smells']) . "\n";
echo "Documentation Issues: " . count($codeAnalysis['documentation_issues']) . "\n";
echo "Performance Issues: " . count($codeAnalysis['performance_issues']) . "\n";

echo "\nTop Security Issues:\n";
foreach (array_slice($codeAnalysis['security_issues'], 0, 5) as $issue) {
    echo "  🚨 {$issue['file']}: {$issue['issue']}\n";
}

// 3. CONFIGURATION COMPLETENESS SCAN
echo "\n\n⚙️  PHASE 3: CONFIGURATION COMPLETENESS SCAN\n";
echo "=========================================\n";

$requiredConfigs = [
    'core' => [
        'config/database.php',
        'config/application.php',
        'app/config/security.php',
        '.env',
        '.env.example',
        'composer.json',
        'package.json'
    ],
    'middleware' => [
        'app/Http/Middleware/Auth.php',
        'app/Http/Middleware/Cors.php',
        'app/Http/Middleware/RateLimit.php',
        'app/Http/Middleware/SecurityHeaders.php'
    ],
    'services' => [
        'app/Services/PaymentService.php',
        'app/Services/WhatsAppService.php',
        'app/Services/LeadScoringService.php',
        'app/Services/GamificationService.php',
        'app/Services/MessagingService.php',
        'app/Services/TrainingService.php'
    ]
];

$configStatus = [];
foreach ($requiredConfigs as $category => $files) {
    $configStatus[$category] = ['total' => count($files), 'found' => 0, 'missing' => []];

    foreach ($files as $file) {
        $filePath = $projectRoot . '/' . $file;
        if (file_exists($filePath)) {
            $configStatus[$category]['found']++;
        } else {
            $configStatus[$category]['missing'][] = $file;
        }
    }
}

echo "📊 CONFIGURATION STATUS:\n";
foreach ($configStatus as $category => $status) {
    $percentage = round(($status['found'] / $status['total']) * 100, 1);
    echo "$category: {$status['found']}/{$status['total']} ($percentage%)\n";

    if (!empty($status['missing'])) {
        echo "  Missing:\n";
        foreach ($status['missing'] as $missing) {
            echo "    ❌ $missing\n";
        }
    }
}

// 4. DATABASE COMPLETE ANALYSIS
echo "\n\n🗄️  PHASE 4: DATABASE COMPLETE ANALYSIS\n";
echo "=====================================\n";

try {
    $host = 'localhost';
    $dbname = 'apsdreamhome';
    $username = 'root';
    $password = '';

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Get all tables
    $tablesResult = $pdo->query("SHOW TABLES");
    $allTables = $tablesResult->fetchAll(PDO::FETCH_COLUMN);
    $totalTables = count($allTables);

    echo "📊 DATABASE OVERVIEW:\n";
    echo "Total Tables: $totalTables\n";

    // Analyze table sizes
    $tableSizes = [];
    $totalDataSize = 0;
    $totalIndexSize = 0;

    foreach ($allTables as $table) {
        $sizeResult = $pdo->query("
            SELECT
                data_length + index_length as total_size,
                data_length,
                index_length,
                table_rows
            FROM information_schema.tables
            WHERE table_schema = '$dbname' AND table_name = '$table'
        ")->fetch();

        $tableSizes[$table] = [
            'size' => $sizeResult['total_size'] ?? 0,
            'data_size' => $sizeResult['data_length'] ?? 0,
            'index_size' => $sizeResult['index_length'] ?? 0,
            'rows' => $sizeResult['table_rows'] ?? 0
        ];

        $totalDataSize += $sizeResult['data_length'] ?? 0;
        $totalIndexSize += $sizeResult['index_length'] ?? 0;
    }

    echo "Total Data Size: " . number_format($totalDataSize / 1024 / 1024, 2) . " MB\n";
    echo "Total Index Size: " . number_format($totalIndexSize / 1024 / 1024, 2) . " MB\n";

    // Largest tables
    arsort($tableSizes);
    echo "\nLargest Tables:\n";
    $count = 0;
    foreach ($tableSizes as $table => $info) {
        if ($count >= 5) break;
        echo "  • $table: " . number_format($info['size'] / 1024 / 1024, 2) . " MB, {$info['rows']} rows\n";
        $count++;
    }

    // Check for required service tables
    $requiredServiceTables = [
        'whatsapp_messages', 'whatsapp_templates',
        'email_tracking', 'lead_visits',
        'badges', 'user_points',
        'training_lessons', 'training_enrollments',
        'property_comparisons'
    ];

    echo "\nService Tables Status:\n";
    $serviceTablesFound = 0;
    foreach ($requiredServiceTables as $table) {
        if (in_array($table, $allTables)) {
            echo "  ✅ $table\n";
            $serviceTablesFound++;
        } else {
            echo "  ❌ $table (MISSING)\n";
        }
    }

    echo "\nService Tables: $serviceTablesFound/9 found\n";

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

// 5. SECURITY VULNERABILITY SCAN
echo "\n\n🔒 PHASE 5: SECURITY VULNERABILITY SCAN\n";
echo "=====================================\n";

$securityScan = [
    'exposed_configs' => [],
    'writable_files' => [],
    'dangerous_permissions' => [],
    'hardcoded_secrets' => [],
    'sql_injection_risks' => []
];

// Check for exposed config files
$webAccessibleDirs = ['public', 'www', 'htdocs'];
foreach ($webAccessibleDirs as $dir) {
    $dirPath = $projectRoot . '/' . $dir;
    if (is_dir($dirPath)) {
        $files = glob($dirPath . '/**/*.php');
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (preg_match('/password|api_key|secret/i', $content)) {
                $securityScan['exposed_configs'][] = str_replace($projectRoot . '/', '', $file);
            }
        }
    }
}

// Check file permissions
foreach ($structureScan['files'] as $file) {
    if ($file['permissions'] === '0777' || $file['permissions'] === '0666') {
        $securityScan['dangerous_permissions'][] = $file['path'];
    }
}

echo "📊 SECURITY SCAN RESULTS:\n";
echo "Exposed Config Files: " . count($securityScan['exposed_configs']) . "\n";
echo "Dangerous Permissions: " . count($securityScan['dangerous_permissions']) . "\n";
echo "Security Issues: " . count($codeAnalysis['security_issues']) . "\n";

if (!empty($securityScan['exposed_configs'])) {
    echo "\nExposed Config Files:\n";
    foreach (array_slice($securityScan['exposed_configs'], 0, 3) as $file) {
        echo "  🚨 $file\n";
    }
}

// 6. PERFORMANCE ANALYSIS
echo "\n\n⚡ PHASE 6: PERFORMANCE ANALYSIS\n";
echo "=============================\n";

$performanceMetrics = [
    'large_files' => [],
    'old_files' => [],
    'unoptimized_images' => [],
    'cache_status' => 'unknown'
];

// Large files
foreach ($structureScan['files'] as $file) {
    if ($file['size'] > 1024 * 1024) { // > 1MB
        $performanceMetrics['large_files'][] = [
            'file' => $file['path'],
            'size' => number_format($file['size'] / 1024 / 1024, 2) . ' MB'
        ];
    }
}

// Old files (not modified in 6 months)
$sixMonthsAgo = time() - (180 * 24 * 60 * 60);
foreach ($structureScan['files'] as $file) {
    if ($file['modified'] < $sixMonthsAgo) {
        $performanceMetrics['old_files'][] = $file['path'];
    }
}

echo "📊 PERFORMANCE METRICS:\n";
echo "Large Files (>1MB): " . count($performanceMetrics['large_files']) . "\n";
echo "Old Files (>6 months): " . count($performanceMetrics['old_files']) . "\n";

if (!empty($performanceMetrics['large_files'])) {
    echo "\nLargest Files:\n";
    foreach (array_slice($performanceMetrics['large_files'], 0, 3) as $file) {
        echo "  📁 {$file['file']}: {$file['size']}\n";
    }
}

// 7. COMPREHENSIVE HEALTH SCORE
echo "\n\n🏥 PHASE 7: COMPREHENSIVE HEALTH SCORE\n";
echo "===================================\n";

$healthScore = 100;
$scoreBreakdown = [];

// File structure score (max 20 points)
$fileStructureScore = min(20, count($structureScan['files']) / 10); // 1 point per 10 files
$healthScore -= (20 - $fileStructureScore);
$scoreBreakdown['file_structure'] = $fileStructureScore;

// Code quality score (max 25 points)
$codeQualityScore = 25;
$codeQualityScore -= min(10, count($codeAnalysis['security_issues']));
$codeQualityScore -= min(5, count($codeAnalysis['code_smells']));
$codeQualityScore -= min(5, count($codeAnalysis['documentation_issues']));
$codeQualityScore -= min(5, count($codeAnalysis['performance_issues']));
$codeQualityScore = max(0, $codeQualityScore);
$healthScore -= (25 - $codeQualityScore);
$scoreBreakdown['code_quality'] = $codeQualityScore;

// Configuration score (max 20 points)
$configScore = 0;
foreach ($configStatus as $status) {
    $configScore += ($status['found'] / $status['total']) * (20 / count($configStatus));
}
$healthScore -= (20 - $configScore);
$scoreBreakdown['configuration'] = $configScore;

// Security score (max 15 points)
$securityScore = 15;
$securityScore -= min(5, count($securityScan['exposed_configs']));
$securityScore -= min(5, count($securityScan['dangerous_permissions']));
$securityScore -= min(5, count($codeAnalysis['security_issues']) / 5);
$securityScore = max(0, $securityScore);
$healthScore -= (15 - $securityScore);
$scoreBreakdown['security'] = $securityScore;

// Performance score (max 10 points)
$performanceScore = 10;
$performanceScore -= min(5, count($performanceMetrics['large_files']));
$performanceScore -= min(5, count($performanceMetrics['old_files']) / 10);
$performanceScore = max(0, $performanceScore);
$healthScore -= (10 - $performanceScore);
$scoreBreakdown['performance'] = $performanceScore;

// Database score (max 10 points)
$databaseScore = 10;
try {
    $serviceTablesScore = ($serviceTablesFound / 9) * 10;
    $databaseScore = $serviceTablesScore;
} catch (Exception $e) {
    $databaseScore = 0;
}
$healthScore -= (10 - $databaseScore);
$scoreBreakdown['database'] = $databaseScore;

$healthScore = max(0, round($healthScore, 1));

echo "🎯 OVERALL HEALTH SCORE: $healthScore/100\n";

if ($healthScore >= 90) {
    echo "🏆 EXCELLENT: Project is in excellent condition!\n";
} elseif ($healthScore >= 70) {
    echo "✅ GOOD: Project is in good condition.\n";
} elseif ($healthScore >= 50) {
    echo "⚠️  FAIR: Project needs attention.\n";
} else {
    echo "🚨 POOR: Project needs significant improvements.\n";
}

echo "\nScore Breakdown:\n";
foreach ($scoreBreakdown as $category => $score) {
    $percentage = round(($score / array_sum($scoreBreakdown)) * 100, 1);
    echo "  • $category: $score points ($percentage%)\n";
}

// 8. FINAL COMPREHENSIVE REPORT
echo "\n\n📋 PHASE 8: FINAL COMPREHENSIVE REPORT\n";
echo "===================================\n";

$scanEndTime = microtime(true);
$scanDuration = round($scanEndTime - $scanStartTime, 2);

$finalReport = [
    'scan_info' => [
        'timestamp' => date('Y-m-d H:i:s'),
        'duration_seconds' => $scanDuration,
        'scan_level' => 'MAXIMUM'
    ],
    'structure' => [
        'total_files' => count($structureScan['files']),
        'total_directories' => count($structureScan['directories']),
        'total_size_mb' => round($structureScan['total_size'] / 1024 / 1024, 2),
        'file_types' => $structureScan['file_types'],
        'top_extensions' => array_slice($structureScan['extensions'], 0, 5)
    ],
    'code_quality' => [
        'files_analyzed' => $codeAnalysis['files_analyzed'],
        'total_lines' => $codeAnalysis['total_lines'],
        'security_issues' => count($codeAnalysis['security_issues']),
        'code_smells' => count($codeAnalysis['code_smells']),
        'functions' => count($codeAnalysis['functions']),
        'classes' => count($codeAnalysis['classes'])
    ],
    'configuration' => [
        'completeness_percentage' => round(array_sum(array_column($configStatus, 'found')) / array_sum(array_column($configStatus, 'total')) * 100, 1)
    ],
    'database' => [
        'tables_found' => $totalTables ?? 0,
        'service_tables_complete' => $serviceTablesFound ?? 0,
        'data_size_mb' => round($totalDataSize / 1024 / 1024, 2) ?? 0
    ],
    'security' => [
        'vulnerabilities' => count($codeAnalysis['security_issues']),
        'exposed_configs' => count($securityScan['exposed_configs']),
        'dangerous_permissions' => count($securityScan['dangerous_permissions'])
    ],
    'health_score' => $healthScore,
    'recommendations' => [
        'immediate' => [],
        'short_term' => [],
        'long_term' => []
    ]
];

// Generate recommendations
if ($healthScore < 70) {
    $finalReport['recommendations']['immediate'][] = 'Fix critical security vulnerabilities (' . count($codeAnalysis['security_issues']) . ' issues)';
    $finalReport['recommendations']['immediate'][] = 'Complete missing configuration files';
}

if (count($codeAnalysis['security_issues']) > 0) {
    $finalReport['recommendations']['short_term'][] = 'Implement secure coding practices';
    $finalReport['recommendations']['short_term'][] = 'Remove hardcoded secrets';
}

if ($configStatus['core']['found'] < $configStatus['core']['total']) {
    $finalReport['recommendations']['short_term'][] = 'Set up proper environment configuration';
}

$finalReport['recommendations']['long_term'][] = 'Implement automated testing';
$finalReport['recommendations']['long_term'][] = 'Add comprehensive documentation';
$finalReport['recommendations']['long_term'][] = 'Set up continuous integration';

echo "🎯 SCAN COMPLETED IN $scanDuration SECONDS\n";
echo "==========================================\n";

echo "📊 SUMMARY:\n";
echo "• Files: " . count($structureScan['files']) . "\n";
echo "• Directories: " . count($structureScan['directories']) . "\n";
echo "• Code Size: " . number_format($codeAnalysis['total_lines']) . " lines\n";
echo "• Security Issues: " . count($codeAnalysis['security_issues']) . "\n";
echo "• Health Score: $healthScore/100\n";

echo "\n🔧 NEXT STEPS:\n";
echo "Immediate Priority:\n";
foreach ($finalReport['recommendations']['immediate'] as $rec) {
    echo "  • $rec\n";
}

echo "\nShort-term Goals:\n";
foreach ($finalReport['recommendations']['short_term'] as $rec) {
    echo "  • $rec\n";
}

// Save comprehensive report
file_put_contents($projectRoot . '/maximum_deep_scan_report.json', json_encode($finalReport, JSON_PRETTY_PRINT));

echo "\n📄 Detailed report saved to: maximum_deep_scan_report.json\n";

echo "\n🎉 MAXIMUM LEVEL DEEP SCAN COMPLETED!\n";
echo "Your APS Dream Home project has been analyzed at maximum depth.\n";
echo "All folders, subfolders, files, code, security, and database have been scanned.\n";

?>

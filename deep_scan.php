<?php
/**
 * APS Dream Home - Simple Deep Scan Tool
 * Comprehensive project analysis and health check
 */

echo "🔍 APS Dream Home - Deep Scan Tool\n";
echo "================================\n\n";

// Define scan results
$scanResults = [
    'security' => [],
    'performance' => [],
    'code_quality' => [],
    'database' => [],
    'documentation' => [],
    'configuration' => [],
    'optimization' => []
];

$totalIssues = 0;
$securityIssues = 0;
$performanceIssues = 0;
$codeQualityIssues = 0;
$configIssues = 0;
$missingDocs = [];
$dbIssues = 0;
$optIssues = 0;
$optimizedCount = 0;

echo "📊 Scanning Project Structure...\n";

// 1. Check project structure
$projectRoot = __DIR__;
$directories = [
    'app/', 'assets/', 'config/', 'database/', 'docs/', 'logs/', 'public/', 'routes/', 'storage/', 'uploads/', 'views/'
];

$missingDirs = [];
foreach ($directories as $dir) {
    if (!is_dir($projectRoot . $dir)) {
        $missingDirs[] = $dir;
    }
}

if (empty($missingDirs)) {
    echo "✅ All required directories exist\n";
} else {
    echo "❌ Missing directories: " . implode(', ', $missingDirs) . "\n";
    $totalIssues += count($missingDirs);
}

// 2. Simple file scan without complex iterators
echo "\n📁 File Scan\n";

// Scan PHP files
$phpFiles = [];
$directoriesToScan = ['app/', 'config/', 'public/', 'routes/'];

foreach ($directoriesToScan as $dir) {
    if (is_dir($projectRoot . $dir)) {
        $files = glob($projectRoot . $dir . '/*.php');
        if ($files) {
            foreach ($files as $file) {
                $phpFiles[] = $file;
            }
        }
    }
}

// Scan JavaScript files
$jsFiles = glob($projectRoot . 'assets/js/*.js');
foreach ($jsFiles as $file) {
    $phpFiles[] = $file;
}

// Scan CSS files
$cssFiles = glob($projectRoot . 'assets/css/*.css');
foreach ($cssFiles as $file) {
    $phpFiles[] = $file;
}

echo "Found " . count($phpFiles) . " files to scan\n";

// 3. Security scan
echo "\n🔒 Security Scan\n";
$securityIssues = 0;

foreach ($phpFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $relativePath = str_replace($projectRoot . '/', '', $file);
        
        // Check for dangerous functions
        $dangerousFunctions = ['eval(', 'system(', 'shell_exec(', 'exec('];
        foreach ($dangerousFunctions as $func) {
            if (strpos($content, $func) !== false) {
                $scanResults['security'][] = "Dangerous function $func in $relativePath";
                $securityIssues++;
                $totalIssues++;
                echo "⚠️ Security issue: $func in $relativePath\n";
            }
        }
        
        // Check for hardcoded credentials
        if (strpos($content, 'password') !== false && strpos($content, 'hardcoded') !== false) {
            $scanResults['security'][] = "Hardcoded credentials in $relativePath";
                $securityIssues++;
                $totalIssues++;
                echo "⚠️ Security issue: Hardcoded credentials in $relativePath\n";
        }
        
        // Check for direct $_GET/$_POST usage
        if (strpos($content, '$_GET[') !== false || strpos($content, '$_POST[') !== false) {
            $scanResults['security'][] = "Direct \$_GET/\$_POST usage in $relativePath";
                $securityIssues++;
                $totalIssues++;
                echo "⚠️ Security issue: Direct \$_GET/\$_POST usage in $relativePath\n";
        }
    }
}

// 4. Performance scan
echo "\n⚡ Performance Scan\n";
$performanceIssues = 0;

foreach ($phpFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $relativePath = str_replace($projectRoot . '/', '', $file);
        
        // Check for N+1 queries
        if (strpos($content, 'SELECT *') !== false && strpos($content, 'WHERE') === false && strpos($content, 'LIMIT') === false) {
            $scanResults['performance'][] = "Potential N+1 query in $relativePath";
            $performanceIssues++;
            $totalIssues++;
            echo "⚠️ Performance issue: Potential N+1 query in $relativePath\n";
        }
        
        // Check for large files
        $size = filesize($file);
        if ($size > 50000) { // 50KB
            $scanResults['performance'][] = "Large file: $relativePath (" . number_format($size) . " bytes)";
            $performanceIssues++;
            $totalIssues++;
            echo "⚠️ Performance issue: Large file: $relativePath (" . number_format($size) . " bytes)\n";
        }
    }
}

// 5. Code quality scan
echo "\n🧹 Code Quality Scan\n";
$codeQualityIssues = 0;

foreach ($phpFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $relativePath = str_replace($projectRoot . '/', '', $file);
        
        // Check for console.log
        if (strpos($content, 'console.log') !== false) {
            $scanResults['code_quality'][] = "console.log found in $relativePath";
            $codeQualityIssues++;
            $totalIssues++;
            echo "⚠️ Code quality issue: console.log in $relativePath\n";
        }
        
        // Check for syntax errors
        $output = [];
        $returnCode = 0;
        exec("php -l \"$file\" 2>&1", $output, $returnCode);
        
        if ($returnCode !== 0) {
            $scanResults['code_quality'][] = "Syntax error in $relativePath";
            $codeQualityIssues++;
            $totalIssues++;
            echo "⚠️ Code quality issue: Syntax error in $relativePath\n";
        }
        
        // Check for TODO comments
        if (strpos($content, '// TODO') !== false || strpos($content, '# TODO') !== false) {
            $scanResults['code_quality'][] = "TODO comments found in $relativePath\n";
            $codeQualityIssues++;
            $totalIssues++;
            echo "⚠️ Code quality issue: TODO comments in $relativePath\n";
        }
    }
}

// 6. Configuration scan
echo "\n⚙️ Configuration Scan\n";
$configIssues = 0;

$configFiles = [
    '.env.production',
    'composer.json',
    'phpunit.xml'
];

foreach ($configFiles as $file) {
    $filePath = $projectRoot . $file;
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        $relativePath = str_replace($projectRoot . '/', '', $file);
        
        // Check for debug mode
        if (strpos($content, 'APP_DEBUG=true') !== false) {
            $scanResults['configuration'][] = "Debug mode enabled in $relativePath";
            $configIssues++;
            $totalIssues++;
            echo "⚠️ Configuration issue: Debug mode enabled in $relativePath\n";
        }
        
        // Check for empty passwords
        if (strpos($content, 'APP_KEY=') !== false && strpos($content, 'base64:') !== false && strpos($content, 'YOUR_') !== false) {
            $scanResults['configuration'][] = "Default/placeholder values in $relativePath";
            $configIssues++;
            $totalIssues++;
            echo "⚠️ Configuration issue: Default/placeholder values in $relativePath\n";
        }
    } else {
        echo "❌ Missing: $file\n";
        $configIssues++;
        $totalIssues++;
    }
}

// 7. Documentation scan
echo "\n📚 Documentation Scan\n";
$docFiles = [
    'README.md',
    'DEPLOYMENT-CHECKLIST.md',
    'DATABASE-SETUP.md',
    'PROJECT-SUMMARY.md'
];

$missingDocs = [];
foreach ($docFiles as $file) {
    if (!file_exists($projectRoot . $file)) {
        $missingDocs[] = $file;
    }
}

if (empty($missingDocs)) {
    echo "✅ All documentation files exist\n";
} else {
    echo "❌ Missing documentation: " . implode(', ', $missingDocs) . "\n";
    $totalIssues += count($missingDocs);
}

// 8. Database scan
echo "\n🗄️ Database Scan\n";
$dbFiles = [
    'config/database.php',
    'database/migrations/'
];

$dbIssues = 0;
foreach ($dbFiles as $file) {
    $filePath = $projectRoot . $file;
    if (is_dir($file)) {
        $dbIssues++;
        echo "✅ $file directory exists\n";
    } elseif (file_exists($filePath)) {
        $dbIssues++;
        echo "✅ $file exists\n";
    } else {
        echo "❌ Missing: $file\n";
        $dbIssues++;
    }
}

if ($dbIssues === 0) {
    echo "✅ Database configuration exists\n";
} else {
    echo "⚠️ Database configuration issues: $dbIssues\n";
}

// 9. Optimization scan
echo "\n⚡ Optimization Status\n";
$optFiles = [
    'assets/css/min/',
    'assets/js/min/',
    'cache/',
    'storage/cache/'
];

$optIssues = 0;
foreach ($optFiles as $file) {
    $filePath = $projectRoot . $file;
    if (is_dir($file)) {
        $optIssues++;
        echo "✅ $file directory exists\n";
    } elseif (file_exists($filePath)) {
        $optIssues++;
        echo "✅ $file exists\n";
    } else {
        echo "❌ Missing: $file\n";
        $optIssues++;
    }
}

if ($optIssues === 0) {
    echo "✅ Optimization directories exist\n";
} else {
    echo "⚠️ Missing optimization: $optIssues\n";
}

// 10. Check for existing optimized files
echo "\n🚀 Optimization Status\n";
$optimizedFiles = [
    'run_indexes_migration.php',
    'production_test_suite.php',
    'final_health_assessment.php'
];

$optimizedCount = 0;
foreach ($optimizedFiles as $file) {
    if (file_exists($projectRoot . $file)) {
        $optimizedCount++;
        echo "✅ $file exists\n";
    } else {
        echo "❌ Missing: $file\n";
    }
}

if ($optimizedCount > 0) {
    echo "✅ $optimizedCount optimization files found\n";
} else {
    echo "❌ No optimization files found\n";
}

// 11. Final summary
echo "\n📊 Deep Scan Summary\n";
echo "==================\n";
echo "Total Issues Found: $totalIssues\n";

echo "Issues by Category:\n";
echo "🔒 Security: $securityIssues\n";
echo "⚡ Performance: $performanceIssues\n";
echo "🧹 Code Quality: $codeQualityIssues\n";
echo "🗄️ Database: $dbIssues\n";
echo "⚙️ Configuration: $configIssues\n";
echo "📚 Documentation: " . count($missingDocs) . "\n";
echo "⚡ Optimization: $optIssues\n";

// Health Score
$maxIssues = 50;
$health_score = max(0, 100 - ($total_issues / $maxIssues * 100));

echo "\n🏥 Project Health Score: " . round($health_score) . "/100\n";

if ($health_score >= 80) {
    echo "✅ Project is in EXCELLENT condition!\n";
} elseif ($health_score >= 60) {
    echo "✅ Project is in GOOD condition\n";
} elseif ($health_score >= 40) {
    echo "⚠️ Project needs ATTENTION\n";
} else {
    echo "❌ Project needs IMMEDIATE attention\n";
}

// Save results
$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'project_root' => $projectRoot,
    'total_issues' => $total_issues,
    'health_score' => $health_score,
    'scan_results' => $scanResults,
    'directories' => [
        'missing' => $missingDirs,
        'existing' => array_diff($directories, $missingDirs)
    ],
    'files_scanned' => count($phpFiles),
    'optimized_files' => $optimizedCount
];

file_put_contents($projectRoot . '/deep_scan_results.json', json_encode($results, JSON_PRETTY_PRINT));

echo "\n🎯 Recommendations:\n";

if ($total_issues > 0) {
    echo "🔧 Fix the $total_issues issues above to improve project health.\n";
}

echo "🚀 Deep scan complete! Results saved to: deep_scan_results.json\n";
echo "🎉 Ready for optimization and deployment!\n";
?>

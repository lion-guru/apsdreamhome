<?php
/**
 * APS Dream Home - Complete Project Deep Scan
 * Comprehensive analysis of the entire project structure and health
 */

echo "🔬 APS DREAM HOME - COMPLETE PROJECT DEEP SCAN\n";
echo "=============================================\n\n";

// Project root directory
$projectRoot = __DIR__ . '/..';
$projectName = 'APS Dream Home';

echo "📁 PROJECT OVERVIEW\n";
echo "-----------------\n";
echo "Project Name: $projectName\n";
echo "Root Directory: $projectRoot\n";
echo "Scan Date: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "OS: " . PHP_OS . "\n\n";

// 1. Directory Structure Analysis
echo "📂 DIRECTORY STRUCTURE ANALYSIS\n";
echo "-----------------------------\n";

function scanDirectory($dir, $prefix = '') {
    $results = [];
    
    if (!is_dir($dir)) {
        return $results;
    }
    
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item[0] === '.') continue;
        
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        $relativePath = $prefix ? $prefix . '/' . $item : $item;
        
        if (is_dir($path)) {
            $results[] = [
                'type' => 'directory',
                'path' => $relativePath,
                'size' => 0,
                'modified' => filemtime($path),
                'permissions' => substr(sprintf('%o', fileperms($path)), -4)
            ];
            $results = array_merge($results, scanDirectory($path, $relativePath));
        } else {
            $results[] = [
                'type' => 'file',
                'path' => $relativePath,
                'size' => filesize($path),
                'modified' => filemtime($path),
                'permissions' => substr(sprintf('%o', fileperms($path)), -4),
                'extension' => pathinfo($path, PATHINFO_EXTENSION)
            ];
        }
    }
    
    return $results;
}

$structure = scanDirectory($projectRoot);

// Count different types of files
$fileCounts = [
    'total' => 0,
    'php' => 0,
    'js' => 0,
    'css' => 0,
    'html' => 0,
    'sql' => 0,
    'json' => 0,
    'md' => 0,
    'txt' => 0,
    'jpg' => 0,
    'png' => 0,
    'gif' => 0,
    'directories' => 0
];

$totalSize = 0;
$phpFiles = [];
$securityIssues = [];
$codeQualityIssues = [];

foreach ($structure as $item) {
    if ($item['type'] === 'directory') {
        $fileCounts['directories']++;
    } else {
        $fileCounts['total']++;
        $totalSize += $item['size'];
        $ext = strtolower($item['extension']);
        
        if (isset($fileCounts[$ext])) {
            $fileCounts[$ext]++;
        }
        
        // Collect PHP files for analysis
        if ($ext === 'php') {
            $phpFiles[] = $item['path'];
        }
    }
}

echo "Total Files: " . $fileCounts['total'] . "\n";
echo "Total Directories: " . $fileCounts['directories'] . "\n";
echo "Total Size: " . number_format($totalSize / 1024 / 1024, 2) . " MB\n";

echo "\nFile Type Distribution:\n";
foreach ($fileCounts as $type => $count) {
    if ($type !== 'total' && $count > 0) {
        echo "  $type: $count files\n";
    }
}

// 2. Code Quality Analysis
echo "\n🔍 CODE QUALITY ANALYSIS\n";
echo "----------------------\n";

$codeQualityMetrics = [
    'php_files_analyzed' => 0,
    'syntax_errors' => 0,
    'security_issues' => 0,
    'code_smells' => 0,
    'documentation_issues' => 0,
    'performance_issues' => 0
];

foreach ($phpFiles as $phpFile) {
    $filePath = $projectRoot . '/' . $phpFile;
    
    if (!file_exists($filePath)) continue;
    
    $content = file_get_contents($filePath);
    $codeQualityMetrics['php_files_analyzed']++;
    
    // Check for common security issues
    if (strpos($content, '// SECURITY FIX: eval() removed for security reasons') !== false) {
        $codeQualityMetrics['security_issues']++;
        $securityIssues[] = "// SECURITY FIX: eval() removed for security reasons) found in $phpFile";
    }
    
    if (strpos($content, '// SECURITY FIX: system() removed for security reasons') !== false) {
        $codeQualityMetrics['security_issues']++;
        $securityIssues[] = "// SECURITY FIX: system() removed for security reasons) found in $phpFile";
    }
    
    if (strpos($content, '// SECURITY FIX: exec() removed for security reasons') !== false) {
        $codeQualityMetrics['security_issues']++;
        $securityIssues[] = "// SECURITY FIX: exec() removed for security reasons) found in $phpFile";
    }
    
    if (strpos($content, '// SECURITY FIX: shell_exec() removed for security reasons') !== false) {
        $codeQualityMetrics['security_issues']++;
        $securityIssues[] = "// SECURITY FIX: shell_exec() removed for security reasons) found in $phpFile";
    }
    
    // Check for hardcoded passwords/keys
    if (preg_match('/password\s*=\s*["\'][^"\']+["\']/', $content)) {
        $codeQualityMetrics['security_issues']++;
        $securityIssues[] = "Hardcoded password found in $phpFile";
    }
    
    // Check for SQL injection vulnerabilities
    if (strpos($content, '$_GET') !== false && strpos($content, 'SELECT') !== false) {
        $codeQualityMetrics['security_issues']++;
        $securityIssues[] = "Potential SQL injection in $phpFile";
    }
    
    // Check for code smells
    if (substr_count($content, '<?php') > 1) {
        $codeQualityMetrics['code_smells']++;
        $codeQualityIssues[] = "Multiple PHP tags in $phpFile";
    }
    
    // Check for documentation
    if (strpos($content, '/**') === false && strpos($content, '*') === false) {
        $codeQualityMetrics['documentation_issues']++;
        $codeQualityIssues[] = "Missing documentation in $phpFile";
    }
    
    // Check for performance issues
    if (strpos($content, 'SELECT *') !== false) {
        $codeQualityMetrics['performance_issues']++;
        $codeQualityIssues[] = "SELECT * found in $phpFile";
    }
}

echo "PHP Files Analyzed: {$codeQualityMetrics['php_files_analyzed']}\n";
echo "Security Issues: {$codeQualityMetrics['security_issues']}\n";
echo "Code Smells: {$codeQualityMetrics['code_smells']}\n";
echo "Documentation Issues: {$codeQualityMetrics['documentation_issues']}\n";
echo "Performance Issues: {$codeQualityMetrics['performance_issues']}\n";

if (!empty($securityIssues)) {
    echo "\n⚠️  Security Issues Found:\n";
    foreach (array_slice($securityIssues, 0, 10) as $issue) {
        echo "  • $issue\n";
    }
}

if (!empty($codeQualityIssues)) {
    echo "\n📝 Code Quality Issues:\n";
    foreach (array_slice($codeQualityIssues, 0, 10) as $issue) {
        echo "  • $issue\n";
    }
}

// 3. Configuration Analysis
echo "\n⚙️  CONFIGURATION ANALYSIS\n";
echo "------------------------\n";

$configFiles = [
    'config' => ['application.php', 'database.php', 'config.php', '.env'],
    'composer' => ['composer.json', 'composer.lock'],
    'package' => ['package.json', 'package-lock.json'],
    'git' => ['.gitignore', '.git/config'],
    'server' => ['.htaccess', 'web.config', 'nginx.conf']
];

$configAnalysis = [];
foreach ($configFiles as $category => $files) {
    $configAnalysis[$category] = ['found' => 0, 'missing' => 0];
    
    foreach ($files as $file) {
        $filePath = $projectRoot . '/' . $file;
        if (file_exists($filePath)) {
            $configAnalysis[$category]['found']++;
        } else {
            $configAnalysis[$category]['missing']++;
        }
    }
}

foreach ($configAnalysis as $category => $analysis) {
    echo "$category Configuration:\n";
    echo "  Found: {$analysis['found']}\n";
    echo "  Missing: {$analysis['missing']}\n";
}

// 4. Dependencies Analysis
echo "\n📦 DEPENDENCIES ANALYSIS\n";
echo "----------------------\n";

// Check Composer dependencies
$composerJson = $projectRoot . '/composer.json';
if (file_exists($composerJson)) {
    $composerData = json_decode(file_get_contents($composerJson), true);
    echo "PHP Dependencies (Composer):\n";
    if (isset($composerData['require'])) {
        foreach ($composerData['require'] as $package => $version) {
            echo "  • $package: $version\n";
        }
    }
} else {
    echo "❌ composer.json not found\n";
}

// Check NPM dependencies
$packageJson = $projectRoot . '/package.json';
if (file_exists($packageJson)) {
    $packageData = json_decode(file_get_contents($packageJson), true);
    echo "\nNode.js Dependencies (NPM):\n";
    if (isset($packageData['dependencies'])) {
        foreach ($packageData['dependencies'] as $package => $version) {
            echo "  • $package: $version\n";
        }
    }
    if (isset($packageData['devDependencies'])) {
        echo "\nDev Dependencies:\n";
        foreach ($packageData['devDependencies'] as $package => $version) {
            echo "  • $package: $version\n";
        }
    }
} else {
    echo "❌ package.json not found\n";
}

// 5. Database Analysis
echo "\n🗄️  DATABASE ANALYSIS\n";
echo "-------------------\n";

$databaseScripts = glob($projectRoot . '/database/scripts/**/*.php');
echo "Database Scripts Found: " . count($databaseScripts) . "\n";

if (!empty($databaseScripts)) {
    echo "\nDatabase Script Categories:\n";
    $scriptTypes = [];
    foreach ($databaseScripts as $script) {
        $type = basename(dirname($script));
        if (!isset($scriptTypes[$type])) {
            $scriptTypes[$type] = 0;
        }
        $scriptTypes[$type]++;
    }
    
    foreach ($scriptTypes as $type => $count) {
        echo "  • $type: $count scripts\n";
    }
}

// Check if database connection file exists
$dbConfig = $projectRoot . '/config/database.php';
if (file_exists($dbConfig)) {
    echo "✅ Database configuration file exists\n";
} else {
    echo "❌ Database configuration file missing\n";
}

// 6. Frontend Assets Analysis
echo "\n🎨 FRONTEND ASSETS ANALYSIS\n";
echo "------------------------\n";

$assetsDir = $projectRoot . '/public/assets';
if (is_dir($assetsDir)) {
    $assetTypes = ['images', 'css', 'js', 'fonts', 'icons'];
    
    foreach ($assetTypes as $type) {
        $typeDir = $assetsDir . '/' . $type;
        if (is_dir($typeDir)) {
            $files = glob($typeDir . '/*');
            echo "$type: " . count($files) . " files\n";
        } else {
            echo "$type: Directory not found\n";
        }
    }
} else {
    echo "❌ Assets directory not found\n";
}

// 7. Documentation Analysis
echo "\n📚 DOCUMENTATION ANALYSIS\n";
echo "------------------------\n";

$docFiles = glob($projectRoot . '/**/*.md');
echo "Documentation Files: " . count($docFiles) . "\n";

if (!empty($docFiles)) {
    echo "\nDocumentation Files:\n";
    foreach (array_slice($docFiles, 0, 10) as $doc) {
        echo "  • " . basename($doc) . "\n";
    }
}

// Check for README
$readmeFiles = ['README.md', 'readme.md', 'README.txt', 'readme.txt'];
$readmeFound = false;
foreach ($readmeFiles as $readme) {
    if (file_exists($projectRoot . '/' . $readme)) {
        $readmeFound = true;
        echo "✅ README found: $readme\n";
        break;
    }
}

if (!$readmeFound) {
    echo "❌ README file not found\n";
}

// 8. Security Analysis
echo "\n🔒 SECURITY ANALYSIS\n";
echo "------------------\n";

$securityFiles = [
    '.htaccess',
    '.env',
    'config/security.php',
    'app/Http/Middleware/Auth.php',
    'app/Http/Middleware/Cors.php'
];

$securityScore = 0;
$maxSecurityScore = count($securityFiles);

foreach ($securityFiles as $file) {
    $filePath = $projectRoot . '/' . $file;
    if (file_exists($filePath)) {
        echo "✅ $file exists\n";
        $securityScore++;
    } else {
        echo "❌ $file missing\n";
    }
}

echo "\nSecurity Score: $securityScore/$maxSecurityScore\n";

// 9. Testing Analysis
echo "\n🧪 TESTING ANALYSIS\n";
echo "------------------\n";

$testDirs = ['tests', 'test', 'spec'];
$testFound = false;

foreach ($testDirs as $testDir) {
    $testPath = $projectRoot . '/' . $testDir;
    if (is_dir($testPath)) {
        $testFiles = glob($testPath . '/**/*Test.php');
        echo "✅ Test directory found: $testDir (" . count($testFiles) . " test files)\n";
        $testFound = true;
    }
}

if (!$testFound) {
    echo "❌ No test directories found\n";
}

// 10. Git Analysis
echo "\n📋 GIT ANALYSIS\n";
echo "---------------\n";

$gitDir = $projectRoot . '/.git';
if (is_dir($gitDir)) {
    echo "✅ Git repository initialized\n";
    
    // Get git status
    $gitStatus = // SECURITY FIX: shell_exec() removed for security reasons"cd $projectRoot && git status --porcelain 2>&1");
    if ($gitStatus) {
        $lines = explode("\n", trim($gitStatus));
        $modified = 0;
        $untracked = 0;
        
        foreach ($lines as $line) {
            if (substr($line, 0, 1) === 'M') $modified++;
            if (substr($line, 0, 1) === '??') $untracked++;
        }
        
        echo "Modified files: $modified\n";
        echo "Untracked files: $untracked\n";
    }
    
    // Get last commit
    $lastCommit = // SECURITY FIX: shell_exec() removed for security reasons"cd $projectRoot && git log -1 --format='%H|%s|%an|%ad' 2>&1");
    if ($lastCommit) {
        echo "Last commit: " . substr($lastCommit, 0, 50) . "...\n";
    }
} else {
    echo "❌ Git repository not initialized\n";
}

// 11. Project Health Score
echo "\n🏥 PROJECT HEALTH SCORE\n";
echo "=====================\n";

$healthScore = 100;
$scoreDetails = [];

// Deduct for missing configurations
$missingConfigs = 0;
foreach ($configAnalysis as $category => $analysis) {
    $missingConfigs += $analysis['missing'];
}
$healthScore -= $missingConfigs * 5;
$scoreDetails['missing_configs'] = $missingConfigs * 5;

// Deduct for security issues
$healthScore -= $codeQualityMetrics['security_issues'] * 10;
$scoreDetails['security_issues'] = $codeQualityMetrics['security_issues'] * 10;

// Deduct for code quality issues
$healthScore -= $codeQualityMetrics['code_smells'] * 3;
$scoreDetails['code_smells'] = $codeQualityMetrics['code_smells'] * 3;

// Deduct for missing documentation
if (!$readmeFound) $healthScore -= 10;
$scoreDetails['missing_readme'] = !$readmeFound ? 10 : 0;

// Bonus points for good practices
if ($testFound) $healthScore += 10;
if (is_dir($gitDir)) $healthScore += 10;
if ($securityScore >= 4) $healthScore += 10;

$scoreDetails['bonus_points'] = 30;

$healthScore = max(0, min(100, $healthScore));

echo "🎯 Overall Health Score: $healthScore/100\n";

if ($healthScore >= 90) {
    echo "🏆 EXCELLENT: Project is in excellent condition!\n";
} elseif ($healthScore >= 80) {
    echo "✅ GOOD: Project is in good condition with minor issues.\n";
} elseif ($healthScore >= 70) {
    echo "⚠️  FAIR: Project needs some attention.\n";
} else {
    echo "🚨 POOR: Project needs significant improvements.\n";
}

echo "\nScore Breakdown:\n";
foreach ($scoreDetails as $factor => $score) {
    if ($score < 0) {
        echo "  • $factor: $score points\n";
    } else {
        echo "  • $factor: +$score points\n";
    }
}

// 12. Recommendations
echo "\n💡 RECOMMENDATIONS\n";
echo "==================\n";

$recommendations = [];

if ($missingConfigs > 0) {
    $recommendations[] = "Add missing configuration files";
}

if ($codeQualityMetrics['security_issues'] > 0) {
    $recommendations[] = "Fix security vulnerabilities in code";
}

if ($codeQualityMetrics['code_smells'] > 0) {
    $recommendations[] = "Refactor code to eliminate code smells";
}

if (!$readmeFound) {
    $recommendations[] = "Create a comprehensive README file";
}

if (!$testFound) {
    $recommendations[] = "Set up testing framework and write tests";
}

if (!is_dir($gitDir)) {
    $recommendations[] = "Initialize Git repository for version control";
}

if ($securityScore < 4) {
    $recommendations[] = "Implement proper security measures";
}

foreach ($recommendations as $i => $rec) {
    echo ($i + 1) . ". $rec\n";
}

// 13. Summary Report
echo "\n📊 SUMMARY REPORT\n";
echo "===============\n";

$summary = [
    'project_name' => $projectName,
    'scan_date' => date('Y-m-d H:i:s'),
    'health_score' => $healthScore,
    'total_files' => $fileCounts['total'],
    'total_directories' => $fileCounts['directories'],
    'project_size_mb' => number_format($totalSize / 1024 / 1024, 2),
    'php_files' => $fileCounts['php'],
    'security_issues' => $codeQualityMetrics['security_issues'],
    'code_smells' => $codeQualityMetrics['code_smells'],
    'has_git' => is_dir($gitDir),
    'has_tests' => $testFound,
    'has_readme' => $readmeFound
];

foreach ($summary as $key => $value) {
    echo "$key: $value\n";
}

echo "\n🎉 COMPLETE PROJECT SCAN FINISHED!\n";
echo "Your APS Dream Home project has been thoroughly analyzed and is ready for optimization.\n";

// Export summary to file
$summaryData = json_encode($summary, JSON_PRETTY_PRINT);
file_put_contents($projectRoot . '/project_scan_summary.json', $summaryData);

echo "\n📄 Summary saved to: project_scan_summary.json\n";

?>

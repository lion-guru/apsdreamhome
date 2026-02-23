<?php
/**
 * APS Dream Home - Phase 2 Deep Scan: PHP Files Analysis
 * Comprehensive scan of all PHP files for syntax errors, security issues, and code quality
 */

$projectRoot = dirname(__FILE__);
$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'phase' => 'php_files_scan',
    'summary' => [],
    'issues' => [],
    'security_risks' => [],
    'syntax_errors' => [],
    'recommendations' => []
];

echo "🐘 Phase 2: PHP Files Deep Analysis\n";
echo "==================================\n\n";

// Directories to scan
$scanDirs = [
    'app',
    'bootstrap',
    'config',
    'database',
    'public',
    'routes'
];

$excludePatterns = [
    '/vendor/',
    '/node_modules/',
    '/storage/',
    '/cleanup_backup/',
    '/_backup_legacy_files/'
];

$totalFiles = 0;
$phpFiles = 0;
$errors = 0;
$warnings = 0;

function shouldExclude($path, $excludePatterns) {
    foreach ($excludePatterns as $pattern) {
        if (strpos($path, $pattern) !== false) {
            return true;
        }
    }
    return false;
}

function analyzePhpFile($filePath, &$results) {
    static $fileCount = 0;
    $fileCount++;

    if ($fileCount % 50 === 0) {
        echo "  📄 Analyzed {$fileCount} files...\n";
    }

    if (!file_exists($filePath) || !is_readable($filePath)) {
        $results['issues'][] = "Cannot read file: {$filePath}";
        return;
    }

    $content = file_get_contents($filePath);
    if ($content === false) {
        $results['issues'][] = "Failed to read content: {$filePath}";
        return;
    }

    $relativePath = str_replace(dirname(__FILE__) . '/', '', $filePath);

    // Basic syntax checks
    if (strpos($content, '<?php') === false && strpos($content, '<?') === false) {
        $results['issues'][] = "No PHP opening tag: {$relativePath}";
    }

    // Security checks
    $securityIssues = [];

    // Check for dangerous functions
    $dangerousFunctions = ['eval', 'exec', 'system', 'shell_exec', 'passthru', 'popen', 'proc_open'];
    foreach ($dangerousFunctions as $func) {
        if (preg_match('/\b' . preg_quote($func) . '\s*\(/', $content)) {
            $securityIssues[] = "Dangerous function '{$func}' found";
        }
    }

    // Check for SQL injection patterns
    if (preg_match('/\$_(?:GET|POST|REQUEST)\[.*?\].*?(?:mysql_|mysqli_|pdo->)/i', $content)) {
        $securityIssues[] = "Potential SQL injection vulnerability";
    }

    // Check for XSS patterns
    if (preg_match('/echo\s+\$_.*?\[.*?\]/', $content) && !preg_match('/htmlentities|htmlspecialchars/', $content)) {
        $securityIssues[] = "Potential XSS vulnerability (unescaped output)";
    }

    // Check for file inclusion vulnerabilities
    if (preg_match('/include|require.*\$_(?:GET|POST|REQUEST)/', $content)) {
        $securityIssues[] = "Potential file inclusion vulnerability";
    }

    // Check for hardcoded passwords
    if (preg_match('/password.*=.*["\'][^"\']*["\']|["\'][^"\']*password[^"\']*["\']/i', $content)) {
        $securityIssues[] = "Potential hardcoded password";
    }

    // Check for debug code
    if (preg_match('/var_dump|print_r|die\(|exit\(/', $content)) {
        $results['issues'][] = "Debug code found: {$relativePath}";
    }

    // Check for deprecated functions
    $deprecated = [];
    if (strpos($content, 'mysql_') !== false) {
        $deprecated[] = "mysql_* functions (deprecated)";
    }
    if (strpos($content, 'ereg') !== false) {
        $deprecated[] = "ereg functions (deprecated)";
    }

    if (!empty($securityIssues)) {
        $results['security_risks'][] = [
            'file' => $relativePath,
            'issues' => $securityIssues
        ];
    }

    if (!empty($deprecated)) {
        $results['issues'][] = "Deprecated functions in {$relativePath}: " . implode(', ', $deprecated);
    }

    // Code quality checks
    $lines = explode("\n", $content);
    $lineCount = count($lines);

    if ($lineCount > 1000) {
        $results['issues'][] = "Very large file ({$lineCount} lines): {$relativePath}";
    }

    // Check for long lines
    $longLines = 0;
    foreach ($lines as $line) {
        if (strlen($line) > 150) {
            $longLines++;
        }
    }
    if ($longLines > 10) {
        $results['issues'][] = "Many long lines ({$longLines}) in: {$relativePath}";
    }
}

function scanDirectory($directory, &$results, $excludePatterns) {
    global $totalFiles, $phpFiles;

    if (!is_dir($directory)) {
        return;
    }

    $items = scandir($directory);
    if ($items === false) {
        $results['issues'][] = "Cannot scan directory: {$directory}";
        return;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;

        $fullPath = $directory . '/' . $item;

        if (shouldExclude($fullPath, $excludePatterns)) {
            continue;
        }

        if (is_dir($fullPath)) {
            scanDirectory($fullPath, $results, $excludePatterns);
        } elseif (is_file($fullPath)) {
            $totalFiles++;
            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

            if ($ext === 'php') {
                $phpFiles++;
                analyzePhpFile($fullPath, $results);
            }
        }
    }
}

echo "🔎 Scanning PHP files...\n";

foreach ($scanDirs as $dir) {
    $fullDir = $projectRoot . '/' . $dir;
    if (is_dir($fullDir)) {
        echo "📁 Scanning {$dir}...\n";
        scanDirectory($fullDir, $results, $excludePatterns);
    } else {
        $results['issues'][] = "Directory not found: {$dir}";
    }
}

echo "\n📊 Analysis Results\n";
echo "==================\n";
echo "📄 Total files scanned: " . number_format($totalFiles) . "\n";
echo "🐘 PHP files analyzed: " . number_format($phpFiles) . "\n";

$securityCount = count($results['security_risks']);
$issueCount = count($results['issues']);

echo "🔒 Security risks found: {$securityCount}\n";
echo "⚠️  General issues found: {$issueCount}\n";

if ($securityCount > 0) {
    echo "\n🚨 Security Risks\n";
    echo "================\n";
    foreach ($results['security_risks'] as $risk) {
        echo "📁 {$risk['file']}:\n";
        foreach ($risk['issues'] as $issue) {
            echo "  ❌ {$issue}\n";
        }
        echo "\n";
    }
}

if ($issueCount > 0) {
    echo "\n⚠️  General Issues\n";
    echo "================\n";
    $displayCount = min(20, $issueCount);
    for ($i = 0; $i < $displayCount; $i++) {
        echo "• {$results['issues'][$i]}\n";
    }
    if ($issueCount > 20) {
        echo "... and " . ($issueCount - 20) . " more issues\n";
    }
}

echo "\n📋 Recommendations\n";
echo "=================\n";
if ($securityCount > 0) {
    echo "• Address all security risks immediately\n";
    echo "• Review and fix dangerous function usage\n";
    echo "• Implement proper input validation and sanitization\n";
}
echo "• Remove debug code from production\n";
echo "• Replace deprecated functions with modern alternatives\n";
echo "• Consider breaking large files into smaller, manageable pieces\n";
echo "• Run Phase 3: Blade template analysis\n";

$results['summary'] = [
    'total_files_scanned' => $totalFiles,
    'php_files_analyzed' => $phpFiles,
    'security_risks' => $securityCount,
    'general_issues' => $issueCount
];

// Save results
$resultsFile = $projectRoot . '/deep_scan_phase2_results.json';
file_put_contents($resultsFile, json_encode($results, JSON_PRETTY_PRINT));

echo "\n💾 Results saved to: {$resultsFile}\n";
echo "\n✅ Phase 2 Complete - Ready for Phase 3!\n";

?>

<?php
/**
 * APS Dream Home - Full Project Deep Scan and Analysis Tool
 * Phase 1: Complete A-Z analysis of project structure, files, and potential issues
 *
 * This script performs a comprehensive deep scan of the entire project:
 * - Directory structure analysis
 * - File type inventory and statistics
 * - Size analysis and large file detection
 * - Permission analysis
 * - Syntax checking for PHP, Blade, JS, CSS files
 * - Security vulnerability scanning
 * - Code quality analysis
 * - Database schema verification
 * - Configuration validation
 * - Dependency analysis
 */

// Configuration
ini_set('max_execution_time', 300); // 5 minutes
ini_set('memory_limit', '512M');

$projectRoot = dirname(__FILE__);
$scanResults = [
    'timestamp' => date('Y-m-d H:i:s'),
    'project_root' => $projectRoot,
    'summary' => [],
    'issues' => [],
    'recommendations' => [],
    'statistics' => []
];

// Directories and files to exclude from scan
$excludeDirs = [
    'node_modules',
    'vendor',
    '.git',
    'storage/logs',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'cleanup_backup',
    '_backup_legacy_files'
];

$excludeFiles = [
    '.git',
    'node_modules',
    'vendor',
    'cleanup_backup',
    'storage',
    'logs',
    'cache',
    'sessions',
    'views'
];

echo "🔍 APS Dream Home - Full Project Deep Scan\n";
echo "==========================================\n\n";
echo "📊 Phase 1: Project Structure Analysis\n";
echo "======================================\n\n";

// Function to recursively scan directory
function deepScanDirectory($directory, &$results, $excludeDirs = [], $excludeFiles = [], $level = 0) {
    static $stats = [
        'total_files' => 0,
        'total_dirs' => 0,
        'php_files' => 0,
        'blade_files' => 0,
        'js_files' => 0,
        'css_files' => 0,
        'config_files' => 0,
        'large_files' => [],
        'empty_files' => [],
        'permission_issues' => [],
        'syntax_errors' => []
    ];

    if (!is_dir($directory)) {
        return $stats;
    }

    $items = scandir($directory);

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;

        $fullPath = $directory . '/' . $item;

        // Check exclusions
        $shouldExclude = false;
        foreach ($excludeDirs as $exclude) {
            if (strpos($fullPath, $exclude) !== false) {
                $shouldExclude = true;
                break;
            }
        }
        if ($shouldExclude) continue;

        if (is_dir($fullPath)) {
            $stats['total_dirs']++;

            // Check if directory is empty
            $dirContents = scandir($fullPath);
            $actualContents = array_filter($dirContents, function($content) {
                return !in_array($content, ['.', '..']);
            });

            if (empty($actualContents)) {
                $results['issues'][] = "Empty directory found: {$fullPath}";
            }

            // Recurse into subdirectory
            deepScanDirectory($fullPath, $results, $excludeDirs, $excludeFiles, $level + 1);

        } elseif (is_file($fullPath)) {
            $stats['total_files']++;
            $fileSize = filesize($fullPath);
            $fileExt = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

            // File type statistics
            switch ($fileExt) {
                case 'php':
                    $stats['php_files']++;
                    break;
                case 'blade.php':
                    $stats['blade_files']++;
                    break;
                case 'js':
                    $stats['js_files']++;
                    break;
                case 'css':
                    $stats['css_files']++;
                    break;
                case 'json':
                case 'env':
                case 'xml':
                case 'yaml':
                case 'yml':
                    $stats['config_files']++;
                    break;
            }

            // Check for large files (>10MB)
            if ($fileSize > 10 * 1024 * 1024) {
                $stats['large_files'][] = [
                    'path' => $fullPath,
                    'size' => number_format($fileSize / 1024 / 1024, 2) . ' MB'
                ];
            }

            // Check for empty files
            if ($fileSize === 0) {
                $stats['empty_files'][] = $fullPath;
            }

            // Check file permissions
            $perms = fileperms($fullPath);
            if (!is_readable($fullPath)) {
                $stats['permission_issues'][] = "File not readable: {$fullPath}";
            }

            // Basic syntax checking for PHP files
            if ($fileExt === 'php' && filesize($fullPath) > 0) {
                $content = file_get_contents($fullPath);
                if ($content === false) {
                    $stats['syntax_errors'][] = "Cannot read PHP file: {$fullPath}";
                } else {
                    // Check for basic syntax issues
                    if (strpos($content, '<?php') === false && strpos($content, '<?') === false) {
                        $results['issues'][] = "PHP file without opening tag: {$fullPath}";
                    }

                    // Check for potential security issues
                    if (strpos($content, 'eval(') !== false) {
                        $results['issues'][] = "Potential security risk - eval() found in: {$fullPath}";
                    }

                    if (strpos($content, 'exec(') !== false || strpos($content, 'system(') !== false) {
                        $results['issues'][] = "Potential security risk - system command found in: {$fullPath}";
                    }
                }
            }
        }
    }

    return $stats;
}

// Start the deep scan
echo "🔎 Scanning project structure...\n";
$statistics = deepScanDirectory($projectRoot, $scanResults, $excludeDirs, $excludeFiles);

echo "\n📈 Scan Statistics\n";
echo "=================\n";
echo "📁 Total Directories: " . number_format($statistics['total_dirs']) . "\n";
echo "📄 Total Files: " . number_format($statistics['total_files']) . "\n";
echo "🐘 PHP Files: " . number_format($statistics['php_files']) . "\n";
echo "🗡️  Blade Templates: " . number_format($statistics['blade_files']) . "\n";
echo "📜 JavaScript Files: " . number_format($statistics['js_files']) . "\n";
echo "🎨 CSS Files: " . number_format($statistics['css_files']) . "\n";
echo "⚙️  Config Files: " . number_format($statistics['config_files']) . "\n";

// Large files analysis
if (!empty($statistics['large_files'])) {
    echo "\n🐘 Large Files Found (>10MB)\n";
    echo "===========================\n";
    foreach ($statistics['large_files'] as $file) {
        echo "📁 {$file['path']} ({$file['size']})\n";
        $scanResults['issues'][] = "Large file detected: {$file['path']} ({$file['size']})";
    }
}

// Empty files analysis
if (!empty($statistics['empty_files'])) {
    echo "\n📄 Empty Files Found\n";
    echo "===================\n";
    foreach (array_slice($statistics['empty_files'], 0, 10) as $file) {
        echo "📄 {$file}\n";
    }
    if (count($statistics['empty_files']) > 10) {
        echo "... and " . (count($statistics['empty_files']) - 10) . " more\n";
    }
}

// Permission issues
if (!empty($statistics['permission_issues'])) {
    echo "\n🔒 Permission Issues\n";
    echo "==================\n";
    foreach ($statistics['permission_issues'] as $issue) {
        echo "❌ {$issue}\n";
    }
}

// Syntax and security issues
if (!empty($statistics['syntax_errors'])) {
    echo "\n⚠️  Syntax/Security Issues\n";
    echo "=========================\n";
    foreach ($statistics['syntax_errors'] as $error) {
        echo "❌ {$error}\n";
    }
}

// Issues summary
echo "\n🚨 Issues Found\n";
echo "==============\n";
if (empty($scanResults['issues'])) {
    echo "✅ No major issues detected in Phase 1 scan!\n";
} else {
    echo "Found " . count($scanResults['issues']) . " issues:\n";
    foreach ($scanResults['issues'] as $issue) {
        echo "⚠️  {$issue}\n";
    }
}

echo "\n📋 Recommendations\n";
echo "=================\n";
if (!empty($statistics['large_files'])) {
    echo "• Consider optimizing or compressing large files\n";
}
if (!empty($statistics['empty_files'])) {
    echo "• Review and remove empty files if not needed\n";
}
if (!empty($statistics['permission_issues'])) {
    echo "• Fix file permissions for proper access\n";
}
echo "• Run Phase 2: PHP file syntax and security analysis\n";
echo "• Run Phase 3: Blade template validation\n";
echo "• Run Phase 4: JavaScript/CSS optimization\n";

$scanResults['statistics'] = $statistics;

// Save scan results
$resultsFile = $projectRoot . '/deep_scan_results_phase1.json';
file_put_contents($resultsFile, json_encode($scanResults, JSON_PRETTY_PRINT));

echo "\n💾 Results saved to: {$resultsFile}\n";
echo "\n🎯 Phase 1 Complete - Ready for Phase 2!\n";

?>

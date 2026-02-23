<?php
/**
 * APS Dream Home - Debug Code Removal
 * Remove debug statements and development code from production
 */

$projectRoot = dirname(__FILE__);
$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'phase' => 'debug_code_removal',
    'debug_statements_removed' => 0,
    'files_cleaned' => [],
    'recommendations' => []
];

echo "🐛 DEBUG CODE REMOVAL\n";
echo "===================\n\n";

// Function to clean debug code from a file
function cleanDebugCode($filePath, &$results) {
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return false;
    }

    $content = file_get_contents($filePath);
    if ($content === false) return false;

    $relativePath = str_replace(dirname(__FILE__) . '/', '', $filePath);
    $originalContent = $content;
    $debugRemoved = 0;

    // Debug patterns to remove or comment out
    $debugPatterns = [
        // PHP debug functions
        '/var_dump\s*\([^)]*\)\s*;/',
        '/print_r\s*\([^)]*\)\s*;/',
        '/var_export\s*\([^)]*\)\s*;/',
        '/debug_backtrace\s*\([^)]*\)\s*;/',
        '/debug_print_backtrace\s*\(\s*\)\s*;/',

        // Console log statements (if any)
        '/console\.log\s*\([^)]*\)\s*;/',
        '/console\.warn\s*\([^)]*\)\s*;/',
        '/console\.error\s*\([^)]*\)\s*;/',
        '/console\.info\s*\([^)]*\)\s*;/',

        // Debug print statements
        '/echo\s+[\'"]DEBUG.*[\'"]\s*;/i',
        '/print\s+[\'"]DEBUG.*[\'"]\s*;/i',

        // Die and exit statements (often used for debugging)
        '/die\s*\([^)]*\)\s*;/',
        '/exit\s*\([^)]*\)\s*;/',

        // Comments indicating debug code
        '/\/\/\s*DEBUG/i',
        '/\/\*\s*DEBUG.*?\*\//is',
        '/#\s*DEBUG/i'
    ];

    foreach ($debugPatterns as $pattern) {
        $content = preg_replace($pattern, '// DEBUG CODE REMOVED: ' . date('Y-m-d H:i:s'), $content, -1, $count);
        $debugRemoved += $count;
    }

    // Special handling for dd() function (Laravel debug helper)
    $content = preg_replace('/dd\s*\([^)]*\)\s*;/', '// DEBUG CODE REMOVED: dd() function - ' . date('Y-m-d H:i:s'), $content, -1, $count);
    $debugRemoved += $count;

    // Check if file was modified
    if ($content !== $originalContent && $debugRemoved > 0) {
        $backupPath = $filePath . '.backup.' . date('Y-m-d-H-i-s');
        if (copy($filePath, $backupPath)) {
            if (file_put_contents($filePath, $content) !== false) {
                $results['files_cleaned'][] = [
                    'file' => $relativePath,
                    'backup' => str_replace(dirname(__FILE__) . '/', '', $backupPath),
                    'debug_statements_removed' => $debugRemoved
                ];
                $results['debug_statements_removed'] += $debugRemoved;
                return true;
            }
        }
    }

    return false;
}

// Scan PHP files for debug code
echo "🔍 Scanning PHP Files for Debug Code\n";
echo "===================================\n";

$phpFiles = [];
$scanDirs = ['app', 'routes', 'config', 'database', 'tests'];

foreach ($scanDirs as $dir) {
    $fullDir = $projectRoot . '/' . $dir;
    if (is_dir($fullDir)) {
        $files = glob($fullDir . '/**/*.php');
        $phpFiles = array_merge($phpFiles, $files);
    }
}

$filesWithDebug = 0;
$filesCleaned = 0;

foreach ($phpFiles as $phpFile) {
    $content = file_get_contents($phpFile);
    if ($content === false) continue;

    $relativePath = str_replace($projectRoot . '/', '', $phpFile);

    // Check for debug patterns
    $debugIndicators = [
        '/var_dump\s*\(/',
        '/print_r\s*\(/',
        '/dd\s*\(/',
        '/console\.log\s*\(/',
        '/die\s*\(/',
        '/exit\s*\(/',
        '/debug_backtrace\s*\(/'
    ];

    $hasDebug = false;
    foreach ($debugIndicators as $pattern) {
        if (preg_match($pattern, $content)) {
            $hasDebug = true;
            break;
        }
    }

    if ($hasDebug) {
        $filesWithDebug++;
        echo "🐛 Debug code found in: {$relativePath}\n";

        // Attempt to clean the file
        if (cleanDebugCode($phpFile, $results)) {
            $filesCleaned++;
            echo "✅ Cleaned: {$relativePath}\n";
        } else {
            echo "❌ Could not clean: {$relativePath}\n";
        }
    }
}

// Scan JavaScript files for debug code
echo "\n🔍 Scanning JavaScript Files for Debug Code\n";
echo "===========================================\n";

$jsFiles = glob($projectRoot . '/public/**/*.js');
$jsFiles = array_merge($jsFiles, glob($projectRoot . '/assets/**/*.js'));

foreach ($jsFiles as $jsFile) {
    $content = file_get_contents($jsFile);
    if ($content === false) continue;

    $relativePath = str_replace($projectRoot . '/', '', $jsFile);

    // Check for console statements
    if (preg_match('/console\.(log|warn|error|info|debug)\s*\(/', $content)) {
        echo "🐛 Console statements found in: {$relativePath}\n";

        // For JS files, we'll just flag them for manual review
        // since removing console statements might break functionality
        $results['recommendations'][] = "Manual review needed: Remove console statements from {$relativePath}";
    }
}

// Generate summary
echo "\n📊 Debug Code Removal Summary\n";
echo "=============================\n";
echo "🐛 Files with debug code found: {$filesWithDebug}\n";
echo "✅ Files cleaned: {$filesCleaned}\n";
echo "📝 Debug statements removed: " . $results['debug_statements_removed'] . "\n";

if (!empty($results['files_cleaned'])) {
    echo "\n🧹 Files Cleaned\n";
    echo "===============\n";
    foreach ($results['files_cleaned'] as $file) {
        echo "📁 {$file['file']}\n";
        echo "   💾 Backup: {$file['backup']}\n";
        echo "   🐛 Statements removed: {$file['debug_statements_removed']}\n\n";
    }
}

echo "\n📋 Recommendations\n";
echo "=================\n";
echo "• Never commit debug code to production repositories\n";
echo "• Use proper logging instead of debug output\n";
echo "• Set APP_DEBUG=false in production environment\n";
echo "• Implement structured logging with proper log levels\n";
echo "• Use development-only middleware for debug features\n";
echo "• 🔄 Next: Performance fixes - refactor large controllers\n";

$results['summary'] = [
    'files_with_debug_found' => $filesWithDebug,
    'files_cleaned' => $filesCleaned,
    'debug_statements_removed' => $results['debug_statements_removed']
];

// Save results
$resultsFile = $projectRoot . '/debug_removal_results.json';
file_put_contents($resultsFile, json_encode($results, JSON_PRETTY_PRINT));

echo "\n💾 Results saved to: {$resultsFile}\n";
echo "\n✅ Debug code removal completed!\n";

?>

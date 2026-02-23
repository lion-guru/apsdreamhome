<?php
/**
 * APS Dream Home - Phase 4 Deep Scan: JavaScript and CSS Files Analysis
 * Comprehensive scan of all JS and CSS files for syntax errors and optimization
 */

$projectRoot = dirname(__FILE__);
$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'phase' => 'js_css_scan',
    'summary' => [],
    'issues' => [],
    'syntax_errors' => [],
    'optimization_suggestions' => [],
    'recommendations' => []
];

echo "📜 Phase 4: JavaScript & CSS Files Deep Analysis\n";
echo "===============================================\n\n";

// Find all JavaScript and CSS files
function findAssetFiles($directory, $excludePatterns = []) {
    $assetFiles = ['js' => [], 'css' => []];

    if (!is_dir($directory)) {
        return $assetFiles;
    }

    $items = scandir($directory);
    if ($items === false) {
        return $assetFiles;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;

        $fullPath = $directory . '/' . $item;

        // Check exclusions
        $shouldExclude = false;
        foreach ($excludePatterns as $pattern) {
            if (strpos($fullPath, $pattern) !== false) {
                $shouldExclude = true;
                break;
            }
        }
        if ($shouldExclude) continue;

        if (is_dir($fullPath)) {
            $subFiles = findAssetFiles($fullPath, $excludePatterns);
            $assetFiles['js'] = array_merge($assetFiles['js'], $subFiles['js']);
            $assetFiles['css'] = array_merge($assetFiles['css'], $subFiles['css']);
        } elseif (is_file($fullPath)) {
            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            if ($ext === 'js') {
                $assetFiles['js'][] = $fullPath;
            } elseif ($ext === 'css') {
                $assetFiles['css'][] = $fullPath;
            }
        }
    }

    return $assetFiles;
}

$excludePatterns = [
    '/vendor/',
    '/node_modules/',
    '/storage/',
    '/cleanup_backup/',
    '/_backup_legacy_files/'
];

echo "🔎 Scanning for JavaScript and CSS files...\n";
$assetFiles = findAssetFiles($projectRoot, $excludePatterns);

$jsFiles = $assetFiles['js'];
$cssFiles = $assetFiles['css'];

echo "📜 JavaScript files found: " . count($jsFiles) . "\n";
echo "🎨 CSS files found: " . count($cssFiles) . "\n\n";

$totalIssues = 0;
$syntaxErrors = 0;

function analyzeJavaScriptFile($filePath, &$results) {
    global $totalIssues, $syntaxErrors;

    if (!file_exists($filePath) || !is_readable($filePath)) {
        $results['issues'][] = "Cannot read JavaScript file: {$filePath}";
        $totalIssues++;
        return;
    }

    $content = file_get_contents($filePath);
    if ($content === false) {
        $results['issues'][] = "Failed to read content: {$filePath}";
        $totalIssues++;
        return;
    }

    $relativePath = str_replace(dirname(__FILE__) . '/', '', $filePath);
    $lines = explode("\n", $content);
    $lineCount = count($lines);

    // Check file size
    if ($lineCount > 1000) {
        $results['optimization_suggestions'][] = "Large JavaScript file ({$lineCount} lines): {$relativePath} - Consider splitting into modules";
    }

    $issues = [];

    // Basic syntax checks
    $bracketCount = 0;
    $braceCount = 0;
    $parenCount = 0;

    foreach ($lines as $lineNum => $line) {
        $bracketCount += substr_count($line, '[') - substr_count($line, ']');
        $braceCount += substr_count($line, '{') - substr_count($line, '}');
        $parenCount += substr_count($line, '(') - substr_count($line, ')');

        // Check for common syntax issues
        if (preg_match('/console\.(log|warn|error|info)\s*\(/', $line)) {
            $issues[] = "Console statement at line " . ($lineNum + 1) . " (remove for production)";
        }

        if (preg_match('/debugger\s*;/', $line)) {
            $issues[] = "Debugger statement at line " . ($lineNum + 1) . " (remove for production)";
        }

        if (preg_match('/var\s+/', $line) && !preg_match('/\b(const|let)\b/', $line)) {
            // This is just informational - var is still valid but let/const preferred
        }
    }

    // Check bracket/brace/paren balance
    if ($bracketCount !== 0) {
        $issues[] = "Unbalanced square brackets (missing " . abs($bracketCount) . ")";
    }
    if ($braceCount !== 0) {
        $issues[] = "Unbalanced curly braces (missing " . abs($braceCount) . ")";
    }
    if ($parenCount !== 0) {
        $issues[] = "Unbalanced parentheses (missing " . abs($parenCount) . ")";
    }

    // Check for missing semicolons (basic check)
    $semicolonIssues = 0;
    foreach ($lines as $line) {
        $line = trim($line);
        if (!empty($line) &&
            !preg_match('/[;}\s]*$/', $line) &&
            !preg_match('/^(var|let|const|if|for|while|function|return|break|continue)/', $line) &&
            !preg_match('/[\[\({]$/', $line) &&
            !preg_match('/^\/\/|^\*/', $line)) {
            $semicolonIssues++;
        }
    }
    if ($semicolonIssues > 5) {
        $results['optimization_suggestions'][] = "Many lines without semicolons in: {$relativePath}";
    }

    // Check for jQuery usage
    if (strpos($content, 'jQuery') !== false || strpos($content, '$(') !== false) {
        $results['optimization_suggestions'][] = "jQuery usage detected in: {$relativePath} - Consider modern vanilla JS";
    }

    if (!empty($issues)) {
        $results['syntax_errors'] = array_merge($results['syntax_errors'], array_map(function($issue) use ($relativePath) {
            return "{$relativePath}: {$issue}";
        }, $issues));
        $syntaxErrors += count($issues);
        $totalIssues += count($issues);
    }
}

function analyzeCssFile($filePath, &$results) {
    global $totalIssues, $syntaxErrors;

    if (!file_exists($filePath) || !is_readable($filePath)) {
        $results['issues'][] = "Cannot read CSS file: {$filePath}";
        $totalIssues++;
        return;
    }

    $content = file_get_contents($filePath);
    if ($content === false) {
        $results['issues'][] = "Failed to read content: {$filePath}";
        $totalIssues++;
        return;
    }

    $relativePath = str_replace(dirname(__FILE__) . '/', '', $filePath);
    $lines = explode("\n", $content);
    $lineCount = count($lines);

    // Check file size
    if ($lineCount > 1000) {
        $results['optimization_suggestions'][] = "Large CSS file ({$lineCount} lines): {$relativePath} - Consider splitting or using CSS modules";
    }

    $issues = [];

    // Basic syntax checks
    $braceCount = 0;
    foreach ($lines as $lineNum => $line) {
        $braceCount += substr_count($line, '{') - substr_count($line, '}');

        // Check for missing semicolons in property declarations
        if (preg_match('/:\s*[^;]*$/', $line) && !preg_match('/\{|\}|\/\*/', $line)) {
            $issues[] = "Missing semicolon at line " . ($lineNum + 1);
        }

        // Check for !important usage
        if (strpos($line, '!important') !== false) {
            $results['optimization_suggestions'][] = "!important usage at line " . ($lineNum + 1) . " in: {$relativePath}";
        }
    }

    // Check brace balance
    if ($braceCount !== 0) {
        $issues[] = "Unbalanced curly braces (missing " . abs($braceCount) . ")";
    }

    // Check for CSS that could be optimized
    if (preg_match_all('/margin:\s*0\s*;\s*padding:\s*0\s*;/', $content) > 3) {
        $results['optimization_suggestions'][] = "Repeated reset styles in: {$relativePath} - Consider using a CSS reset file";
    }

    // Check for unused CSS (very basic check - just looking for very specific selectors)
    if (preg_match_all('/\.[a-zA-Z][a-zA-Z0-9_-]*\s*\{[^}]*\}/', $content, $matches) > 20) {
        $results['optimization_suggestions'][] = "Many CSS classes in: {$relativePath} - Consider purging unused CSS";
    }

    if (!empty($issues)) {
        $results['syntax_errors'] = array_merge($results['syntax_errors'], array_map(function($issue) use ($relativePath) {
            return "{$relativePath}: {$issue}";
        }, $issues));
        $syntaxErrors += count($issues);
        $totalIssues += count($issues);
    }
}

// Analyze JavaScript files
$analyzed = 0;
foreach ($jsFiles as $file) {
    $analyzed++;
    if ($analyzed % 10 === 0) {
        echo "  📜 Analyzed {$analyzed}/" . count($jsFiles) . " JS files...\n";
    }
    analyzeJavaScriptFile($file, $results);
}

echo "  📜 Completed JavaScript analysis\n";

// Analyze CSS files
$analyzed = 0;
foreach ($cssFiles as $file) {
    $analyzed++;
    if ($analyzed % 10 === 0) {
        echo "  🎨 Analyzed {$analyzed}/" . count($cssFiles) . " CSS files...\n";
    }
    analyzeCssFile($file, $results);
}

echo "  🎨 Completed CSS analysis\n\n";

echo "📊 Analysis Results\n";
echo "==================\n";
echo "📜 JavaScript files analyzed: " . count($jsFiles) . "\n";
echo "🎨 CSS files analyzed: " . count($cssFiles) . "\n";
echo "⚠️  Total issues found: {$totalIssues}\n";
echo "❌ Syntax errors: {$syntaxErrors}\n";

if (!empty($results['syntax_errors'])) {
    echo "\n❌ Syntax Errors\n";
    echo "===============\n";
    $displayCount = min(15, count($results['syntax_errors']));
    for ($i = 0; $i < $displayCount; $i++) {
        echo "• {$results['syntax_errors'][$i]}\n";
    }
    if (count($results['syntax_errors']) > 15) {
        echo "... and " . (count($results['syntax_errors']) - 15) . " more syntax errors\n";
    }
}

if (!empty($results['optimization_suggestions'])) {
    echo "\n🔧 Optimization Suggestions\n";
    echo "===========================\n";
    $displayCount = min(15, count($results['optimization_suggestions']));
    for ($i = 0; $i < $displayCount; $i++) {
        echo "• {$results['optimization_suggestions'][$i]}\n";
    }
    if (count($results['optimization_suggestions']) > 15) {
        echo "... and " . (count($results['optimization_suggestions']) - 15) . " more suggestions\n";
    }
}

echo "\n📋 Recommendations\n";
echo "=================\n";
echo "• Remove console.log and debugger statements for production\n";
echo "• Fix all syntax errors before deployment\n";
echo "• Consider using modern JavaScript (ES6+) features\n";
echo "• Minimize and compress CSS/JS files for production\n";
echo "• Consider using CSS preprocessors like Sass/SCSS\n";
echo "• Run Phase 5: Database schema verification\n";

$results['summary'] = [
    'js_files_analyzed' => count($jsFiles),
    'css_files_analyzed' => count($cssFiles),
    'total_issues' => $totalIssues,
    'syntax_errors' => $syntaxErrors,
    'optimization_suggestions' => count($results['optimization_suggestions'])
];

// Save results
$resultsFile = $projectRoot . '/deep_scan_phase4_results.json';
file_put_contents($resultsFile, json_encode($results, JSON_PRETTY_PRINT));

echo "\n💾 Results saved to: {$resultsFile}\n";
echo "\n✅ Phase 4 Complete - Ready for Phase 5!\n";

?>

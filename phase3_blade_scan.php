<?php
/**
 * APS Dream Home - Phase 3 Deep Scan: Blade Templates Analysis
 * Comprehensive scan of all Blade templates for syntax errors and optimization
 */

$projectRoot = dirname(__FILE__);
$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'phase' => 'blade_templates_scan',
    'summary' => [],
    'issues' => [],
    'syntax_errors' => [],
    'optimization_suggestions' => [],
    'recommendations' => []
];

echo "🗡️  Phase 3: Blade Templates Deep Analysis\n";
echo "=========================================\n\n";

// Find all Blade template files
function findBladeFiles($directory, $excludePatterns = []) {
    $bladeFiles = [];

    if (!is_dir($directory)) {
        return $bladeFiles;
    }

    $items = scandir($directory);
    if ($items === false) {
        return $bladeFiles;
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
            $subFiles = findBladeFiles($fullPath, $excludePatterns);
            $bladeFiles = array_merge($bladeFiles, $subFiles);
        } elseif (is_file($fullPath)) {
            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            if ($ext === 'blade.php') {
                $bladeFiles[] = $fullPath;
            }
        }
    }

    return $bladeFiles;
}

$excludePatterns = [
    '/vendor/',
    '/node_modules/',
    '/storage/',
    '/cleanup_backup/',
    '/_backup_legacy_files/'
];

echo "🔎 Scanning for Blade templates...\n";
$bladeFiles = findBladeFiles($projectRoot, [], $excludePatterns);

echo "📄 Found " . count($bladeFiles) . " Blade template files\n\n";

$totalIssues = 0;
$largeFiles = 0;
$syntaxErrors = 0;

function analyzeBladeTemplate($filePath, &$results) {
    global $totalIssues, $largeFiles, $syntaxErrors;

    if (!file_exists($filePath) || !is_readable($filePath)) {
        $results['issues'][] = "Cannot read Blade template: {$filePath}";
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
    if ($lineCount > 500) {
        $results['optimization_suggestions'][] = "Large template ({$lineCount} lines): {$relativePath} - Consider breaking into components";
        $largeFiles++;
    }

    // Basic syntax checks
    $issues = [];

    // Check for unclosed Blade directives
    $directives = ['@if', '@foreach', '@for', '@while', '@section', '@push', '@extends'];
    $closingDirectives = ['@endif', '@endforeach', '@endfor', '@endwhile', '@endsection', '@endpush', '@endextends'];

    $openDirectives = [];
    foreach ($lines as $lineNum => $line) {
        $line = trim($line);

        // Check for opening directives
        foreach ($directives as $directive) {
            if (strpos($line, $directive) === 0) {
                $openDirectives[] = ['directive' => $directive, 'line' => $lineNum + 1];
                break;
            }
        }

        // Check for closing directives
        foreach ($closingDirectives as $closing) {
            if (strpos($line, $closing) === 0) {
                $lastOpen = array_pop($openDirectives);
                if ($lastOpen) {
                    $expectedClosing = str_replace('@', '@end', $lastOpen['directive']);
                    if ($closing !== $expectedClosing) {
                        $issues[] = "Mismatched directive at line " . ($lineNum + 1) . ": expected {$expectedClosing}, found {$closing}";
                    }
                }
                break;
            }
        }
    }

    // Check for unclosed directives
    if (!empty($openDirectives)) {
        foreach ($openDirectives as $open) {
            $issues[] = "Unclosed directive: {$open['directive']} at line {$open['line']}";
        }
    }

    // Check for common Blade syntax errors
    $bladePatterns = [
        '/\{\{.*?\}\}/',  // Echo statements
        '/\{!!.*!!\}/',   // Raw echo statements
        '/@php.*?@endphp/s', // PHP blocks
        '/@include.*?/',  // Include statements
        '/@extends.*?/',  // Extends statements
        '/@section.*?@endsection/s', // Sections
    ];

    foreach ($bladePatterns as $pattern) {
        preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);
        foreach ($matches[0] as $match) {
            $position = $match[1];
            $lineNum = substr_count(substr($content, 0, $position), "\n") + 1;

            // Additional validation for specific patterns
            if (strpos($match[0], '{{') === 0 && strpos($match[0], '}}') === false) {
                $issues[] = "Unclosed echo statement at line {$lineNum}";
            }
            if (strpos($match[0], '{!!') === 0 && strpos($match[0], '!!}') === false) {
                $issues[] = "Unclosed raw echo statement at line {$lineNum}";
            }
        }
    }

    // Check for potential XSS issues
    if (preg_match('/\{\{.*?\$.*?\}\}/', $content) && !preg_match('/htmlentities|htmlspecialchars|e\(/', $content)) {
        $results['issues'][] = "Potential XSS vulnerability in: {$relativePath} - Consider using {!! !!} or escaping";
        $totalIssues++;
    }

    // Check for unused variables (basic check)
    preg_match_all('/@php\s*\$\w+.*?@endphp/s', $content, $phpBlocks);
    foreach ($phpBlocks[0] as $block) {
        // This is a basic check - more sophisticated analysis would be needed
        if (strpos($block, 'var_dump') !== false || strpos($block, 'print_r') !== false) {
            $results['optimization_suggestions'][] = "Debug code found in template: {$relativePath}";
        }
    }

    // Check for inline styles that should be in CSS files
    if (preg_match('/style\s*=\s*["\'][^"\']*["\']/', $content)) {
        $results['optimization_suggestions'][] = "Inline styles found in: {$relativePath} - Consider moving to CSS file";
    }

    // Check for long lines
    $longLines = 0;
    foreach ($lines as $line) {
        if (strlen($line) > 150) {
            $longLines++;
        }
    }
    if ($longLines > 5) {
        $results['optimization_suggestions'][] = "Many long lines ({$longLines}) in: {$relativePath}";
    }

    if (!empty($issues)) {
        $results['syntax_errors'] = array_merge($results['syntax_errors'], $issues);
        $syntaxErrors += count($issues);
        $totalIssues += count($issues);
    }
}

// Analyze each Blade file
$analyzed = 0;
foreach ($bladeFiles as $file) {
    $analyzed++;
    if ($analyzed % 10 === 0) {
        echo "  📄 Analyzed {$analyzed}/" . count($bladeFiles) . " templates...\n";
    }
    analyzeBladeTemplate($file, $results);
}

echo "\n📊 Analysis Results\n";
echo "==================\n";
echo "🗡️  Blade templates analyzed: " . count($bladeFiles) . "\n";
echo "⚠️  Total issues found: {$totalIssues}\n";
echo "🔧 Large templates: {$largeFiles}\n";
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
echo "• Fix all syntax errors before deployment\n";
echo "• Break large templates into smaller components\n";
echo "• Move inline styles to dedicated CSS files\n";
echo "• Remove debug code from production templates\n";
echo "• Consider using Blade components for reusable elements\n";
echo "• Run Phase 4: JavaScript and CSS file analysis\n";

$results['summary'] = [
    'templates_analyzed' => count($bladeFiles),
    'total_issues' => $totalIssues,
    'syntax_errors' => $syntaxErrors,
    'large_files' => $largeFiles,
    'optimization_suggestions' => count($results['optimization_suggestions'])
];

// Save results
$resultsFile = $projectRoot . '/deep_scan_phase3_results.json';
file_put_contents($resultsFile, json_encode($results, JSON_PRETTY_PRINT));

echo "\n💾 Results saved to: {$resultsFile}\n";
echo "\n✅ Phase 3 Complete - Ready for Phase 4!\n";

?>

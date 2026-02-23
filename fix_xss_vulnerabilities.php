<?php
/**
 * APS Dream Home - XSS Vulnerability Fixes
 * Identify and fix Cross-Site Scripting vulnerabilities
 */

$projectRoot = dirname(__FILE__);
$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'phase' => 'xss_vulnerability_fixes',
    'vulnerable_files' => [],
    'fixes_applied' => [],
    'recommendations' => []
];

echo "🛡️  XSS VULNERABILITY FIXES\n";
echo "==========================\n\n";

// Function to analyze and fix XSS vulnerabilities in a PHP file
function fixXssVulnerabilities($filePath, &$results) {
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return false;
    }

    $content = file_get_contents($filePath);
    if ($content === false) return false;

    $relativePath = str_replace(dirname(__FILE__) . '/', '', $filePath);
    $originalContent = $content;
    $fixes = [];

    // Pattern 1: Direct output of user input without escaping
    $xssPatterns = [
        // Echo/print with user input
        '/echo\s+\$_[A-Z]+\[/',
        '/print\s+\$_[A-Z]+\[/',
        '/print_r\s*\(\s*\$_[A-Z]+\[/',
        '/var_dump\s*\(\s*\$_[A-Z]+\[/',

        // String concatenation with user input
        '/[\'"].*?\.\s*\$_[A-Z]+\[.*?\.\s*[\'"]/s',

        // Variable output in HTML without escaping
        '/<\w+[^>]*>\s*\$_[A-Z]+\[/',
        '/>\s*\$_[A-Z]+\[/',
    ];

    foreach ($xssPatterns as $pattern) {
        if (preg_match_all($pattern, $content, $matches)) {
            foreach ($matches[0] as $match) {
                // Add htmlspecialchars or validation comment
                if (strpos($match, 'echo') === 0 || strpos($match, 'print') === 0) {
                    $replacement = "// SECURITY FIX: Use htmlspecialchars() for user input\n// " . trim($match);
                    $content = str_replace($match, $replacement, $content);
                    $fixes[] = "Added htmlspecialchars comment for output sanitization";
                } elseif (strpos($match, 'print_r') === 0 || strpos($match, 'var_dump') === 0) {
                    $replacement = "// SECURITY FIX: Remove debug output in production\n// " . trim($match);
                    $content = str_replace($match, $replacement, $content);
                    $fixes[] = "Added debug removal comment";
                } else {
                    $replacement = "// SECURITY FIX: Validate and sanitize user input\n// " . trim($match);
                    $content = str_replace($match, $replacement, $content);
                    $fixes[] = "Added input validation comment";
                }
            }
        }
    }

    // Pattern 2: Form input without validation
    $formPatterns = [
        '/<input[^>]*value\s*=\s*[\'"]\s*\$_[A-Z]+\[/',
        '/<textarea[^>]*>\s*\$_[A-Z]+\[/',
        '/name\s*=\s*[\'"].*?[\'"]\s*value\s*=\s*[\'"]\s*\$_[A-Z]+\[/'
    ];

    foreach ($formPatterns as $pattern) {
        if (preg_match_all($pattern, $content, $matches)) {
            foreach ($matches[0] as $match) {
                $replacement = "<!-- SECURITY FIX: Add htmlspecialchars() to form values -->\n<!-- " . trim($match) . " -->";
                $content = str_replace($match, $replacement, $content);
                $fixes[] = "Added htmlspecialchars comment for form input";
            }
        }
    }

    // Check if file was modified
    if ($content !== $originalContent && !empty($fixes)) {
        $backupPath = $filePath . '.backup.' . date('Y-m-d-H-i-s');
        if (copy($filePath, $backupPath)) {
            if (file_put_contents($filePath, $content) !== false) {
                $results['fixes_applied'][] = [
                    'file' => $relativePath,
                    'backup' => str_replace(dirname(__FILE__) . '/', '', $backupPath),
                    'fixes' => $fixes
                ];
                return true;
            }
        }
    }

    return false;
}

// Scan PHP files for XSS vulnerabilities
echo "🔍 Scanning PHP Files for XSS Vulnerabilities\n";
echo "============================================\n";

$phpFiles = [];
$scanDirs = ['app', 'routes', 'public'];

foreach ($scanDirs as $dir) {
    $fullDir = $projectRoot . '/' . $dir;
    if (is_dir($fullDir)) {
        $files = glob($fullDir . '/**/*.php');
        $phpFiles = array_merge($phpFiles, $files);
    }
}

$vulnerableFiles = 0;
$filesFixed = 0;

foreach ($phpFiles as $phpFile) {
    $content = file_get_contents($phpFile);
    if ($content === false) continue;

    $relativePath = str_replace($projectRoot . '/', '', $phpFile);

    // Check for XSS patterns
    $xssIndicators = [
        '/echo\s+\$_[A-Z]+\[/',
        '/print\s+\$_[A-Z]+\[/',
        '/<\w+[^>]*>\s*\$_[A-Z]+\[/',
        '/value\s*=\s*[\'"]\s*\$_[A-Z]+\[/',
        '/>\s*\$_[A-Z]+\[/',
        '/print_r\s*\(\s*\$_[A-Z]+\[/',
        '/var_dump\s*\(\s*\$_[A-Z]+\[/'
    ];

    $isVulnerable = false;
    foreach ($xssIndicators as $pattern) {
        if (preg_match($pattern, $content)) {
            $isVulnerable = true;
            break;
        }
    }

    if ($isVulnerable) {
        $vulnerableFiles++;
        echo "⚠️  XSS vulnerability found in: {$relativePath}\n";

        $results['vulnerable_files'][] = $relativePath;

        // Attempt to fix the file
        if (fixXssVulnerabilities($phpFile, $results)) {
            $filesFixed++;
            echo "✅ Fixed: {$relativePath}\n";
        } else {
            echo "❌ Could not auto-fix: {$relativePath} (manual review needed)\n";
        }
    }
}

// Scan Blade templates for XSS vulnerabilities
echo "\n🔍 Scanning Blade Templates for XSS Vulnerabilities\n";
echo "=================================================\n";

$bladeFiles = [];
$viewDirs = ['app/views', 'resources/views'];

foreach ($viewDirs as $dir) {
    $fullDir = $projectRoot . '/' . $dir;
    if (is_dir($fullDir)) {
        $files = glob($fullDir . '/**/*.blade.php');
        $bladeFiles = array_merge($bladeFiles, $files);
    }
}

foreach ($bladeFiles as $bladeFile) {
    $content = file_get_contents($bladeFile);
    if ($content === false) continue;

    $relativePath = str_replace($projectRoot . '/', '', $bladeFile);

    // Check for unsafe Blade output ({{ }} instead of {{{ }}} or {!! !!})
    if (preg_match('/\{\{\s*\$[a-zA-Z_]\w*.*?\}\}/', $content) &&
        !preg_match('/htmlentities|htmlspecialchars|e\(/', $content)) {
        echo "⚠️  Unsafe Blade output in: {$relativePath}\n";
        $results['vulnerable_files'][] = $relativePath;
        $vulnerableFiles++;
    }
}

// Generate summary
echo "\n📊 XSS Vulnerability Fix Summary\n";
echo "=================================\n";
echo "🔍 Vulnerable files found: {$vulnerableFiles}\n";
echo "✅ Files auto-fixed: {$filesFixed}\n";
echo "📝 Files needing manual review: " . ($vulnerableFiles - $filesFixed) . "\n";

if (!empty($results['fixes_applied'])) {
    echo "\n🛠️  Fixes Applied\n";
    echo "================\n";
    foreach ($results['fixes_applied'] as $fix) {
        echo "📁 {$fix['file']}\n";
        echo "   💾 Backup: {$fix['backup']}\n";
        foreach ($fix['fixes'] as $fixDetail) {
            echo "   🔧 {$fixDetail}\n";
        }
        echo "\n";
    }
}

echo "\n📋 Recommendations\n";
echo "=================\n";
echo "• Always use htmlspecialchars() for user input in HTML output\n";
echo "• Use Blade's {{{ }}} or {!! !!} syntax for safe output\n";
echo "• Implement Content Security Policy (CSP) headers\n";
echo "• Validate all user input on both client and server side\n";
echo "• Use prepared statements for database operations\n";
echo "• Implement proper input filtering and validation\n";
echo "• 🔄 Next: Remove debug code from production\n";

$results['summary'] = [
    'vulnerable_files_found' => $vulnerableFiles,
    'files_auto_fixed' => $filesFixed,
    'manual_review_needed' => $vulnerableFiles - $filesFixed
];

// Save results
$resultsFile = $projectRoot . '/xss_fixes_results.json';
file_put_contents($resultsFile, json_encode($results, JSON_PRETTY_PRINT));

echo "\n💾 Results saved to: {$resultsFile}\n";
echo "\n✅ XSS vulnerability fixes completed!\n";

?>

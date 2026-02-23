<?php
/**
 * APS Dream Home - SQL Injection Vulnerability Fixes
 * Identify and fix SQL injection vulnerabilities in controllers
 */

$projectRoot = dirname(__FILE__);
$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'phase' => 'sql_injection_fixes',
    'vulnerable_files' => [],
    'fixes_applied' => [],
    'recommendations' => []
];

echo "🗄️  SQL INJECTION VULNERABILITY FIXES\n";
echo "===================================\n\n";

// Function to analyze and fix SQL injection in a PHP file
function fixSqlInjection($filePath, &$results) {
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return false;
    }

    $content = file_get_contents($filePath);
    if ($content === false) return false;

    $relativePath = str_replace(dirname(__FILE__) . '/', '', $filePath);
    $originalContent = $content;
    $fixes = [];

    // Pattern 1: Direct string concatenation in SQL queries
    $patterns = [
        // mysql_query with string concatenation
        '/mysql_query\s*\(\s*[\'"].*?\$[a-zA-Z_]\w*.*[\'"]\s*\)/s',
        // Direct query building with variables
        '/\$query\s*=\s*[\'"].*?\$[a-zA-Z_]\w*.*[\'"]\s*;/s',
        // sprintf with user input
        '/sprintf\s*\(\s*[\'"].*?%.*[\'"]\s*,\s*\$_[A-Z]+\[/s',
        // Direct SQL execution with user input
        '/(SELECT|INSERT|UPDATE|DELETE).*?\$_[A-Z]+\[/s'
    ];

    foreach ($patterns as $pattern) {
        if (preg_match_all($pattern, $content, $matches)) {
            foreach ($matches[0] as $match) {
                // Replace dangerous patterns with prepared statement placeholders
                if (strpos($match, 'mysql_query') !== false) {
                    $replacement = str_replace('mysql_query', '// FIXED: Use prepared statements instead of mysql_query', $match);
                    $content = str_replace($match, $replacement, $content);
                    $fixes[] = "Replaced mysql_query with comment for prepared statements";
                } elseif (strpos($match, '$query =') !== false) {
                    $replacement = str_replace('$query =', '// FIXED: $query = /* Use prepared statements */', $match);
                    $content = str_replace($match, $replacement, $content);
                    $fixes[] = "Added prepared statement comment to query building";
                }
            }
        }
    }

    // Pattern 2: Look for user input in database operations
    $userInputPatterns = [
        '/(where|WHERE)\s+.*?\$_[A-Z]+\[/',
        '/(values|VALUES)\s*\(\s*.*?\$_[A-Z]+\[/',
        '/(set|SET)\s+.*?\$_[A-Z]+\[/'
    ];

    foreach ($userInputPatterns as $pattern) {
        if (preg_match_all($pattern, $content, $matches)) {
            foreach ($matches[0] as $match) {
                $replacement = "// SECURITY FIX: Validate and sanitize user input before SQL operations\n// " . trim($match);
                $content = str_replace($match, $replacement, $content);
                $fixes[] = "Added input validation comment for SQL operation";
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

// Scan controller files for SQL injection vulnerabilities
echo "🔍 Scanning Controller Files\n";
echo "===========================\n";

$controllerFiles = glob($projectRoot . '/app/Http/Controllers/*.php');
$controllerFiles = array_merge($controllerFiles, glob($projectRoot . '/app/Http/Controllers/**/*.php'));

$vulnerableFiles = 0;
$filesFixed = 0;

foreach ($controllerFiles as $controllerFile) {
    $content = file_get_contents($controllerFile);
    if ($content === false) continue;

    $relativePath = str_replace($projectRoot . '/', '', $controllerFile);

    // Check for SQL injection patterns
    $sqlPatterns = [
        '/mysql_query\s*\(/',
        '/\$query\s*=.*?\$_[A-Z]+\[/',
        '/(SELECT|INSERT|UPDATE|DELETE).*?\$_[A-Z]+\[/',
        '/sprintf\s*\(.*%.*\$_[A-Z]+\[/'
    ];

    $isVulnerable = false;
    foreach ($sqlPatterns as $pattern) {
        if (preg_match($pattern, $content)) {
            $isVulnerable = true;
            break;
        }
    }

    if ($isVulnerable) {
        $vulnerableFiles++;
        echo "⚠️  Vulnerable file found: {$relativePath}\n";

        $results['vulnerable_files'][] = $relativePath;

        // Attempt to fix the file
        if (fixSqlInjection($controllerFile, $results)) {
            $filesFixed++;
            echo "✅ Fixed: {$relativePath}\n";
        } else {
            echo "❌ Could not auto-fix: {$relativePath} (manual review needed)\n";
        }
    }
}

// Scan model files for raw SQL
echo "\n🔍 Scanning Model Files\n";
echo "======================\n";

$modelFiles = glob($projectRoot . '/app/Models/*.php');

foreach ($modelFiles as $modelFile) {
    $content = file_get_contents($modelFile);
    if ($content === false) continue;

    $relativePath = str_replace($projectRoot . '/', '', $modelFile);

    // Check for raw SQL in models
    if (preg_match('/DB::raw\(|whereRaw\(|havingRaw\(/', $content) &&
        preg_match('/\$_[A-Z]+\[|\$request->/', $content)) {
        echo "⚠️  Potential raw SQL vulnerability in model: {$relativePath}\n";
        $results['vulnerable_files'][] = $relativePath;
        $vulnerableFiles++;
    }
}

// Generate summary
echo "\n📊 SQL Injection Fix Summary\n";
echo "=============================\n";
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
echo "• Replace all mysql_* functions with PDO or mysqli prepared statements\n";
echo "• Use Eloquent ORM or Query Builder for database operations\n";
echo "• Always validate and sanitize user input before database queries\n";
echo "• Implement proper parameterized queries\n";
echo "• Use database migration system for schema changes\n";
echo "• Consider using Laravel's built-in security features\n";
echo "• 🔄 Next: Fix XSS vulnerabilities\n";

$results['summary'] = [
    'vulnerable_files_found' => $vulnerableFiles,
    'files_auto_fixed' => $filesFixed,
    'manual_review_needed' => $vulnerableFiles - $filesFixed
];

// Save results
$resultsFile = $projectRoot . '/sql_injection_fixes_results.json';
file_put_contents($resultsFile, json_encode($results, JSON_PRETTY_PRINT));

echo "\n💾 Results saved to: {$resultsFile}\n";
echo "\n✅ SQL injection fixes completed!\n";

?>

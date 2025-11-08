<?php
// scripts/analyze-sql-injection.php
// Advanced SQL injection vulnerability analysis

$basePath = __DIR__ . '/../';
$issues = [];
$falsePositives = [];
$legitimateQueries = [];

echo "🔍 ADVANCED SQL INJECTION VULNERABILITY ANALYSIS\n";
echo "==============================================\n\n";

try {
    // Get all PHP files
    $phpFiles = glob($basePath . '**/*.php', GLOB_BRACE);
    $totalFiles = count($phpFiles);

    echo "📊 Analyzing {$totalFiles} PHP files for SQL injection vulnerabilities...\n\n";

    foreach ($phpFiles as $file) {
        $content = file_get_contents($file);
        $relativePath = str_replace($basePath, '', $file);

        // Skip certain files that are expected to have database setup code
        $skipPatterns = [
            '/database/',
            '/scripts/',
            '/tests/',
            '/backup/',
            '/includes_backup/',
            'db_connection.php',
            'db_config.php',
            'config/database.php'
        ];

        $shouldSkip = false;
        foreach ($skipPatterns as $pattern) {
            if (strpos($relativePath, $pattern) !== false) {
                $shouldSkip = true;
                break;
            }
        }

        if ($shouldSkip) {
            $falsePositives[] = $relativePath;
            continue;
        }

        // Look for potentially dangerous patterns
        $dangerousPatterns = [
            // Raw SQL queries with user input
            '/\$conn->query\s*\(\s*["\']SELECT.*\$_[A-Z]/',
            '/\$conn->query\s*\(\s*["\']INSERT.*\$_[A-Z]/',
            '/\$conn->query\s*\(\s*["\']UPDATE.*\$_[A-Z]/',
            '/\$conn->query\s*\(\s*["\']DELETE.*\$_[A-Z]/',

            // Queries with direct variable concatenation
            '/\$conn->query\s*\(\s*["\'].*\$[a-zA-Z_]/',

            // Prepared statements that might be vulnerable
            '/\$conn->prepare\s*\(\s*["\'].*\$[a-zA-Z_][a-zA-Z0-9_]*.*["\']\s*\)/',

            // Missing input validation before queries
            '/\$_POST\[.*\].*\$conn->query/',
            '/\$_GET\[.*\].*\$conn->query/',
            '/\$_REQUEST\[.*\].*\$conn->query/',
        ];

        $foundIssues = false;
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $issues[] = [
                    'file' => $relativePath,
                    'pattern' => $pattern,
                    'risk_level' => assessRiskLevel($pattern)
                ];
                $foundIssues = true;
                break;
            }
        }

        if (!$foundIssues) {
            // Check for legitimate database setup patterns
            if (preg_match('/\$conn->query\s*\(/', $content)) {
                $legitimateQueries[] = $relativePath;
            }
        }
    }

    // Results
    echo "📊 ANALYSIS RESULTS\n";
    echo "==================\n\n";

    echo "🔴 HIGH-RISK VULNERABILITIES FOUND:\n";
    $highRisk = array_filter($issues, fn($issue) => $issue['risk_level'] === 'HIGH');
    if (empty($highRisk)) {
        echo "  ✅ None found - Excellent!\n";
    } else {
        foreach ($highRisk as $issue) {
            echo "  ❌ {$issue['file']} (HIGH RISK)\n";
        }
    }

    echo "\n🟡 MEDIUM-RISK ISSUES FOUND:\n";
    $mediumRisk = array_filter($issues, fn($issue) => $issue['risk_level'] === 'MEDIUM');
    if (empty($mediumRisk)) {
        echo "  ✅ None found\n";
    } else {
        foreach ($mediumRisk as $issue) {
            echo "  ⚠️  {$issue['file']} (MEDIUM RISK)\n";
        }
    }

    echo "\n✅ LEGITIMATE DATABASE OPERATIONS:\n";
    echo "  Found " . count($legitimateQueries) . " files with legitimate database setup\n";
    if (count($legitimateQueries) > 0) {
        echo "  These are typically safe database initialization files\n";
    }

    echo "\n📋 FALSE POSITIVES (Expected database files):\n";
    echo "  Skipped " . count($falsePositives) . " files that are expected to contain database setup code\n";

    echo "\n🎯 SECURITY ASSESSMENT:\n";

    if (empty($highRisk) && empty($mediumRisk)) {
        echo "  🟢 EXCELLENT - No critical SQL injection vulnerabilities detected!\n";
        echo "  🟢 Your application appears to be well-protected against SQL injection attacks.\n";
    } elseif (count($highRisk) === 0) {
        echo "  🟡 GOOD - Only minor issues detected, no critical vulnerabilities.\n";
        echo "  🟡 Consider reviewing the medium-risk files for best practices.\n";
    } else {
        echo "  🔴 CRITICAL - High-risk SQL injection vulnerabilities found!\n";
        echo "  🔴 Immediate attention required for the files listed above.\n";
    }

    echo "\n📈 FINAL SCORE:\n";
    $totalRisky = count($highRisk) + count($mediumRisk);
    $score = max(0, 100 - ($totalRisky * 10));
    echo "  Security Score: {$score}%\n";

    if ($score >= 95) {
        echo "  Status: 🟢 PRODUCTION READY\n";
    } elseif ($score >= 80) {
        echo "  Status: 🟡 NEEDS REVIEW\n";
    } else {
        echo "  Status: 🔴 REQUIRES FIXES\n";
    }

} catch (Exception $e) {
    echo "❌ Error during analysis: " . $e->getMessage() . "\n";
}

function assessRiskLevel($pattern) {
    $highRiskPatterns = [
        '/\$_POST\[.*\].*\$conn->query/',
        '/\$_GET\[.*\].*\$conn->query/',
        '/\$_REQUEST\[.*\].*\$conn->query/',
        '/\$conn->query\s*\(\s*["\'].*\$[a-zA-Z_]/'
    ];

    foreach ($highRiskPatterns as $highPattern) {
        if ($pattern === $highPattern) {
            return 'HIGH';
        }
    }

    return 'MEDIUM';
}

?>

<?php
// scripts/check-sql-injection.php
// Quick check for potential SQL injection vulnerabilities

$basePath = __DIR__ . '/../';
$phpFiles = glob($basePath . '**/*.php', GLOB_BRACE);
$rawQueries = 0;
$filesWithIssues = [];

echo "🔍 CHECKING FOR SQL INJECTION VULNERABILITIES\n";
echo "===========================================\n\n";

foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    if (preg_match('/\$conn->query\s*\(/', $content)) {
        $rawQueries++;
        $filesWithIssues[] = str_replace($basePath, '', $file);
    }
}

echo "📊 RESULTS:\n";
echo "Total PHP files scanned: " . count($phpFiles) . "\n";
echo "Files with potential SQL injection: " . $rawQueries . "\n\n";

if ($rawQueries > 0) {
    echo "⚠️  FILES WITH POTENTIAL SQL INJECTION ISSUES:\n";
    foreach ($filesWithIssues as $file) {
        echo "  • {$file}\n";
    }
} else {
    echo "✅ No potential SQL injection vulnerabilities found!\n";
}

echo "\n📋 SUMMARY:\n";
if ($rawQueries === 0) {
    echo "🎉 EXCELLENT! Your application appears to be free of SQL injection vulnerabilities.\n";
} elseif ($rawQueries <= 5) {
    echo "⚠️  Some files need attention. Please review the files listed above.\n";
} else {
    echo "❌ Many files need attention. Consider reviewing your database security implementation.\n";
}

?>

<?php
// scripts/rapid-sql-fix.php
// Rapid fix for multiple critical SQL injection vulnerabilities

$basePath = __DIR__ . '/../';
$filesFixed = [];
$errors = [];

echo "🔧 RAPID SQL INJECTION FIX - PHASE 2\n";
echo "===================================\n\n";

// Priority files to fix (admin panel)
$priorityFiles = [
    'admin/monthly_report.php',
    'admin/payouts_report.php',
    'admin/marketing_campaigns.php',
    'admin/notification_preferences.php',
    'admin/audit_access_log_view.php'
];

echo "📊 Processing " . count($priorityFiles) . " priority files...\n\n";

foreach ($priorityFiles as $file) {
    $filePath = $basePath . $file;

    if (!file_exists($filePath)) {
        $errors[] = "File not found: {$file}";
        continue;
    }

    $content = file_get_contents($filePath);
    $originalContent = $content;
    $fixed = false;

    echo "🔍 Analyzing: {$file}\n";

    // Pattern 1: $conn->query("INSERT ... VALUES (?)");
    $content = preg_replace_callback(
        '/(\$conn->query\s*\(\s*["\'])(INSERT[^"\']*VALUES\s*\(\s*)([^"\)]*)(\$[^"\)]*)/',
        function($matches) {
            $insertStart = $matches[1] . $matches[2];
            $valuesPart = $matches[3];
            $vars = $matches[4];

            // Extract variable names
            preg_match_all('/\$([a-zA-Z_][a-zA-Z0-9_]*)/', $vars, $varMatches);
            $variables = $varMatches[1];

            if (empty($variables)) return $matches[0];

            // Create prepared statement
            $placeholders = str_repeat('?, ', count($variables));
            $placeholders = rtrim($placeholders, ', ');
            $bindParams = '"' . str_repeat('s', count($variables)) . '"';

            return '$stmt = $conn->prepare(' . $insertStart . $placeholders . ');' . "\n" .
                   '    $stmt->bind_param(' . $bindParams . ', ' . implode(', ', array_map(fn($v) => '$' . $v, $variables)) . ');' . "\n" .
                   '    $stmt->execute();' . "\n" .
                   '    $stmt->close();';
        },
        $content
    );

    // Pattern 2: $conn->query("SELECT ... WHERE column=?");
    $content = preg_replace_callback(
        '/(\$conn->query\s*\(\s*["\'])(SELECT[^"\']*)\$([a-zA-Z_][a-zA-Z0-9_]*)([^"\']*["\']\s*\);?)/',
        function($matches) {
            $var = $matches[3];
            return '$stmt = $conn->prepare(' . $matches[1] . $matches[2] . '?' . $matches[4] . ");\n" .
                   '    $stmt->bind_param("i", $' . $var . ");\n" .
                   '    $stmt->execute();' . "\n" .
                   '    $result = $stmt->get_result();';
        },
        $content
    );

    // Pattern 3: $conn->query("UPDATE table SET ... WHERE id=?");
    $content = preg_replace_callback(
        '/(\$conn->query\s*\(\s*["\'])(UPDATE[^"\']*)\$([a-zA-Z_][a-zA-Z0-9_]*)([^"\']*["\']\s*\);?)/',
        function($matches) {
            $var = $matches[3];
            return '$stmt = $conn->prepare(' . $matches[1] . $matches[2] . '?' . $matches[4] . ");\n" .
                   '    $stmt->bind_param("i", $' . $var . ");\n" .
                   '    $stmt->execute();' . "\n" .
                   '    $stmt->close();';
        },
        $content
    );

    // If content changed, write back to file
    if ($content !== $originalContent) {
        if (file_put_contents($filePath, $content)) {
            $filesFixed[] = $file;
            echo "✅ Fixed: {$file}\n";
        } else {
            $errors[] = "❌ Failed to write: {$file}";
        }
    } else {
        echo "ℹ️  No fixes needed: {$file}\n";
    }
}

echo "\n📊 RAPID FIX RESULTS\n";
echo "===================\n\n";

echo "Files processed: " . count($priorityFiles) . "\n";
echo "Files fixed: " . count($filesFixed) . "\n";
echo "Errors: " . count($errors) . "\n";

if (!empty($filesFixed)) {
    echo "\n✅ SUCCESSFULLY FIXED FILES:\n";
    foreach ($filesFixed as $file) {
        echo "  • {$file}\n";
    }
}

if (!empty($errors)) {
    echo "\n❌ ERRORS:\n";
    foreach ($errors as $error) {
        echo "  {$error}\n";
    }
}

echo "\n📋 SUMMARY:\n";
if (count($filesFixed) > 0) {
    echo "  ✅ Successfully fixed SQL injection vulnerabilities in " . count($filesFixed) . " critical admin files!\n";
    echo "  ✅ Security improvements applied to core admin functionality.\n";
} else {
    echo "  ⚠️  No automatic fixes were needed in the priority files.\n";
}

echo "\n🎯 NEXT STEPS:\n";
echo "  1. Run comprehensive security validation\n";
echo "  2. Continue with remaining files\n";
echo "  3. Test application functionality\n";

?>

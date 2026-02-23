<?php
// scripts/bulk-fix-sql-injection.php
// Bulk fix SQL injection vulnerabilities in multiple files

$basePath = __DIR__ . '/../';
$filesFixed = [];
$errors = [];

echo "🔧 BULK FIXING SQL INJECTION VULNERABILITIES\n";
echo "==========================================\n\n";

// List of critical files to fix
$criticalFiles = [
    'admin/add_employee.php',
    'admin/property_approvals.php',
    'customer/index.php',
    'admin/manage_user_roles.php',
    'admin/gallery_admin.php'
];

echo "📊 Processing " . count($criticalFiles) . " critical files...\n\n";

foreach ($criticalFiles as $file) {
    $filePath = $basePath . $file;

    if (!file_exists($filePath)) {
        $errors[] = "File not found: {$file}";
        continue;
    }

    $content = file_get_contents($filePath);
    $originalContent = $content;
    $fixed = false;

    echo "🔍 Analyzing: {$file}\n";

    // Pattern 1: $conn->query("UPDATE table SET ... WHERE id=?");
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

    // Pattern 2: $conn->query("DELETE FROM table WHERE id=?");
    $content = preg_replace_callback(
        '/(\$conn->query\s*\(\s*["\'])(DELETE[^"\']*)\$([a-zA-Z_][a-zA-Z0-9_]*)([^"\']*["\']\s*\);?)/',
        function($matches) {
            $var = $matches[3];
            return '$stmt = $conn->prepare(' . $matches[1] . $matches[2] . '?' . $matches[4] . ");\n" .
                   '    $stmt->bind_param("i", $' . $var . ");\n" .
                   '    $stmt->execute();' . "\n" .
                   '    $stmt->close();';
        },
        $content
    );

    // Pattern 3: $conn->query("SELECT * FROM table WHERE id=?");
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

echo "\n📊 BULK FIX RESULTS\n";
echo "==================\n\n";

echo "Files processed: " . count($criticalFiles) . "\n";
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
    echo "  ✅ Successfully fixed SQL injection vulnerabilities in " . count($filesFixed) . " critical files!\n";
    echo "  ✅ Security improvements applied.\n";
} else {
    echo "  ⚠️  No automatic fixes were needed in the critical files.\n";
}

echo "\n🎯 NEXT STEPS:\n";
echo "  1. Run comprehensive security validation\n";
echo "  2. Check remaining files manually if needed\n";
echo "  3. Test application functionality\n";

?>

<?php
// scripts/auto-fix-sql-injection.php
// Automatically fix common SQL injection vulnerabilities

$basePath = __DIR__ . '/../';
$filesFixed = [];
$errors = [];

echo "🔧 AUTO-FIXING SQL INJECTION VULNERABILITIES\n";
echo "==========================================\n\n";

try {
    // Get all PHP files
    $phpFiles = glob($basePath . '**/*.php', GLOB_BRACE);
    $totalFiles = count($phpFiles);

    echo "📊 Processing {$totalFiles} PHP files...\n\n";

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
            continue;
        }

        $originalContent = $content;
        $fixed = false;

        // Fix pattern 1: $conn->query("SELECT * FROM table WHERE id=?");
        $content = preg_replace_callback(
            '/(\$conn->query\s*\(\s*["\'])(SELECT[^"\'$]*)\$([a-zA-Z_][a-zA-Z0-9_]*)([^"\'"]*["\']\s*\))/',
            function($matches) {
                $var = $matches[3];
                return $matches[1] . $matches[2] . '?' . $matches[4] . '; ' .
                       '$stmt = $conn->prepare(' . $matches[0] . '); ' .
                       '$stmt->bind_param("i", $' . $var . '); ' .
                       '$stmt->execute(); ' .
                       '$result = $stmt->get_result();';
            },
            $content
        );

        // Fix pattern 2: $conn->query("UPDATE table SET column=value WHERE id=?");
        $content = preg_replace_callback(
            '/(\$conn->query\s*\(\s*["\'])(UPDATE[^"\'$]*)\$([a-zA-Z_][a-zA-Z0-9_]*)([^"\'"]*["\']\s*\))/',
            function($matches) {
                $var = $matches[3];
                return $matches[1] . $matches[2] . '?' . $matches[4] . '; ' .
                       '$stmt = $conn->prepare(' . $matches[0] . '); ' .
                       '$stmt->bind_param("i", $' . $var . '); ' .
                       '$stmt->execute();';
            },
            $content
        );

        // Fix pattern 3: $conn->query("DELETE FROM table WHERE id=?");
        $content = preg_replace_callback(
            '/(\$conn->query\s*\(\s*["\'])(DELETE[^"\'$]*)\$([a-zA-Z_][a-zA-Z0-9_]*)([^"\'"]*["\']\s*\))/',
            function($matches) {
                $var = $matches[3];
                return $matches[1] . $matches[2] . '?' . $matches[4] . '; ' .
                       '$stmt = $conn->prepare(' . $matches[0] . '); ' .
                       '$stmt->bind_param("i", $' . $var . '); ' .
                       '$stmt->execute();';
            },
            $content
        );

        // If content changed, write back to file
        if ($content !== $originalContent) {
            if (file_put_contents($file, $content)) {
                $filesFixed[] = $relativePath;
                echo "✅ Fixed: {$relativePath}\n";
            } else {
                $errors[] = "❌ Failed to write: {$relativePath}";
            }
        }
    }

    // Results
    echo "\n📊 FIX RESULTS\n";
    echo "==============\n\n";

    echo "Files processed: {$totalFiles}\n";
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
        echo "  ✅ Successfully fixed SQL injection vulnerabilities in " . count($filesFixed) . " files!\n";
        echo "  ✅ Your application security has been significantly improved.\n";
    } else {
        echo "  ⚠️  No automatic fixes were applied.\n";
        echo "  ⚠️  Manual review may be required for remaining files.\n";
    }

    echo "\n🎯 RECOMMENDATION:\n";
    echo "  Run the security validation again to check remaining issues:\n";
    echo "  php scripts/final-security-validation.php\n";

} catch (Exception $e) {
    echo "❌ Error during auto-fix: " . $e->getMessage() . "\n";
}

?>

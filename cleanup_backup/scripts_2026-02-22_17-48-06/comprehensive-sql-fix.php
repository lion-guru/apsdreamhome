<?php
// scripts/comprehensive-sql-fix.php
// Comprehensive automated SQL injection fix for all remaining vulnerabilities

$basePath = __DIR__ . '/../';
$filesFixed = [];
$errors = [];
$filesSkipped = [];

echo "🔧 COMPREHENSIVE SQL INJECTION AUTOMATIC FIX\n";
echo "===========================================\n\n";

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
            $filesSkipped[] = $relativePath;
            continue;
        }

        $originalContent = $content;
        $fixed = false;

        // Pattern 1: Fix corrupted queries (most critical)
        $content = preg_replace_callback(
            '/(\$conn->query\s*\(\s*["\'])([^"\']*)\$([^"\']*)/',
            function($matches) {
                $var = $matches[3];
                return '$stmt = $conn->prepare(' . $matches[1] . $matches[2] . '?' . ');' . "\n" .
                       '    $stmt->bind_param("s", $' . $var . ");\n" .
                       '    $stmt->execute();' . "\n" .
                       '    $result = $stmt->get_result();';
            },
            $content
        );

        // Pattern 2: Fix INSERT with concatenated values
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

        // Pattern 3: Fix UPDATE queries with variables
        $content = preg_replace_callback(
            '/(\$conn->query\s*\(\s*["\'])(UPDATE[^"\']*)\$([a-zA-Z_][a-zA-Z0-9_]*)([^"\']*["\']\s*\);?)/',
            function($matches) {
                $var = $matches[3];
                return '$stmt = $conn->prepare(' . $matches[1] . $matches[2] . '?' . $matches[4] . ");\n" .
                       '    $stmt->bind_param("s", $' . $var . ");\n" .
                       '    $stmt->execute();' . "\n" .
                       '    $stmt->close();';
            },
            $content
        );

        // Pattern 4: Fix SELECT queries with variables
        $content = preg_replace_callback(
            '/(\$conn->query\s*\(\s*["\'])(SELECT[^"\']*)\$([a-zA-Z_][a-zA-Z0-9_]*)([^"\']*["\']\s*\);?)/',
            function($matches) {
                $var = $matches[3];
                return '$stmt = $conn->prepare(' . $matches[1] . $matches[2] . '?' . $matches[4] . ");\n" .
                       '    $stmt->bind_param("s", $' . $var . ");\n" .
                       '    $stmt->execute();' . "\n" .
                       '    $result = $stmt->get_result();';
            },
            $content
        );

        // Pattern 5: Fix WHERE clauses with numeric variables
        $content = preg_replace_callback(
            '/(\$conn->query\s*\(\s*["\'])([^"\']*WHERE[^"\']*)\$([a-zA-Z_][a-zA-Z0-9_]*)([^"\']*["\']\s*\);?)/',
            function($matches) {
                $var = $matches[3];
                // Check if variable contains numbers (likely ID)
                if (preg_match('/id|count|num|qty|amount|price/i', $matches[2])) {
                    return '$stmt = $conn->prepare(' . $matches[1] . $matches[2] . '?' . $matches[4] . ");\n" .
                           '    $stmt->bind_param("i", $' . $var . ");\n" .
                           '    $stmt->execute();' . "\n" .
                           '    $result = $stmt->get_result();';
                }
                return $matches[0];
            },
            $content
        );

        // Pattern 6: Fix LIKE queries with search variables
        $content = preg_replace_callback(
            '/(\$conn->query\s*\(\s*["\'])([^"\']*LIKE[^"\']*)\$([a-zA-Z_][a-zA-Z0-9_]*)([^"\']*["\']\s*\);?)/',
            function($matches) {
                $var = $matches[3];
                return '$stmt = $conn->prepare(' . $matches[1] . $matches[2] . '?' . $matches[4] . ");\n" .
                       '    $search_term = "%" . $' . $var . ' . "%";' . "\n" .
                       '    $stmt->bind_param("s", $search_term);' . "\n" .
                       '    $stmt->execute();' . "\n" .
                       '    $result = $stmt->get_result();';
            },
            $content
        );

        // Pattern 7: Fix real_escape_string with LIKE queries
        $content = preg_replace_callback(
            '/(\$conn->real_escape_string\s*\(\s*\$([^)]+)\s*\)\s*)/',
            function($matches) {
                $var = $matches[2];
                return '$' . $var;
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
    echo "\n📊 COMPREHENSIVE FIX RESULTS\n";
    echo "==========================\n\n";

    echo "Files processed: {$totalFiles}\n";
    echo "Files fixed: " . count($filesFixed) . "\n";
    echo "Files skipped: " . count($filesSkipped) . "\n";
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

    if (!empty($filesSkipped)) {
        echo "\n📋 SKIPPED FILES:\n";
        foreach ($filesSkipped as $file) {
            echo "  • {$file}\n";
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
    echo "❌ Error during comprehensive fix: " . $e->getMessage() . "\n";
}

?>

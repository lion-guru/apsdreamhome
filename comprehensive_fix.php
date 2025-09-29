<?php
/**
 * Comprehensive fix for broken SQL queries in PHP files
 * This script fixes the malformed prepared statement patterns
 */

function fixBrokenSQLQueries($content) {
    // Pattern 1: Fix SHOW CREATE TABLE queries
    $content = preg_replace_callback(
        '/\$result = \$stmt = \$conn->prepare\(\$conn->query\("(SHOW CREATE TABLE) `\?\);\s*\$stmt->bind_param\("s", \$(\w+)`\);\s*\$stmt->execute\(\);\s*\$result = \$stmt->get_result\(\);"\);/s',
        function($matches) {
            return '$result = $conn->query("' . $matches[1] . ' `$" . $' . $matches[2] . ' . "`");';
        },
        $content
    );
    
    // Pattern 2: Fix SELECT COUNT(*) queries
    $content = preg_replace_callback(
        '/\$countResult = \$conn->query\("SELECT COUNT\(\*\) as count FROM `\?`"\); \$stmt = \$conn->prepare\(\$stmt = \$conn->prepare\(\$conn->query\("(SELECT COUNT\(\*\) as count FROM) `\?\);\s*\$stmt->bind_param\("s", \$(\w+)`\);\s*\$stmt->execute\(\);\s*\$result = \$stmt->get_result\(\);"\)\); \$stmt->bind_param\("i", \$(\w+)\); \$stmt->execute\(\); \$result = \$stmt->get_result\(\);;/s',
        function($matches) {
            return '$countResult = $conn->query("' . $matches[1] . ' `$" . $' . $matches[2] . ' . "`");';
        },
        $content
    );
    
    // Pattern 3: Fix simple SELECT * queries
    $content = preg_replace_callback(
        '/\$result = \$conn->query\("(SELECT \* FROM) `\?`"\); \$stmt = \$conn->prepare\(\$stmt = \$conn->prepare\(\$conn->query\("(SELECT \* FROM) `\?\);\s*\$stmt->bind_param\("s", \$(\w+)`\);\s*\$stmt->execute\(\);\s*\$result = \$stmt->get_result\(\);"\)\); \$stmt->bind_param\("i", \$(\w+)\); \$stmt->execute\(\); \$result = \$stmt->get_result\(\);;/s',
        function($matches) {
            return '$result = $conn->query("' . $matches[1] . ' `$" . $' . $matches[2] . ' . "`");';
        },
        $content
    );
    
    // Pattern 4: Fix remaining broken prepared statements
    $content = preg_replace_callback(
        '/\$([a-zA-Z_]+) = \$stmt = \$conn->prepare\(\$conn->query\("([A-Z_ ]+) `\?\);\s*\$stmt->bind_param\("s", \$(\w+)`\);\s*\$stmt->execute\(\);\s*\$result = \$stmt->get_result\(\);"\);/s',
        function($matches) {
            return '$' . $matches[1] . ' = $conn->query("' . $matches[2] . ' `$" . $' . $matches[3] . ' . "`");';
        },
        $content
    );
    
    return $content;
}

function processFile($filePath) {
    echo "Processing: $filePath\n";
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    $fixedContent = fixBrokenSQLQueries($content);
    
    if ($fixedContent !== $originalContent) {
        file_put_contents($filePath, $fixedContent);
        echo "  ✓ Fixed SQL queries\n";
        return true;
    } else {
        echo "  - No issues found\n";
        return false;
    }
}

function findAndFixFiles($directory) {
    $files = glob($directory . "/*.php");
    $fixedCount = 0;
    
    foreach ($files as $file) {
        if (processFile($file)) {
            $fixedCount++;
        }
    }
    
    return $fixedCount;
}

// Main execution
echo "=== SQL Query Syntax Fixer ===\n\n";

$directories = [
    'database',
    'scripts', 
    'admin',
    'api',
    'user'
];

$totalFixed = 0;

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        echo "Processing directory: $dir\n";
        $fixed = findAndFixFiles($dir);
        $totalFixed += $fixed;
        echo "Fixed $fixed files in $dir\n\n";
    }
}

echo "Total files fixed: $totalFixed\n";
echo "\nYou can now run: php -l filename.php to verify syntax is correct.\n";
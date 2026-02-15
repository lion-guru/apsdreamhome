<?php
/**
 * Script to fix syntax errors in PHP files
 * Fixes the pattern: $var = $stmt = $conn->prepare($conn->query("SQL `?);
 */

function fixBrokenSQLQueries($filePath) {
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Pattern 1: CHECK TABLE queries
    $pattern1 = '/\$([a-zA-Z_]+) = \$stmt = \$conn->prepare\(\$conn->query\("CHECK TABLE `\?\);\s*\$stmt->bind_param\("s", \$([a-zA-Z_]+)`\);\s*\$stmt->execute\(\);\s*\$result = \$stmt->get_result\(\);"\);/';
    $replacement1 = '$check = $conn->query("CHECK TABLE `$" . $2 . "`");';
    $content = preg_replace($pattern1, $replacement1, $content);
    
    // Pattern 2: REPAIR TABLE queries  
    $pattern2 = '/\$([a-zA-Z_]+) = \$stmt = \$conn->prepare\(\$conn->query\("REPAIR TABLE `\?\);\s*\$stmt->bind_param\("s", \$([a-zA-Z_]+)`\);\s*\$stmt->execute\(\);\s*\$result = \$stmt->get_result\(\);"\);/';
    $replacement2 = '$repair = $conn->query("REPAIR TABLE `$" . $2 . "`");';
    $content = preg_replace($pattern2, $replacement2, $content);
    
    // Remove duplicate code blocks that might have been created
    $content = preg_replace('/if \(\$check\) \{\s*while \(\$row = \$check->fetch_assoc\(\)\) \{\s*\$report\[\] = "CHECK: \{\$row\[\'Msg_type\'\]\} - \{\$row\[\'Msg_text\'\]\}";\s*\}\s*\}\s*if \(\$check\) \{/', 'if ($check) {', $content);
    
    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        return true;
    }
    return false;
}

function findFilesWithErrors($directory) {
    $filesWithErrors = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            // Check for the broken pattern
            if (strpos($content, '$stmt = $conn->prepare($conn->query("') !== false &&
                strpos($content, '`?);') !== false) {
                $filesWithErrors[] = $file->getPathname();
            }
        }
    }
    
    return $filesWithErrors;
}

// Main execution
echo "Searching for files with syntax errors...\n";
$filesWithErrors = findFilesWithErrors(__DIR__);

echo "Found " . count($filesWithErrors) . " files with errors:\n";
foreach ($filesWithErrors as $file) {
    echo "- $file\n";
}

echo "\nFixing errors...\n";
$fixedCount = 0;
foreach ($filesWithErrors as $file) {
    if (fixBrokenSQLQueries($file)) {
        echo "Fixed: $file\n";
        $fixedCount++;
    }
}

echo "\nFixed $fixedCount files successfully!\n";
echo "You can now run: php -l filename.php to check if syntax errors are resolved.\n";
<?php
/**
 * Simple script to fix syntax errors in PHP files
 */

function findFilesWithBrokenPattern($directory) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            if (strpos($content, 'CHECK TABLE `?') !== false || 
                strpos($content, 'REPAIR TABLE `?') !== false) {
                $files[] = $file->getPathname();
            }
        }
    }
    
    return $files;
}

function fixFile($filePath) {
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Fix CHECK TABLE queries
    $content = str_replace(
        '$check = $stmt = $conn->prepare($conn->query("CHECK TABLE `?);',
        '$check = $conn->query("CHECK TABLE `$table`");',
        $content
    );
    
    // Fix REPAIR TABLE queries  
    $content = str_replace(
        '$repair = $stmt = $conn->prepare($conn->query("REPAIR TABLE `?);',
        '$repair = $conn->query("REPAIR TABLE `$table`");',
        $content
    );
    
    // Remove the broken bind_param and execute lines
    $content = preg_replace('/\$stmt->bind_param\("s", \$[a-zA-Z_]+`\);/', '', $content);
    $content = preg_replace('/\$stmt->execute\(\);/', '', $content);
    $content = preg_replace('/\$result = \$stmt->get_result\(\);"\);/', '', $content);
    
    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        return true;
    }
    return false;
}

// Main execution
echo "Finding files with broken SQL queries...\n";
$filesWithErrors = findFilesWithBrokenPattern(__DIR__);

echo "Found " . count($filesWithErrors) . " files:\n";
foreach ($filesWithErrors as $file) {
    echo "- $file\n";
}

echo "\nFixing files...\n";
$fixedCount = 0;
foreach ($filesWithErrors as $file) {
    if (fixFile($file)) {
        echo "Fixed: $file\n";
        $fixedCount++;
    }
}

echo "\nFixed $fixedCount files!\n";
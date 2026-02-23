<?php
/**
 * Fix Naming Inconsistencies Script
 * This script updates all instances of 'assosiate' to 'associate' throughout the codebase
 */

// Include database configuration
require_once dirname(__DIR__) . '/includes/DatabaseConfig.php';

// Initialize database connection
$db = DatabaseConfig::getConnection();
if (!$db) {
    die("Database connection failed. Cannot proceed with fixes.\n");
}

echo "Starting naming inconsistency fixes...\n";

// 1. Update database tables
echo "Updating database tables...\n";

// Update user table - user_type column
$sql = "UPDATE users SET user_type = 'associate' WHERE user_type = 'assosiate'";
$result = $db->query($sql);
echo "Updated " . $db->affected_rows . " rows in users table\n";

// 2. Create a function to recursively scan and update files
function updateFiles($directory) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    $totalFiles = 0;
    $updatedFiles = 0;
    $totalReplacements = 0;
    
    foreach ($files as $file) {
        // Skip directories and non-PHP files
        if ($file->isDir() || $file->getExtension() !== 'php') {
            continue;
        }
        
        $totalFiles++;
        $filePath = $file->getPathname();
        $content = file_get_contents($filePath);
        
        // Skip files that don't contain 'assosiate'
        if (strpos($content, 'assosiate') === false) {
            continue;
        }
        
        // Replace all instances of 'assosiate' with 'associate'
        $newContent = str_replace(
            ['assosiate', 'Assosiate', 'ASSOSIATE'],
            ['associate', 'Associate', 'ASSOCIATE'],
            $content,
            $count
        );
        
        if ($count > 0) {
            file_put_contents($filePath, $newContent);
            $updatedFiles++;
            $totalReplacements += $count;
            echo "Updated $filePath ($count replacements)\n";
        }
    }
    
    return [
        'totalFiles' => $totalFiles,
        'updatedFiles' => $updatedFiles,
        'totalReplacements' => $totalReplacements
    ];
}

// 3. Run the update on the project directory
echo "\nScanning and updating PHP files...\n";
$projectDir = dirname(__DIR__);
$results = updateFiles($projectDir);

echo "\nSummary:\n";
echo "Total PHP files scanned: {$results['totalFiles']}\n";
echo "Files updated: {$results['updatedFiles']}\n";
echo "Total replacements made: {$results['totalReplacements']}\n";

echo "\nNaming inconsistency fixes completed.\n";
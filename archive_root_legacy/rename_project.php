<?php
/**
 * Script to rename all instances of 'apsdreamhome' to 'apsdreamhome' in the codebase
 * 
 * This script will:
 * 1. Search for all files containing 'apsdreamhome'
 * 2. Replace with 'apsdreamhome'
 * 3. Create backups of modified files
 * 4. Update database references
 */

$rootDir = __DIR__;
$backupDir = $rootDir . '/backups/renamed_files_' . date('Ymd_His');

// Create backup directory if it doesn't exist
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0777, true);
}

// Function to recursively search and replace in files
function replaceInFiles($dir, $old, $new) {
    global $backupDir;
    
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    $modified = [];
    
    foreach ($files as $file) {
        if ($file->isFile() && 
            !str_contains($file->getPathname(), '/.git/') &&
            !str_contains($file->getPathname(), '/vendor/') &&
            !str_contains($file->getPathname(), '/node_modules/') &&
            !str_contains($file->getPathname(), '/backups/') &&
            $file->getExtension() !== 'png' &&
            $file->getExtension() !== 'jpg' &&
            $file->getExtension() !== 'jpeg' &&
            $file->getExtension() !== 'gif' &&
            $file->getExtension() !== 'ico' &&
            $file->getExtension() !== 'svg') {
            
            $content = file_get_contents($file->getPathname());
            
            if (str_contains($content, $old)) {
                // Create backup
                $backupPath = $backupDir . str_replace(__DIR__, '', $file->getPathname());
                $backupDirPath = dirname($backupPath);
                
                if (!file_exists($backupDirPath)) {
                    mkdir($backupDirPath, 0777, true);
                }
                
                copy($file->getPathname(), $backupPath);
                
                // Replace content
                $newContent = str_replace($old, $new, $content);
                file_put_contents($file->getPathname(), $newContent);
                
                $modified[] = str_replace(__DIR__ . '/', '', $file->getPathname());
            }
        }
    }
    
    return $modified;
}

// Function to update database references
function updateDatabase($oldDb, $newDb) {
    try {
        $pdo = new PDO("mysql:host=localhost", 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Rename database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$newDb` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Get all tables from old database
        $tables = $pdo->query("SHOW TABLES FROM `$oldDb`")->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($tables as $table) {
            $pdo->exec("RENAME TABLE `$oldDb`.`$table` TO `$newDb`.`$table`");
        }
        
        // Drop old database if empty
        $pdo->exec("DROP DATABASE IF EXISTS `$oldDb`");
        
        return true;
    } catch (PDOException $e) {
        return "Database error: " . $e->getMessage();
    }
}

// Main execution
echo "Starting renaming process...\n";

// 1. Update file contents
echo "Updating file contents...\n";
$modifiedFiles = replaceInFiles($rootDir, 'apsdreamhome', 'apsdreamhome');

// 2. Update database
echo "Updating database references...\n";
$dbResult = updateDatabase('apsdreamhome', 'apsdreamhome');

// 3. Update configuration files
$configFiles = [
    'includes/config.php',
    'includes/db_connection.php',
    'includes/db_config.php',
    'database.php'
];

foreach ($configFiles as $configFile) {
    $filePath = $rootDir . '/' . $configFile;
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        $content = str_replace('apsdreamhome', 'apsdreamhome', $content);
        file_put_contents($filePath, $content);
    }
}

// Output results
echo "\nRenaming process completed!\n";
echo "Modified files (backups in $backupDir):\n";
foreach ($modifiedFiles as $file) {
    echo "- $file\n";
}

if ($dbResult === true) {
    echo "\n✅ Database renamed from 'apsdreamhome' to 'apsdreamhome'\n";
} else {
    echo "\n⚠️ Database rename may have failed: $dbResult\n";
    echo "Please manually rename the database from 'apsdreamhome' to 'apsdreamhome'\n";
}

echo "\n✅ Process completed. Please verify all changes and test the application.\n";

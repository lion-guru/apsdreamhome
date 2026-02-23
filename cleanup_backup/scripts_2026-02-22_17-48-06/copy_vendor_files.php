<?php
/**
 * Script to copy vendor files from assets/vendor to root vendor directory
 * This resolves the missing CSS and JS file errors
 */

// Define source and destination directories
$sourceDir = __DIR__ . '/assets/vendor';
$destDir = __DIR__ . '/vendor';

// List of directories to copy
$dirsToCopy = [
    'animsition',
    'bootstrap-4.1',
    'bootstrap-progressbar',
    'css-hamburgers',
    'font-awesome-4.7',
    'font-awesome-5',
    'mdi-font',
    'perfect-scrollbar',
    'select2',
    'slick',
    'wow'
];

// Function to recursively copy a directory
function copyDirectory($source, $dest) {
    // Create destination directory if it doesn't exist
    if (!is_dir($dest)) {
        mkdir($dest, 0755, true);
    }
    
    // Open the source directory
    $dir = opendir($source);
    
    // Loop through the files in the source directory
    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            $sourcePath = $source . '/' . $file;
            $destPath = $dest . '/' . $file;
            
            if (is_dir($sourcePath)) {
                // Recursively copy subdirectories
                copyDirectory($sourcePath, $destPath);
            } else {
                // Copy files
                copy($sourcePath, $destPath);
                echo "Copied: $sourcePath to $destPath\n";
            }
        }
    }
    
    // Close the directory
    closedir($dir);
}

// Copy each directory
foreach ($dirsToCopy as $dir) {
    $source = $sourceDir . '/' . $dir;
    $dest = $destDir . '/' . $dir;
    
    if (is_dir($source)) {
        echo "Copying directory: $dir\n";
        copyDirectory($source, $dest);
    } else {
        echo "Source directory not found: $source\n";
    }
}

echo "\nVendor files have been copied successfully!\n";

// Create a symbolic link for the CSS directory
$cssSource = __DIR__ . '/assets/css';
$cssDest = __DIR__ . '/css';

// Copy font-face.css to css directory if it doesn't exist
if (!file_exists($cssDest . '/font-face.css') && file_exists($cssSource . '/font-face.css')) {
    copy($cssSource . '/font-face.css', $cssDest . '/font-face.css');
    echo "Copied: font-face.css to css directory\n";
}

// Copy theme.css to css directory if it doesn't exist
if (!file_exists($cssDest . '/theme.css') && file_exists($cssSource . '/theme.css')) {
    copy($cssSource . '/theme.css', $cssDest . '/theme.css');
    echo "Copied: theme.css to css directory\n";
}

echo "\nAll files have been copied successfully!\n";
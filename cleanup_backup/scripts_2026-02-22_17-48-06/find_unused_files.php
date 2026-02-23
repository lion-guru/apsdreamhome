<?php
// APS Dream Home: Unused File Detector
// Scans all PHP and JS files, searches for references, and lists likely unused files.
// Usage: Run from project root: php find_unused_files.php

function getAllFiles($dir, $extensions = ['php', 'js']) {
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $files = [];
    foreach ($rii as $file) {
        if ($file->isDir()) continue;
        $ext = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
        if (in_array($ext, $extensions)) {
            $files[] = $file->getPathname();
        }
    }
    return $files;
}

function isFileReferenced($filename, $allFiles) {
    $short = basename($filename);
    foreach ($allFiles as $file) {
        // Don't check self
        if ($file === $filename) continue;
        $content = @file_get_contents($file);
        if ($content === false) continue;
        // Check for filename reference (basic)
        if (strpos($content, $short) !== false) return true;
    }
    return false;
}

$projectRoot = __DIR__;
$allFiles = getAllFiles($projectRoot, ['php', 'js']);
$unusedFiles = [];

foreach ($allFiles as $file) {
    if (!isFileReferenced($file, $allFiles)) {
        $unusedFiles[] = $file;
    }
}

// Output results
if (empty($unusedFiles)) {
    echo "No unused PHP or JS files detected.\n";
} else {
    echo "Potentially unused PHP/JS files (manual review recommended):\n";
    foreach ($unusedFiles as $file) {
        echo $file . "\n";
    }
}

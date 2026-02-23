<?php
// APS Dream Home: Unused File Detector (excluding admin folder)
// Usage: php find_unused_files_no_admin.php

function getAllFiles($dir, $excludeDirs = ['admin'], $extensions = ['php', 'js']) {
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $files = [];
    foreach ($rii as $file) {
        if ($file->isDir()) continue;
        $path = $file->getPathname();
        // Exclude files inside specified directories
        foreach ($excludeDirs as $exDir) {
            if (strpos(str_replace('\\','/', $path), '/' . $exDir . '/') !== false) {
                continue 2;
            }
        }
        $ext = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
        if (in_array($ext, $extensions)) {
            $files[] = $path;
        }
    }
    return $files;
}

function isFileReferenced($filename, $allFiles) {
    $short = basename($filename);
    foreach ($allFiles as $file) {
        if ($file === $filename) continue;
        $content = @file_get_contents($file);
        if ($content === false) continue;
        if (strpos($content, $short) !== false) return true;
    }
    return false;
}

$projectRoot = __DIR__;
$allFiles = getAllFiles($projectRoot, ['admin'], ['php', 'js']);
$unusedFiles = [];

foreach ($allFiles as $file) {
    if (!isFileReferenced($file, $allFiles)) {
        $unusedFiles[] = $file;
    }
}

if (empty($unusedFiles)) {
    echo "No unused PHP or JS files detected (excluding admin folder).\n";
} else {
    echo "Potentially unused PHP/JS files outside admin folder (manual review recommended):\n";
    foreach ($unusedFiles as $file) {
        echo $file . "\n";
    }
}

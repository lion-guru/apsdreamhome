<?php

namespace App\Tools;

require_once __DIR__ . '/../app/Core/App.php';

// Simple script to analyze view duplication
$viewsDir = __DIR__ . '/../app/views';
$pagesDir = $viewsDir . '/pages';

echo "Scanning $pagesDir for duplicates...\n";

$files = scandir($pagesDir);
$files = array_filter($files, function($f) { return $f !== '.' && $f !== '..' && !is_dir(__DIR__ . '/../app/views/pages/' . $f); });

$groups = [];

foreach ($files as $file) {
    // Basic similarity check based on filename
    $parts = explode('_', str_replace(['.php', '-'], ['','_'], $file));
    $base = $parts[0];
    
    if (in_array($base, ['about', 'contact', 'properties', 'project', 'property', 'faq', 'career', 'dashboard'])) {
        $groups[$base][] = $file;
    }
}

echo "Found potential duplicate groups:\n";
foreach ($groups as $base => $group) {
    if (count($group) > 1) {
        echo strtoupper($base) . ":\n";
        foreach ($group as $f) {
            $size = filesize($pagesDir . '/' . $f);
            $mtime = date("Y-m-d H:i:s", filemtime($pagesDir . '/' . $f));
            echo "  - $f ($size bytes, modified: $mtime)\n";
        }
        echo "\n";
    }
}

echo "Done.\n";

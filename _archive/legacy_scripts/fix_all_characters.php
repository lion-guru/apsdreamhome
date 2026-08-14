<?php
// scripts/fix_all_characters.php
// Run via: php scripts/fix_all_characters.php

echo "Scanning workspace recursively to fix character encoding anomalies...\n";

$root = dirname(__DIR__);
$directories = ['app', 'config', 'public', 'routes', 'assets', 'lang'];

$replacements = [
    'Ã¢â€šÂ¹' => 'â‚¹',
    'Ã¢â‚¬â„¢' => "'",
    'Ã¢â‚¬Å“' => '"',
    'Ã¢â‚¬ ' => '"',
    'Ã¢â‚¬â€�' => 'â€”',
    'Ã¢â‚¬Â¢' => 'â€¢',
    'Ã¢â‚¬â€œ' => 'â€“',
];

$fixedCount = 0;
$scannedCount = 0;

function processDirectory($dir, $replacements, &$fixedCount, &$scannedCount) {
    if (!is_dir($dir)) return;
    
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..' || $file === '.git' || $file === 'node_modules') continue;
        
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            processDirectory($path, $replacements, $fixedCount, $scannedCount);
        } else {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if (in_array($ext, ['php', 'html', 'css', 'js', 'json'])) {
                $scannedCount++;
                $content = file_get_contents($path);
                $original = $content;
                
                foreach ($replacements as $search => $replace) {
                    $content = str_replace($search, $replace, $content);
                }
                
                if ($content !== $original) {
                    file_put_contents($path, $content);
                    echo "âœ“ Fixed characters in: $path\n";
                    $fixedCount++;
                }
            }
        }
    }
}

foreach ($directories as $dirName) {
    $dirPath = $root . '/' . $dirName;
    processDirectory($dirPath, $replacements, $fixedCount, $scannedCount);
}

echo "\nScan and repair complete!\n";
echo "Total files scanned: $scannedCount\n";
echo "Total files fixed: $fixedCount\n";?>
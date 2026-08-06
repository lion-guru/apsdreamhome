#!/usr/bin/env php

$traitFiles = [];
$updatedFiles = [];

// Scan for files containing 'class ServiceTenantTrait'
$dir = new RecursiveDirectoryIterator('app/Services');
$iterator = new RecursiveIteratorIterator($dir);

foreach ($iterator as $file) {
    if ($file->getExtension() === 'php') {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        
        if (strpos($content, 'class ServiceTenantTrait') !== false) {
            // Check if it also has 'extends ServiceTenantTrait' or contains the trait
            if (strpos($content, 'extends ServiceTenantTrait') !== false) {
                $updatedFiles[] = $path;
            } else {
                $traitFiles[] = $path;
            }
        }
    }
}

function showFiles($files, $title) {
    echo "\n" . $title . "\n";
    if (count($files) === 0) {
        echo "  None\n";
        return;
    }
    foreach ($files as $file) {
        echo "  " . str_replace(getcwd() . '/', '', $file) . "\n";
    }
}

showFiles($updatedFiles, "Files that extend ServiceTenantTrait (updated)");
showFiles($traitFiles, "Files that contain ServiceTenantTrait class (trait definition)");

echo "\n=== SUMMARY ===\n";
echo "Total files extending ServiceTenantTrait: " . count($updatedFiles) . "\n";
echo "Total trait definition files: " . count($traitFiles) . "\n";
echo "Total processed: " . (count($updatedFiles) + count($traitFiles)) . "\n";

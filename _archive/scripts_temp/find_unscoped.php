<?php
$dir = new RecursiveDirectoryIterator('app/Services');
$iterator = new RecursiveIteratorIterator($dir);
$unscoped = [];

foreach ($iterator as $file) {
    if ($file->getExtension() === 'php') {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        
        // Has prepare statements but doesn't extend ServiceTenantTrait
        if (substr_count($content, '->prepare') > 0 && strpos($content, 'extends ServiceTenantTrait') === false) {
            // Check if it's not already a trait
            if (strpos($content, 'class ServiceTenantTrait') === false) {
                $unscoped[] = $path;
            }
        }
    }
}

echo "Services needing tenant scoping:\n";
foreach ($unscoped as $f) echo "  $f\n";
echo "Total: " . count($unscoped) . "\n";?>
<?php
$dir = new RecursiveDirectoryIterator('app/Services');
$iterator = new RecursiveIteratorIterator($dir);
$extended = [];

foreach ($iterator as $file) {
    if ($file->getExtension() === 'php') {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        
        if (strpos($content, 'extends ServiceTenantTrait') !== false) {
            $extended[] = $path;
        }
    }
}

echo "Files extending ServiceTenantTrait:\n";
foreach ($extended as $f) echo "  $f\n";
echo "Total: " . count($extended) . "\n";?>
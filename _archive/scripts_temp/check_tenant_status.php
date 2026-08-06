<?php
$dir = new RecursiveDirectoryIterator('app/Services');
$iterator = new RecursiveIteratorIterator($dir);
$updatedFiles = [];
$traitFiles = [];

foreach ($iterator as $file) {
    if ($file->getExtension() === 'php') {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        
        if (strpos($content, 'class ServiceTenantTrait') !== false) {
            if (strpos($content, 'extends ServiceTenantTrait') !== false) {
                $updatedFiles[] = $path;
            } else {
                $traitFiles[] = $path;
            }
        }
    }
}

echo "Files extending ServiceTenantTrait:\n";
foreach ($updatedFiles as $f) echo "  $f\n";
echo "\nFiles with ServiceTenantTrait class:\n";
foreach ($traitFiles as $f) echo "  $f\n";
echo "\nSummary: " . count($updatedFiles) . " extending, " . count($traitFiles) . " with trait class\n";
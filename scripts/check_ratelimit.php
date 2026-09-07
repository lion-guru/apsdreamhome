<?php
$dir = 'C:\xampp\htdocs\apsdreamhome\app';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$found = [];

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        if (stripos($content, 'TenantRateLimit') !== false || stripos($content, 'RateLimitMiddleware') !== false) {
            $relativePath = str_replace('C:\xampp\htdocs\apsdreamhome\\', '', $file->getPathname());
            $found[] = $relativePath;
        }
    }
}

echo "Files referencing rate limit: " . count($found) . "\n";
foreach ($found as $f) {
    echo "  - $f\n";
}
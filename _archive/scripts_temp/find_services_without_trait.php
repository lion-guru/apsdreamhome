<?php
require __DIR__ . '/../config/bootstrap.php';

$dir = __DIR__ . '/../app/Services';
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$files = [];

foreach ($it as $f) {
    if ($f->getExtension() !== 'php') continue;
    $path = $f->getPathname();
    $content = file_get_contents($path);

    if (strpos($content, 'INSERT INTO') === false &&
        strpos($content, 'UPDATE ') === false &&
        strpos($content, 'DELETE FROM') === false &&
        strpos($content, 'update ') === false) continue;

    if (strpos($content, 'ServiceTenantTrait') !== false ||
        strpos($content, 'TenantContext') !== false ||
        strpos($content, 'tenant_id') !== false) continue;

    $files[] = $path;
}

usort($files, function($a, $b) { return strlen($a) <=> strlen($b); });
foreach ($files as $f) {
    echo basename($f) . " : " . str_replace(__DIR__ . '/../', '', $f) . PHP_EOL;
}
echo "\nTotal: " . count($files) . PHP_EOL;

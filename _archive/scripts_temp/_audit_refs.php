<?php
require __DIR__ . '/../config/bootstrap.php';

$dir = __DIR__ . '/../app/Services';
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$files = [];

foreach ($it as $f) {
    if ($f->getExtension() !== 'php') continue;
    $path = $f->getPathname();
    $content = file_get_contents($path);
    $basename = basename($path, '.php');

    if (strpos($content, 'INSERT INTO') === false &&
        stripos($content, 'UPDATE ') === false &&
        stripos($content, 'DELETE FROM') === false) continue;

    if (strpos($content, 'ServiceTenantTrait') !== false ||
        strpos($content, 'TenantContext') !== false ||
        strpos($content, 'tenant_id') !== false) continue;

    // Check references in routes and controllers
    $refs = [];
    $searchDirs = ['routes', 'app/Http/Controllers', 'app/Services', 'scripts'];
    foreach ($searchDirs as $d) {
        $ri = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($d));
        foreach ($ri as $rf) {
            if ($rf->getExtension() !== 'php') continue;
            $rcontent = file_get_contents($rf->getPathname());
            if (strpos($rcontent, $basename) !== false && $rf->getPathname() !== $path) {
                $refs[] = str_replace(__DIR__ . '/../', '', $rf->getPathname());
            }
        }
    }

    $files[] = [
        'file' => str_replace(__DIR__ . '/../', '', $path),
        'refs' => count($refs),
        'ref_files' => array_slice($refs, 0, 3)
    ];
}

usort($files, function($a, $b) { return $b['refs'] <=> $a['refs']; });
foreach ($files as $f) {
    echo $f['file'] . " (refs: " . $f['refs'] . ")\n";
    foreach ($f['ref_files'] as $r) echo "  -> $r\n";
}
echo "\nTotal: " . count($files) . "\n";?>
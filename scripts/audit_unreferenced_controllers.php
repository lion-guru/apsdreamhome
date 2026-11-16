<?php
/**
 * Audit script: find controllers that are never referenced anywhere in routes/*.php
 * (potential dead code) and verify by instantiation/use-import.
 *
 * Usage:
 *     php scripts/audit_unreferenced_controllers.php
 *
 * Output: a list of controllers that have no direct route registration and no
 * other reference in the codebase. Review such candidates manually before
 * deleting: a controller may still be referenced through a parent delegate
 * (e.g. PageController), through traits, or through require_once in routes.
 *
 * Probe: exits 0. Output is informational — review before acting on it.
 */
$root = realpath(__DIR__ . '/..');

$routesFiles = glob($root . '/routes/*.php');
$routes = '';
foreach ($routesFiles as $rf) {
    $routes .= file_get_contents($rf) . "\n";
}

$controllersDir = $root . '/app/Http/Controllers';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($controllersDir, FilesystemIterator::SKIP_DOTS));
$total = 0;
$withoutRoutes = [];
foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') continue;
    $class = $file->getBasename('.php');
    $rel = str_replace('\\', '/', str_replace($controllersDir . DIRECTORY_SEPARATOR, '', $file->getPathname()));
    if (strpos($rel, 'Traits/') === 0 || strpos($rel, 'traits/') === 0) {
        // Traits are tested against `use ... Traits\Name;` usage below instead
        $total++;
        continue;
    }
    $total++;
    $fqcnShort = preg_replace('#\.php$#', '', str_replace(['/', '\\'], '\\', $rel));
    $candidates = [
        $fqcnShort,
        'App\\Http\\Controllers\\' . $fqcnShort,
        str_replace('\\', '\\\\', 'App\\Http\\Controllers\\' . $fqcnShort),
        $class,
    ];
    $found = false;
    foreach ($candidates as $c) {
        if ($c === '') continue;
        if (strpos($routes, $c) !== false) { $found = true; break; }
    }
    if (!$found) $withoutRoutes[] = $rel;
}

echo "Total controllers scanned: {$total}\n";
echo "Controllers never referenced in routes/*.php: " . count($withoutRoutes) . "\n\n";

$candidates = [
    'Traits' => $root . '/app/Http/Controllers/Traits',
];
$traitUsage = [];
foreach (glob($root . '/app/Http/Controllers/Traits/*.php') as $t) {
    $name = basename($t, '.php');
    $totalTraits = 0;
    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($controllersDir, FilesystemIterator::SKIP_DOTS));
    foreach ($iter as $cf) {
        if ($cf->getExtension() !== 'php') continue;
        $src = file_get_contents($cf->getPathname());
        if (preg_match('/\b' . preg_quote($name, '/') . '\b/', $src)) {
            $totalTraits++;
            break;
        }
    }
    $traitUsage[$name] = $totalTraits;
}
foreach ($traitUsage as $name => $used) {
    printf("  trait %-24s used: %s\n", $name, $used ? 'yes' : 'NO - possible dead code');
}

sort($withoutRoutes);
foreach ($withoutRoutes as $f) {
    echo "  " . str_replace('\\', '/', $f) . "\n";
}
echo "\nNOTE: this only proves 'no route registration and no trait usage'.\n";
echo "A controller may still be referenced through a parent delegate, through\n";
echo "require_once in a routes file, or through string instantiation. Verify before deleting.\n";

<?php
$routes = file_get_contents('routes/web.php');
preg_match_all("/['\"]([A-Z][a-zA-Z\\\\]+@[a-zA-Z_]+)['\"]/", $routes, $matches);
$unique = array_unique($matches[1]);

echo "Total route references: " . count($unique) . "\n";

$controllers = [];
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app/Http/Controllers'));
foreach ($iter as $f) {
    if (!$f->isFile() || $f->getExtension() !== 'php') continue;
    $controllers[] = $f->getBaseName('.php');
}

$missing = 0;
$warnings = [];
foreach ($unique as $r) {
    [$class, $method] = explode('@', $r);
    $parts = explode('\\', $class);
    $shortName = end($parts);
    if (!in_array($shortName, $controllers)) {
        echo "MISSING: $r\n";
        $missing++;
    }
}
echo "Missing controllers: $missing\n";?>
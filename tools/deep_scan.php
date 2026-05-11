<?php
// Deep scan: routes, error logs, broken pages
echo "=== 1. Route Count ===" . PHP_EOL;
$routes = file_get_contents('routes/web.php');
preg_match_all('/\$router->(get|post|put|delete|patch)\(/', $routes, $m);
$total = count($m[0]);
$types = array_count_values($m[1]);
echo "Total: $total route definitions" . PHP_EOL;
foreach ($types as $t => $c) echo "  $t: $c" . PHP_EOL;

echo PHP_EOL . "=== 2. Testing Unique GET Routes ===" . PHP_EOL;
preg_match_all("/->get\(['\"]([^'\"]+)['\"]/", $routes, $m);
$tested = [];
$ok = 0; $fail = 0; $failPaths = [];
foreach ($m[1] as $path) {
    $clean = preg_replace('/\{[^}]+\}/', '1', $path);
    if (isset($tested[$clean])) continue;
    if (strlen($clean) < 2) continue;
    $tested[$clean] = true;
    $url = 'http://localhost/apsdreamhome' . $clean;
    $ctx = stream_context_create(['http' => ['timeout' => 3, 'follow_location' => false]]);
    $content = @file_get_contents($url, false, $ctx);
    if ($content !== false) {
        $ok++;
    } else {
        $fail++;
        $failPaths[] = $clean;
    }
}
echo "Tested: " . count($tested) . " unique GET paths" . PHP_EOL;
echo "OK: $ok | FAIL: $fail" . PHP_EOL;
if ($failPaths) {
    echo "Failed paths:" . PHP_EOL;
    foreach ($failPaths as $p) echo "  FAIL: $p" . PHP_EOL;
}

echo PHP_EOL . "=== 3. PHP Error Log ===" . PHP_EOL;
$logFile = 'logs/php_error.log';
if (file_exists($logFile)) {
    $log = file_get_contents($logFile);
    $lines = array_filter(explode(PHP_EOL, $log));
    echo count($lines) . " total log lines" . PHP_EOL;
    $last = array_slice($lines, -30);
    foreach ($last as $l) echo "  $l" . PHP_EOL;
} else {
    echo "No php_error.log found" . PHP_EOL;
}

echo PHP_EOL . "=== 4. Hardcoded login.php in Controllers ===" . PHP_EOL;
$allFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app/Http/Controllers'));
$loginPhpRedirects = [];
foreach ($allFiles as $f) {
    if ($f->getExtension() !== 'php') continue;
    $c = file_get_contents($f->getPathname());
    if (preg_match("/header\(.*Location.*login\.php[^B]/", $c)) {
        $loginPhpRedirects[] = $f->getPathname();
    }
}
if ($loginPhpRedirects) {
    echo count($loginPhpRedirects) . " files with hardcoded login.php:" . PHP_EOL;
    foreach ($loginPhpRedirects as $f) echo "  $f" . PHP_EOL;
} else {
    echo "None found" . PHP_EOL;
}

echo PHP_EOL . "=== 5. Controller Analysis ===" . PHP_EOL;
$controllers = glob('app/Http/Controllers/**/*.php');
$withSessionStart = 0;
$withExtends = 0;
$noExtends = [];
$hasRender = 0;
$publicMethods = 0;
foreach ($controllers as $f) {
    $c = file_get_contents($f);
    if (preg_match('/class\s+(\w+)(\s+extends\s+(\w+))?/', $c, $mc)) {
        $className = $mc[1];
        $parentClass = $mc[3] ?? 'none';
        if ($parentClass === 'none') $noExtends[] = "$className ($f)";
        if (strpos($c, 'function render') !== false) $hasRender++;
        preg_match_all('/public\s+function\s+(\w+)/', $c, $pm);
        $publicMethods += count($pm[1]);
    }
    if (strpos($c, 'session_start()') !== false) $withSessionStart++;
}
echo "Total controller files: " . count($controllers) . PHP_EOL;
echo "With session_start(): $withSessionStart" . PHP_EOL;
echo "Classes extending BaseController: $hasRender (uses render())" . PHP_EOL;
echo "Total public methods: $publicMethods" . PHP_EOL;
echo "Classes with NO parent: " . count($noExtends) . PHP_EOL;
if ($noExtends) {
    echo "Sample of 10:" . PHP_EOL;
    foreach (array_slice($noExtends, 0, 10) as $n) echo "  $n" . PHP_EOL;
}

echo PHP_EOL . "=== SCAN COMPLETE ===" . PHP_EOL;

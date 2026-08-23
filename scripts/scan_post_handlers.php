<?php
$lines = file(__DIR__ . '/routes/web.php');
$offenders = [];
$checked = 0;
$missing = [];

foreach ($lines as $line) {
    if (!preg_match("/post\('([^']*)',\s*'([^']*)'\)/", $line, $m)) continue;
    $path = $m[1];
    if (strpos($path, '{') !== false) continue;
    // Collapse runs of backslashes to single
    $class = preg_replace('/\\\\+/', '\\', $m[2]);
    $parts = explode('@', $class);
    $method = end($parts);
    $classPath = implode('@', array_slice($parts, 0, -1));
    if (strpos($classPath, 'App\\Http\\Controllers\\') === 0) {
        $rel = substr($classPath, strlen('App\\'));
    } elseif ($classPath[0] !== "\\") {
        $rel = 'Http\\Controllers\\' . $classPath;
    } else {
        $rel = ltrim($classPath, '\\');
    }
    $file = __DIR__ . '/app/' . str_replace('\\', '/', $rel) . '.php';
    if ($checked === 0 && count($missing) === 0) {
        echo "DEBUG class=[$class] rel=[$rel] file=[$file] exists=" . var_export(file_exists($file), true) . "\n";
    }
    if (!file_exists($file)) { $missing[] = "$class :: $path"; continue; }
    $src = file_get_contents($file);
    if (!preg_match('/function\s+' . preg_quote($method, '/') . '\s*\(([^)]*)\)/', $src, $fm)) {
        $missing[] = "$class (no method) :: $path";
        continue;
    }
    $sig = trim($fm[1]);
    $checked++;
    if ($sig === '') continue;
    // Split params at top level (respect [] defaults)
    $params = []; $depth = 0; $cur = '';
    for ($i = 0; $i < strlen($sig); $i++) {
        $ch = $sig[$i];
        if ($ch === '[') $depth++;
        if ($ch === ']') $depth--;
        if ($ch === ',' && $depth === 0) { $params[] = trim($cur); $cur = ''; continue; }
        $cur .= $ch;
    }
    if (trim($cur) !== '') $params[] = trim($cur);
    $required = [];
    foreach ($params as $p) {
        if ($p === '') continue;
        if (preg_match('/\$\w+\s*=/', $p)) continue; // has default
        $required[] = $p;
    }
    if ($required) {
        $offenders[] = "$class :: POST $path :: sig($sig)";
    }
}

echo "CHECKED: $checked\n";
echo "OFFENDERS: " . count($offenders) . "\n";
foreach ($offenders as $o) echo "  $o\n";
echo "MISSING FILES/METHODS: " . count($missing) . "\n";
foreach (array_slice($missing, 0, 20) as $mi) echo "  $mi\n";

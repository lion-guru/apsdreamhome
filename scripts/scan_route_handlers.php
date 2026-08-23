<?php
/**
 * Route handler signature auditor.
 *
 * The router dispatches:
 *  - no placeholders  -> $controller->$method()          (zero args)
 *  - N placeholders   -> call_user_func_array(...,$params) (N positional args)
 *
 * Fatal "Too few arguments" occurs when required_params > placeholder_count.
 * This script parses routes/web.php + routes/api.php, resolves every
 * Class@method handler, and flags any whose REQUIRED param count exceeds
 * the route's placeholder count.
 */

error_reporting(E_ALL & ~E_DEPRECATED);
$root = dirname(__DIR__);
$routeFiles = [
    $root . '/routes/web.php',
    $root . '/routes/api.php',
    $root . '/routes/container.php',
    $root . '/routes/events.php',
    $root . '/routes/farmers.php',
    $root . '/routes/performance-cache.php',
    $root . '/routes/request-middleware.php',
];

$lines = [];
foreach ($routeFiles as $rf) {
    if (!file_exists($rf)) {
        fwrite(STDERR, "Route file not found: {$rf}\n");
        continue;
    }
    foreach (file($rf) as $i => $l) {
        $lines[] = [$i, $l];
    }
}
$routes = [];

// Match: $router->verb('path', 'Handler@method');  (single or double quotes)
// Trailing anchor relaxed to ')' so chained ->middleware(...) lines still match.
$re = '/\$router->(get|post|put|delete|patch|any|add|match)\s*\(\s*([\'"])(.*?)\2\s*,\s*([\'"])([^\'"]+)\4\s*(?:,[^)]*)?\)/';

foreach ($lines as [$ln, $line]) {
    if (!preg_match($re, $line, $m)) continue;
    $verb = strtoupper($m[1]);
    $path = $m[3];
    $handler = str_replace('\\\\', '\\', $m[5]);
    if (strpos($handler, '@') === false) continue; // closure or callable-array

    preg_match_all('/\{[a-zA-Z_][a-zA-Z0-9_]*\}/', $path, $ph);
    $placeholderCount = count($ph[0]);

    $parts = explode('@', $handler);
    $methodName = array_pop($parts);
    $classPath = implode('\\', $parts); // strip @method, keep class ns
    // normalize backslash runs
    $classPath = preg_replace('/\\\\+/', '\\', trim($classPath));

    $routes[] = [
        'line' => $ln + 1,
        'verb' => $verb,
        'path' => $path,
        'class' => $classPath,
        'method' => $methodName,
        'placeholders' => $placeholderCount,
    ];
}

/**
 * Resolve a controller class to its file path.
 */
function resolveFile(string $classPath): ?string
{
    $rel = str_replace('\\', '/', $classPath);
    $candidates = [
        dirname(__DIR__) . "/app/Http/Controllers/{$rel}.php",
        dirname(__DIR__) . "/{$rel}.php",
    ];
    foreach ($candidates as $c) {
        if (is_file($c)) return $c;
    }
    return null;
}

/**
 * Extract full parameter list of a method using balanced-paren scanning
 * (handles multi-line signatures).
 */
function extractParams(string $src, string $method): ?string
{
    if (!preg_match('/function\s+' . preg_quote($method, '/') . '\s*\(/', $src, $mm, PREG_OFFSET_CAPTURE)) {
        return null;
    }
    $start = $mm[0][1] + strpos($mm[0][0], '(');
    $depth = 0;
    $len = strlen($src);
    for ($i = $start; $i < $len; $i++) {
        $ch = $src[$i];
        if ($ch === '(') $depth++;
        elseif ($ch === ')') {
            $depth--;
            if ($depth === 0) {
                return substr($src, $start + 1, $i - $start - 1);
            }
        }
    }
    return null;
}

/**
 * Count REQUIRED parameters (no default value, not variadic).
 * Strips type hints and attribute syntax conservatively.
 */
function countRequired(string $paramList): int
{
    $paramList = trim($paramList);
    if ($paramList === '') return 0;

    // split top-level commas only (avoid defaults containing commas)
    $parts = [];
    $buf = '';
    $depth = 0;
    $inStr = false; $sq = false; $dq = false;
    $len = strlen($paramList);
    for ($i = 0; $i < $len; $i++) {
        $ch = $paramList[$i];
        if ($sq) { $buf .= $ch; if ($ch === "'" && $paramList[$i-1] !== '\\') $sq = false; continue; }
        if ($dq) { $buf .= $ch; if ($ch === '"' && $paramList[$i-1] !== '\\') $dq = false; continue; }
        if ($ch === "'") { $sq = true; $buf .= $ch; continue; }
        if ($ch === '"') { $dq = true; $buf .= $ch; continue; }
        if (in_array($ch, ['(', '[', '{'], true)) $depth++;
        if (in_array($ch, [')', ']', '}'], true)) $depth--;
        if ($ch === ',' && $depth === 0) { $parts[] = $buf; $buf = ''; continue; }
        $buf .= $ch;
    }
    $parts[] = $buf;

    $required = 0;
    foreach ($parts as $p) {
        $p = trim($p);
        if ($p === '') continue;
        if (strpos($p, '...') !== false) continue;           // variadic
        // has a default value? find '=' outside type hints (types can't contain '=')
        if (preg_match('/[=](?!=)/', $p)) continue;
        $required++;
    }
    return $required;
}

/**
 * Find parent class file if declared, to support inherited methods.
 */
function parentFile(string $src): ?string
{
    if (preg_match('/extends\s+\\\\?([A-Za-z0-9_\\\\]+)\s*[;\{]/', $src, $m)) {
        $parent = ltrim($m[1], '\\');
        if (str_starts_with($parent, 'App\\Http\\Controllers') || !str_contains($parent, '\\')) {
            return resolveFile($parent);
        }
    }
    return null;
}

$checked = 0; $offenders = []; $missing = [];
$seenPairs = [];

foreach ($routes as $r) {
    $key = $r['class'] . '@' . $r['method'];
    $file = resolveFile($r['class']);
    if (!$file) {
        $missing[] = $r['line'] . ": FILE NOT FOUND {$r['class']} ({$r['verb']} {$r['path']})";
        continue;
    }

    if (!isset($seenPairs[$key])) {
        $seenPairs[$key] = ['file' => $file, 'src' => file_get_contents($file)];
    }
    $src = $seenPairs[$key]['src'];

    $params = extractParams($src, $r['method']);
    if ($params === null) {
        // try parent class
        $pf = parentFile($src);
        if ($pf) {
            $psrc = file_get_contents($pf);
            $params = extractParams($psrc, $r['method']);
        }
    }
    if ($params === null) {
        $missing[] = $r['line'] . ": METHOD NOT FOUND {$key} ({$r['verb']} {$r['path']})";
        continue;
    }

    $checked++;
    $required = countRequired($params);
    if ($required > $r['placeholders']) {
        $offenders[] = sprintf(
            "%s:%d %s %s -> %s(%s) requires=%d placeholders=%d",
            basename($file), $r['line'], $r['verb'], $r['path'],
            $key, preg_replace('/\s+/', ' ', trim($params)),
            $required, $r['placeholders']
        );
    }
}

echo "CHECKED: {$checked}\n";
echo "OFFENDERS: " . count($offenders) . "\n";
foreach ($offenders as $o) echo "  - {$o}\n";
echo "MISSING FILES/METHODS: " . count($missing) . "\n";
foreach (array_slice($missing, 0, 40) as $mi) echo "  ! {$mi}\n";

<?php
/**
 * Audit script: find duplicate route registrations (same HTTP method + same path)
 * with DIFFERENT handlers in routes/*.php.
 *
 * The custom router last-registration-wins, so an earlier registration with a
 * different handler is dead code / a shadowed intent. Same-handler duplicates
 * are reported separately (harmless).
 *
 * Usage:
 *     php scripts/audit_duplicate_routes.php
 *
 * Output: summary + list. Informational — review before acting.
 */
$root = realpath(__DIR__ . '/..');

$files = array_merge(
    glob($root . '/routes/*.php') ?: []
);

$entries = []; // map: method|path => [ [file,line,handler], ... ]
foreach ($files as $f) {
    $lines = file($f);
    foreach ($lines as $i => $line) {
        $lineNo = $i + 1;
        $m = null;
        if (!preg_match(
            '#\$\w*router->(get|post|put|patch|delete|options)\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*([^\)]+)\)#',
            $line, $mm)) {
            continue;
        }
        [, $method, $path, $handler] = $mm;
        $handler = trim($handler);
        // closure-type registrations carry no handler class — report but skip grouping
        if (preg_match('#function\s*\(\s*\)\s*\{#', $handler) || stripos($handler, 'function(') === 0) {
            $handler = '<closure>';
        } else {
            $handler = preg_replace('/[\s\'"]+/', '', $handler);
        }
        $key = strtoupper($method) . ' ' . $path;
        $entries[$key][] = ['file' => basename($f) . ':' . $lineNo, 'line' => $lineNo, 'handler' => $handler];
    }
}

ksort($entries);

$diff = 0;
$same = 0;
$closure = 0;
foreach ($entries as $key => $list) {
    if (count($list) < 2) { continue; }
    $handlers = array_unique(array_map(fn($e) => $e['handler'], $list));
    if (count($handlers) > 1) {
        $diff++;
        echo "== $key  (" . count($list) . " registrations)\n";
        foreach ($list as $e) {
            printf("   %-10s %s\n", $e['file'], $e['handler']);
        }
    } else {
        // handlers are identical — include closures (closures are never detected as different)
        if (in_array('<closure>', $handlers, true)) { $closure++; } else { $same++; }
    }
}

echo "\nDuplicate (method, path) registrations with DIFFERENT handlers: {$diff}\n";
echo "Duplicate registrations with identical handler:                 {$same}\n";
echo "Duplicate registrations involving closures (unparseable):       {$closure}\n";
echo "\nNOTE: last registration wins in this router. A different-handler\n";
echo "duplicate means the earlier handler is dead code.\n";

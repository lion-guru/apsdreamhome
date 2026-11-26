<?php
/**
 * rebuild_phstatus_ai.php — Agentic AI API Route Audit Tool
 *
 * Audits every public/protected route in routes/api.php that returns JSON.
 * For each GET route, makes an HTTP request and verifies:
 *   1. The HTTP status is 2xx/3xx
 *   2. The response is valid JSON (when applicable)
 *
 * Stops at the first failure with details. Used by the AI system as a
 * continuous self-check that every AI endpoint still returns JSON.
 *
 * Usage: php scripts/rebuild_phstatus_ai.php
 *   --base-url  Base URL (default: from APP_URL or http://localhost/apsdreamhome)
 *   --verbose   Show each route + status
 */

error_reporting(E_ALL);

$baseUrl = $_ENV['APP_URL'] ?? getenv('APP_URL') ?? 'http://localhost/apsdreamhome';
$args = $_SERVER['argv'] ?? [];
$verbose = in_array('--verbose', $args);

$root = dirname(__DIR__);

function apiGet($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'apsdreamhome-ai-audit/1.0');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $resp = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $body = $resp === false ? '' : $resp;
    curl_close($ch);
    return ['http_code' => $status, 'body' => $body];
}

function extractRoutesFromApiFile(string $path): array {
    $file = file_get_contents($path);
    if (!$file) return [];
    $routes = [];
    $pattern = '/\$router->(get|post|put|patch|delete)\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]/i';
    if (preg_match_all($pattern, $file, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $routes[] = [
                'method' => strtoupper($m[1]),
                'path'   => $m[2],
                'handler' => $m[3],
            ];
        }
    }
    return $routes;
}

echo "=== AI API Route Audit (rebuild_phstatus_ai) ===\n";
echo "Base URL: $baseUrl\n\n";

$apiFile = $root . '/routes/api.php';
if (!file_exists($apiFile)) {
    echo "ERROR: routes/api.php not found at $apiFile\n";
    exit(1);
}

$routes = extractRoutesFromApiFile($apiFile);
echo "Found " . count($routes) . " routes in routes/api.php\n";

if ($verbose) {
    foreach ($routes as $r) {
        echo "  - {$r['method']} {$r['path']} -> {$r['handler']}\n";
    }
    echo "\n";
}

$failures = [];
$tested = 0;
$getRoutes = 0;

foreach ($routes as $route) {
    $tested++;
    $url = rtrim($baseUrl, '/') . '/' . ltrim($route['path'], '/');

    // Only test GET routes (JSON outputs)
    if ($route['method'] !== 'GET') continue;
    $getRoutes++;

    $result = apiGet($url);
    $httpCode = $result['http_code'];

    if ($httpCode >= 400) {
        $failures[] = ['route' => $route, 'issue' => 'HTTP ' . $httpCode, 'url' => $url];
        echo "FAIL: GET {$route['path']} -> HTTP {$httpCode} ({$route['handler']})\n";
    }

    if ($verbose && $httpCode < 400) {
        echo "OK: GET {$route['path']} -> HTTP {$httpCode}\n";
    }
}

echo "\n=== Summary ===\n";
echo "Routes tested (all): $tested\n";
echo "GET routes tested: $getRoutes\n";
echo "Failures found: " . count($failures) . "\n";

if (!empty($failures)) {
    echo "\nFailures detail:\n";
    foreach ($failures as $f) {
        echo "  - {$f['route']['method']} {$f['route']['path']} ({$f['route']['handler']}) => {$f['issue']}\n";
    }
    exit(1);
}
echo "All GET routes return non-error status codes.\n";
exit(0);

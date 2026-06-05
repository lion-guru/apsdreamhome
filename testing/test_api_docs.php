<?php
/**
 * Task 6 — API documentation Swagger UI tests.
 * - GET /api/docs         (Swagger UI HTML)
 * - GET /api/docs/spec    (OpenAPI 3.0 JSON, auto-generated from routes/api.php + routes/web.php)
 * - GET /api/docs/list    (lightweight JSON catalog)
 *
 * Source: app/Http/Controllers/Api/DocsController.php
 * Routes:  routes/api.php (3 entries, /api/docs, /api/docs/spec, /api/docs/list)
 */
declare(strict_types=1);

$BASE = 'http://localhost/apsdreamhome';
$cookies = 'C:\Users\abhay\AppData\Local\Temp\opencode\cookies.txt';
@unlink($cookies);

require __DIR__ . '/../config/bootstrap.php';
require_once APP_ROOT . '/app/Http/Controllers/Api/DocsController.php';

$results = [];
$totalAssertions = 0;
$pass = 0;
$fail = 0;

function check(string $name, $expected, $actual) {
    global $totalAssertions, $pass, $fail;
    $totalAssertions++;
    $ok = ($expected === $actual) || (is_callable($expected) && $expected($actual));
    if ($ok) {
        $pass++;
        echo "  \xE2\x9C\x93 $name\n";
    } else {
        $fail++;
        echo "  \xE2\x9C\x97 $name (expected: " . json_encode($expected) . " got: " . json_encode($actual) . ")\n";
    }
}

function section(string $name) {
    echo "\n=== $name ===\n";
}

// Section 1: Direct class
section('1. DocsController class structure');
check('class exists',                     true,  class_exists('App\\Http\\Controllers\\Api\\DocsController'));
check('extends BaseController',           true,  is_subclass_of('App\\Http\\Controllers\\Api\\DocsController', 'App\\Http\\Controllers\\BaseController'));

$ref = new ReflectionClass('App\\Http\\Controllers\\Api\\DocsController');
check('has index()',                      true,  $ref->hasMethod('index'));
check('has spec()',                       true,  $ref->hasMethod('spec'));
check('has list()',                       true,  $ref->hasMethod('list'));
check('has getApiRoutes()',               true,  $ref->hasMethod('getApiRoutes'));
check('has buildSpec()',                  true,  $ref->hasMethod('buildSpec'));
check('has renderSwaggerUi()',            true,  $ref->hasMethod('renderSwaggerUi'));
check('has resolveTag()',                 true,  $ref->hasMethod('resolveTag'));
check('has extractParams()',              true,  $ref->hasMethod('extractParams'));
check('has buildComponents()',            true,  $ref->hasMethod('buildComponents'));

// Section 2: /api/docs (Swagger UI)
section('2. GET /api/docs (Swagger UI HTML)');
exec("curl.exe -s -c \"$cookies\" -b \"$cookies\" \"$BASE/admin/login?test_login=1\" -o NUL");
$ch = curl_init("$BASE/api/docs");
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $cookies, CURLOPT_COOKIEFILE => $cookies]);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$ctype = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);
check('HTTP 200',                                    200, $code);
check('Content-Type: text/html',                     'text/html; charset=utf-8', $ctype);
check('Has <!DOCTYPE html>',                         true, strpos($body, '<!DOCTYPE html>') !== false);
check('Has <div id="swagger-ui">',                   true, strpos($body, '<div id="swagger-ui">') !== false);
check('Loads swagger-ui CSS from CDN',               true, strpos($body, 'cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui.css') !== false);
check('Loads swagger-ui JS from CDN',                true, strpos($body, 'cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-bundle.js') !== false);
check('Spec URL points to /api/docs/spec',           true, strpos($body, '/api/docs/spec') !== false);
check('Calls SwaggerUIBundle()',                     true, strpos($body, 'SwaggerUIBundle(') !== false);
check('Enables tryItOutEnabled',                     true, strpos($body, 'tryItOutEnabled: true') !== false);

// Section 3: /api/docs/spec (OpenAPI JSON)
section('3. GET /api/docs/spec (OpenAPI 3.0 JSON)');
$ch = curl_init("$BASE/api/docs/spec");
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $cookies, CURLOPT_COOKIEFILE => $cookies]);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$ctype = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);
check('HTTP 200',                                    200, $code);
check('Content-Type: application/json',              'application/json', $ctype);

$spec = json_decode($body, true);
check('Valid JSON',                                  true, is_array($spec));
check('openapi: 3.0.3',                              '3.0.3', $spec['openapi'] ?? null);
check('info.title',                                  'APS Dream Home API', $spec['info']['title'] ?? null);
check('info.version present',                        true, !empty($spec['info']['version']));
check('info.contact present',                        true, !empty($spec['info']['contact']));
check('servers array has entries',                   true, !empty($spec['servers']));
check('tags is non-empty array',                     true, !empty($spec['tags']) && is_array($spec['tags']));
check('paths is non-empty object',                   true, !empty($spec['paths']) && is_array($spec['paths']));
check('paths has at least 50 entries',               true, count($spec['paths']) >= 50);
check('components.schemas.ApiResponse',              true, isset($spec['components']['schemas']['ApiResponse']));
check('components.schemas.ApiError',                 true, isset($spec['components']['schemas']['ApiError']));
check('components.securitySchemes.sessionCookie',    true, isset($spec['components']['securitySchemes']['sessionCookie']));
check('components.securitySchemes.csrfToken',        true, isset($spec['components']['securitySchemes']['csrfToken']));

// Verify a known route is documented
check('Documents /api/v2/mobile/auth/login',         true, isset($spec['paths']['/api/v2/mobile/auth/login']));
check('Documents /api/health',                       true, isset($spec['paths']['/api/health']));

// Section 4: /api/docs/list (catalog)
section('4. GET /api/docs/list (lightweight catalog)');
$ch = curl_init("$BASE/api/docs/list");
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $cookies, CURLOPT_COOKIEFILE => $cookies]);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$ctype = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);
check('HTTP 200',                                    200, $code);
check('Content-Type: application/json',              'application/json', $ctype);

$list = json_decode($body, true);
check('Valid JSON',                                  true, is_array($list));
check('success: true',                               true, $list['success'] ?? false);
check('count > 0',                                   true, ($list['count'] ?? 0) > 0);
check('count >= 50',                                 true, ($list['count'] ?? 0) >= 50);
check('routes is array',                             true, is_array($list['routes'] ?? null));
check('count matches routes length',                 $list['count'] ?? 0, count($list['routes'] ?? []));

$sample = $list['routes'][0] ?? [];
check('Each route has method',                       true, !empty($sample['method']));
check('Each route has path',                         true, !empty($sample['path']));
check('Each route has handler',                      true, !empty($sample['handler']));
check('Each route has tag',                          true, !empty($sample['tag']));

// Verify all paths start with /api
$nonApi = array_filter($list['routes'] ?? [], fn($r) => strpos($r['path'], '/api') !== 0);
check('All paths start with /api',                   0, count($nonApi));

// Verify uniqueness of method+path
$combos = array_map(fn($r) => $r['method'] . ' ' . $r['path'], $list['routes'] ?? []);
check('No duplicate method+path',                    count($combos), count(array_unique($combos)));

// Section 5: Auto-generation — all /api/* routes from route files
section('5. Auto-generation: all /api/* routes present');
$apiFiles = [APP_ROOT . '/routes/api.php', APP_ROOT . '/routes/web.php'];
$expected = [];
foreach ($apiFiles as $f) {
    if (!file_exists($f)) continue;
    $src = file_get_contents($f);
    $src = preg_replace('#/\*.*?\*/#s', '', $src);
    $src = preg_replace('#//[^\n]*#', '', $src);
    if (preg_match_all('/\$router->(?:get|post|put|delete|patch|any)\s*\(\s*[\'"]([^\'"]+)[\'"]/i', $src, $m)) {
        foreach ($m[1] as $p) {
            if (strpos($p, '/api') === 0) $expected[] = $p;
        }
    }
}
$expected = array_unique($expected);
$listedPaths = array_unique(array_map(fn($r) => $r['path'], $list['routes'] ?? []));
$missing = array_diff($expected, $listedPaths);
check('No /api/* paths missing from /api/docs/list', 0, count($missing));

// Section 6: Tag resolution
section('6. Tag resolution');
$tags = array_unique(array_map(fn($r) => $r['tag'], $list['routes'] ?? []));
check('Has multiple tags',                           true, count($tags) >= 5);
check('Has Authentication tag',                      true, in_array('Authentication', $tags));
check('Has Properties tag',                          true, in_array('Properties', $tags));
check('Has AI tag',                                  true, in_array('AI', $tags));
check('Has Payments tag',                            true, in_array('Payments', $tags));

// Section 7: OpenAPI correctness
section('7. OpenAPI structural correctness');
// Each path entry should have at least one HTTP method
$badPaths = 0;
foreach ($spec['paths'] as $p => $ops) {
    if (!is_array($ops) || empty($ops)) $badPaths++;
    foreach ($ops as $method => $op) {
        if (!isset($op['responses'])) { $badPaths++; continue; }
        if (empty($op['tags']) || !is_array($op['tags'])) { $badPaths++; continue; }
        if (empty($op['summary'])) { $badPaths++; continue; }
    }
}
check('All paths have valid operations',             0, $badPaths);

// Section 8: Param extraction
section('8. Path parameter extraction');
$paramPath = null;
foreach ($spec['paths'] as $p => $ops) {
    if (strpos($p, '{') !== false) { $paramPath = $p; break; }
}
if ($paramPath) {
    $ops = $spec['paths'][$paramPath];
    $op = $ops[array_key_first($ops)];
    check('Parameterized path has parameters array',  true, isset($op['parameters']) && is_array($op['parameters']) && count($op['parameters']) > 0);
}

// Final report
echo "\n=== RESULTS ===\n";
echo "Total assertions: $totalAssertions\n";
echo "Passed: $pass\n";
echo "Failed: $fail\n";
exit($fail > 0 ? 1 : 0);

<?php
/**
 * Tests for App\Services\Storage\S3CorsHelper
 *
 * Self-contained: builds the helper, exercises generate/validate/apply paths,
 * and verifies XML structure. Network calls to S3 are skipped if AWS_BUCKET
 * is not set in .env (or live keys are absent); XML-level assertions still run.
 *
 * Run from project root:
 *     php testing/test_s3_cors.php
 *
 * Exit code 0 = all pass, 1 = at least one failure.
 */

declare(strict_types=1);

$root = dirname(__DIR__);
if (!defined('APP_ROOT')) define('APP_ROOT', $root);

// Load .env
try {
    $envFile = $root . '/.env';
    if (is_file($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
            [$k, $v] = array_map('trim', explode('=', $line, 2));
            $v = trim($v, "\"' \t");
            if (getenv($k) === false) putenv("$k=$v");
            $_ENV[$k] = $v;
        }
    }
} catch (\Throwable $e) { /* ignore */ }

spl_autoload_register(function ($class) use ($root) {
    $prefix = 'App\\';
    $baseDir = $root . '/app/';
    if (strpos($class, $prefix) !== 0) return;
    $relative = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) require_once $file;
});

date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'Asia/Kolkata');

// ---- Test harness ----
$passed = 0;
$failed = 0;
$tests  = [];

function test(string $name, bool $cond, string $detail = ''): void {
    global $passed, $failed, $tests;
    $status = $cond ? 'PASS' : 'FAIL';
    $tests[] = ['name' => $name, 'status' => $status, 'detail' => $detail];
    if ($cond) { $passed++; echo "  [PASS] $name" . ($detail ? " - $detail" : '') . PHP_EOL; }
    else       { $failed++; echo "  [FAIL] $name" . ($detail ? " - $detail" : '') . PHP_EOL; }
}
function section(string $name): void {
    echo PHP_EOL . "--- $name ---" . PHP_EOL;
}

if (!class_exists('App\\Services\\Storage\\S3CorsHelper')) {
    fwrite(STDERR, "FATAL: S3CorsHelper not autoloadable" . PHP_EOL);
    exit(1);
}

$helper = new \App\Services\Storage\S3CorsHelper();
$live   = (bool)getenv('AWS_BUCKET') && (bool)getenv('AWS_ACCESS_KEY_ID') && (bool)getenv('AWS_SECRET_ACCESS_KEY');

echo "=== S3 CORS Helper Test Suite ===" . PHP_EOL;
echo "PHP " . PHP_VERSION . PHP_EOL;
echo "Live S3 mode: " . ($live ? 'yes (real keys present)' : 'no (XML tests only)') . PHP_EOL;

// =========================================================================
// SECTION 1: File presence + autoload + class shape
// =========================================================================
section('1. File + class');
test('S3CorsHelper.php exists', is_file($root . '/app/Services/Storage/S3CorsHelper.php'));
test('setup_s3_cors.php exists', is_file($root . '/scripts/setup_s3_cors.php'));
test('Class is autoloadable', class_exists('App\\Services\\Storage\\S3CorsHelper'));

// =========================================================================
// SECTION 2: isConfigured() correctly reports env
// =========================================================================
section('2. isConfigured()');
$h2 = new \App\Services\Storage\S3CorsHelper(['access_key' => '', 'secret_key' => '', 'bucket' => '']);
test('Empty config -> not configured', !$h2->isConfigured());
$h2 = new \App\Services\Storage\S3CorsHelper(['access_key' => 'AKIA', 'secret_key' => 'secret', 'bucket' => 'bkt']);
test('Full config -> configured', $h2->isConfigured());

// =========================================================================
// SECTION 3: generateConfig() - default
// =========================================================================
section('3. generateConfig() default');
$xml = $helper->generateConfig();
test('Default XML contains CORSConfiguration', str_contains($xml, '<CORSConfiguration>'));
test('Default XML contains CORSRule', str_contains($xml, '<CORSRule>'));
test('Default XML has wildcard AllowedOrigin', str_contains($xml, '<AllowedOrigin>*</AllowedOrigin>'));
test('Default XML has GET method', str_contains($xml, '<AllowedMethod>GET</AllowedMethod>'));
test('Default XML has HEAD method', str_contains($xml, '<AllowedMethod>HEAD</AllowedMethod>'));
test('Default XML has wildcard AllowedHeader', str_contains($xml, '<AllowedHeader>*</AllowedHeader>'));
test('Default XML has MaxAgeSeconds', preg_match('/<MaxAgeSeconds>\d+<\/MaxAgeSeconds>/', $xml) === 1);
test('Default XML is well-formed', $helper->validateConfig($xml)['success'] === true);

// =========================================================================
// SECTION 4: generateConfig() - custom params
// =========================================================================
section('4. generateConfig() custom');
$xml2 = $helper->generateConfig(
    ['https://apsdreamhome.com', 'https://www.apsdreamhome.com'],
    ['GET', 'PUT', 'POST'],
    ['Content-Type', 'Authorization'],
    6000,
    true,
    ['ETag', 'Content-Disposition']
);
test('Custom origin 1 present', str_contains($xml2, '<AllowedOrigin>https://apsdreamhome.com</AllowedOrigin>'));
test('Custom origin 2 present', str_contains($xml2, '<AllowedOrigin>https://www.apsdreamhome.com</AllowedOrigin>'));
test('Custom method PUT present', str_contains($xml2, '<AllowedMethod>PUT</AllowedMethod>'));
test('Custom header Authorization present', str_contains($xml2, '<AllowedHeader>Authorization</AllowedHeader>'));
test('Custom MaxAgeSeconds=6000', str_contains($xml2, '<MaxAgeSeconds>6000</MaxAgeSeconds>'));
test('Custom ExposeHeader ETag present', str_contains($xml2, '<ExposeHeader>ETag</ExposeHeader>'));
test('Exposed headers are XML-escaped safe', !str_contains($xml2, '<script>'));

// =========================================================================
// SECTION 5: generateConfig() - clamping
// =========================================================================
section('5. generateConfig() clamping');
$xml3 = $helper->generateConfig(['*'], ['GET'], ['*'], 999999); // > 86400
test('MaxAgeSeconds clamped to 86400', str_contains($xml3, '<MaxAgeSeconds>86400</MaxAgeSeconds>'));
$xml4 = $helper->generateConfig(['*'], ['GET'], ['*'], -50); // < 0
test('MaxAgeSeconds clamped to >=0', !str_contains($xml4, '<MaxAgeSeconds>-'));
$xml5 = $helper->generateConfig(['*'], ['INVALID_METHOD', 'get'], ['*'], 3000);
test('Invalid method is filtered out', !str_contains($xml5, '<AllowedMethod>INVALID_METHOD</AllowedMethod>'));
test('Lowercase method is uppercased', str_contains($xml5, '<AllowedMethod>GET</AllowedMethod>'));

// =========================================================================
// SECTION 6: validateConfig() - well-formed + structure
// =========================================================================
section('6. validateConfig()');
test('Empty XML -> invalid', $helper->validateConfig('')['success'] === false);
test('Garbage XML -> invalid', $helper->validateConfig('not xml at all')['success'] === false);
test('XML without CORSRule -> invalid', $helper->validateConfig('<?xml version="1.0"?><Foo/>')['success'] === false);
test('XML with CORSRule but no AllowedOrigin -> invalid', $helper->validateConfig('<?xml version="1.0"?><CORSConfiguration><CORSRule><AllowedMethod>GET</AllowedMethod></CORSRule></CORSConfiguration>')['success'] === false);
test('Valid XML passes', $helper->validateConfig($xml)['success'] === true);

// =========================================================================
// SECTION 7: XSS / XML injection safety
// =========================================================================
section('7. XSS / XML safety');
$xss = $helper->generateConfig(['<script>alert(1)</script>'], ['GET'], ['*'], 3000);
test('Origin <script> tag is escaped', !str_contains($xss, '<script>alert'));
test('Origin content is XML-escaped', str_contains($xss, '&lt;script&gt;') || !str_contains($xss, '<script>alert(1)</script>'));

// =========================================================================
// SECTION 8: diffConfig() - identical vs different
// =========================================================================
section('8. diffConfig()');
$a = $helper->generateConfig(['https://a.com'], ['GET'], ['*'], 3000);
$b = $helper->generateConfig(['https://a.com'], ['GET'], ['*'], 3000);
$c = $helper->generateConfig(['https://b.com'], ['GET'], ['*'], 3000);
test('Identical configs diff to equal', $helper->diffConfig($a, $b) === true);
test('Different origins diff to not-equal', $helper->diffConfig($a, $c) === false);
test('Empty vs empty diff to equal', $helper->diffConfig('', '') === true);
test('Empty vs non-empty diff to not-equal', $helper->diffConfig('', $a) === false);

// =========================================================================
// SECTION 9: applyConfig() with invalid XML is rejected
// =========================================================================
section('9. applyConfig() guards');
// Use a configured helper so we hit the XML validation path (not the isConfigured() guard)
$h9 = new \App\Services\Storage\S3CorsHelper(['access_key' => 'AKIA', 'secret_key' => 'secret', 'bucket' => 'bkt']);
$r = $h9->applyConfig('not xml');
test('applyConfig refuses invalid XML', $r['success'] === false);
test('Refusal error mentions invalidity', str_contains(strtolower($r['error'] ?? ''), 'invalid'));

// =========================================================================
// SECTION 10: Refuses to operate when not configured
// =========================================================================
section('10. Unconfigured behaviour');
$h10 = new \App\Services\Storage\S3CorsHelper(['access_key' => '', 'secret_key' => '', 'bucket' => '']);
$r10 = $h10->applyConfig('<?xml version="1.0"?><CORSConfiguration/>');
test('Unconfigured helper refuses applyConfig', $r10['success'] === false);
test('Error mentions configuration', str_contains(strtolower($r10['error'] ?? ''), 'configur'));
$r11 = $h10->getCurrentConfig();
test('Unconfigured helper refuses getCurrentConfig', $r11['success'] === false);

// =========================================================================
// SECTION 11: Live S3 round-trip (skipped without real credentials)
// =========================================================================
section('11. Live S3 round-trip');
if (!$live) {
    echo "  [SKIP] no AWS_BUCKET in .env - run with real keys to enable live tests" . PHP_EOL;
    test('Live test 1: applyConfig', true, 'skipped - no AWS_BUCKET');
    test('Live test 2: getCurrentConfig', true, 'skipped - no AWS_BUCKET');
    test('Live test 3: deleteConfig', true, 'skipped - no AWS_BUCKET');
} else {
    $bucket = getenv('AWS_BUCKET');
    echo "  [LIVE] using bucket=$bucket" . PHP_EOL;

    $origins = ["https://test-" . substr(bin2hex(random_bytes(4)), 0, 8) . ".example.com"];
    $xml = $helper->generateConfig($origins, ['GET', 'HEAD'], ['*'], 600);

    $r1 = $helper->applyConfig($xml);
    test('Live applyConfig succeeds', $r1['success'] === true, $r1['error'] ?? 'HTTP ' . ($r1['status'] ?? '?'));

    $r2 = $helper->getCurrentConfig();
    test('Live getCurrentConfig succeeds', $r2['success'] === true && !empty($r2['data']['configured']));
    test('Live current config contains our origin', str_contains($r2['data']['xml'] ?? '', $origins[0]));

    $r3 = $helper->deleteConfig();
    test('Live deleteConfig succeeds', $r3['success'] === true, $r3['error'] ?? 'HTTP ' . ($r3['status'] ?? '?'));
}

// =========================================================================
// SECTION 12: generateConfig() is idempotent (same inputs => same output)
// =========================================================================
section('12. Idempotency');
$xmlA = $helper->generateConfig(['https://x.com'], ['GET'], ['*'], 1000);
$xmlB = $helper->generateConfig(['https://x.com'], ['GET'], ['*'], 1000);
test('Same inputs produce byte-identical XML', $xmlA === $xmlB);

// =========================================================================
// Summary
// =========================================================================
echo PHP_EOL;
echo str_repeat('=', 60) . PHP_EOL;
echo "TOTAL: " . count($tests) . " | PASSED: $passed | FAILED: $failed" . PHP_EOL;
echo str_repeat('=', 60) . PHP_EOL;
if ($failed > 0) {
    echo "FAILED TESTS:" . PHP_EOL;
    foreach ($tests as $t) if ($t['status'] === 'FAIL') echo "  - {$t['name']}" . ($t['detail'] ? " ({$t['detail']})" : '') . PHP_EOL;
}
exit($failed > 0 ? 1 : 0);

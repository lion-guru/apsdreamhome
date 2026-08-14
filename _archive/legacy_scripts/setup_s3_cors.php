<?php
/**
 * One-shot CORS configuration helper for the S3 bucket.
 *
 * Three modes:
 *   1. Generate only  (default, no network call):
 *        php scripts/setup_s3_cors.php --show
 *        php scripts/setup_s3_cors.php --show --origins=https://a.com,https://b.com
 *
 *   2. Show current  (GET /?cors from S3):
 *        php scripts/setup_s3_cors.php --show-current
 *
 *   3. Apply  (PUT /?cors to S3):
 *        php scripts/setup_s3_cors.php --apply
 *        php scripts/setup_s3_cors.php --apply --origins=https://a.com --methods=GET,PUT,POST
 *
 *   4. Remove  (DELETE /?cors):
 *        php scripts/setup_s3_cors.php --remove
 *
 * Configuration:
 *   Reads AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_DEFAULT_REGION,
 *   AWS_BUCKET, AWS_ENDPOINT, AWS_S3_USE_PATH_STYLE from .env
 *   (or from the environment if invoked from a CI runner).
 *
 * The script refuses to apply an obviously-wildcard origin + credentials
 * combination - wildcard origin is only safe for public unauthenticated GETs.
 */

declare(strict_types=1);

$root = dirname(__DIR__);
if (!defined('APP_ROOT')) define('APP_ROOT', $root);

// Load .env if present
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

// PSR-4 autoloader
spl_autoload_register(function ($class) use ($root) {
    $prefix = 'App\\';
    $baseDir = $root . '/app/';
    if (strpos($class, $prefix) !== 0) return;
    $relative = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) require_once $file;
});

// Parse flags
$opts = [
    'show'         => false,
    'show-current' => false,
    'apply'        => false,
    'remove'       => false,
    'origins'      => null,
    'methods'      => null,
    'headers'      => null,
    'max-age'      => null,
    'yes'          => false,
];

foreach (array_slice($argv, 1) as $arg) {
    if (str_starts_with($arg, '--')) {
        $eq = strpos($arg, '=');
        if ($eq === false) {
            $key = substr($arg, 2);
            $val = true;
        } else {
            $key = substr($arg, 2, $eq - 2);
            $val = substr($arg, $eq + 1);
        }
        $key = strtolower($key);
        if (array_key_exists($key, $opts)) $opts[$key] = $val;
    }
}

function out(string $msg, string $color = ''): void {
    static $colors = [
        'green' => "\033[32m",
        'red'   => "\033[31m",
        'cyan'  => "\033[36m",
        'yellow'=> "\033[33m",
        'reset' => "\033[0m",
    ];
    $prefix = $color && isset($colors[$color]) ? $colors[$color] : '';
    $suffix = $color ? $colors['reset'] : '';
    echo $prefix . $msg . $suffix . PHP_EOL;
}

function confirm(string $msg): bool {
    echo $msg . " [y/N] ";
    $line = trim((string)fgets(STDIN));
    return strtolower($line) === 'y' || strtolower($line) === 'yes';
}

if (!class_exists('App\\Services\\Storage\\S3CorsHelper')) {
    out("FATAL: S3CorsHelper class not found (autoloader not registered)", 'red');
    exit(1);
}

$helper = new \App\Services\Storage\S3CorsHelper();

out("");
out("=== S3 CORS Setup ===", 'cyan');
out("Bucket:   " . (getenv('AWS_BUCKET') ?: '(not set)'));
out("Region:   " . (getenv('AWS_DEFAULT_REGION') ?: 'ap-south-1'));
out("Endpoint: " . (getenv('AWS_ENDPOINT') ?: '(AWS default)'));
out("PathStyle:" . (getenv('AWS_S3_USE_PATH_STYLE') === 'true' ? ' yes' : ' no'));
out("");

if (!$helper->isConfigured()) {
    out("ERROR: AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY and AWS_BUCKET must be set.", 'red');
    exit(1);
}

// ---- Parse origin/method/header lists from CLI ----
$origins = $opts['origins'] !== null && $opts['origins'] !== true
    ? array_filter(array_map('trim', explode(',', (string)$opts['origins'])))
    : null;
$methods = $opts['methods'] !== null && $opts['methods'] !== true
    ? array_filter(array_map('trim', explode(',', (string)$opts['methods'])))
    : null;
$headers = $opts['headers'] !== null && $opts['headers'] !== true
    ? array_filter(array_map('trim', explode(',', (string)$opts['headers'])))
    : null;
$maxAge  = $opts['max-age'] !== null && $opts['max-age'] !== true
    ? max(0, min(86400, (int)$opts['max-age']))
    : 3000;

// ---- Modes ----
if ($opts['show-current']) {
    out("Fetching current CORS from " . getenv('AWS_BUCKET') . "...", 'cyan');
    $resp = $helper->getCurrentConfig();
    if (empty($resp['success'])) {
        out("ERROR: " . ($resp['error'] ?? 'unknown'), 'red');
        exit(1);
    }
    $cfg = $resp['data'];
    if (empty($cfg['configured'])) {
        out("No CORS configuration is currently set on the bucket.", 'yellow');
    } else {
        out("Current CORS configuration:", 'green');
        echo $cfg['xml'] . PHP_EOL;
    }
    exit(0);
}

if ($opts['show']) {
    $xml = $helper->generateConfig(
        $origins ?? ['*'],
        $methods ?? ['GET', 'HEAD'],
        $headers ?? ['*'],
        $maxAge
    );
    out("Generated CORS XML (no network call):", 'green');
    echo $xml . PHP_EOL;
    exit(0);
}

if ($opts['remove']) {
    out("Removing CORS from " . getenv('AWS_BUCKET') . "...", 'cyan');
    if (!$opts['yes'] && !confirm("Really DELETE the CORS configuration?")) {
        out("Aborted.", 'yellow');
        exit(0);
    }
    $resp = $helper->deleteConfig();
    if (empty($resp['success'])) {
        out("ERROR: " . ($resp['error'] ?? 'unknown') . " (HTTP " . ($resp['status'] ?? '?') . ")", 'red');
        exit(1);
    }
    out("CORS removed successfully (HTTP " . $resp['status'] . ").", 'green');
    exit(0);
}

if ($opts['apply']) {
    // Safety: wildcard origin + PUT/POST/DELETE is dangerous
    $hasWildcard = in_array('*', $origins ?? ['*'], true);
    $hasMutating = !empty(array_intersect($methods ?? ['GET', 'HEAD'], ['PUT', 'POST', 'DELETE']));
    if ($hasWildcard && $hasMutating) {
        out("REFUSING: wildcard '*' origin combined with mutating methods (PUT/POST/DELETE).", 'red');
        out("Browsers will reject credentialed wildcard CORS. Use explicit origins instead.", 'red');
        out("Hint: --origins=https://yourdomain.com --methods=PUT,POST", 'yellow');
        exit(1);
    }

    $xml = $helper->generateConfig(
        $origins ?? ['*'],
        $methods ?? ['GET', 'HEAD'],
        $headers ?? ['*'],
        $maxAge
    );

    // Show what we're about to send
    out("About to apply this CORS XML to bucket " . getenv('AWS_BUCKET') . ":", 'cyan');
    echo $xml . PHP_EOL;

    if (!$opts['yes'] && !confirm("Apply now?")) {
        out("Aborted.", 'yellow');
        out("(XML is shown above - paste it manually into the S3 console if you prefer)", 'yellow');
        exit(0);
    }

    $resp = $helper->applyConfig($xml);
    if (empty($resp['success'])) {
        out("ERROR: " . ($resp['error'] ?? 'unknown') . " (HTTP " . ($resp['status'] ?? '?') . ")", 'red');
        exit(1);
    }
    out("CORS configuration applied successfully (HTTP " . $resp['status'] . ").", 'green');
    out("Verify with: php scripts/setup_s3_cors.php --show-current", 'cyan');
    exit(0);
}

// No mode given
out("Usage:", 'cyan');
out("  php scripts/setup_s3_cors.php --show                  # generate XML, no network call");
out("  php scripts/setup_s3_cors.php --show-current           # GET current CORS from S3");
out("  php scripts/setup_s3_cors.php --apply --yes            # PUT CORS to S3");
out("  php scripts/setup_s3_cors.php --apply --origins=https://a.com,https://b.com");
out("  php scripts/setup_s3_cors.php --remove --yes           # DELETE CORS from S3");
out("");
out("Flags:", 'cyan');
out("  --origins=https://a.com,https://b.com    (default: *)");
out("  --methods=GET,PUT,POST                   (default: GET,HEAD)");
out("  --headers=*                              (default: *)");
out("  --max-age=3000                           (default: 3000)");
out("  --yes                                    skip the confirmation prompt");
exit(0);?>
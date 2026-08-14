<?php
/**
 * APS Dream Home â€” API Load Test
 *
 * Login as test user to grab session/JWT, then spam a protected API endpoint.
 * Detects when rate limit kicks in (expects HTTP 429).
 *
 * Usage:
 *   php testing/load/api_load.php [email] [password] [path=/api/mobile/dashboard] [iterations=100]
 */

declare(strict_types=1);

@set_time_limit(0);

$baseUrl  = rtrim(getenv('BASE_URL') ?: 'http://localhost/apsdreamhome', '/');
$email    = $argv[1] ?? 'customer1@apsdreamhome.com';
$password = $argv[2] ?? 'Test1234';
$apiPath  = $argv[3] ?? '/api/mobile/dashboard';
$iterations = max(1, (int)($argv[4] ?? 100));

echo "â•”â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•—\n";
echo "â•‘         APS Dream Home â€” API Load Test                        â•‘\n";
echo "â•šâ•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�\n\n";
echo "Base URL   : {$baseUrl}\n";
echo "Login as   : {$email}\n";
echo "Target API : {$apiPath}\n";
echo "Iterations : {$iterations}\n\n";

// -------- 1) Login to get a session cookie --------
$cookieJar = tempnam(sys_get_temp_dir(), 'apsloadcookie');
$loginUrl  = $baseUrl . '/login';

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $loginUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_COOKIEJAR      => $cookieJar,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_NOSIGNAL       => 1,
]);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Try to extract CSRF token from login page
$csrf = null;
if ($body && preg_match('/name=["\']csrf_token["\']\s+value=["\']([^"\']+)["\']/i', $body, $m)) {
    $csrf = $m[1];
} elseif ($body && preg_match('/name=["\']_token["\']\s+value=["\']([^"\']+)["\']/i', $body, $m)) {
    $csrf = $m[1];
} elseif ($body && preg_match('/<meta[^>]+name=["\']csrf-token["\'][^>]+content=["\']([^"\']+)["\']/i', $body, $m)) {
    $csrf = $m[1];
}

$postFields = http_build_query([
    'identity' => $email,
    'password' => $password,
    'email'    => $email,  // fallback
]);
if ($csrf) $postFields .= '&_token=' . urlencode($csrf) . '&csrf_token=' . urlencode($csrf);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $loginUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $postFields,
    CURLOPT_COOKIEFILE     => $cookieJar,
    CURLOPT_COOKIEJAR      => $cookieJar,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_NOSIGNAL       => 1,
]);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$urlAfter = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
curl_close($ch);

echo "Login POST: HTTP {$code} â†’ " . ($urlAfter ?: '(no redirect)') . "\n";

// Determine if session is authenticated
$isAuthed = (stripos((string)$urlAfter, '/login') === false && stripos((string)$urlAfter, '/dashboard') !== false)
         || ($code >= 200 && $code < 400);

// Alternative: check by visiting a known authenticated page
$probe = $baseUrl . '/user/dashboard';
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $probe,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_COOKIEFILE     => $cookieJar,
    CURLOPT_NOSIGNAL       => 1,
    CURLOPT_TIMEOUT        => 15,
]);
curl_exec($ch);
$probeCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$isAuthed = $isAuthed || ($probeCode === 200);

if (!$isAuthed) {
    echo "âš ï¸�  Login may have failed (probed /user/dashboard â†’ HTTP {$probeCode}). Will continue anyway.\n\n";
} else {
    echo "âœ… Authenticated. Session cookie stored.\n\n";
}

// -------- 2) Spam the API endpoint --------
$targetUrl = $baseUrl . $apiPath;
$latencies  = [];
$statuses   = [];
$errors     = 0;
$rateLimited = 0;
$first429At  = null;
$bytesTotal  = 0;

echo "Firing {$iterations} requests to {$apiPath} ...\n";
$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $t0 = microtime(true);
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $targetUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEFILE     => $cookieJar,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_NOSIGNAL       => 1,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    $elapsed = microtime(true) - $t0;

    $latencies[] = $elapsed;
    $statuses[(string)$code] = ($statuses[(string)$code] ?? 0) + 1;
    $bytesTotal += strlen($body ?: '');
    if ($err || $code >= 500) $errors++;
    if ($code === 429) {
        $rateLimited++;
        if ($first429At === null) $first429At = $i + 1;
    }
}
$wallTime = microtime(true) - $start;

sort($latencies);
$count = count($latencies);
$avg = array_sum($latencies) / $count;
$p50 = $latencies[(int)($count * 0.5)];
$p95 = $latencies[(int)($count * 0.95)];
$p99 = $latencies[(int)($count * 0.99)];

@unlink($cookieJar);

// -------- 3) Output --------
$report = [
    'meta' => [
        'test_name'  => 'APS Dream Home API Load',
        'timestamp'  => date('c'),
        'base_url'   => $baseUrl,
        'api_path'   => $apiPath,
        'iterations' => $iterations,
        'authenticated' => $isAuthed,
    ],
    'summary' => [
        'wall_time_s'  => round($wallTime, 3),
        'throughput_rps' => $count > 0 && $wallTime > 0 ? round($count / $wallTime, 2) : 0,
        'avg_ms'       => round($avg * 1000, 1),
        'p50_ms'       => round($p50 * 1000, 1),
        'p95_ms'       => round($p95 * 1000, 1),
        'p99_ms'       => round($p99 * 1000, 1),
        'errors'       => $errors,
        'rate_limited' => $rateLimited,
        'first_429_at' => $first429At,
        'total_bytes'  => $bytesTotal,
    ],
    'status_distribution' => $statuses,
];

$jsonFile = __DIR__ . '/api_load_results.json';
file_put_contents($jsonFile, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

$hr = str_repeat('â”€', 64);
echo "\n" . $hr . "\n";
echo "  WALL TIME    : " . round($wallTime, 2) . "s\n";
echo "  THROUGHPUT   : " . round($count / max($wallTime, 0.001), 1) . " req/s\n";
echo "  AVG LATENCY  : " . round($avg * 1000, 1) . "ms\n";
echo "  p50          : " . round($p50 * 1000, 1) . "ms\n";
echo "  p95          : " . round($p95 * 1000, 1) . "ms\n";
echo "  p99          : " . round($p99 * 1000, 1) . "ms\n";
echo "  ERRORS       : {$errors}\n";
echo "  RATE LIMITED : {$rateLimited}" . ($first429At ? " (first 429 at request #{$first429At})" : " (no 429 â€” no rate limit enforced or limit > {$iterations})") . "\n";
echo $hr . "\n";
echo "  STATUS DISTRIBUTION\n";
foreach ($statuses as $code => $cnt) {
    $pct = round($cnt / $count * 100, 1);
    printf("    HTTP %-3s : %4d  (%5.1f%%)\n", $code, $cnt, $pct);
}
echo $hr . "\n";
echo "ðŸ“„ JSON â†’ " . realpath($jsonFile) . "\n";
echo "\nâœ… API load test complete.\n";
exit(0);?>
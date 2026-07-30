<?php
/**
 * APS Dream Home — Load Test
 *
 * Simulates concurrent users hitting random endpoints using curl_multi.
 * Reports: avg/p95/p99 response time, throughput, error rate, status code distribution.
 *
 * Usage:
 *   php testing/load/load_test.php [users=100] [requests_per_user=10] [duration=30] [output=json|human|both]
 *
 * Defaults: 100 concurrent users, 10 requests each, 30s soft cap, both outputs.
 */

declare(strict_types=1);

@set_time_limit(0);
@ignore_user_abort(true);

// ---------------------- Args ----------------------
$argvAll  = $argv ?? [];
$users           = isset($argvAll[1]) ? (int)$argvAll[1] : 100;
$reqPerUser      = isset($argvAll[2]) ? (int)$argvAll[2] : 10;
$durationCap     = isset($argvAll[3]) ? (int)$argvAll[3] : 30;
$outputMode      = isset($argvAll[4]) ? strtolower((string)$argvAll[4]) : 'both';
$baseUrl         = getenv('BASE_URL') ?: 'http://localhost/apsdreamhome';

// Sanity
$users      = max(1, $users);
$reqPerUser = max(1, $reqPerUser);
$durationCap = max(1, $durationCap);

$endpoints = [
    '/'                                    => 'GET',
    '/properties'                          => 'GET',
    '/admin/dashboard?test_login=1'        => 'GET',
    '/api/mobile/dashboard'                => 'GET',
    '/auctions'                            => 'GET',
    '/contact'                             => 'GET',
    '/about'                               => 'GET',
    '/services'                            => 'GET',
    '/projects'                            => 'GET',
    '/user/login'                          => 'GET',
];

// ---------------------- Storage ----------------------
$resultsDir = __DIR__;
$resultsFile = $resultsDir . '/load_test_results.json';
$humanFile   = $resultsDir . '/load_test_results.txt';

// ---------------------- Helpers ----------------------
function fmtMs(float $sec): string {
    return number_format($sec * 1000, 1) . 'ms';
}
function fmtBytes(int $bytes): string {
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . 'MB';
    if ($bytes >= 1024)    return number_format($bytes / 1024, 1) . 'KB';
    return $bytes . 'B';
}
function pct(array $vals, float $p): float {
    if (empty($vals)) return 0.0;
    sort($vals);
    $idx = (int)ceil(($p / 100) * count($vals)) - 1;
    if ($idx < 0) $idx = 0;
    if ($idx >= count($vals)) $idx = count($vals) - 1;
    return (float)$vals[$idx];
}
function quantile_summary(array $vals): array {
    if (empty($vals)) {
        return [
            'count' => 0, 'min' => 0, 'max' => 0, 'avg' => 0, 'median' => 0,
            'p50' => 0, 'p90' => 0, 'p95' => 0, 'p99' => 0, 'stddev' => 0,
        ];
    }
    sort($vals);
    $count = count($vals);
    $sum   = array_sum($vals);
    $avg   = $sum / $count;
    $sqDiffs = array_map(fn($v) => pow($v - $avg, 2), $vals);
    $stddev = sqrt(array_sum($sqDiffs) / $count);
    return [
        'count'  => $count,
        'min'    => round(min($vals), 4),
        'max'    => round(max($vals), 4),
        'avg'    => round($avg, 4),
        'median' => round(pct($vals, 50), 4),
        'p50'    => round(pct($vals, 50), 4),
        'p90'    => round(pct($vals, 90), 4),
        'p95'    => round(pct($vals, 95), 4),
        'p99'    => round(pct($vals, 99), 4),
        'stddev' => round($stddev, 4),
    ];
}

// ---------------------- Main Loop ----------------------
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║         APS Dream Home — Load Test (curl_multi)                ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";
echo "Base URL        : {$baseUrl}\n";
echo "Concurrent users: {$users}\n";
echo "Requests / user : {$reqPerUser}\n";
echo "Target total    : " . ($users * $reqPerUser) . " requests\n";
echo "Soft duration   : {$durationCap}s\n";
echo "Output mode     : {$outputMode}\n\n";

$endpointKeys = array_keys($endpoints);
$totalRequests = $users * $reqPerUser;
$allLatencies  = [];          // seconds
$allResults    = [];          // detailed per-request
$statusCounts  = [];          // 200, 302, 404, 500, etc
$perEndpoint   = [];          // path => [latencies, status counts]
$totalBytes    = 0;
$errored       = 0;
$startTime     = microtime(true);
$completed     = 0;

// Pre-allocate per-endpoint buckets
foreach ($endpointKeys as $p) {
    $perEndpoint[$p] = ['latencies' => [], 'status' => [], 'bytes' => 0, 'count' => 0];
}

// curl_multi pipeline: keep `users` parallel requests in flight, refill as they finish.
$mh = curl_multi_init();
curl_multi_setopt($mh, CURLMOPT_MAX_TOTAL_CONNECTIONS, $users);
curl_multi_setopt($mh, CURLMOPT_MAXCONNECTS, $users);

// Per user handle: keep their request count, jitter, etc.
$inflight = [];   // handle => ['user' => int, 'req' => int, 'path' => string, 'start' => float]
$userState = [];  // userId => ['done' => int, 'plan' => [path, ...]]
for ($u = 0; $u < $users; $u++) {
    $userState[$u] = [
        'done' => 0,
        'plan' => [],
    ];
    for ($r = 0; $r < $reqPerUser; $r++) {
        $userState[$u]['plan'][] = $endpointKeys[array_rand($endpointKeys)];
    }
}

function newRequest(string $url, int $userId, int $reqNum, string $path): array {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,  // we want raw timing without redirect chase
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_NOSIGNAL       => 1,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_USERAGENT      => 'APSLoadTest/1.0 (User-' . $userId . ')',
        CURLOPT_HEADER         => false,
    ]);
    return [
        'ch'    => $ch,
        'user'  => $userId,
        'req'   => $reqNum,
        'path'  => $path,
        'start' => microtime(true),
    ];
}

// Seed initial wave
for ($u = 0; $u < $users; $u++) {
    $path = $userState[$u]['plan'][0] ?? '/';
    $fullUrl = $baseUrl . $path;
    $info = newRequest($fullUrl, $u, 0, $path);
    curl_multi_add_handle($mh, $info['ch']);
    $inflight[(int)$info['ch']] = $info;
}

$running = null;
$lastProgress = $startTime;
do {
    $status = curl_multi_exec($mh, $running);
    if ($running > 0) {
        curl_multi_select($mh, 0.05);
    }
    // Process completed
    while ($info = curl_multi_info_read($mh)) {
        $h = $info['handle'];
        $key = (int)$h;
        $meta = $inflight[$key] ?? null;
        if (!$meta) {
            curl_multi_remove_handle($mh, $h);
            curl_close($h);
            continue;
        }
        $elapsed = microtime(true) - $meta['start'];
        $body = curl_multi_getcontent($h);
        $httpCode = curl_getinfo($h, CURLINFO_HTTP_CODE);
        $err = curl_error($h);
        $bytes = strlen($body ?: '');

        $allLatencies[] = $elapsed;
        $statusKey = (string)$httpCode;
        $statusCounts[$statusKey] = ($statusCounts[$statusKey] ?? 0) + 1;
        $totalBytes += $bytes;

        $perEndpoint[$meta['path']]['latencies'][] = $elapsed;
        $perEndpoint[$meta['path']]['status'][$statusKey] = ($perEndpoint[$meta['path']]['status'][$statusKey] ?? 0) + 1;
        $perEndpoint[$meta['path']]['bytes'] += $bytes;
        $perEndpoint[$meta['path']]['count']++;

        $allResults[] = [
            'user'      => $meta['user'],
            'req'       => $meta['req'],
            'path'      => $meta['path'],
            'status'    => $httpCode,
            'time_s'    => round($elapsed, 4),
            'time_ms'   => round($elapsed * 1000, 1),
            'bytes'     => $bytes,
            'error'     => $err ?: null,
        ];
        if ($err || $httpCode >= 500) $errored++;

        $completed++;
        $userState[$meta['user']]['done']++;

        // Remove finished
        curl_multi_remove_handle($mh, $h);
        curl_close($h);
        unset($inflight[$key]);

        // If user has more requests, enqueue next
        $nextIdx = $userState[$meta['user']]['done'];
        if ($nextIdx < $reqPerUser) {
            $nextPath = $userState[$meta['user']]['plan'][$nextIdx];
            $new = newRequest($baseUrl . $nextPath, $meta['user'], $nextIdx, $nextPath);
            curl_multi_add_handle($mh, $new['ch']);
            $inflight[(int)$new['ch']] = $new;
        }
    }

    // Progress line every 2s
    $now = microtime(true);
    if ($now - $lastProgress > 2) {
        $pct = $totalRequests > 0 ? round(($completed / $totalRequests) * 100, 1) : 0;
        $elapsedTotal = $now - $startTime;
        $rps = $elapsedTotal > 0 ? round($completed / $elapsedTotal, 1) : 0;
        fwrite(STDERR, sprintf("\r[%6.1fs] %5d / %d (%5.1f%%) — %.1f req/s   ", $elapsedTotal, $completed, $totalRequests, $pct, $rps));
        $lastProgress = $now;
    }

    // Duration cap (soft): if we've been running longer than the cap and are done with all *queued*, bail.
    if ((microtime(true) - $startTime) > $durationCap && $completed >= $totalRequests) {
        break;
    }
} while ($status === CURLM_OK && ($running > 0 || count($inflight) > 0));

// Drain any stragglers
do {
    $status = curl_multi_exec($mh, $running);
    if ($running > 0) curl_multi_select($mh, 0.05);
    while ($info = curl_multi_info_read($mh)) {
        $h = $info['handle'];
        $key = (int)$h;
        $meta = $inflight[$key] ?? null;
        if (!$meta) { curl_multi_remove_handle($mh, $h); curl_close($h); continue; }
        $elapsed = microtime(true) - $meta['start'];
        $body = curl_multi_getcontent($h);
        $httpCode = curl_getinfo($h, CURLINFO_HTTP_CODE);
        $bytes = strlen($body ?: '');
        $allLatencies[] = $elapsed;
        $statusKey = (string)$httpCode;
        $statusCounts[$statusKey] = ($statusCounts[$statusKey] ?? 0) + 1;
        $totalBytes += $bytes;
        $perEndpoint[$meta['path']]['latencies'][] = $elapsed;
        $perEndpoint[$meta['path']]['status'][$statusKey] = ($perEndpoint[$meta['path']]['status'][$statusKey] ?? 0) + 1;
        $perEndpoint[$meta['path']]['bytes'] += $bytes;
        $perEndpoint[$meta['path']]['count']++;
        $allResults[] = ['user' => $meta['user'], 'req' => $meta['req'], 'path' => $meta['path'], 'status' => $httpCode, 'time_s' => round($elapsed, 4), 'time_ms' => round($elapsed * 1000, 1), 'bytes' => $bytes, 'error' => null];
        $completed++;
        $userState[$meta['user']]['done']++;
        curl_multi_remove_handle($mh, $h);
        curl_close($h);
        unset($inflight[$key]);
    }
} while ($running > 0);

curl_multi_close($mh);

$endTime = microtime(true);
$totalElapsed = $endTime - $startTime;
$throughput   = $totalElapsed > 0 ? $completed / $totalElapsed : 0;
$summary      = quantile_summary($allLatencies);

// Per-endpoint summary
$endpointSummary = [];
foreach ($perEndpoint as $path => $bucket) {
    if ($bucket['count'] === 0) continue;
    $endpointSummary[$path] = quantile_summary($bucket['latencies']);
    $endpointSummary[$path]['count']    = $bucket['count'];
    $endpointSummary[$path]['status']   = $bucket['status'];
    $endpointSummary[$path]['avg_bytes'] = (int)($bucket['bytes'] / $bucket['count']);
    $endpointSummary[$path]['total_bytes'] = $bucket['bytes'];
}

ksort($statusCounts);

$result = [
    'meta' => [
        'test_name'        => 'APS Dream Home Load Test',
        'timestamp'        => date('c'),
        'base_url'         => $baseUrl,
        'concurrent_users' => $users,
        'requests_per_user'=> $reqPerUser,
        'target_total'     => $totalRequests,
        'duration_cap_s'   => $durationCap,
        'php_version'      => PHP_VERSION,
    ],
    'totals' => [
        'completed'  => $completed,
        'errors'     => $errored,
        'error_rate' => $completed > 0 ? round($errored / $completed, 4) : 0,
        'total_time_s' => round($totalElapsed, 2),
        'throughput_rps' => round($throughput, 2),
        'total_bytes'  => $totalBytes,
        'avg_bytes'    => $completed > 0 ? (int)($totalBytes / $completed) : 0,
    ],
    'latency_summary' => $summary,
    'status_distribution' => $statusCounts,
    'per_endpoint'   => $endpointSummary,
    // We deliberately do NOT include allRequests (could be 1000s of rows) — kept on disk only.
];

if ($outputMode === 'json' || $outputMode === 'both') {
    file_put_contents($resultsFile, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo "\n\n📄 JSON results → " . realpath($resultsFile) . "\n";
}

if ($outputMode === 'human' || $outputMode === 'both') {
    $hr = str_repeat('─', 72);
    $lines = [];
    $lines[] = $hr;
    $lines[] = "  APS DREAM HOME — LOAD TEST REPORT";
    $lines[] = $hr;
    $lines[] = "  Timestamp        : " . $result['meta']['timestamp'];
    $lines[] = "  Base URL         : {$baseUrl}";
    $lines[] = "  Concurrent users : {$users}";
    $lines[] = "  Requests / user  : {$reqPerUser}";
    $lines[] = "  Target total     : " . number_format($totalRequests);
    $lines[] = "  Wall time        : " . round($totalElapsed, 2) . "s";
    $lines[] = "";
    $lines[] = "  ── TOTALS ──";
    $lines[] = "  Completed        : " . number_format($completed);
    $lines[] = "  Errors (≥500)    : {$errored}";
    $lines[] = "  Error rate       : " . number_format($result['totals']['error_rate'] * 100, 2) . "%";
    $lines[] = "  Throughput       : " . round($throughput, 2) . " req/s";
    $lines[] = "  Total bytes      : " . fmtBytes($totalBytes);
    $lines[] = "  Avg response size: " . fmtBytes($result['totals']['avg_bytes']);
    $lines[] = "";
    $lines[] = "  ── LATENCY (seconds) ──";
    $lines[] = "  Count            : " . $summary['count'];
    $lines[] = "  Min              : " . fmtMs($summary['min']);
    $lines[] = "  Max              : " . fmtMs($summary['max']);
    $lines[] = "  Avg              : " . fmtMs($summary['avg']);
    $lines[] = "  Median           : " . fmtMs($summary['median']);
    $lines[] = "  P50              : " . fmtMs($summary['p50']);
    $lines[] = "  P90              : " . fmtMs($summary['p90']);
    $lines[] = "  P95              : " . fmtMs($summary['p95']);
    $lines[] = "  P99              : " . fmtMs($summary['p99']);
    $lines[] = "  Stddev           : " . fmtMs($summary['stddev']);
    $lines[] = "";
    $lines[] = "  ── STATUS DISTRIBUTION ──";
    foreach ($statusCounts as $code => $cnt) {
        $pct = $completed > 0 ? round($cnt / $completed * 100, 1) : 0;
        $lines[] = sprintf("  HTTP %-3s : %5d  (%5.1f%%)", $code, $cnt, $pct);
    }
    $lines[] = "";
    $lines[] = "  ── PER-ENDPOINT ──";
    foreach ($endpointSummary as $path => $es) {
        $lines[] = "  {$path}";
        $lines[] = "      count={$es['count']}  avg=" . fmtMs($es['avg']) .
                   "  p95=" . fmtMs($es['p95']) . "  p99=" . fmtMs($es['p99']) .
                   "  max=" . fmtMs($es['max']) . "  avg_size=" . fmtBytes($es['avg_bytes']);
    }
    $lines[] = "";
    $lines[] = $hr;

    $txt = implode("\n", $lines) . "\n";
    file_put_contents($humanFile, $txt);
    echo "\n" . $txt;
    echo "📄 Human-readable report → " . realpath($humanFile) . "\n";
}

echo "\n✅ Load test complete.\n";
exit(0);

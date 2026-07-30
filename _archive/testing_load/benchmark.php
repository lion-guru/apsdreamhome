<?php
/**
 * APS Dream Home — Single-Endpoint Benchmark
 *
 * Hammer one endpoint N times, output histogram + stats.
 * No external deps. Uses curl to fetch with timing.
 *
 * Usage:
 *   php testing/load/benchmark.php [path=/] [iterations=100] [output=human|json|both]
 */

declare(strict_types=1);

@set_time_limit(0);

$baseUrl     = getenv('BASE_URL') ?: 'http://localhost/apsdreamhome';
$path        = $argv[1] ?? '/';
$iterations  = max(1, (int)($argv[2] ?? 100));
$outputMode  = strtolower($argv[3] ?? 'both');

$url = $baseUrl . $path;

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║         APS Dream Home — Endpoint Benchmark                  ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";
echo "URL        : {$url}\n";
echo "Iterations : {$iterations}\n\n";

// ---------------- Run benchmark ----------------
$latencies = [];   // seconds
$statuses  = [];
$bytes     = 0;
$errors    = 0;

$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $t0 = microtime(true);
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_NOSIGNAL       => 1,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_USERAGENT      => 'APSBenchmark/1.0',
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    $elapsed = microtime(true) - $t0;

    $latencies[] = $elapsed;
    $statuses[(string)$code] = ($statuses[(string)$code] ?? 0) + 1;
    $bytes += strlen($body ?: '');
    if ($err || $code >= 500) $errors++;
}
$wallTime = microtime(true) - $start;

// ---------------- Stats ----------------
sort($latencies);
$count = count($latencies);
$sum   = array_sum($latencies);
$avg   = $sum / $count;
$min   = min($latencies);
$max   = max($latencies);
$median= $latencies[(int)($count / 2)];
$sqDiffs = array_map(fn($v) => pow($v - $avg, 2), $latencies);
$stddev = sqrt(array_sum($sqDiffs) / $count);
$pct = function (float $p) use ($latencies, $count) {
    $idx = (int)ceil(($p / 100) * $count) - 1;
    if ($idx < 0) $idx = 0;
    if ($idx >= $count) $idx = $count - 1;
    return $latencies[$idx];
};
$p50 = $pct(50);
$p75 = $pct(75);
$p90 = $pct(90);
$p95 = $pct(95);
$p99 = $pct(99);

$summary = [
    'path'         => $path,
    'url'          => $url,
    'iterations'   => $count,
    'wall_time_s'  => round($wallTime, 3),
    'rps'          => $count > 0 && $wallTime > 0 ? round($count / $wallTime, 2) : 0,
    'min_ms'       => round($min * 1000, 1),
    'max_ms'       => round($max * 1000, 1),
    'avg_ms'       => round($avg * 1000, 1),
    'median_ms'    => round($median * 1000, 1),
    'stddev_ms'    => round($stddev * 1000, 1),
    'p50_ms'       => round($p50 * 1000, 1),
    'p75_ms'       => round($p75 * 1000, 1),
    'p90_ms'       => round($p90 * 1000, 1),
    'p95_ms'       => round($p95 * 1000, 1),
    'p99_ms'       => round($p99 * 1000, 1),
    'errors'       => $errors,
    'error_rate'   => $count > 0 ? round($errors / $count, 4) : 0,
    'total_bytes'  => $bytes,
    'avg_bytes'    => $count > 0 ? (int)($bytes / $count) : 0,
    'status_codes' => $statuses,
    'timestamp'    => date('c'),
];

// ---------------- Histogram (20 buckets) ----------------
$bucketCount = 20;
$range = $max - $min;
$bucketSize = $range > 0 ? $range / $bucketCount : 1;
$buckets = array_fill(0, $bucketCount, 0);
$bucketLabels = [];
for ($b = 0; $b < $bucketCount; $b++) {
    $lo = $min + $b * $bucketSize;
    $hi = $min + ($b + 1) * $bucketSize;
    $bucketLabels[$b] = sprintf('%6.1f-%6.1fms', $lo * 1000, $hi * 1000);
}
foreach ($latencies as $v) {
    $idx = $bucketSize > 0 ? (int)(($v - $min) / $bucketSize) : 0;
    if ($idx >= $bucketCount) $idx = $bucketCount - 1;
    if ($idx < 0) $idx = 0;
    $buckets[$idx]++;
}
$maxBucketCount = max(1, max($buckets));

// ---------------- Output ----------------
$resultsDir  = __DIR__;
$jsonFile    = $resultsDir . '/benchmark_results.json';
$reportFile  = $resultsDir . '/benchmark_results.txt';

if ($outputMode === 'json' || $outputMode === 'both') {
    $out = $summary;
    $out['histogram'] = [];
    foreach ($buckets as $i => $c) {
        $out['histogram'][] = ['bucket' => $bucketLabels[$i], 'count' => $c];
    }
    file_put_contents($jsonFile, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo "📄 JSON → " . realpath($jsonFile) . "\n";
}

if ($outputMode === 'human' || $outputMode === 'both') {
    echo "\n";
    $hr = str_repeat('─', 64);
    echo $hr . "\n";
    echo "  URL           : {$url}\n";
    echo "  Iterations    : {$count}\n";
    echo "  Wall time     : " . round($wallTime, 2) . "s\n";
    echo "  Throughput    : " . round($count / max($wallTime, 0.001), 1) . " req/s\n";
    echo "  Total bytes   : " . number_format($bytes) . "B\n";
    echo "  Errors (≥500) : {$errors}\n";
    echo $hr . "\n";
    printf("  %-12s : %8s\n", 'Min', number_format($min * 1000, 1) . 'ms');
    printf("  %-12s : %8s\n", 'Max', number_format($max * 1000, 1) . 'ms');
    printf("  %-12s : %8s\n", 'Avg', number_format($avg * 1000, 1) . 'ms');
    printf("  %-12s : %8s\n", 'Median', number_format($median * 1000, 1) . 'ms');
    printf("  %-12s : %8s\n", 'Stddev', number_format($stddev * 1000, 1) . 'ms');
    echo $hr . "\n";
    printf("  %-12s : %8s\n", 'p50', number_format($p50 * 1000, 1) . 'ms');
    printf("  %-12s : %8s\n", 'p75', number_format($p75 * 1000, 1) . 'ms');
    printf("  %-12s : %8s\n", 'p90', number_format($p90 * 1000, 1) . 'ms');
    printf("  %-12s : %8s\n", 'p95', number_format($p95 * 1000, 1) . 'ms');
    printf("  %-12s : %8s\n", 'p99', number_format($p99 * 1000, 1) . 'ms');
    echo $hr . "\n";
    echo "  Status codes  :\n";
    foreach ($statuses as $code => $cnt) {
        $pct = $count > 0 ? round($cnt / $count * 100, 1) : 0;
        printf("    HTTP %-3s : %4d  (%5.1f%%)\n", $code, $cnt, $pct);
    }
    echo $hr . "\n";
    echo "  Histogram (20 buckets, max=" . $maxBucketCount . "):\n";
    foreach ($buckets as $i => $c) {
        $barLen = $maxBucketCount > 0 ? (int)round(($c / $maxBucketCount) * 30) : 0;
        $bar = str_repeat('█', $barLen);
        printf("    %s : %4d  %s\n", $bucketLabels[$i], $c, $bar);
    }
    echo $hr . "\n\n";

    // Save human version
    ob_start();
    echo $hr . "\n";
    echo "  URL           : {$url}\n";
    echo "  Iterations    : {$count}\n";
    echo "  Wall time     : " . round($wallTime, 2) . "s\n";
    echo "  Throughput    : " . round($count / max($wallTime, 0.001), 1) . " req/s\n";
    echo "  Errors (≥500) : {$errors}\n";
    echo $hr . "\n";
    printf("  %-12s : %8s\n", 'Min',    number_format($min * 1000, 1) . 'ms');
    printf("  %-12s : %8s\n", 'Max',    number_format($max * 1000, 1) . 'ms');
    printf("  %-12s : %8s\n", 'Avg',    number_format($avg * 1000, 1) . 'ms');
    printf("  %-12s : %8s\n", 'Median', number_format($median * 1000, 1) . 'ms');
    printf("  %-12s : %8s\n", 'p95',    number_format($p95 * 1000, 1) . 'ms');
    printf("  %-12s : %8s\n", 'p99',    number_format($p99 * 1000, 1) . 'ms');
    $txt = ob_get_clean();
    file_put_contents($reportFile, $txt);
    echo "📄 Report → " . realpath($reportFile) . "\n";
}

echo "\n✅ Benchmark complete.\n";
exit(0);

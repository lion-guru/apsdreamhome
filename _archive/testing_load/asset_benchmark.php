<?php
/**
 * APS Dream Home Ã¢â‚¬â€� Static Asset Benchmark
 *
 * Fetch the homepage, extract all <link>/<script>/<img alt="image"> URLs, download each,
 * report: total size, gzipped size, cache headers, optimization recos.
 *
 * Usage:
 *   php testing/load/asset_benchmark.php [url=http://localhost/apsdreamhome/]
 */

declare(strict_types=1);

@set_time_limit(0);

$baseUrl = rtrim($argv[1] ?? getenv('BASE_URL') ?: 'http://localhost/apsdreamhome', '/');
$pagePath = '/';

echo "Ã¢â€¢â€�Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢â€”\n";
echo "Ã¢â€¢â€˜         APS Dream Home Ã¢â‚¬â€� Static Asset Benchmark               Ã¢â€¢â€˜\n";
echo "Ã¢â€¢Å¡Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½Ã¢â€¢ï¿½\n\n";
echo "Origin : {$baseUrl}\n";
echo "Page   : {$pagePath}\n\n";

// ---------------- Fetch homepage ----------------
$homepageUrl = $baseUrl . $pagePath;
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $homepageUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_HEADER         => true,
    CURLOPT_NOSIGNAL       => 1,
    CURLOPT_USERAGENT      => 'APSAssetBench/1.0',
    CURLOPT_ENCODING       => '',  // accept gzip
]);
$raw = curl_exec($ch);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$homeHeaders= substr((string)$raw, 0, $headerSize);
$homeBody   = substr((string)$raw, $headerSize);
$err = curl_error($ch);
curl_close($ch);

if ($err || $httpCode >= 400 || !$homeBody) {
    fwrite(STDERR, "Ã¢ï¿½Å’ Failed to fetch homepage: HTTP {$httpCode}, err={$err}\n");
    exit(1);
}
echo "Homepage fetched: " . number_format(strlen($homeBody)) . "B (HTTP {$httpCode})\n\n";

// ---------------- Extract asset URLs ----------------
$assetUrls = [];
$patterns = [
    'css'  => '/<link[^>]+rel=["\']stylesheet["\'][^>]+href=["\']([^"\']+)["\']/i',
    'js'   => '/<script[^>]+src=["\']([^"\']+)["\']/i',
    'img'  => '/<img[^ alt="image">]+src=["\']([^"\']+)["\']/i',
];
foreach ($patterns as $type => $re) {
    if (preg_match_all($re, $homeBody, $m)) {
        foreach ($m[1] as $u) {
            $assetUrls[$type][] = $u;
        }
    }
}

$counts = [
    'css' => count($assetUrls['css'] ?? []),
    'js'  => count($assetUrls['js']  ?? []),
    'img' => count($assetUrls['img'] ?? []),
];
echo "Assets discovered: CSS={$counts['css']}  JS={$counts['js']}  IMG={$counts['img']}\n\n";

// ---------------- Resolve absolute URLs ----------------
$resolveUrl = function (string $ref) use ($baseUrl) {
    if (preg_match('#^https?://#i', $ref)) return $ref;
    if (str_starts_with($ref, '//')) return 'http:' . $ref;
    if (str_starts_with($ref, '/')) return $baseUrl . $ref;
    return $baseUrl . '/' . $ref;
};
$allAssets = [];
foreach ($assetUrls as $type => $list) {
    foreach ($list as $u) {
        $allAssets[] = ['type' => $type, 'url' => $resolveUrl($u)];
    }
}
$allAssets = array_values(array_unique($allAssets, SORT_REGULAR));

// ---------------- Download each asset ----------------
$results = [];
$totals = [
    'css' => ['count' => 0, 'raw' => 0, 'gzip' => 0],
    'js'  => ['count' => 0, 'raw' => 0, 'gzip' => 0],
    'img' => ['count' => 0, 'raw' => 0, 'gzip' => 0],
];
$badHeaders = [];
$noCacheList = [];

foreach ($allAssets as $a) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $a['url'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_HEADER         => true,
        CURLOPT_NOSIGNAL       => 1,
        CURLOPT_USERAGENT      => 'APSAssetBench/1.0',
    ]);
    $resp = curl_exec($ch);
    $hs   = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $hdrs = substr((string)$resp, 0, $hs);
    $body = substr((string)$resp, $hs);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err || $code >= 400 || !$body) {
        $results[] = ['type' => $a['type'], 'url' => $a['url'], 'status' => $code, 'error' => $err ?: "HTTP {$code}", 'size' => 0];
        continue;
    }
    $rawSize   = strlen($body);
    $gzipSize  = strlen(gzencode($body, 6));
    $cc = 'none';
    if (preg_match('/Cache-Control:\s*([^\r\n]+)/i', $hdrs, $m)) $cc = trim($m[1]);
    $ct = 'unknown';
    if (preg_match('/Content-Type:\s*([^\r\n]+)/i', $hdrs, $m)) $ct = trim($m[1]);

    $results[] = [
        'type'      => $a['type'],
        'url'       => $a['url'],
        'status'    => $code,
        'content_type' => $ct,
        'cache_control' => $cc,
        'raw_bytes'  => $rawSize,
        'gzip_bytes' => $gzipSize,
    ];
    $totals[$a['type']]['count']++;
    $totals[$a['type']]['raw']   += $rawSize;
    $totals[$a['type']]['gzip']  += $gzipSize;

    // Bad cache?
    if ($a['type'] === 'css' || $a['type'] === 'js') {
        if (stripos($cc, 'max-age') === false) $noCacheList[] = $a['url'];
    }
}

// ---------------- Recommendations ----------------
$recos = [];
foreach ($totals as $t => $v) {
    if ($v['raw'] > 0 && ($v['raw'] - $v['gzip']) / $v['raw'] > 0.3) {
        $recos[] = strtoupper($t) . ": gzip saves " . round((1 - $v['gzip'] / $v['raw']) * 100, 1) . "% ({$v['raw']}B Ã¢â€ â€™ {$v['gzip']}B). Ensure mod_deflate is enabled.";
    }
    if ($t === 'img' && $v['raw'] > 500 * 1024) {
        $recos[] = "IMAGES total {$v['raw']}B Ã¢â‚¬â€� consider WebP/AVIF, srcset, lazy loading.";
    }
    if (count(array_filter($results, fn($r) => $r['type'] === $t)) > 12) {
        $recos[] = strtoupper($t) . ": " . $v['count'] . " files Ã¢â‚¬â€� consider bundling/concatenation to reduce HTTP/2 streams.";
    }
}
if (!empty($noCacheList)) {
    $recos[] = "Cache-Control missing on " . count($noCacheList) . " CSS/JS assets Ã¢â‚¬â€� set long max-age with versioning.";
}

// ---------------- Output ----------------
$report = [
    'meta' => [
        'test_name'  => 'APS Dream Home Asset Benchmark',
        'timestamp'  => date('c'),
        'homepage'   => $homepageUrl,
        'homepage_size_bytes' => strlen($homeBody),
        'homepage_status'     => $httpCode,
    ],
    'totals' => $totals,
    'grand_total' => [
        'count' => array_sum(array_column($totals, 'count')),
        'raw'   => array_sum(array_column($totals, 'raw')),
        'gzip'  => array_sum(array_column($totals, 'gzip')),
        'savings_pct' => 0,
    ],
    'recommendations' => $recos,
    'assets' => $results,
];
$report['grand_total']['savings_pct'] = $report['grand_total']['raw'] > 0
    ? round((1 - $report['grand_total']['gzip'] / $report['grand_total']['raw']) * 100, 1)
    : 0;

$jsonFile = __DIR__ . '/asset_benchmark_results.json';
file_put_contents($jsonFile, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

$hr = str_repeat('Ã¢â€�â‚¬', 72);
echo $hr . "\n";
echo "  TOTALS BY TYPE\n";
echo $hr . "\n";
printf("  %-8s %6s %12s %12s %8s\n", 'type', 'count', 'raw', 'gzip', 'savings');
echo "  " . str_repeat('Ã‚Â·', 50) . "\n";
foreach ($totals as $t => $v) {
    $sav = $v['raw'] > 0 ? round((1 - $v['gzip'] / $v['raw']) * 100, 1) : 0;
    printf("  %-8s %6d %12s %12s %7.1f%%\n",
        $t, $v['count'],
        number_format($v['raw']) . 'B',
        number_format($v['gzip']) . 'B',
        $sav);
}
echo "  " . str_repeat('Ã‚Â·', 50) . "\n";
printf("  %-8s %6d %12s %12s %7.1f%%\n",
    'TOTAL',
    $report['grand_total']['count'],
    number_format($report['grand_total']['raw']) . 'B',
    number_format($report['grand_total']['gzip']) . 'B',
    $report['grand_total']['savings_pct']);
echo $hr . "\n";

if (!empty($recos)) {
    echo "  RECOMMENDATIONS\n";
    echo $hr . "\n";
    foreach ($recos as $r) echo "  Ã¢â‚¬Â¢ {$r}\n";
    echo $hr . "\n";
}
if (!empty($noCacheList)) {
    echo "  ASSETS MISSING Cache-Control (first 5):\n";
    foreach (array_slice($noCacheList, 0, 5) as $u) echo "    Ã¢â‚¬Â¢ {$u}\n";
    echo $hr . "\n";
}

echo "Ã°Å¸â€œâ€ž JSON Ã¢â€ â€™ " . realpath($jsonFile) . "\n";
echo "\nÃ¢Å“â€¦ Asset benchmark complete.\n";
exit(0);?>
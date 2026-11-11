<?php
/**
 * Lightweight load smoke (no heavy lib) — sequential + timing
 * Usage: php scripts/load_smoke.php [--n=50] [--url=/properties]
 * Default: 50 sequential GETs on /properties, /colonies, /api/v2/mobile/properties/browse
 * Prints avg / p95 / max ms, HTTP codes count.
 */
$base = 'http://localhost/apsdreamhome';
$opts = getopt('', ['n::', 'url::']);
$n = max(5, min(200, (int)($opts['n'] ?? 50)));
$urls = [
  '/properties',
  '/colonies',
  '/api/v2/mobile/properties/browse',
  '/api/v2/mobile/colonies',
];

echo "Load smoke: $n requests per URL (sequential)\n";
echo str_repeat('=', 70) . "\n";
foreach ($urls as $path) {
  $url = $base . $path;
  $times = [];
  $ok = 0; $fail = 0;
  for ($i = 1; $i <= $n; $i++) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $start = microtime(true);
    $html = curl_exec($ch);
    $elapsed = (microtime(true) - $start) * 1000;
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $times[] = $elapsed;
    if ($code >= 200 && $code < 400) $ok++; else $fail++;
  }
  sort($times);
  $avg = array_sum($times) / count($times);
  $p95 = $times[(int)ceil(count($times) * 0.95) - 1];
  $max = end($times);
  printf("%-45s ok=%d fail=%d avg=%.0fms p95=%.0fms max=%.0fms\n", $path, $ok, $fail, $avg, $p95, $max);
}
echo "Done. Heavy load (k6/Locust) for 50 VU is owner call — this is smoke only.\n";

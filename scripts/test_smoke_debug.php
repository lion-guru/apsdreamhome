<?php
$BASE = 'http://localhost/apsdreamhome';
$tmp = sys_get_temp_dir();
$jars = ['public' => "$tmp/smoke_public.txt", 'admin' => "$tmp/smoke_admin.txt", 'assoc' => "$tmp/smoke_assoc.txt"];
foreach ($jars as $j) @unlink($j);

function hit(string $url, string $jar): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => false,
        CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 5,
        CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_TIMEOUT => 15,
        CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar,
        CURLOPT_USERAGENT => 'APS-Smoke-Runner/1.0',
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $t0 = microtime(true);
    $body = curl_exec($ch);
    $ms = round((microtime(true) - $t0) * 1000, 1);
    $info = ['code' => (int)curl_getinfo($ch, CURLINFO_HTTP_CODE),
        'ctype' => (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE),
        'size' => (int)curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD),
        'err' => curl_errno($ch) ? curl_error($ch) : ''];
    curl_close($ch);
    return [$info, (string)$body, $ms];
}

$jars = ['public' => sys_get_temp_dir() . '/smoke_public.txt', 'admin' => sys_get_temp_dir() . '/smoke_admin.txt', 'assoc' => sys_get_temp_dir() . '/smoke_assoc.txt'];
foreach ($jars as $j) @unlink($j);

$BASE = 'http://localhost/apsdreamhome';

// --- establish role sessions (dev bypass) ---
echo "Auth: admin login?test_login=2 / associate login?test_login=5\n";
[$ai] = hit("$BASE/admin/login?test_login=2", $jars['admin']);
[$si] = hit("$BASE/admin/login?test_login=5", $jars['assoc']);
printf("  admin jar -> HTTP %d | assoc jar -> HTTP %d\n", $ai['code'], $si['code']);

$probes = [
    ['api-health', '/api/health', 'public', [200], 1],
    ['api-colonies', '/api/v2/mobile/colonies', 'public', [200], 1],
    ['api-plots', '/api/v2/mobile/plots/all', 'public', [200], 1],
];

foreach ($probes as [$label, $path, $jar, $expect, $wantJson]) {
    [$info, $body, $ms] = hit($BASE . $path, $jars[$jar]);
    $ok = in_array($info['code'], $expect, true) && $info['err'] === '' && $ms < 10000;
    $note = '';
    if ($ok && $wantJson) {
        $isJson = stripos($info['ctype'], 'json') !== false || (json_decode($body) !== null);
        if (!$isJson) { $ok = false; $note = 'non-JSON'; }
    }
    if ($info['err'] !== '') $note = $info['err'];
    $ok ? $pass++ : $fail++;
    echo "$label: $path -> HTTP {$info['code']} ({$info['ctype']}) " . ($ok ? 'PASS' : 'FAIL');
    if ($note) echo " ($note)";
    echo "\n";
    echo "  Body preview: " . substr($body, 0, 200) . "\n";
    echo "  json_decode: " . (json_decode($body) !== null ? "OK" : "FAIL") . "\n";
    echo "  ctype: " . $info['ctype'] . "\n";
    echo "  ctype contains json: " . (stripos($info['ctype'], 'json') !== false ? "YES" : "NO") . "\n";
    echo "  json_decode(body): " . (json_decode($body) !== null ? "OK" : "FAIL") . "\n\n";
}
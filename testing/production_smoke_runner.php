<?php
/**
 * APS Dream Home — 1-Click Production Smoke Runner (Session 119)
 * File: testing/production_smoke_runner.php
 * Usage (CLI): php testing/production_smoke_runner.php [--base=http://localhost/apsdreamhome] [--no-color]
 *
 * Probes 38 endpoints (public + associate + employee + customer + admin + APIs) with cURL:
 *   - timeout 15s, follow redirects, per-role cookie jars
 *   - protected routes use dev bypass /admin/login?test_login=2 (super_admin),
 *     ?test_login=5 (associate), ?test_login=4 (employee), ?test_login=7 (customer).
 *     Bypass is 403-blocked in production (see AdminAuthController), so this runner is
 *     LOCAL-DEV ONLY.
 *
 * Spec-to-reality mapping (verified against routes/api.php before probing):
 *   - spec `/api/v2/mobile/health` does NOT exist -> probes real `/api/health`
 *   - spec `/api/v2/mobile/plots`  does NOT exist -> probes real `/api/v2/mobile/plots/all`
 *
 * Exit code 0 = all PASS, 1 = any FAIL.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$BASE = 'http://localhost/apsdreamhome';
$NO_COLOR = false;
foreach ($argv ?? [] as $a) {
    if (str_starts_with($a, '--base=')) $BASE = rtrim(substr($a, 7), '/');
    if ($a === '--no-color') $NO_COLOR = true;
}
function c(string $t, string $color = ''): string {
    global $NO_COLOR;
    if ($NO_COLOR || $color === '') return $t;
    $m = ['green' => "\033[32m", 'red' => "\033[31m", 'yellow' => "\033[33m", 'cyan' => "\033[36m", 'bold' => "\033[1m", 'reset' => "\033[0m"];
    return ($m[$color] ?? '') . $t . $m['reset'];
}

$tmp = sys_get_temp_dir();
$jars = ['public' => "$tmp/smoke_public.txt", 'admin' => "$tmp/smoke_admin.txt", 'assoc' => "$tmp/smoke_assoc.txt", 'employee' => "$tmp/smoke_employee.txt", 'customer' => "$tmp/smoke_customer.txt"];
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

// --- establish role sessions (dev bypass) ---
echo c('Auth: ', 'cyan') . "admin ?test_login=2 / associate ?test_login=5 / employee ?test_login=4 / customer ?test_login=7\n";
[$ai] = hit("$BASE/admin/login?test_login=2", $jars['admin']);
[$si] = hit("$BASE/admin/login?test_login=5", $jars['assoc']);
[$ei] = hit("$BASE/employee/login?test_login=4", $jars['employee']);
[$ci] = hit("$BASE/login?test_login=7", $jars['customer']);
printf("  admin -> HTTP %d | assoc -> HTTP %d | employee -> HTTP %d | customer -> HTTP %d\n", $ai['code'], $si['code'], $ei['code'], $ci['code']);
// Fast-fail auth guard: if an admin session did not stick (throttle / stale jar),
// every admin probe would land on a public login page and report "missing needle".
// Detect that up-front and fail with a crystal-clear message instead of 7 ghost FAILs.
[$authInfo, $authBody] = hit("$BASE/admin/ai/hub", $jars['admin']);
if (stripos((string)$authBody, 'AI Command Center') === false) {
    echo c("FATAL: admin bypass did not persist — /admin/ai/hub did not render. Likely login throttling or stale cookie jar. Re-run in a few seconds.\n", 'red');
    exit(2);
}

// [label, path, jar, expectCodes, flags(json?), needle?]  — needle is a case-insensitive substring that must appear in the body
$probes = [
    // public (9)
    ['home', '/', 'public', [200], 0], ['properties', '/properties', 'public', [200], 0],
    ['colonies', '/colonies', 'public', [200], 0], ['login', '/login', 'public', [200], 0],
    ['register', '/register', 'public', [200], 0], ['become-associate', '/become-associate', 'public', [200], 0],
    ['contact', '/contact', 'public', [200], 0], ['terms', '/terms', 'public', [200], 0],
    ['privacy', '/privacy', 'public', [200], 0],
    // associate (5) — login form on fresh jar; rest on associate session
    ['assoc-login', '/associate/login', 'public', [200], 0],
    ['assoc-dashboard', '/associate/dashboard', 'assoc', [200], 0],
    ['assoc-leads', '/associate/leads', 'assoc', [200], 0],
    ['assoc-genealogy', '/associate/genealogy', 'assoc', [200], 0],
    ['assoc-wallet', '/associate/wallet', 'assoc', [200], 0],
    // employee + customer AI coach (S547) - session jars
    ['assoc-ai-coach', '/associate/ai-coach', 'assoc', [200], 0, "AI Coach — APS Associate Portal"],
    ['employee-ai-coach', '/employee/ai-coach', 'employee', [200], 0, "AI Coach — APS Employee Portal"],
    ['customer-ai-assistant', '/user/ai-assistant', 'customer', [200], 0, 'APS AI Assistant'],
    // admin (7) — login form on fresh jar; rest on super_admin session
    ['admin-login', '/admin/login', 'public', [200], 0],
    ['admin-dashboard', '/admin/dashboard', 'admin', [200], 0],
    ['admin-plots', '/admin/plots', 'admin', [200], 0],
    ['admin-bookings', '/admin/bookings', 'admin', [200], 0],
    ['admin-payout-batches', '/admin/payout-batches', 'admin', [200], 0],
    ['admin-leads', '/admin/leads', 'admin', [200], 0],
    ['admin-users', '/admin/users', 'admin', [200], 0],
    ['admin-ai-voice-users', '/admin/voice-users', 'admin', [200], 0],
    ['admin-ai-voice-history', '/admin/voice-users/history', 'admin', [200], 0],
    ['admin-ai-voice-schedule', '/admin/voice-users/schedule', 'admin', [200], 0],
    ['admin-ai-voice-settings', '/admin/voice-users/settings', 'admin', [200], 0],
    // admin AI surface (S547) - 200 + content needle (S551): a 200-but-blank/error page must FAIL
    ['admin-ai-hub', '/admin/ai/hub', 'admin', [200], 0, 'AI Command Center'],
    ['admin-ai-morning-brief', '/admin/ai-morning-brief', 'admin', [200], 0, 'AI Morning Brief'],
    ['admin-ai-executive', '/admin/ai/executive-assistant', 'admin', [200], 0, 'APS AI Assistant'],
    ['admin-ai-management', '/admin/ai-management', 'admin', [200], 0, 'AI Management'],
    ['admin-ai-insights', '/admin/ai-analytics/insights', 'admin', [200], 0, 'AI Insights'],
    ['admin-ai-lead-scores', '/admin/ai-management/lead-scores', 'admin', [200], 0, 'AI Lead Scores'],
    ['admin-rag', '/admin/rag', 'admin', [200], 0, 'RAG Knowledge Base'],
    // apis (3) — expect HTTP 200 + JSON body
    ['api-health', '/api/health', 'public', [200], 1],
    ['api-colonies', '/api/v2/mobile/colonies', 'public', [200], 1],
    ['api-plots', '/api/v2/mobile/plots/all', 'public', [200], 1],
];

$pass = 0; $fail = 0; $rows = [];
echo "\n" . str_pad('ENDPOINT', 24) . str_pad('URL', 46) . str_pad('CODE', 7) . str_pad('TIME', 10) . "STATUS\n";
echo str_repeat('-', 100) . "\n";
function probeOnce(string $url, string $jar, array $expect, int $wantJson, ?string $needle): array {
    [$info, $body, $ms] = hit($url, $jar);
    $ok = in_array($info['code'], $expect, true) && $info['err'] === '' && $ms < 10000;
    $note = '';
    if ($ok && !empty($needle)) {
        if (stripos($body, $needle) === false) { $ok = false; $note = 'missing needle'; }
    }
    if ($ok && $wantJson) {
        $isJson = stripos($info['ctype'], 'json') !== false || (json_decode($body) !== null);
        if (!$isJson) { $ok = false; $note = 'non-JSON'; }
    }
    return [$ok, $note, $info, $ms];
}
foreach ($probes as $probe) {
    $label = $probe[0]; $path = $probe[1]; $jar = $probe[2]; $expect = $probe[3]; $wantJson = $probe[4]; $needle = $probe[5] ?? null;
    [$ok, $note, $info, $ms] = probeOnce($BASE . $path, $jars[$jar], $expect, $wantJson, $needle);
    if (!$ok) {
        // One retry: an occasional empty-body render (S492-class race) is transient; retry distinguishes that from a real failure.
        usleep(400000);
        [$ok2, $note2, $info2, $ms2] = probeOnce($BASE . $path, $jars[$jar], $expect, $wantJson, $needle);
        if ($ok2) { $ok = true; $note = 'FLAKY-RETRY'; $info = $info2; $ms = $ms2; } else { $note = $note2 !== '' ? $note2 : $note; }
    }
    if ($info['err'] !== '') $note = $info['err'];
    $ok ? $pass++ : $fail++;
    $rows[] = ['label' => $label, 'url' => $path, 'code' => $info['code'], 'ms' => $ms,
        'pass' => $ok, 'note' => $note, 'bytes' => $info['size']];
    printf("%-24s%-46s%-7d%-10s%s%s\n", $label, $path, $info['code'], $ms . 'ms',
        $ok ? c('PASS', 'green') : c('FAIL', 'red'), $note !== '' ? " ($note)" : '');
    usleep(150000); // gentle pacing
}

echo str_repeat('-', 100) . "\n";
printf(c("TOTAL: %d  PASSED: %d  FAILED: %d", $fail ? 'red' : 'green') . "\n", $pass + $fail, $pass, $fail);

$report = ['generated_at' => date('c'), 'base' => $BASE, 'total' => $pass + $fail,
    'passed' => $pass, 'failed' => $fail, 'overall' => $fail ? 'FAIL' : 'PASS', 'probes' => $rows];
$logDir = dirname(__DIR__) . '/storage/logs';
if (!is_dir($logDir)) @mkdir($logDir, 0775, true);
file_put_contents($logDir . '/smoke_report.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "JSON: storage/logs/smoke_report.json\n";
foreach ($jars as $j) @unlink($j);
exit($fail ? 1 : 0);

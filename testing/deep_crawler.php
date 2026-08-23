<?php
/**
 * DEEP COMPREHENSIVE CRAWLER v2
 * - Authenticated admin session (test_login=1)
 * - All admin sidebar routes (DB) + public pages
 * - Detects: HTTP errors, redirects-to-login, inline PHP errors in HTML body,
 *   SQLSTATE leaks, undefined vars/indices, empty content, broken sections
 */
$base = 'http://localhost/apsdreamhome';
$report = [];
$errors = [];

// ---------- helpers ----------
function fetchPage($url, $sid) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_HTTPHEADER => ["Cookie: PHPSESSID=$sid"],
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_TIMEOUT => 30,
    ]);
    $resp = curl_exec($ch);
    $hsize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $err = curl_error($ch);
    curl_close($ch);
    return [
        'code' => $code,
        'finalUrl' => $finalUrl,
        'body' => substr((string)$resp, $hsize),
        'curlErr' => $err,
    ];
}

function scanBody($body) {
    $issues = [];
    // Fatal / Parse / Uncaught = page-breaking
    if (preg_match('/(Fatal error|Parse error|Uncaught [A-Za-z]+Exception)/i', $body, $m)) {
        preg_match('/in\s+([^\s]+\.php)[^\n]*?(\d+)/', $body, $loc);
        $issues['FATAL'] = trim(($m[0] ?? '') . ' @ ' . ($loc[1] ?? '?') . ':' . ($loc[2] ?? '?'));
    }
    // Inline warnings/notices rendered into HTML
    if (preg_match_all('/(?:Warning|Deprecated|Notice)\s*:\s*[^\n<]{10,180}/i', $body, $ms)) {
        $uniq = array_unique(array_map('trim', $ms[0]));
        $issues['WARNINGS'] = array_slice($uniq, 0, 4);
    }
    // SQL errors leaking into output
    if (preg_match('/SQLSTATE\[[^\]]+\][^<\n]{0,160}/', $body, $m)) {
        $issues['SQL'] = trim($m[0]);
    }
    // Undefined variable/index/property
    if (preg_match_all('/Undefined (?:variable|array key|index|property|offset)[^\n<]{0,120}/i', $body, $ms)) {
        $issues['UNDEFINED'] = array_slice(array_unique(array_map('trim', $ms[0])), 0, 4);
    }
    // Laravel-ish trace dumps or raw exception pages
    if (preg_match('/Whoops|exception.*trace/i', $body)) $issues['TRACE'] = true;
    return $issues;
}

// ---------- Step 1: admin session ----------
$ch = curl_init("$base/admin/login?test_login=1");
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 5]);
$resp = curl_exec($ch);
$hsize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
curl_close($ch);
preg_match('/PHPSESSID=([a-zA-Z0-9,\-]+)/', substr((string)$resp, 0, $hsize), $sm);
$sid = $sm[1] ?? '';
if (!$sid) { die("NO SESSION\n"); }

$check = fetchPage("$base/admin/dashboard", $sid);
echo "Auth check /admin/dashboard: HTTP {$check['code']} size=" . strlen($check['body']) . "\n";
if ($check['finalUrl'] !== "$base/admin/dashboard" && strpos($check['body'], 'logout') === false) { die("AUTH FAILED\n"); }

// ---------- Step 2: build route list ----------
$routes = [];
// Admin menu from DB
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '2jcePXuNaOfEyo6I5wJVkG');
    foreach ($pdo->query("SELECT url FROM admin_menu_items WHERE url LIKE '/%' AND is_active=1") as $r) {
        $routes['admin:' . $r['url']] = $base . $r['url'];
    }
} catch (Exception $e) { echo "DB WARN: ", $e->getMessage(), "\n"; }

// Public + portal pages
$publicPaths = ['/', '/properties', '/services', '/tools-hub', '/projects', '/about', '/contact', '/team',
    '/blog', '/careers', '/mobile-app', '/partner-tools', '/login', '/register', '/auth/login', '/auth/air-login',
    '/user/dashboard', '/associate/dashboard', '/agent/dashboard', '/employee/notifications', '/compare'];
foreach ($publicPaths as $p) $routes['public:' . $p] = $base . $p;

$total = count($routes);
echo "Crawling $total routes...\n";
$i = 0;

foreach ($routes as $label => $url) {
    $i++;
    $res = fetchPage($url, $sid);
    $entry = ['http' => $res['code'], 'url' => $url];

    if ($res['curlErr']) {
        $entry['problem'] = 'CURL: ' . $res['curlErr'];
        $errors[] = $entry + ['label' => $label];
        echo "[$i/$total] ERR  $label -> curl fail\n";
        continue;
    }

    // bounced to login?
    $bounced = (strpos($res['finalUrl'], '/login') !== false || strpos($res['finalUrl'], '/auth') !== false)
        && strpos($label, 'public:/login') === false && strpos($label, 'public:/register') === false
        && strpos($label, 'public:/auth') === false;

    $bodyIssues = scanBody($res['body']);
    $entry['finalUrl'] = $res['finalUrl'];

    $flagged = false;
    if ($res['code'] >= 400) { $entry['problem'] = "HTTP {$res['code']}"; $flagged = true; }
    elseif ($bounced && strpos($label, 'admin:') === 0 && strpos($res['finalUrl'], '/admin/login') !== false) {
        $entry['problem'] = 'BOUNCED_TO_LOGIN'; $flagged = true;
    }
    if ($bodyIssues) { $entry['bodyIssues'] = $bodyIssues; $flagged = true; }
    if (strlen(trim($res['body'])) < 200 && $res['code'] == 200) { $entry['problem'] = ($entry['problem'] ?? '') . ' EMPTY_BODY'; $flagged = true; }

    if ($flagged) {
        $errors[] = $entry + ['label' => $label];
        echo "[$i/$total] !!   $label -> " . json_encode(array_intersect_key($entry, array_flip(['problem', 'bodyIssues']))) . "\n";
    } else {
        echo "[$i/$total] ok   $label\n";
    }
}

// ---------- Report ----------
file_put_contents(__DIR__ . '/deep_crawl_report.json', json_encode(['generated' => date('c'), 'total' => $total, 'issues' => $errors], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "\n=== DONE: $total crawled, " . count($errors) . " flagged ===\n";
echo "Report: testing/deep_crawl_report.json\n";

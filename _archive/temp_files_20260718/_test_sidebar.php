<?php
error_reporting(0);

$base = 'http://localhost/apsdreamhome';

// Get admin session
$ch = curl_init("$base/admin/login?test_login=1");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/_test_ck.txt');
curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/_test_ck.txt');
curl_exec($ch);
curl_close($ch);

// Get all sidebar URLs from DB
$ch = curl_init("$base/admin/sites");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/_test_ck.txt');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$html = curl_exec($ch);
curl_close($ch);

// Direct DB query for all unique URLs
$dbUrls = [];
$output = shell_exec('"C:\xampp\mysql\bin\mysql.exe" -u root -P 3307 apsdreamhome -N -e "SELECT DISTINCT url FROM admin_menu_items WHERE is_active = 1 AND url != \'\' ORDER BY url;"');
$urls = array_filter(array_map('trim', explode("\n", $output)));

echo "Found " . count($urls) . " unique sidebar URLs\n\n";

$results = ['ok' => [], 'redirect' => [], '404' => [], 'error' => [], 'login_redirect' => []];

foreach ($urls as $url) {
    if (empty($url)) continue;
    
    $fullUrl = $base . $url;
    $ch = curl_init($fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/_test_ck.txt');
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    
    $status = 'ok';
    if ($code == 200) {
        $status = 'ok';
    } elseif ($code >= 300 && $code < 400) {
        if (strpos($finalUrl, 'login') !== false || strpos($finalUrl, '/auth/') !== false) {
            $status = 'login_redirect';
        } else {
            $status = 'redirect';
        }
    } elseif ($code == 404) {
        $status = '404';
    } else {
        $status = 'error';
    }
    
    $results[$status][] = ['url' => $url, 'code' => $code, 'final' => $finalUrl];
    
    $icon = $status === 'ok' ? 'âœ…' : ($status === 'login_redirect' ? 'ðŸ”’' : ($status === '404' ? 'â�Œ' : 'âš ï¸�'));
    echo "$icon $code $url" . ($status !== 'ok' ? " -> $finalUrl" : "") . "\n";
}

echo "\n=== SUMMARY ===\n";
echo "âœ… OK: " . count($results['ok']) . "\n";
echo "ðŸ”’ Login redirect: " . count($results['login_redirect']) . "\n";
echo "â†—ï¸� Redirect: " . count($results['redirect']) . "\n";
echo "â�Œ 404: " . count($results['404']) . "\n";
echo "âš ï¸� Error: " . count($results['error']) . "\n";

// Save results
file_put_contents(__DIR__ . '/_sidebar_results.json', json_encode($results, JSON_PRETTY_PRINT));
echo "\nResults saved to _sidebar_results.json\n";?>
<?php
// Batch test all sidebar URLs
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome','root','');
$items = $db->query("SELECT id, name, url, section FROM admin_menu_items WHERE is_active = 1 ORDER BY section, order_index")->fetchAll(PDO::FETCH_ASSOC);

echo "Total active menu items: " . count($items) . "\n\n";

// First login as admin
$ch = curl_init('http://localhost/apsdreamhome/admin/login?test_login=1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_exec($ch);
curl_close($ch);

// Verify login
$ch = curl_init('http://localhost/apsdreamhome/admin/dashboard');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Admin login: " . ($code == 200 ? "OK" : "FAIL ($code)") . "\n\n";

// Test URLs - skip POST-only/param URLs
$skip = ['/admin/association', '/admin/commission/associate/structure'];
$paramPatterns = ['/{id}', '/create', '/{id}/edit'];

$results = ['200'=>[], '302'=>[], '403'=>[], '404'=>[], '500'=>[], 'other'=>[]];
$tested = 0;
$skipped = 0;

foreach ($items as $i) {
    $url = $i['url'];
    
    // Skip POST-only patterns (create routes are GET+POST, so test them)
    // Also skip URLs with {id} params (separately tested)
    if (preg_match('/\{[^}]+\}/', $url) || in_array($url, $skip)) {
        $skipped++;
        continue;
    }
    
    $tested++;
    $fullUrl = 'http://localhost/apsdreamhome' . $url;
    $ch = curl_init($fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    
    if ($code >= 200 && $code < 300) $results['200'][] = $url;
    elseif ($code == 302) $results['302'][] = $url;
    elseif ($code == 403) $results['403'][] = $url;
    elseif ($code == 404) $results['404'][] = "$url (-> $effUrl)";
    elseif ($code >= 500) $results['500'][] = "$url ($code)";
    else $results['other'][] = "$url ($code)";
}

// Print results
echo "=== RESULTS ===\n";
echo "Tested: $tested, Skipped (param): $skipped\n\n";

echo "--- 200 OK: " . count($results['200']) . " ---\n";
echo "--- 302 Redirect: " . count($results['302']) . " ---\n";
foreach ($results['302'] as $u) echo "  $u\n";
if (count($results['403'])) {
    echo "\n--- 403 Forbidden: " . count($results['403']) . " ---\n";
    foreach ($results['403'] as $u) echo "  $u\n";
}
if (count($results['404'])) {
    echo "\n--- 404 NOT FOUND: " . count($results['404']) . " ---\n";
    foreach ($results['404'] as $u) echo "  $u\n";
}
if (count($results['500'])) {
    echo "\n--- 500 ERROR: " . count($results['500']) . " ---\n";
    foreach ($results['500'] as $u) echo "  $u\n";
}
if (count($results['other'])) {
    echo "\n--- OTHER: " . count($results['other']) . " ---\n";
    foreach ($results['other'] as $u) echo "  $u\n";
}

// Cleanup
unlink('cookies.txt');

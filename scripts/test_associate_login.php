<?php
$ch = curl_init('http://localhost/apsdreamhome/associate/login');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'email' => 'associate@apsdreamhome.com',
    'password' => 'Aps@2026',
    'csrf_token' => ''  // will need actual token
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
curl_close($ch);

echo "Code: $code\n";
echo "Final URL: $url\n";
echo "Body length: " . strlen($body) . "\n";

// Check if redirected to dashboard
if (strpos($url, '/associate/dashboard') !== false) {
    echo "✅ Redirected to dashboard\n";
} elseif (strpos($url, '/associate/login') !== false) {
    echo "❌ Stuck on login page\n";
} else {
    echo "? Redirected to: $url\n";
}
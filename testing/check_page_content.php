<?php
$cookieFile = __DIR__ . '/cookie.txt';

$ch = curl_init("http://localhost/apsdreamhome/admin/dashboard");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$body = curl_exec($ch);
curl_close($ch);

if (strpos($body, 'id="page-content"') !== false) {
    echo "SUCCESS: page-content is present!\n";
} else {
    echo "FAILED: page-content is MISSING!\n";
    // Check if it's the login page
    if (strpos($body, 'login') !== false) {
        echo "It seems it's the login page.\n";
    }
}

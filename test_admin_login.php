<?php
$BASE = 'http://localhost/apsdreamhome';
$jar = 'C:/temp/test_admin_' . date('YmdHis') . '.jar';
if (!is_dir('C:/temp')) @mkdir('C:/temp', 0777, true);

// Step 1: GET admin/login with test_login=1
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $BASE . '/admin/login?test_login=1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $jar);
curl_setopt($ch, CURLOPT_COOKIEJAR, $jar);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "Step 1 - GET /admin/login?test_login=1: HTTP $code\n";
if (preg_match('/Location:\s*(\S+)/', $body, $m)) {
    echo "  Redirect to: " . $m[1] . "\n";
}
curl_close($ch);

// Check cookies in jar
echo "\nCookies after step 1:\n";
if (file_exists($jar)) {
    echo file_get_contents($jar) . "\n";
}

// Step 2: GET admin/mlm/withdrawals with the session
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $BASE . '/admin/mlm/withdrawals');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $jar);
curl_setopt($ch, CURLOPT_COOKIEJAR, $jar);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "Step 2 - GET /admin/mlm/withdrawals: HTTP $code\n";
if (preg_match('/Location:\s*(\S+)/', $body, $m)) {
    echo "  Redirect to: " . $m[1] . "\n";
}
curl_close($ch);
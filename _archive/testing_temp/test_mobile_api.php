<?php
/**
 * Mobile API V2 — JWT Auth Test Suite
 * 6 test cases:
 *  1. Login with valid credentials → returns JWT
 *  2. Login with invalid credentials → 401
 *  3. Profile with valid token → 200
 *  4. Profile with invalid token → 401
 *  5. Dashboard with valid token → returns stats
 *  6. Rate limit (61st request) → 429
 *
 * Run: php testing/test_mobile_api.php
 */

$BASE_URL = getenv('MOBILE_API_BASE_URL') ?: 'http://localhost/apsdreamhome';
$EMAIL    = getenv('MOBILE_TEST_EMAIL') ?: 'customer1@apsdreamhome.com';
$PASSWORD = getenv('MOBILE_TEST_PASSWORD') ?: 'Aps@2026';

$pass = 0;
$fail = 0;
$failures = [];

function check(string $name, bool $cond, string $detail = ''): void
{
    global $pass, $fail, $failures;
    if ($cond) {
        $pass++;
        echo "  PASS  $name\n";
    } else {
        $fail++;
        $failures[] = "$name — $detail";
        echo "  FAIL  $name — $detail\n";
    }
}

function httpRequest(string $method, string $url, ?array $body = null, ?string $bearer = null, bool $captureStatus = false)
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_TIMEOUT        => 10,
    ]);
    $headers = ['Content-Type: application/json', 'Accept: application/json'];
    if ($bearer) {
        $headers[] = 'Authorization: Bearer ' . $bearer;
    }
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $resp = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    $rawHeaders = substr($resp, 0, $headerSize);
    $rawBody    = substr($resp, $headerSize);
    $json = json_decode($rawBody, true);
    return [
        'status'  => $status,
        'headers' => $rawHeaders,
        'body'    => $rawBody,
        'json'    => $json,
    ];
}

echo "Base URL: $BASE_URL\n";
echo "Test user: $EMAIL\n\n";

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
    $pdo->exec("DELETE FROM rate_limits WHERE rate_key IN ('mobile_user_3','mobile_login_3','mobile_3')");
    $pdo->exec("DELETE FROM api_tokens WHERE user_id = 3");
} catch (\Throwable $e) {
    // ignore
}

echo "== Test 1: Login with valid creds ==\n";
$r = httpRequest('POST', "$BASE_URL/api/mobile/auth/login", [
    'email'    => $EMAIL,
    'password' => $PASSWORD,
]);
$token = $r['json']['access_token'] ?? null;
$refresh = $r['json']['refresh_token'] ?? null;
check('1.1 login returns 200',          $r['status'] === 200, "got {$r['status']}");
check('1.2 returns access_token',       is_string($token) && strlen($token) > 50, 'token missing or too short');
check('1.3 returns refresh_token',      is_string($refresh) && strlen($refresh) > 20, 'refresh missing');
check('1.4 token has 3 JWT parts',      is_string($token) && substr_count($token, '.') === 2, 'malformed JWT');
check('1.5 expires_in = 86400',          ($r['json']['expires_in'] ?? null) === 86400, 'wrong expiry');

echo "\n== Test 2: Login with invalid creds ==\n";
$r = httpRequest('POST', "$BASE_URL/api/mobile/auth/login", [
    'email'    => $EMAIL,
    'password' => 'wrong_password_xxx',
]);
check('2.1 invalid login returns 401', $r['status'] === 401, "got {$r['status']}");
check('2.2 no access_token returned',  empty($r['json']['access_token'] ?? null), 'token leaked');
check('2.3 error message present',     !empty($r['json']['error'] ?? null), 'no error message');

echo "\n== Test 3: Profile with valid token ==\n";
$r = httpRequest('GET', "$BASE_URL/api/mobile/profile", null, $token);
check('3.1 profile returns 200',        $r['status'] === 200, "got {$r['status']}");
check('3.2 data has user id',           !empty($r['json']['data']['id']), 'missing id');
check('3.3 data has email',             !empty($r['json']['data']['email']), 'missing email');
check('3.4 data has role',              !empty($r['json']['data']['role']), 'missing role');

echo "\n== Test 4: Profile with invalid token ==\n";
$r = httpRequest('GET', "$BASE_URL/api/mobile/profile", null, 'invalid.token.here');
check('4.1 invalid profile returns 401', $r['status'] === 401, "got {$r['status']}");
check('4.2 error message present',       !empty($r['json']['error'] ?? null), 'no error message');

echo "\n== Test 5: Dashboard with valid token ==\n";
$r = httpRequest('GET', "$BASE_URL/api/mobile/dashboard", null, $token);
check('5.1 dashboard returns 200',      $r['status'] === 200, "got {$r['status']}");
check('5.2 has property_count',         isset($r['json']['data']['property_count']), 'missing property_count');
check('5.3 has lead_count',             isset($r['json']['data']['lead_count']), 'missing lead_count');
check('5.4 has unread_notifications',   isset($r['json']['data']['unread_notifications']), 'missing unread_notifications');

echo "\n== Test 6: Rate limit (61st request returns 429) ==\n";
$hit429 = false;
$lastStatus = 0;
$totalRequests = 65;
for ($i = 1; $i <= $totalRequests; $i++) {
    $r = httpRequest('GET', "$BASE_URL/api/mobile/dashboard", null, $token);
    $lastStatus = $r['status'];
    if ($r['status'] === 429) {
        $hit429 = true;
        echo "  -> 429 hit on request #{$i}\n";
        break;
    }
}
check('6.1 rate limit returned 429 at least once', $hit429, "no 429 across {$totalRequests} requests; last status {$lastStatus}");

echo "\n== Test 7: Refresh token ==\n";
if ($refresh) {
    $r = httpRequest('POST', "$BASE_URL/api/mobile/auth/refresh", [
        'refresh_token' => $refresh,
    ]);
    check('7.1 refresh returns 200',     $r['status'] === 200, "got {$r['status']}");
    check('7.2 new access_token returned', !empty($r['json']['access_token'] ?? null), 'no new token');
} else {
    check('7.1 refresh test skipped (no refresh_token)', false, 'no refresh_token from test 1');
}

echo "\n=========================================\n";
echo "PASSED: $pass\n";
echo "FAILED: $fail\n";
if ($fail > 0) {
    echo "\nFailures:\n";
    foreach ($failures as $f) {
        echo "  - $f\n";
    }
    exit(1);
}
echo "All tests passed.\n";
exit(0);

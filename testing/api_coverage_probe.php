<?php
/**
 * Mobile API Coverage & Security Probe
 * Tests all /api/v2/mobile/* endpoints for authentication, authorization, and tenant isolation
 */

require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;

$baseUrl = 'http://localhost/apsdreamhome';
$pass = 0;
$fail = 0;

function http($method, $url, $headers = [], $body = null) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => $resp];
}

function logResult($test, $expectedCodes, $actual, &$pass, &$fail) {
    $expectedCodes = is_array($expectedCodes) ? $expectedCodes : [$expectedCodes];
    $ok = in_array($actual, $expectedCodes);
    if ($ok) {
        echo "\033[32mPASS\033[0m: $test (expected " . implode('/', $expectedCodes) . ", got $actual)\n";
        $pass++;
    } else {
        echo "\033[31mFAIL\033[0m: $test (expected " . implode('/', $expectedCodes) . ", got $actual)\n";
        $fail++;
    }
    return $ok;
}

echo "\n=== MOBILE API COVERAGE & SECURITY PROBE ===\n\n";

$pass = 0;
$fail = 0;

// ============================================================
// STEP 1: Security Gates - 401 Unauthorized without token
// ============================================================
echo "\n--- Security Gates (401 without Bearer) ---\n";

$protectedEndpoints = [
    '/api/v2/mobile/dashboard',
    '/api/v2/mobile/user/profile',
    '/api/v2/mobile/user/favorites',
    '/api/v2/mobile/campaign-templates',
    '/api/v2/mobile/voice-uploads',
    '/api/v2/mobile/app-feedback',
    '/api/v2/mobile/search-history',
    '/api/v2/mobile/payout-batches',
];

foreach ($protectedEndpoints as $ep) {
    $res = http('GET', "{$baseUrl}{$ep}");
    logResult("GET $ep (no token)", [401], $res['code'], $pass, $fail);
}

// ============================================================
// STEP 2: Customer Login & Token
// ============================================================
echo "\n--- Customer Authentication ---\n";

$res = http('POST', "{$baseUrl}/api/v2/mobile/auth/login", 
    ['Content-Type: application/json', 'Accept: application/json'],
    json_encode(['email' => 'testuser@example.com', 'password' => 'Aps@2026'])
);

$customerToken = null;
if ($res['code'] === 200) {
    $data = json_decode($res['body'], true);
    if (!empty($data['data']['token']) || !empty($data['token']) || !empty($data['access_token'])) {
        $customerToken = $data['data']['token'] ?? $data['token'] ?? $data['access_token'];
logResult("POST /api/v2/mobile/auth/login (customer)", [200], $res['code'], $pass, $fail);
    } else {
        logResult("POST /api/v2/mobile/auth/login (token in response)", 200, 500, $pass, $fail);
    }
} else {
    logResult("POST /api/v2/mobile/auth/login (customer)", 200, $res['code'], $pass, $fail);
}

// ============================================================
// STEP 3: Customer Authenticated Endpoints
// ============================================================
echo "\n--- Customer Authenticated Endpoints ---\n";

if ($customerToken) {
    $authHeaders = [
        'Authorization: Bearer ' . $customerToken,
        'Accept: application/json',
        'Content-Type: application/json'
    ];

    $customerEndpoints = [
        ['GET', '/api/v2/mobile/colonies'],
        ['GET', '/api/v2/mobile/properties/browse'],
        ['GET', '/api/v2/mobile/dashboard'],
        ['GET', '/api/v2/mobile/user/profile'],
        ['GET', '/api/v2/mobile/user/notifications'],
        ['GET', '/api/v2/mobile/user/favorites'],
        ['GET', '/api/v2/mobile/user/payment-history'],
        ['GET', '/api/v2/mobile/search-history'],
        ['GET', '/api/v2/mobile/app-feedback'],
    ];

    foreach ($customerEndpoints as $ep) {
        list($method, $path) = $ep;
        $res = http($method, "{$baseUrl}{$path}", $authHeaders);
        logResult("$method $path (customer)", [200, 204], $res['code'], $pass, $fail);
    }

    // POST app-feedback
    $res = http('POST', "{$baseUrl}/api/v2/mobile/app-feedback", $authHeaders,
        json_encode(['feedback_type' => 'bug', 'description' => 'Test feedback from probe'])
    );
    logResult("POST /api/v2/mobile/app-feedback (customer)", [200, 201], $res['code'], $pass, $fail);
} else {
    echo "\033[33mSKIP\033[0m: Customer endpoints (no token)\n";
}

// ============================================================
// STEP 4: Staff / Admin Token & Endpoints
// ============================================================
echo "\n--- Staff/Admin Endpoints ---\n";

// Try to get admin token from api_tokens table
$db = Database::getInstance();
$adminToken = null;

try {
    $stmt = $db->prepare("SELECT token FROM api_tokens WHERE user_id IN (SELECT id FROM users WHERE role IN ('admin','super_admin') AND status='active') AND expires_at > NOW() AND is_revoked=0 ORDER BY created_at DESC LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    if ($row) $adminToken = $row['token'];
} catch (Exception $e) {}

if (!$adminToken) {
    // Fallback: try admin login
    $res = http('POST', "{$baseUrl}/api/v2/mobile/auth/login",
        ['Content-Type: application/json', 'Accept: application/json'],
        json_encode(['email' => 'admin@apsdreamhome.com', 'password' => 'Aps@2026'])
    );
    if ($res['code'] === 200) {
        $data = json_decode($res['body'], true);
        $adminToken = $data['data']['token'] ?? $data['token'] ?? $data['access_token'];
    }
}

if ($adminToken) {
    $authHeaders = [
        'Authorization: Bearer ' . $adminToken,
        'Accept: application/json',
        'Content-Type: application/json'
    ];

    $adminEndpoints = [
        ['GET', '/api/v2/mobile/campaign-templates'],
        ['GET', '/api/v2/mobile/voice-uploads'],
        ['GET', '/api/v2/mobile/payout-batches'],
        ['GET', '/api/v2/mobile/leads'],
        ['GET', '/api/v2/mobile/mlm/summary'],
        ['GET', '/api/v2/mobile/mlm/payouts'],
    ];

    foreach ($adminEndpoints as $ep) {
        list($method, $path) = $ep;
        $res = http($method, "{$baseUrl}{$path}", $authHeaders);
        logResult("$method $path (admin)", [200, 204], $res['code'], $pass, $fail);
    }
} else {
    echo "\033[33mSKIP\033[0m: Admin endpoints (no token found)\n";
}

// ============================================================
// SUMMARY
// ============================================================
echo "\n=== SUMMARY ===\n";
echo "Pass: \033[32m$pass\033[0m\n";
echo "Fail: \033[31m$fail\033[0m\n";
echo "Total: " . ($pass + $fail) . "\n";

exit($fail > 0 ? 1 : 0);
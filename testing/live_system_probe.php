<?php
/**
 * Comprehensive Live Testing Script: Registration, Logins, Associate MLM, Admin Dashboard
 */

$baseUrl = 'http://localhost/apsdreamhome';
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=apsdreamhome', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

echo "========================================================================\n";
echo "      APS DREAM HOME — LIVE SYSTEM PROBE (AUTH, MLM & ADMIN)\n";
echo "========================================================================\n\n";

function request($url, $method = 'GET', $data = null, $isJson = false, $sessionFile = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    if ($sessionFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $sessionFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $sessionFile);
    }
    
    $headers = [];
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($isJson) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
    }
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    $body = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $lastUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    return ['status' => $http, 'body' => $body, 'effective_url' => $lastUrl];
}

// 1. REGISTRATION PAGES & VALIDATION
echo "1. REGISTRATION PAGES & VALIDATION AUDIT\n";
$customerReg = request("$baseUrl/register");
echo "   - Customer Registration GET /register: HTTP {$customerReg['status']}" . ($customerReg['status'] === 200 ? " [OK]" : " [FAIL]") . "\n";

$associateReg = request("$baseUrl/associate/register");
echo "   - Associate Registration GET /associate/register: HTTP {$associateReg['status']}" . ($associateReg['status'] === 200 ? " [OK]" : " [FAIL]") . "\n";

// Check if sponsor referral code lookup works in associate registration
$hasSponsorField = (strpos($associateReg['body'], 'sponsor') !== false || strpos($associateReg['body'], 'referral') !== false);
echo "   - Associate Registration Sponsor/Referral Field: " . ($hasSponsorField ? "PRESENT [OK]" : "MISSING") . "\n\n";

// 2. MOBILE API LOGIN TEST
echo "2. MOBILE API AUTHENTICATION TEST (/api/v2/mobile/auth/login)\n";
$mobileLogin = request("$baseUrl/api/v2/mobile/auth/login", 'POST', [
    'email' => 'testuser@example.com',
    'password' => 'Aps@2026'
], true);
$mJson = json_decode($mobileLogin['body'], true);
if ($mobileLogin['status'] === 200 && !empty($mJson['data']['token'])) {
    echo "   - Customer Mobile Login: HTTP 200 [SUCCESS] User: {$mJson['data']['user']['name']} (Role: {$mJson['data']['user']['role']})\n";
    $customerToken = $mJson['data']['token'];
} else {
    echo "   - Customer Mobile Login: HTTP {$mobileLogin['status']} [FAILED]\n";
}
echo "\n";

// 3. ASSOCIATE WEB LOGIN & MLM GENEALOGY AUDIT
echo "3. ASSOCIATE WEB LOGIN & MLM GENEALOGY AUDIT\n";
$assocCookie = __DIR__ . '/_assoc_cookie.txt';
@unlink($assocCookie);

// Associate Login Page
$assocLoginPage = request("$baseUrl/associate/login", 'GET', null, false, $assocCookie);
echo "   - Associate Login Page GET /associate/login: HTTP {$assocLoginPage['status']}\n";

// Fetch an active associate from DB
$assocUser = $pdo->query("SELECT id, email, password FROM users WHERE role = 'associate' AND status = 'active' LIMIT 1")->fetch();
if ($assocUser) {
    echo "   - Active Associate in DB: ID {$assocUser['id']} ({$assocUser['email']})\n";
    
    // Check MLM Profile
    $profile = $pdo->query("SELECT * FROM mlm_profiles WHERE user_id = {$assocUser['id']}")->fetch();
    if ($profile) {
        echo "   - MLM Profile: Rank '{$profile['current_level']}', Team Size: {$profile['total_team_size']}, Total Comm: ₹" . number_format($profile['total_commission'], 2) . "\n";
    } else {
        echo "   - MLM Profile: NOT FOUND in mlm_profiles\n";
    }
    
    // Check Network Tree node
    $treeNode = $pdo->query("SELECT * FROM mlm_network_tree WHERE associate_id = {$assocUser['id']}")->fetch();
    if ($treeNode) {
        echo "   - Genealogy Tree Node: Level {$treeNode['level']}, Position '{$treeNode['position']}', Sponsor ID {$treeNode['sponsor_id']}\n";
    }
    
    // Check Downline
    $downline = $pdo->query("SELECT count(*) FROM mlm_network_tree WHERE sponsor_id = {$assocUser['id']} OR parent_id = {$assocUser['id']}")->fetchColumn();
    echo "   - Active Downline Members in Tree: $downline\n";
    
    // Check Web Genealogy Page access
    $genealogyPage = request("$baseUrl/associate/genealogy", 'GET', null, false, $assocCookie);
    echo "   - Web GET /associate/genealogy: HTTP {$genealogyPage['status']} (Effective URL: {$genealogyPage['effective_url']})\n";
}
@unlink($assocCookie);
echo "\n";

// 4. ADMIN DASHBOARD & MLM MANAGEMENT
echo "4. ADMIN DASHBOARD & MLM MANAGEMENT AUDIT\n";
$adminCookie = __DIR__ . '/_admin_cookie.txt';
@unlink($adminCookie);

// Admin Login Page
$adminLogin = request("$baseUrl/admin/login", 'GET', null, false, $adminCookie);
echo "   - Admin Login Page GET /admin/login: HTTP {$adminLogin['status']} [OK]\n";

// Admin Endpoints
$adminRoutes = [
    'Dashboard'         => '/admin/dashboard',
    'MLM Genealogy'     => '/admin/mlm/genealogy',
    'MLM Commissions'   => '/admin/mlm/commissions',
    'Plots Management'  => '/admin/plots',
    'Colonies Pipeline' => '/admin/colonies',
    'User Management'   => '/admin/users'
];

foreach ($adminRoutes as $title => $uri) {
    $res = request("$baseUrl$uri", 'GET', null, false, $adminCookie);
    echo sprintf("   - %-20s GET %-25s: HTTP %d\n", $title, $uri, $res['status']);
}

@unlink($adminCookie);

echo "\n========================================================================\n";
echo "                         PROBE COMPLETE\n";
echo "========================================================================\n";

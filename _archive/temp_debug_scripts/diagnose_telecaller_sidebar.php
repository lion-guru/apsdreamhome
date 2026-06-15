<?php
/**
 * Diagnostic: Simulate telecaller login → sidebar render to find the real role
 */
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

function httpGetWithCookie($url, $cookie) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIE => "PHPSESSID=$cookie",
        CURLOPT_COOKIEFILE => '',
        CURLOPT_HEADER => false,
        CURLOPT_NOBODY => false,
        CURLOPT_TIMEOUT => 10,
    ]);
    $body = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return ['status' => $info['http_code'], 'body' => $body, 'size' => strlen($body)];
}

$baseUrl = 'http://localhost/apsdreamhome';

// Step 1: Login as telecaller (test_login=3)
$loginRaw = httpGetWithCookie("$baseUrl/admin/login?test_login=3", '');
preg_match('/PHPSESSID=([a-f0-9]+)/', $loginRaw['body'] ?? '', $m);
if (!$m) {
    // Try to extract from response headers
    $ch = curl_init("$baseUrl/admin/login?test_login=3");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER => true,
        CURLOPT_NOBODY => false,
        CURLOPT_TIMEOUT => 10,
    ]);
    $resp = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    preg_match_all('/Set-Cookie: PHPSESSID=([a-f0-9]+)/', $resp, $m2);
    $cookie = $m2[1][0] ?? null;
    echo "Login response status: {$info['http_code']}\n";
} else {
    $cookie = $m[1];
}
echo "Session cookie: {$cookie}\n";

if (!$cookie) {
    echo "ERROR: No session cookie. Trying direct approach...\n";
    // Direct curl with cookie jar
    $tmpCookie = tempnam(sys_get_temp_dir(), 'aps');
    $ch = curl_init("$baseUrl/admin/login?test_login=3");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEJAR => $tmpCookie,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 10,
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    $cookieFile = file_get_contents($tmpCookie);
    echo "Cookie file content: " . substr($cookieFile, 0, 200) . "\n";
    unlink($tmpCookie);
    exit(1);
}

// Step 2: Hit /admin/dashboard and extract sidebar HTML
$dash = httpGetWithCookie("$baseUrl/admin/dashboard", $cookie);
echo "Dashboard status: {$dash['status']}, size: {$dash['size']}\n";

// Extract sidebar section
if (preg_match('/sidebar-nav[\s\S]*?<\/ul>/', $dash['body'], $sidebarMatch)) {
    $sidebar = $sidebarMatch[0];
    $linkCount = preg_match_all('/sidebar-link/', $sidebar);
    echo "Sidebar links found: {$linkCount}\n";
    
    // Extract role title
    if (preg_match('/<h[1-6][^>]*>(.*?)<\/h[1-6]>/i', $dash['body'], $titleMatch)) {
        echo "Page title: {$titleMatch[1]}\n";
    }
    
    // Check if "Telecaller Dashboard" or "Admin Dashboard" appears
    if (stripos($dash['body'], 'Telecaller Dashboard') !== false) {
        echo "PAGE TITLE: Telecaller Dashboard detected\n";
    } elseif (stripos($dash['body'], 'Super Admin Dashboard') !== false) {
        echo "PAGE TITLE: Super Admin Dashboard detected\n";
    } elseif (stripos($dash['body'], 'Admin Dashboard') !== false) {
        echo "PAGE TITLE: Admin Dashboard detected\n";
    }
} else {
    echo "No sidebar-nav found in response\n";
    // Check first 2000 chars
    echo "First 500 chars: " . substr($dash['body'], 0, 500) . "\n";
}

// Step 3: Simulate what AdminMenuService does — set session and run query directly
echo "\n=== DIRECT ROLE CHECK ===\n";
$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Check what role the test user has in DB
$stmt = $pdo->prepare("SELECT id, email, role FROM users WHERE id = ? OR email = ?");
$stmt->execute([69, 'telecaller@test.com']);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
echo "DB user: " . json_encode($user) . "\n";

// Check what admin_login does for test_login=3
// The admin login queries users WHERE id=? or email=? based on $_POST['identity']
// For test_login=3, it likely fetches by id
$stmt2 = $pdo->prepare("SELECT id, email, role, status FROM users WHERE role = 'telecaller' LIMIT 5");
$stmt2->execute();
$telecallers = $stmt2->fetchAll(PDO::FETCH_ASSOC);
echo "All users with role='telecaller':\n";
foreach ($telecallers as $tc) {
    echo "  ID={$tc['id']} email={$tc['email']} role={$tc['role']} status={$tc['status']}\n";
}

// Also check users with role LIKE '%telecaller%'
$stmt3 = $pdo->prepare("SELECT id, email, role, status FROM users WHERE role LIKE '%telecaller%' LIMIT 10");
$stmt3->execute();
$allTc = $stmt3->fetchAll(PDO::FETCH_ASSOC);
echo "All users with role LIKE %telecaller%: " . count($allTc) . "\n";
foreach ($allTc as $tc) {
    echo "  ID={$tc['id']} email={$tc['email']} role={$tc['role']} status={$tc['status']}\n";
}

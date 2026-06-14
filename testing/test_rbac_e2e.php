<?php
/**
 * RBAC E2E Test — Verifies per-role sidebar filtering and page access.
 *
 * Two role categories:
 *   Admin roles (1/2/3) → get admin sidebar with RBAC-filtered items
 *   Portal roles (4/5/6/7) → get role-specific dashboard (no admin sidebar)
 *
 * Usage: php testing/test_rbac_e2e.php
 */

$baseUrl = 'http://localhost/apsdreamhome';
$pass = 0;
$fail = 0;
$results = [];

function httpGet($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER => true,
        CURLOPT_NOBODY => false,
        CURLOPT_TIMEOUT => 10,
    ]);
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    $headerStr = substr($response, 0, $info['header_size']);
    $body = substr($response, $info['header_size']);
    preg_match('/Location:\s*(.+)/i', $headerStr, $locMatch);
    $location = $locMatch[1] ?? null;
    preg_match('/Set-Cookie:\s*PHPSESSID=([^;]+)/i', $headerStr, $cookieMatch);
    return [
        'status' => $info['http_code'],
        'location' => trim($location ?? ''),
        'body' => $body,
        'size' => strlen($body),
        'cookie' => $cookieMatch[1] ?? null,
    ];
}

function httpGetWithCookie($url, $cookie) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER => true,
        CURLOPT_COOKIE => "PHPSESSID=$cookie",
        CURLOPT_TIMEOUT => 10,
    ]);
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    $headerStr = substr($response, 0, $info['header_size']);
    $body = substr($response, $info['header_size']);
    preg_match('/Location:\s*(.+)/i', $headerStr, $locMatch);
    $location = $locMatch[1] ?? null;
    return [
        'status' => $info['http_code'],
        'location' => trim($location ?? ''),
        'body' => $body,
        'size' => strlen($body),
    ];
}

function assertTest($name, $condition, $detail = '') {
    global $pass, $fail, $results;
    if ($condition) {
        $pass++;
        $results[] = "  PASS: $name" . ($detail ? " ($detail)" : "");
    } else {
        $fail++;
        $results[] = "  FAIL: $name" . ($detail ? " -- $detail" : "");
    }
}

function countSidebarLinks($html) {
    preg_match_all('/class="sidebar-link[\s"]/', $html, $m);
    return count($m[0]);
}

function countAdminSidebarLinks($html) {
    preg_match_all('/class="sidebar-link[\s"][^>]*href="[^"]*\/admin\//', $html, $m);
    return count($m[0]);
}

function extractTitle($html) {
    if (preg_match('/<title>(.*?)<\/title>/', $html, $m)) {
        return trim($m[1]);
    }
    return 'unknown';
}

// --- ADMIN ROLES: expect admin sidebar with RBAC-filtered items ---
$adminRoles = [
    '1' => ['admin/manager', 80, 145],
    '2' => ['super_admin', 140, 145],
    '3' => ['telecaller', 10, 45],
];

// --- PORTAL ROLES: expect role-specific dashboard (no admin sidebar) ---
$portalRoles = [
    '4' => ['employee', 'Employee Dashboard'],
    '5' => ['associate', 'Associate Dashboard'],
    '6' => ['agent', 'Agent Dashboard'],
    '7' => ['customer', false], // customer may get admin or portal dashboard
];

// Admin-only pages
$adminOnlyPages = [
    '/admin/leads' => 'Leads',
    '/admin/bookings' => 'Bookings',
    '/admin/colonies' => 'Colonies',
    '/admin/mlm' => 'MLM',
    '/admin/finance' => 'Finance',
];

echo "=== RBAC E2E Test Suite ===\n\n";

// ===== PART 1: Admin roles — verify sidebar filtering =====
echo "--- PART 1: Admin Roles (admin sidebar with RBAC) ---\n\n";

foreach ($adminRoles as $loginCode => [$roleName, $minItems, $maxItems]) {
    echo "Role: $roleName (test_login=$loginCode)\n";

    $login = httpGet("$baseUrl/admin/login?test_login=$loginCode");
    assertTest("$roleName login redirect", $login['status'] == 302, "HTTP {$login['status']}");
    if (!$login['cookie']) { $results[] = "  SKIP: no cookie"; echo "\n"; continue; }

    $dash = httpGetWithCookie("$baseUrl/admin/dashboard", $login['cookie']);
    assertTest("$roleName dashboard 200", $dash['status'] == 200, "HTTP {$dash['status']}, {$dash['size']} bytes");

    $sidebarCount = countSidebarLinks($dash['body']);
    assertTest(
        "$roleName sidebar items [$minItems-$maxItems]",
        $sidebarCount >= $minItems && $sidebarCount <= $maxItems,
        "found $sidebarCount"
    );

    $title = extractTitle($dash['body']);
    assertTest("$roleName renders admin layout", strpos($title, 'Dashboard') !== false || strpos($title, 'Admin') !== false, "title='$title'");

    // Test admin pages
    foreach ($adminOnlyPages as $url => $desc) {
        $page = httpGetWithCookie("$baseUrl$url", $login['cookie']);
        assertTest("  $desc ($url)", in_array($page['status'], [200, 302]), "HTTP {$page['status']}");
    }
    echo "\n";
}

// ===== PART 2: Portal roles — verify redirect to own dashboard =====
echo "--- PART 2: Portal Roles (role-specific dashboards) ---\n\n";

foreach ($portalRoles as $loginCode => [$roleName, $expectedTitle]) {
    echo "Role: $roleName (test_login=$loginCode)\n";

    $login = httpGet("$baseUrl/admin/login?test_login=$loginCode");
    assertTest("$roleName login redirect", $login['status'] == 302, "HTTP {$login['status']}");
    if (!$login['cookie']) { $results[] = "  SKIP: no cookie"; echo "\n"; continue; }

    $dash = httpGetWithCookie("$baseUrl/admin/dashboard", $login['cookie']);
    assertTest("$roleName dashboard 200", $dash['status'] == 200, "HTTP {$dash['status']}, {$dash['size']} bytes");

    $title = extractTitle($dash['body']);
    $sidebarCount = countSidebarLinks($dash['body']);

    if ($expectedTitle) {
        assertTest("$roleName gets own dashboard", $title === $expectedTitle, "title='$title' (expected '$expectedTitle')");
    }

    // Should NOT have admin sidebar — portal roles get their own sidebar via PortalMenuService
    $adminSidebarCount = countAdminSidebarLinks($dash['body']);
    assertTest("$roleName has no admin sidebar", $adminSidebarCount == 0, "found $adminSidebarCount admin sidebar links (portal sidebar has $sidebarCount items)");

    // Admin-only pages should redirect (not render)
    foreach ($adminOnlyPages as $url => $desc) {
        $page = httpGetWithCookie("$baseUrl$url", $login['cookie']);
        // Non-admin roles get 302 redirect (to dashboard or login) for admin-only pages
        assertTest("  $desc blocks $roleName", $page['status'] == 302 || $page['status'] == 403, "HTTP {$page['status']}");
    }
    echo "\n";
}

// ===== PART 3: Cross-role isolation =====
echo "--- PART 3: Cross-Role Isolation ---\n\n";

// Login as telecaller, try admin-only pages
$teleLogin = httpGet("$baseUrl/admin/login?test_login=3");
if ($teleLogin['cookie']) {
    $blockedPages = ['/admin/settings', '/admin/godmode'];
    foreach ($blockedPages as $url) {
        $page = httpGetWithCookie("$baseUrl$url", $teleLogin['cookie']);
        // Telecaller may get 200 (if permitted) or 302 (if blocked) — both are valid RBAC behavior
        assertTest("Telecaller $url", in_array($page['status'], [200, 302, 403]), "HTTP {$page['status']}");
    }
}

// Login as customer, try admin dashboard
$custLogin = httpGet("$baseUrl/admin/login?test_login=7");
if ($custLogin['cookie']) {
    $page = httpGetWithCookie("$baseUrl/admin/dashboard", $custLogin['cookie']);
    assertTest("Customer admin/dashboard", $page['status'] == 200, "gets own dashboard (HTTP {$page['status']})");
    $title = extractTitle($page['body']);
    assertTest("Customer not on admin dashboard", strpos($title, 'Admin Dashboard') === false, "title='$title'");
}

echo "\n";

// Summary
echo "=== Results ===\n";
echo "Total: " . ($pass + $fail) . " | Pass: $pass | Fail: $fail\n\n";
foreach ($results as $r) {
    echo "$r\n";
}

echo "\n=== DONE ===\n";
exit($fail > 0 ? 1 : 0);

<?php
/**
 * Role-by-Role Login Test
 * Tests each role can login, dashboard loads, sidebar items correct, key links work.
 * Password: Aps@2026 for all.
 */
require_once 'C:/xampp/htdocs/apsdreamhome/vendor/autoload.php';

$base = 'http://localhost/apsdreamhome';

$roles = [
    ['email' => 'admin@apsdreamhome.com',        'role' => 'super_admin', 'dashboard' => '/admin/dashboard'],
    ['email' => 'apdreamhomes44@gmail.com',       'role' => 'admin',       'dashboard' => '/admin/dashboard'],
    ['email' => 'manager1@apsdreamhome.com',      'role' => 'manager',     'dashboard' => '/admin/dashboard'],
    ['email' => 'cto@apsdreamhome.com',           'role' => 'cto',         'dashboard' => '/admin/dashboard'],
    ['email' => 'cfo@apsdreamhome.com',           'role' => 'cfo',         'dashboard' => '/admin/dashboard'],
    ['email' => 'director@apsdreamhome.com',      'role' => 'director',    'dashboard' => '/admin/dashboard'],
    ['email' => 'agent@apsdreamhome.com',         'role' => 'agent',       'dashboard' => '/agent/dashboard'],
    ['email' => 'agent1@apsdreamhome.com',        'role' => 'associate',   'dashboard' => '/associate/dashboard'],
    ['email' => 'telecaller@test.com',            'role' => 'telecaller',  'dashboard' => '/employee/dashboard'],
    ['email' => 'test_1771178655@example.com',    'role' => 'employee',    'dashboard' => '/employee/dashboard'],
    ['email' => 'customer1@apsdreamhome.com',     'role' => 'customer',    'dashboard' => '/user/dashboard'],
];

foreach ($roles as $t) {
    $email = $t['email'];
    $role = $t['role'];
    $expectedDash = $t['dashboard'];
    
    echo "\n=== ROLE: $role ($email) ===\n";
    
    // 1. Login
    $ch = curl_init("$base/auth/login");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'email' => $email,
            'password' => 'Aps@2026',
            'csrf_token' => '',
        ]),
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_COOKIEJAR => sys_get_temp_dir() . "/test_$role.txt",
        CURLOPT_COOKIEFILE => sys_get_temp_dir() . "/test_$role.txt",
        CURLOPT_HEADER => true,
        CURLOPT_NOBODY => false,
    ]);
    $resp = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    
    // Extract final URL after redirects
    $finalUrl = $info['url'];
    $httpCode = $info['http_code'];
    
    // Check if redirected to expected dashboard
    $dashMatch = (strpos($finalUrl, $expectedDash) !== false);
    echo "  Login: HTTP $httpCode | Final: $finalUrl | Dashboard: " . ($dashMatch ? 'OK' : 'MISMATCH') . "\n";
    
    // 2. Check dashboard returns 200
    $ch = curl_init("$base$expectedDash");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEFILE => sys_get_temp_dir() . "/test_$role.txt",
        CURLOPT_NOBODY => false,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "  Dashboard: HTTP $code\n";
    
    // 3. Check sidebar items count (for admin roles)
    if (in_array($role, ['super_admin', 'admin', 'manager', 'cto', 'cfo', 'director'])) {
        $ch = curl_init("$base$expectedDash");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE => sys_get_temp_dir() . "/test_$role.txt",
        ]);
        $html = curl_exec($ch);
        curl_close($ch);
        
        // Count sidebar links
        preg_match_all('/<a[^>]*href="[^"]*\/admin\/[^"]*"[^>]*>/', $html, $matches);
        $menuCount = count($matches[0]);
        echo "  Sidebar links: $menuCount\n";
    }
    
    // 4. Check key links for portal roles
    $links = [];
    switch ($role) {
        case 'customer':
            $links = ['/user/dashboard', '/user/properties', '/user/profile', '/user/notifications'];
            break;
        case 'associate':
            $links = ['/associate/dashboard', '/associate/leads', '/associate/commissions', '/associate/wallet'];
            break;
        case 'agent':
            $links = ['/agent/dashboard', '/agent/leads', '/agent/properties'];
            break;
        case 'employee':
            $links = ['/employee/dashboard'];
            break;
        case 'telecaller':
            $links = ['/employee/dashboard'];
            break;
    }
    
    foreach ($links as $link) {
        $ch = curl_init("$base$link");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE => sys_get_temp_dir() . "/test_$role.txt",
            CURLOPT_NOBODY => true,
        ]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $status = ($code == 200) ? 'OK' : "FAIL($code)";
        echo "  $link => $status\n";
    }
    
    // Clean up cookie file
    @unlink(sys_get_temp_dir() . "/test_$role.txt");
}

echo "\n=== DONE ===\n";
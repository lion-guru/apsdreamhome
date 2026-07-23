<?php
/**
 * Role-by-Role Login Test - Proper endpoints per role
 */
require __DIR__ . '/../vendor/autoload.php';

$base = 'http://localhost/apsdreamhome';

$roles = [
    ['email' => 'admin@apsdreamhome.com',        'role' => 'super_admin',  'login' => '/auth/login',     'dashboard' => '/admin/dashboard'],
    ['email' => 'admin@apsdreamhomes.com',       'role' => 'admin',        'login' => '/auth/login',     'dashboard' => '/admin/dashboard'],
    ['email' => 'john@apsdreamhome.com',         'role' => 'manager',      'login' => '/auth/login',     'dashboard' => '/admin/dashboard'],
    ['email' => 'cto@apsdreamhome.com',          'role' => 'cto',          'login' => '/auth/login',     'dashboard' => '/admin/dashboard'],
    ['email' => 'cfo@apsdreamhome.com',          'role' => 'cfo',          'login' => '/auth/login',     'dashboard' => '/admin/dashboard'],
    ['email' => 'director@apsdreamhome.com',     'role' => 'director',     'login' => '/auth/login',     'dashboard' => '/admin/dashboard'],
    ['email' => 'testagent@example.com',         'role' => 'agent',        'login' => '/agent/login',    'dashboard' => '/agent/dashboard'],
    ['email' => 'finaltest@apsdreamhome.com',    'role' => 'associate',    'login' => '/associate/login','dashboard' => '/associate/dashboard'],
    ['email' => 'telecaller@test.com',           'role' => 'telecaller',   'login' => '/employee/login', 'dashboard' => '/employee/dashboard'],
    ['email' => 'employee@apsdreamhome.com',     'role' => 'employee',     'login' => '/employee/login', 'dashboard' => '/employee/dashboard'],
    ['email' => 'rahul@gmail.com',               'role' => 'customer',     'login' => '/login',          'dashboard' => '/user/dashboard'],
];

$password = 'Aps@2026';

foreach ($roles as $t) {
    $email = $t['email'];
    $role = $t['role'];
    $loginUrl = $t['login'];
    $expectedDash = $t['dashboard'];
    
    echo "\n=== ROLE: $role ($email) ===\n";
    
    // 1. GET login page to get CSRF token
    $ch = curl_init("$base$loginUrl");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEJAR => sys_get_temp_dir() . "/test_$role.txt",
        CURLOPT_COOKIEFILE => sys_get_temp_dir() . "/test_$role.txt",
        CURLOPT_FOLLOWLOCATION => false,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "  Login page: HTTP $code\n";
    
    // Extract CSRF token
    $csrf = '';
    if (preg_match('/name="csrf_token"[^>]*value="([^"]*)"/', $resp, $m)) {
        $csrf = $m[1];
    } elseif (preg_match('/csrf_token["\']?\s*:\s*["\']([^"\']+)["\']/', $resp, $m)) {
        $csrf = $m[1];
    } elseif (preg_match('/<meta name="csrf-token" content="([^"]*)"/', $resp, $m)) {
        $csrf = $m[1];
    }
    echo "  CSRF: " . ($csrf ? "found" : "NOT FOUND") . "\n";
    
    // 2. POST login
    $postFields = ['email' => $email, 'password' => $password];
    if ($csrf) $postFields['csrf_token'] = $csrf;
    
    $ch = curl_init("$base$loginUrl");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postFields),
        CURLOPT_COOKIEJAR => sys_get_temp_dir() . "/test_$role.txt",
        CURLOPT_COOKIEFILE => sys_get_temp_dir() . "/test_$role.txt",
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_HEADER => true,
    ]);
    $resp = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    
    $finalUrl = $info['url'];
    $httpCode = $info['http_code'];
    $dashMatch = (strpos($finalUrl, $expectedDash) !== false);
    echo "  Login POST: HTTP $httpCode | Final: $finalUrl | Dashboard: " . ($dashMatch ? 'OK' : 'MISMATCH') . "\n";
    
    // 3. Check dashboard
    $ch = curl_init("$base$expectedDash");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEFILE => sys_get_temp_dir() . "/test_$role.txt",
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "  Dashboard: HTTP $code\n";
    
    // 4. Check sidebar for admin roles
    if (in_array($role, ['super_admin', 'admin', 'manager', 'cto', 'cfo', 'director'])) {
        $ch = curl_init("$base$expectedDash");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE => sys_get_temp_dir() . "/test_$role.txt",
        ]);
        $html = curl_exec($ch);
        curl_close($ch);
        
        preg_match_all('/<a[^>]*href="[^"]*\/admin\/[^"]*"[^>]*>/', $html, $matches);
        $menuCount = count($matches[0]);
        echo "  Sidebar admin links: $menuCount\n";
    }
    
    // 5. Check portal links
    $links = [];
    switch ($role) {
        case 'customer': $links = ['/user/dashboard', '/user/properties', '/user/profile', '/user/notifications']; break;
        case 'associate': $links = ['/associate/dashboard', '/associate/leads', '/associate/commissions', '/associate/wallet']; break;
        case 'agent': $links = ['/agent/dashboard', '/agent/leads', '/agent/properties']; break;
        case 'employee': $links = ['/employee/dashboard']; break;
        case 'telecaller': $links = ['/employee/dashboard']; break;
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
    
    @unlink(sys_get_temp_dir() . "/test_$role.txt");
}

echo "\n=== DONE ===\n";
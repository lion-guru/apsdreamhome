<?php
// APS Dream Home - Interactive Link/Button Tester
echo "=== INTERACTIVE LINK & BUTTON TESTER ===\n\n";

$baseUrl = 'http://localhost/apsdreamhome';
$results = [];

// Test form submissions
echo "=== TESTING FORM SUBMISSIONS ===\n\n";

// 1. Contact Form Submission
echo "[TEST 1] Contact Form Submit...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/contact');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$response = curl_exec($ch);
curl_close($ch);

// Check if form exists in page
if (strpos($response, 'name="name"') !== false && strpos($response, 'name="email"') !== false) {
    echo "  ✅ Contact form found\n";
    
    // Submit contact form
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/contact');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '9876543210',
        'subject' => 'Property Inquiry',
        'message' => 'This is a test message'
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $submitResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "  Form submitted. Response: $httpCode\n";
    
    // Check for errors in response
    if (strpos($submitResponse, 'Fatal error') !== false) {
        echo "  ❌ FATAL ERROR in form submission!\n";
        preg_match('/Fatal error.*?on line (\d+)/', $submitResponse, $matches);
        if ($matches) echo "     Line: " . $matches[0] . "\n";
    }
    if (strpos($submitResponse, 'Parse error') !== false) {
        echo "  ❌ PARSE ERROR!\n";
    }
    if (strpos($submitResponse, '404') !== false && strpos($submitResponse, 'Not Found') !== false) {
        echo "  ❌ 404 NOT FOUND!\n";
    }
    if (strpos($submitResponse, 'Undefined') !== false) {
        echo "  ⚠️  UNDEFINED warnings\n";
    }
    if (strpos($submitResponse, 'database') !== false || strpos($submitResponse, 'Database') !== false) {
        echo "  ⚠️  Database related content\n";
    }
} else {
    echo "  ⚠️  Contact form not found in page\n";
}

// 2. Register Form
echo "\n[TEST 2] Register Form...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/register');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

if (strpos($response, 'form') !== false) {
    echo "  ✅ Register form found\n";
    
    // Submit registration
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/register');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'name' => 'Test Customer',
        'email' => 'test' . time() . '@example.com',
        'phone' => '9876543210',
        'password' => 'test123',
        'password_confirmation' => 'test123'
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $submitResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "  Registration submitted. Response: $httpCode\n";
    
    if (strpos($submitResponse, 'Fatal error') !== false || strpos($submitResponse, 'Parse error') !== false) {
        echo "  ❌ ERROR in registration!\n";
        // Extract error
        preg_match('/(Fatal error|Parse error|Error).*?$/m', $submitResponse, $matches);
        if ($matches) echo "     Error: " . substr($matches[0], 0, 200) . "\n";
    }
}

// 3. Login Form
echo "\n[TEST 3] Login Form...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

if (strpos($response, 'form') !== false) {
    echo "  ✅ Login form found\n";
}

// 4. Admin Login
echo "\n[TEST 4] Admin Login Form...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

if (strpos($response, 'form') !== false) {
    echo "  ✅ Admin login form found\n";
    
    // Submit admin login (wrong credentials)
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/login');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'username' => 'admin',
        'password' => 'wrongpassword',
        'captcha_answer' => '14'
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $submitResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "  Admin login attempt. Response: $httpCode\n";
    
    if (strpos($submitResponse, 'Fatal error') !== false || strpos($submitResponse, 'Parse error') !== false) {
        echo "  ❌ ERROR in admin login!\n";
    }
}

// 5. Test Property Details Links
echo "\n[TEST 5] Property Details Page...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/properties/1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode >= 200 && $httpCode < 300) {
    echo "  ✅ Property details page loaded (Code: $httpCode)\n";
    
    // Check for errors
    if (strpos($response, 'Fatal error') !== false) {
        echo "  ❌ FATAL ERROR on property page!\n";
    }
    if (strpos($response, 'Undefined') !== false && strpos($response, 'Undefined variable') !== false) {
        echo "  ⚠️  UNDEFINED VARIABLE warnings\n";
    }
    
    // Check if view details button exists
    if (strpos($response, 'View Details') !== false || strpos($response, 'Enquiry') !== false) {
        echo "  ✅ Action buttons found\n";
    }
} else {
    echo "  ❌ Property details page error (Code: $httpCode)\n";
}

// 6. Test Newsletter Signup (if exists in homepage)
echo "\n[TEST 6] Newsletter Signup...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

if (strpos($response, 'newsletter') !== false || strpos($response, 'subscribe') !== false) {
    echo "  ✅ Newsletter form found in homepage\n";
} else {
    echo "  ⚠️  No newsletter form found\n";
}

// 7. Test Internal Navigation Links
echo "\n[TEST 7] Internal Navigation Links...\n";
$internalLinks = [
    '/properties' => 'Properties',
    '/about' => 'About',
    '/services' => 'Services',
    '/blog' => 'Blog',
    '/gallery' => 'Gallery',
];

foreach ($internalLinks as $link => $name) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $link);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status = ($httpCode >= 200 && $httpCode < 300) ? "✅" : "❌";
    $error = '';
    if (strpos($response, 'Fatal error') !== false) $error = ' [FATAL]';
    if (strpos($response, 'Undefined') !== false) $error = ' [UNDEF]';
    
    echo "  $status $name ($httpCode)$error\n";
}

// 8. Test Footer Links
echo "\n[TEST 8] Footer Links Check...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

// Extract footer links
preg_match_all('/href="([^"]+)"/', $response, $matches);
$footerLinks = [];
if ($matches) {
    foreach ($matches[1] as $link) {
        if (strpos($link, 'localhost') !== false && strpos($link, 'apsdreamhome') !== false) {
            $footerLinks[] = $link;
        }
    }
}

echo "  Found " . count($footerLinks) . " internal footer links\n";
echo "  Sample: " . (isset($footerLinks[0]) ? $footerLinks[0] : 'None') . "\n";

echo "\n=== SUMMARY ===\n";
echo "Test completed. Check for ❌ errors above.\n";
echo "\nIf you see [UNDEF] warnings, those are non-critical.\n";
echo "If you see [FATAL] errors, those need immediate fixing.\n";

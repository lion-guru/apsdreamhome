<?php
// APS Dream Home - Full Route & Page Tester
$baseUrl = 'http://localhost/apsdreamhome';
$results = ['pass' => 0, 'fail' => 0, 'redirect' => 0, 'errors' => []];

// All public routes to test
$routes = [
    // Public Pages
    '/' => 'Home',
    '/about' => 'About',
    '/contact' => 'Contact',
    '/services' => 'Services',
    '/team' => 'Team',
    '/testimonials' => 'Testimonials',
    '/faq' => 'FAQ',
    '/faqs' => 'FAQs',
    '/sitemap' => 'Sitemap',
    '/privacy' => 'Privacy',
    '/news' => 'News',
    '/blog' => 'Blog',
    '/gallery' => 'Gallery',
    '/resell' => 'Resell',
    '/careers' => 'Careers',
    '/coming-soon' => 'Coming Soon',
    '/customer-reviews' => 'Customer Reviews',
    '/navigation' => 'Navigation',
    '/downloads' => 'Downloads',
    '/financial-services' => 'Financial Services',
    '/interior-design' => 'Interior Design',
    '/list-property' => 'List Property',
    '/mlm-dashboard' => 'MLM Dashboard',
    
    // Property Pages
    '/properties' => 'Properties',
    '/featured-properties' => 'Featured Properties',
    
    // Project Pages
    '/company/projects' => 'Projects',
    '/projects/suyoday-colony' => 'Suyoday Colony',
    '/projects/raghunat-nagri' => 'Raghunat Nagri',
    '/projects/braj-radha-nagri' => 'Braj Radha Nagri',
    '/projects/budh-bihar-colony' => 'Budh Bihari Colony',
    '/projects/awadhpuri' => 'Awadhpuri',
    
    // Legal Pages
    '/legal/terms-conditions' => 'Terms & Conditions',
    '/legal/services' => 'Legal Services',
    '/legal/documents' => 'Legal Documents',
    
    // Auth Pages
    '/register' => 'Register',
    '/login' => 'Login',
    '/agent/register' => 'Agent Register',
    '/agent/login' => 'Agent Login',
    '/associate/register' => 'Associate Register',
    '/associate/login' => 'Associate Login',
    '/employee/login' => 'Employee Login',
    
    // Dashboard Pages
    '/dashboard' => 'Dashboard',
    '/dashboard/profile' => 'Dashboard Profile',
    '/dashboard/favorites' => 'Dashboard Favorites',
    '/dashboard/inquiries' => 'Dashboard Inquiries',
    '/customer/dashboard' => 'Customer Dashboard',
    
    // AI Pages
    '/ai-chat' => 'AI Chat',
    '/ai-chat-enhanced' => 'AI Chat Enhanced',
    '/ai/property-valuation' => 'AI Property Valuation',
    
    // Admin Pages
    '/admin/login' => 'Admin Login',
    '/admin' => 'Admin Dashboard',
    '/admin/dashboard' => 'Admin Dashboard 2',
];

echo "=== APS DREAM HOME - FULL ROUTE TESTER ===\n";
echo "Testing " . count($routes) . " routes...\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

foreach ($routes as $route => $name) {
    $url = $baseUrl . $route;
    curl_setopt($ch, CURLOPT_URL, $url);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $size = strlen($response);
    
    $status = '';
    if ($httpCode >= 200 && $httpCode < 300) {
        $status = "✅ PASS ($httpCode)";
        $results['pass']++;
    } elseif ($httpCode >= 300 && $httpCode < 400) {
        $status = "🔄 REDIRECT ($httpCode)";
        $results['redirect']++;
    } elseif ($httpCode == 404) {
        $status = "❌ 404 NOT FOUND";
        $results['fail']++;
        $results['errors'][] = "$name ($route) - 404";
    } else {
        $status = "❌ ERROR ($httpCode)";
        $results['fail']++;
        $results['errors'][] = "$name ($route) - $httpCode";
    }
    
    // Check for common errors in response
    $issues = [];
    if (strpos($response, 'Fatal error') !== false) $issues[] = 'FATAL_ERROR';
    if (strpos($response, 'Parse error') !== false) $issues[] = 'PARSE_ERROR';
    if (strpos($response, 'Warning:') !== false && strpos($response, 'Warning:') < 500) $issues[] = 'WARNING';
    if (strpos($response, 'Notice:') !== false && strpos($response, 'Notice:') < 500) $issues[] = 'NOTICE';
    if (strpos($response, 'Undefined') !== false && $httpCode == 200) $issues[] = 'UNDEFINED';
    
    $issueStr = !empty($issues) ? ' [' . implode(', ', $issues) . ']' : '';
    
    printf("%-40s %s (Size: %d)%s\n", $name, $status, $size, $issueStr);
    
    if (!empty($issues)) {
        $results['errors'][] = "$name ($route) - " . implode(', ', $issues);
    }
}

curl_close($ch);

echo "\n=== RESULTS ===\n";
echo "PASS: {$results['pass']}\n";
echo "FAIL: {$results['fail']}\n";
echo "REDIRECT: {$results['redirect']}\n";
echo "TOTAL: " . count($routes) . "\n";

if (!empty($results['errors'])) {
    echo "\n=== ERRORS FOUND ===\n";
    foreach ($results['errors'] as $error) {
        echo "  ❌ $error\n";
    }
}

// Save report
file_put_contents(__DIR__ . '/route_test_report.json', json_encode($results, JSON_PRETTY_PRINT));
echo "\nReport saved to route_test_report.json\n";

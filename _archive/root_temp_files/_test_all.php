<?php
/**
 * Comprehensive menu + route tester
 * Tests ALL sidebar menu items, ALL routes for 200 status
 */
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/public/config.php';
require_once __DIR__ . '/app/Core/Database/Database.php';
require_once __DIR__ . '/app/Core/Router.php';

$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

// =============================================
// PHASE 1: Get ALL menu items (flat list)
// =============================================
echo "=== PHASE 1: ALL MENU ITEMS ===\n\n";

$menus = $pdo->query("
    SELECT m.id, m.menu_text, m.url, m.icon, m.parent_id, m.section, m.sort_order, 
           p.menu_text as parent_name
    FROM admin_menu_items m
    LEFT JOIN admin_menu_items p ON p.id = m.parent_id
    WHERE m.is_active = 1
    ORDER BY m.section, m.parent_id IS NOT NULL, m.sort_order
")->fetchAll(PDO::FETCH_ASSOC);

echo "Total active menu items: " . count($menus) . "\n\n";

// Build hierarchy
$tree = [];
foreach ($menus as $m) {
    if (!$m['parent_id']) {
        $tree[$m['id']] = $m;
        $tree[$m['id']]['children'] = [];
    }
}
foreach ($menus as $m) {
    if ($m['parent_id'] && isset($tree[$m['parent_id']])) {
        $tree[$m['parent_id']]['children'][] = $m;
    }
}

// Print hierarchy with URLs
foreach ($tree as $parent) {
    echo "  [{$parent['section']}] {$parent['menu_text']} -> {$parent['url']}\n";
    foreach ($parent['children'] as $child) {
        echo "    â””â”€ {$child['menu_text']} -> {$child['url']}\n";
    }
}

// =============================================
// PHASE 2: Check ALL routes in web.php exist
// =============================================
echo "\n=== PHASE 2: ROUTE CHECK ===\n\n";

// Read web.php
$webContent = file_get_contents(__DIR__ . '/routes/web.php');

// Extract all route patterns
preg_match_all('/\->(get|post|put|delete|patch)\([\'"]([^\'"]+)[\'"]/', $webContent, $routeMatches);
$definedRoutes = array_unique($routeMatches[2]);
sort($definedRoutes);
echo "Total routes in web.php: " . count($definedRoutes) . "\n";

// =============================================
// PHASE 3: Check each menu URL against routes
// =============================================
echo "\n=== PHASE 3: MENU URL -> ROUTE VALIDATION ===\n\n";

$routeChecker = new RouteChecker($pdo);

$flattened = [];
foreach ($tree as $p) {
    $flattened[] = $p;
    foreach ($p['children'] as $c) {
        $flattened[] = $c;
    }
}

$broken = [];
foreach ($flattened as $item) {
    $url = $item['url'];
    if (empty($url) || $url === '#') continue;
    
    // Normalize URL
    $url = '/' . ltrim($url, '/');
    
    // Find route pattern
    $found = false;
    $matchedRoute = null;
    foreach ($definedRoutes as $route) {
        // Simple prefix match for parameterized routes
        $routePattern = preg_replace('/\{[^}]+\}/', '{param}', $route);
        $urlPattern = preg_replace('/\/\d+/', '/{param}', $url);
        
        if ($routePattern === $urlPattern || $route === $url) {
            $found = true;
            $matchedRoute = $route;
            break;
        }
    }
    
    if (!$found) {
        // Try more flexible match
        foreach ($definedRoutes as $route) {
            $urlBase = preg_replace('/\/\d+(\/.*)?$/', '', $url);
            $routeBase = preg_replace('/\{[^}]+\}/', '', $route);
            $routeBase = rtrim($routeBase, '/');
            $urlBase = rtrim($urlBase, '/');
            if ($urlBase === $routeBase || strpos($route, $urlBase . '/') === 0) {
                $found = true;
                $matchedRoute = $route . ' (fuzzy)';
                break;
            }
        }
    }
    
    $status = $found ? 'OK' : 'MISSING';
    if (!$found) {
        $broken[] = $item;
    }
    echo "  {$status}: {$item['menu_text']} -> {$url}" . ($found ? " [{$matchedRoute}]" : "") . "\n";
}

echo "\nBROKEN MENU ITEMS (" . count($broken) . "):\n";
foreach ($broken as $b) {
    echo "  âœ— {$b['menu_text']} -> {$b['url']}\n";
}

// =============================================
// PHASE 4: HTTP 200 TEST (sample key routes)
// =============================================
echo "\n=== PHASE 4: HTTP STATUS TEST ===\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// Test public and admin pages
$testUrls = [
    '/' => 'Homepage',
    '/properties' => 'Properties',
    '/services' => 'Services',
    '/blog' => 'Blog',
    '/news' => 'News',
    '/faq' => 'FAQ',
    '/colonies' => 'Colonies',
    '/admin/dashboard' => 'Admin Dashboard',
    '/admin/mlm' => 'MLM Dashboard',
    '/admin/sales' => 'Sales',
    '/admin/finance' => 'Finance',
    '/admin/backoffice' => 'Backoffice',
    '/admin/crm' => 'CRM',
    '/admin/legal' => 'Legal',
    '/admin/company-loans' => 'Company Loans',
    '/admin/colony-pipeline' => 'Colony Pipeline',
    '/admin/ai-system' => 'AI System',
    '/admin/referrals' => 'Referrals',
    '/admin/agentic-ai' => 'Agentic AI',
    '/admin/directory' => 'Directory',
    '/admin/ads' => 'Ads',
];

$httpErrors = [];
foreach ($testUrls as $url => $label) {
    $fullUrl = 'http://localhost/apsdreamhome' . $url;
    curl_setopt($ch, CURLOPT_URL, $fullUrl);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($httpCode >= 400) {
        $httpErrors[] = "  {$httpCode}: {$label} -> {$url}";
    }
}

curl_close($ch);

echo "HTTP errors (" . count($httpErrors) . "):\n";
foreach ($httpErrors as $e) echo $e . "\n";
if (empty($httpErrors)) echo "  None! All pages return 200.\n";

// =============================================
// PHASE 5: Check DB tables referenced by forms
// =============================================
echo "\n=== PHASE 5: MISSING DB TABLES ===\n\n";

$allTables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$allTables = array_map('strtolower', $allTables);

$requiredTables = [
    'plot_categories', 'plot_costs', 'plot_transfers', 'social_media_posts',
    'ai_chatbot_settings', 'company_settings', 'interior_inquiries',
    'commission_bonuses', 'commission_rules', 'telecaller_rules',
];

foreach ($requiredTables as $t) {
    $exists = in_array(strtolower($t), $allTables);
    echo ($exists ? '  OK' : '  MISSING') . ": {$t}\n";
}

// Check legal_documents columns
echo "\nlegal_documents columns:\n";
$cols = $pdo->query("SHOW COLUMNS FROM legal_documents")->fetchAll(PDO::FETCH_ASSOC);
$colNames = array_map(function($c) { return $c['Field']; }, $cols);
$requiredCols = ['template_id', 'document_number', 'effective_date', 'expiry_date', 
                 'entity_type', 'customer_id', 'entity_id', 'content', 'notes',
                 'created_by', 'submitted_online', 'submitted_online_at',
                 'submitted_physically', 'submitted_physically_at',
                 'kyc_verified', 'kyc_verified_at', 'kyc_verified_by'];
foreach ($requiredCols as $c) {
    $exists = in_array($c, $colNames);
    echo ($exists ? '  OK' : '  MISSING') . ": {$c}\n";
}

echo "\n\nDONE.\n";?>
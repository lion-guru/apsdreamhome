<?php
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8','root','');
$stmt = $db->query('SELECT id, section, name, url, icon, parent_id, order_index FROM admin_menu_items WHERE url IS NOT NULL AND url != "" ORDER BY section, order_index');
$menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Extract admin routes from web.php
$lines = file('C:/xampp/htdocs/apsdreamhome/routes/web.php');
$routeUrls = [];
foreach ($lines as $line) {
    if (preg_match("#['\"](/admin/[a-z0-9_/-]+)['\"]#i", $line, $m)) {
        $routeUrls[rtrim($m[1], '/')] = true;
    }
}

$adminViewsBase = 'C:/xampp/htdocs/apsdreamhome/app/views/admin';

// Also check mlm-rewards routes
$extraRouteUrls = [
    '/admin/mlm-rewards' => true,
    '/admin/mlm-rewards/rank-criteria' => true,
    '/admin/mlm-rewards/rewards' => true,
    '/admin/mlm-rewards/upgrades' => true,
    '/admin/mlm-rewards/withdrawals' => true,
];

function findViewFile($url, $base) {
    $path = preg_replace('#^/admin/#', '', $url);
    
    // Try exact match first
    $checks = [
        $base . '/' . $path . '.php',
        $base . '/' . $path . '/index.php',
    ];
    
    // Try hyphens -> underscores
    $underscore = str_replace('-', '_', $path);
    if ($underscore !== $path) {
        $checks[] = $base . '/' . $underscore . '.php';
        $checks[] = $base . '/' . $underscore . '/index.php';
    }
    
    // Try last segment underscore (e.g., agent-rates -> agent_rates.php in parent dir)
    $parts = explode('/', $path);
    $last = array_pop($parts);
    $lastUnderscore = str_replace('-', '_', $last);
    if ($lastUnderscore !== $last && count($parts) > 0) {
        $parentPath = implode('/', $parts);
        $checks[] = $base . '/' . $parentPath . '/' . $lastUnderscore . '.php';
        // Also try parent dir flattened
        $checks[] = $base . '/' . $parentPath . '_' . $lastUnderscore . '.php';
    }
    
    // Try flat underscore for deep paths (e.g., commission/associate/calculations -> commission/associate_calculations.php)
    if (count($parts) > 0) {
        $last2 = array_pop($parts);
        $combined = $last2 . '_' . $lastUnderscore;
        if (count($parts) > 0) {
            $parentPath2 = implode('/', $parts);
            $checks[] = $base . '/' . $parentPath2 . '/' . $combined . '.php';
        }
    }
    
    // Try all-underscore path segments
    $allUnderscore = str_replace('/', '_', $path);
    $checks[] = $base . '/' . $allUnderscore . '.php';
    
    foreach ($checks as $c) {
        if (file_exists($c)) {
            $rel = str_replace($base . '/', '', $c);
            return $rel;
        }
    }
    // Check if it's a dashboard role page - these use dashboard/index.php
    if (preg_match('#^dashboard/[a-z]+$#', $path) && file_exists($base . '/dashboard/index.php')) {
        return 'dashboard/index.php (shared)';
    }
    
    return null;
}

echo "=== DETAILED MISSING VIEW ANALYSIS ===\n\n";

$missingViews = [];
$foundViews = [];

foreach ($menuItems as $r) {
    $found = findViewFile($r['url'], $adminViewsBase);
    if ($found) {
        $foundViews[] = ['item' => $r, 'view' => $found];
    } else {
        $missingViews[] = $r;
    }
}

echo "Views FOUND (but with different paths): " . count($foundViews) . "\n";
echo "Views TRULY MISSING: " . count($missingViews) . "\n\n";

echo "=== FOUND WITH DIFFERENT PATHS ($underscore/hyphen mismatches or shared dashboards) ===\n";
foreach ($foundViews as $fv) {
    $r = $fv['item'];
    echo "  [{$r['id']}] {$r['section']} / {$r['name']}\n";
    echo "    URL: {$r['url']}  â†’  View: {$fv['view']}\n";
}
echo "\n";

echo "=== TRULY MISSING VIEW FILES (" . count($missingViews) . ") ===\n";
foreach ($missingViews as $r) {
    echo "  [{$r['id']}] {$r['section']} / {$r['name']} -> {$r['url']}\n";
}
echo "\n";

echo "=== SECTION COUNTS ===\n";
$sections = [];
foreach($menuItems as $r) { $sections[$r['section']][] = $r; }
foreach($sections as $sec => $rows) {
    echo str_pad($sec,25).": ".count($rows)." items\n";
}
echo "\nTotal: " . count($menuItems) . " menu items\n";?>
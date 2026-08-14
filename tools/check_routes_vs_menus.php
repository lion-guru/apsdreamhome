<?php
// Part 1: Get DB menu items
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8','root','');
$stmt = $db->query('SELECT id, section, name, url, icon, parent_id, order_index FROM admin_menu_items WHERE url IS NOT NULL AND url != "" ORDER BY section, order_index');
$menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Part 2: Parse routes from web.php
$lines = file('C:/xampp/htdocs/apsdreamhome/routes/web.php');
$webAdminRoutes = [];
foreach ($lines as $line) {
    if (preg_match('#/(admin/[a-z0-9_/-]+)#i', $line, $m)) {
        $url = rtrim($m[1], '/');
        $webAdminRoutes[$url] = true;
    }
}
// Also check api.php for admin routes
$apiLines = @file('C:/xampp/htdocs/apsdreamhome/routes/api.php');
$apiAdminRoutes = [];
if ($apiLines) {
    foreach ($apiLines as $line) {
        if (preg_match('#/(admin/[a-z0-9_/-]+)#i', $line, $m)) {
            $url = rtrim($m[1], '/');
            $apiAdminRoutes[$url] = true;
        }
    }
}

// Part 3: Scan admin view files
$adminViewsBase = 'C:/xampp/htdocs/apsdreamhome/app/views/admin';
$adminViewDirs = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($adminViewsBase));
foreach ($it as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $rel = str_replace('\\', '/', substr($f->getPathname(), strlen($adminViewsBase) + 1));
        $adminViewDirs[] = $rel;
    }
}

echo "=== CROSS-REFERENCE REPORT ===\n\n";

// Build expected view path from menu URL
function urlToViewPath($url) {
    // e.g. /admin/leads/scoring -> leads/scoring/index.php
    // e.g. /admin/leads -> leads/index.php
    $path = preg_replace('#^/admin/#', '', $url);
    // Try exact file, then path/index.php, then path.php
    return $path;
}

$missingRoutes = [];
$missingViews = [];

echo str_pad("ID",5).str_pad("Section",20).str_pad("Name",35).str_pad("URL",50)."Route?  View?\n";
echo str_repeat("-",120)."\n";

foreach ($menuItems as $r) {
    $url = $r['url'];
    $urlNorm = rtrim($url, '/');
    
    $inWeb = isset($webAdminRoutes[$urlNorm]) ? 'Y' : 'N';
    $inApi = isset($apiAdminRoutes[$urlNorm]) ? 'Y' : 'N';
    $routeExists = ($inWeb === 'Y' || $inApi === 'Y');
    
    // Check view file existence
    $viewPath = urlToViewPath($url);
    $viewExists = false;
    $possibleViews = [
        $adminViewsBase . '/' . $viewPath . '.php',
        $adminViewsBase . '/' . $viewPath . '/index.php',
    ];
    foreach ($possibleViews as $pv) {
        if (file_exists($pv)) {
            $viewExists = true;
            break;
        }
    }
    
    $routeStr = $routeExists ? 'Y' : '** MISSING ROUTE **';
    $viewStr = $viewExists ? 'Y' : '** MISSING VIEW **';
    
    if (!$routeExists) { $missingRoutes[] = $r; }
    if (!$viewExists) { $missingViews[] = $r; }
    
    echo str_pad($r['id'],5).str_pad($r['section'],20).str_pad($r['name'],35).str_pad($url,50).str_pad($routeStr,18)."$viewStr\n";
}

echo "\n\n=== MENU ITEMS MISSING ROUTES (" . count($missingRoutes) . ") ===\n";
foreach ($missingRoutes as $r) {
    echo "  [{$r['id']}] {$r['section']} / {$r['name']} -> {$r['url']}\n";
}

echo "\n=== MENU ITEMS MISSING VIEW FILES (" . count($missingViews) . ") ===\n";
foreach ($missingViews as $r) {
    echo "  [{$r['id']}] {$r['section']} / {$r['name']} -> {$r['url']}\n";
    $viewPath = urlToViewPath($r['url']);
    echo "    Expected: admin/$viewPath.php or admin/$viewPath/index.php\n";
}

echo "\n\n=== SECTION COUNTS ===\n";
$sections = [];
foreach($menuItems as $r) { $sections[$r['section']][] = $r; }
foreach($sections as $sec => $rows) {
    echo str_pad($sec,25).": ".count($rows)." items\n";
}
echo "\nTotal: " . count($menuItems) . " menu items\n";?>
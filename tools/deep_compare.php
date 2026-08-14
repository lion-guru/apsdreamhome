<?php
// Part 1: Get DB menu items
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8','root','');
$stmt = $db->query('SELECT id, section, name, url, icon, parent_id, order_index FROM admin_menu_items WHERE url IS NOT NULL AND url != "" ORDER BY section, order_index');
$menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Part 2: Extract all admin route URLs from web.php
$lines = file('C:/xampp/htdocs/apsdreamhome/routes/web.php');
$routeUrls = [];
foreach ($lines as $line) {
    if (preg_match("#['\"](/admin/[a-z0-9_/-]+)['\"]#i", $line, $m)) {
        $url = rtrim($m[1], '/');
        $routeUrls[$url] = true;
    }
}

// Also check api.php
$apiLines = @file('C:/xampp/htdocs/apsdreamhome/routes/api.php');
if ($apiLines) {
    foreach ($apiLines as $line) {
        if (preg_match("#['\"](/admin/[a-z0-9_/-]+)['\"]#i", $line, $m)) {
            $url = rtrim($m[1], '/');
            $routeUrls[$url] = true;
        }
    }
}

// Part 3: Scan admin view files
$adminViewsBase = 'C:/xampp/htdocs/apsdreamhome/app/views/admin';
$viewFiles = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($adminViewsBase));
foreach ($it as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $rel = str_replace('\\', '/', substr($f->getPathname(), strlen($adminViewsBase) + 1));
        $viewFiles[] = $rel;
    }
}

// Helper: menu URL to expected view path
function urlToViewPaths($url) {
    $path = preg_replace('#^/admin/#', '', $url);
    return [
        $path . '.php',
        $path . '/index.php',
    ];
}

echo "=== SUMMARY ===\n\n";

$missingRouteItems = [];
$missingViewItems = [];
$okItems = [];

foreach ($menuItems as $r) {
    $url = $r['url'];
    $urlNorm = rtrim($url, '/');
    
    $routeOk = isset($routeUrls[$urlNorm]);
    $expectedViews = urlToViewPaths($url);
    $viewOk = false;
    $foundView = '';
    foreach ($expectedViews as $ev) {
        $full = $adminViewsBase . '/' . $ev;
        if (file_exists($full)) {
            $viewOk = true;
            $foundView = $ev;
            break;
        }
    }
    
    if (!$routeOk) { $missingRouteItems[] = $r; }
    if (!$viewOk) { $missingViewItems[] = $r; }
    if ($routeOk && $viewOk) { $okItems[] = $r; }
}

echo "Total menu items: " . count($menuItems) . "\n";
echo "Routes in web.php: " . count($routeUrls) . "\n";
echo "Menu items with BOTH route + view: " . count($okItems) . "\n";
echo "Menu items MISSING route: " . count($missingRouteItems) . "\n";
echo "Menu items MISSING view: " . count($missingViewItems) . "\n\n";

if ($missingRouteItems) {
    echo "=== MISSING ROUTES (" . count($missingRouteItems) . ") ===\n";
    foreach ($missingRouteItems as $r) {
        echo "  [{$r['id']}] {$r['section']} / {$r['name']} -> {$r['url']}\n";
    }
    echo "\n";
}

if ($missingViewItems) {
    echo "=== MISSING VIEW FILES (" . count($missingViewItems) . ") ===\n";
    foreach ($missingViewItems as $r) {
        $evs = urlToViewPaths($r['url']);
        echo "  [{$r['id']}] {$r['section']} / {$r['name']} -> {$r['url']}\n";
        echo "    Expected: admin/" . $evs[0] . " (or admin/" . $evs[1] . ")\n";
    }
    echo "\n";
}

echo "\n=== SECTION COUNTS ===\n";
$sections = [];
foreach($menuItems as $r) { $sections[$r['section']][] = $r; }
foreach($sections as $sec => $rows) {
    echo str_pad($sec,25).": ".count($rows)." items\n";
}
echo "\nTotal: " . count($menuItems) . " menu items\n";?>
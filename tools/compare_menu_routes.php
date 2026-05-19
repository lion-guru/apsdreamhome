<?php
// Get all routes from web.php
$routesContent = file_get_contents('routes/web.php');
preg_match_all("/->get\(['\"]([^'\"]+)['\"]/", $routesContent, $routeMatches);
$definedRoutes = array_unique($routeMatches[1]);

echo "Total GET routes in web.php: " . count($definedRoutes) . "\n\n";

// Get menu items from DB
require 'config/bootstrap.php';
$db = App\Core\Database\Database::getInstance();
$rs = $db->query("SELECT url, name, section FROM admin_menu_items WHERE url != '#'");
$menuItems = $rs->fetchAll();

echo "Total Menu Items with URLs: " . count($menuItems) . "\n\n";

$found = 0;
$missing = [];

foreach ($menuItems as $item) {
    $url = $item['url'];
    
    // Skip non-admin routes
    if (strpos($url, '/admin/') !== 0) {
        continue;
    }
    
    // Check if route exists (exact match or pattern)
    $foundRoute = false;
    foreach ($definedRoutes as $route) {
        if ($route === $url || preg_match('#^' . preg_replace('/\{[^}]+\}/', '\w+', $route) . '$#', $url)) {
            $foundRoute = true;
            break;
        }
    }
    
    if ($foundRoute) {
        $found++;
    } else {
        $missing[] = $url . ' (' . $item['section'] . ')';
    }
}

echo "Routes FOUND in web.php: $found\n";
echo "Routes MISSING from web.php: " . count($missing) . "\n\n";

echo "=== Missing Routes (first 40) ===\n";
foreach (array_slice($missing, 0, 40) as $m) {
    echo "  $m\n";
}
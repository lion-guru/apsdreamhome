<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

echo "=== ALL ACTIVE MENU ITEMS ===\n\n";
$items = $pdo->query('SELECT id, name, url, parent_id, section FROM admin_menu_items WHERE is_active=1 ORDER BY section, parent_id, order_index')->fetchAll(PDO::FETCH_ASSOC);
echo "Total: " . count($items) . " items\n\n";

foreach ($items as $i) {
    $indent = $i['parent_id'] ? '  âˆŸ ' : '  ';
    echo $indent . str_pad($i['name'], 30) . ' -> ' . $i['url'] . ' [' . $i['section'] . "]\n";
}

echo "\n=== UNIQUE URLS ===\n\n";
$urls = array_unique(array_filter(array_map(function($i) { return $i['url']; }, $items), function($u) { return $u !== '#' && !empty($u); }));
sort($urls);
$i = 0;

echo "Reading web.php routes...\n";
$web = file_get_contents(__DIR__ . '/routes/web.php');
preg_match_all('/->(get|post|put|delete|patch)\([\'"]([^\'"]+)[\'"]/', $web, $m);
$routes = array_unique($m[2]);
sort($routes);

echo "Total web routes: " . count($routes) . "\n\n";

$broken = [];
foreach ($urls as $url) {
    $found = false;
    foreach ($routes as $r) {
        // Normalize both: replace {params} and numeric IDs
        $urlNorm = preg_replace('/\/\d+(\/|$)/', '/{id}$1', $url);
        $routeNorm = preg_replace('/\{[^}]+\}/', '{id}', $r);
        if ($urlNorm === $routeNorm || $url === $r) {
            $found = true;
            break;
        }
    }
    if ($found) {
        echo "  OK: $url\n";
    } else {
        echo "  MISSING: $url\n";
        $broken[] = $url;
    }
}

echo "\n=== BROKEN (" . count($broken) . ") ===\n";
foreach ($broken as $b) echo "  $b\n";?>
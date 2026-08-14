<?php
require_once __DIR__ . '/../vendor/autoload.php';

$db = \App\Core\Database\Database::getInstance();
$items = $db->fetchAll('SELECT id, name, url, icon, parent_id, section, order_index, is_active, permission_key FROM admin_menu_items ORDER BY section, order_index');

$sections = [];
foreach ($items as $item) {
    $sections[$item['section']][] = $item;
}

foreach ($sections as $section => $sectionItems) {
    echo "\n=== " . strtoupper($section) . " (" . count($sectionItems) . " items) ===\n";
    foreach ($sectionItems as $item) {
        $status = $item['is_active'] ? 'OK' : 'OFF';
        $parent = $item['parent_id'] ? "parent:{$item['parent_id']}" : 'root';
        echo sprintf("[%s] %-40s %-50s (%s)\n", $status, $item['name'], $item['url'], $parent);
    }
}

echo "\nTotal: " . count($items) . " items across " . count($sections) . " sections\n";

// Check which URLs exist as routes in web.php
echo "\n=== ROUTE COVERAGE ===\n";
$webRoutes = file_get_contents(__DIR__ . '/../routes/web.php');
$missing = [];
$found = 0;
foreach ($items as $item) {
    if (!$item['is_active'] || empty($item['url'])) continue;
    $url = trim($item['url'], '/');
    // Check various route patterns
    if (strpos($webRoutes, "'/$url'") !== false || 
        strpos($webRoutes, "/$url") !== false ||
        strpos($webRoutes, "GET /$url") !== false ||
        strpos($webRoutes, "POST /$url") !== false) {
        $found++;
    } else {
        $missing[] = sprintf("%-40s %s", $item['name'], $item['url']);
    }
}
echo "Routes found: $found\n";
echo "Routes missing: " . count($missing) . "\n";
foreach ($missing as $m) {
    echo "  MISSING: $m\n";
}

// Check for duplicate URLs
echo "\n=== DUPLICATE URLS ===\n";
$urlMap = [];
foreach ($items as $item) {
    if (!empty($item['url'])) {
        $urlMap[$item['url']][] = $item['name'];
    }
}
foreach ($urlMap as $url => $names) {
    if (count($names) > 1) {
        echo "  DUPLICATE: $url => " . implode(', ', $names) . "\n";
    }
}

// Check parent references
echo "\n=== ORPHANED PARENTS ===\n";
$ids = array_column($items, 'id');
foreach ($items as $item) {
    if ($item['parent_id'] && !in_array($item['parent_id'], $ids)) {
        echo "  ORPHAN: {$item['name']} references parent {$item['parent_id']}\n";
    }
}

// Check for empty URLs
echo "\n=== EMPTY URLs ===\n";
foreach ($items as $item) {
    if (empty($item['url']) || $item['url'] === '#') {
        echo "  EMPTY: {$item['name']} (section: {$item['section']})\n";
    }
}?>
<?php
require 'config/bootstrap.php';
$db = App\Core\Database\Database::getInstance();

// Get all menu items with URL
$rs = $db->query("SELECT id, name, url, section FROM admin_menu_items WHERE url != '#' ORDER BY section");
$menuItems = $rs->fetchAll();

echo "=== Checking Routes for " . count($menuItems) . " Menu Items ===\n\n";

$working = 0;
$broken = 0;
$redirects = 0;
$notFound = [];

foreach ($menuItems as $item) {
    $url = $item['url'];
    
    // Skip external URLs or special patterns
    if (strpos($url, 'http') === 0 || strpos($url, '//') !== false) {
        continue;
    }
    
    // Build full URL
    $fullUrl = 'http://localhost/apsdreamhome' . $url;
    
    // Make request
    $ctx = stream_context_create(['http' => ['timeout' => 3, 'follow_location' => 0]]);
    $code = @file_get_contents($fullUrl, false, $ctx);
    $status = isset($http_response_header[0]) ? $http_response_header[0] : '';
    
    if (preg_match('/HTTP\/\d+.(\d+)/', $status, $m)) {
        $code = $m[1];
    } else {
        $code = 0;
    }
    
    if ($code == 200) {
        $working++;
    } elseif ($code == 302 || $code == 301) {
        $redirects++;
    } else {
        $broken++;
        $notFound[] = "$url (HTTP $code)";
    }
}

echo "Working (200): $working\n";
echo "Redirects (302): $redirects\n";
echo "Broken/Not Found: $broken\n\n";

echo "=== Broken Routes ===\n";
foreach (array_slice($notFound, 0, 30) as $r) {
    echo "  $r\n";
}

echo "\nTotal checked: " . count($menuItems) . "\n";
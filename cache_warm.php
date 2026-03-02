<?php
/**
 * APS Dream Home - Cache Warming Script
 */

require_once __DIR__ . '/config/paths.php';
require_once APP_PATH . '/Core/Cache.php';

echo '🔥 APS DREAM HOME - CACHE WARMING\n';
echo '================================\n\n';

$cache = App\Core\Cache::getInstance();

// Warm common pages
$pages = [
    'home' => '/',
    'properties' => '/properties',
    'about' => '/about',
    'contact' => '/contact'
];

foreach ($pages as $name => $url) {
    echo "Warming cache for $name...\n";
    // Simulate page content
    $content = "<html><body><h1>$name</h1></body></html>";
    $cache->set('page_' . md5($url), $content, 3600);
    echo "✅ $name cached\n";
}

echo '\n🔥 Cache warming completed!\n';

<?php

namespace App\Services\Legacy;
// Include unified config for proper BASE_URL
require_once __DIR__ . '/config/unified_config.php';

// Set content type to XML for search engines
header('Content-Type: application/xml; charset=utf-8');

// Get the base URL
$baseUrl = BASE_URL;

// Define all pages in the website
$pages = [
    // Main pages
    [
        'url' => $baseUrl,
        'changefreq' => 'daily',
        'priority' => '1.0',
        'lastmod' => date('Y-m-d')
    ],
    [
        'url' => $baseUrl . 'properties',
        'changefreq' => 'weekly',
        'priority' => '0.9',
        'lastmod' => date('Y-m-d')
    ],
    [
        'url' => $baseUrl . 'projects',
        'changefreq' => 'weekly',
        'priority' => '0.9',
        'lastmod' => date('Y-m-d')
    ],
    [
        'url' => $baseUrl . 'about',
        'changefreq' => 'monthly',
        'priority' => '0.8',
        'lastmod' => date('Y-m-d')
    ],
    [
        'url' => $baseUrl . 'contact',
        'changefreq' => 'monthly',
        'priority' => '0.8',
        'lastmod' => date('Y-m-d')
    ],
    [
        'url' => $baseUrl . 'gallery',
        'changefreq' => 'weekly',
        'priority' => '0.8',
        'lastmod' => date('Y-m-d')
    ],
    [
        'url' => $baseUrl . 'testimonials',
        'changefreq' => 'monthly',
        'priority' => '0.7',
        'lastmod' => date('Y-m-d')
    ],
    [
        'url' => $baseUrl . 'team',
        'changefreq' => 'monthly',
        'priority' => '0.7',
        'lastmod' => date('Y-m-d')
    ],
    
    // User pages
    [
        'url' => $baseUrl . 'login',
        'changefreq' => 'monthly',
        'priority' => '0.6',
        'lastmod' => date('Y-m-d')
    ],
    [
        'url' => $baseUrl . 'register_simple',
        'changefreq' => 'monthly',
        'priority' => '0.6',
        'lastmod' => date('Y-m-d')
    ],
    
    // Legal pages
    [
        'url' => $baseUrl . 'privacy-policy',
        'changefreq' => 'yearly',
        'priority' => '0.3',
        'lastmod' => date('Y-m-d')
    ],
    [
        'url' => $baseUrl . 'terms-of-service',
        'changefreq' => 'yearly',
        'priority' => '0.3',
        'lastmod' => date('Y-m-d')
    ]
];

// Output XML sitemap
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

foreach ($pages as $page) {
    echo '<url>';
    echo '<loc>' . h($page['url']) . '</loc>';
    echo '<lastmod>' . $page['lastmod'] . '</lastmod>';
    echo '<changefreq>' . $page['changefreq'] . '</changefreq>';
    echo '<priority>' . $page['priority'] . '</priority>';
    echo '</url>';
}

echo '</urlset>';
?>

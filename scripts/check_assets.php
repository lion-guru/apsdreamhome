<?php
/**
 * Check if required assets are accessible
 */

// Include configuration
require_once __DIR__ . '/includes/config.php';

// Required assets to check
$assets = [
    'Bootstrap CSS (CDN)' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'Custom CSS' => '/assets/css/style.css',
    'Font Awesome (CDN)' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'jQuery (CDN)' => 'https://code.jquery.com/jquery-3.6.0.min.js',
    'Bootstrap JS (CDN)' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'Swiper JS (CDN)' => 'https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js',
    'Custom JS' => '/assets/js/main.js'
];

// Check if file exists and is readable/accessible
function checkFile($path, $basePath = null) {
    // For remote files (CDN)
    if (strpos($path, 'http') === 0) {
        $headers = @get_headers($path);
        return [
            'exists' => $headers && strpos($headers[0], '200'),
            'is_remote' => true,
            'url' => $path
        ];
    }
    
    // For local files
    $fullPath = ($basePath ?: $_SERVER['DOCUMENT_ROOT']) . (strpos($path, '/') === 0 ? $path : '/' . $path);
    $exists = file_exists($fullPath) && is_readable($fullPath);
    
    return [
        'exists' => $exists,
        'is_remote' => false,
        'path' => $fullPath,
        'url' => SITE_URL . (strpos($path, '/') === 0 ? $path : '/' . $path)
    ];
}

// Check SITE_URL constant
echo "<h2>Configuration Check</h2>";
if (!defined('SITE_URL')) {
    // Try to define it if not defined
    define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/apsdreamhomefinal');
    echo "<div style='color: orange;'>⚠️ SITE_URL was not defined, using: " . SITE_URL . "</div>";
} else {
    echo "<div style='color: green;'>✓ SITE_URL is defined as: " . SITE_URL . "</div>";
}

// Check if config.php was loaded
echo "<div style='color: green;'>✓ Configuration loaded from: " . realpath(__DIR__ . '/includes/config.php') . "</div>";

// Check assets
echo "<h2>Asset Check</h2>";
echo "<table border='1' cellpadding='10' cellspacing='0' style='width: 100%; border-collapse: collapse; font-family: Arial, sans-serif;'>";
echo "<tr style='background-color: #f8f9fa;'><th style='text-align: left; padding: 12px;'>Asset</th><th style='text-align: left; padding: 12px;'>Type</th><th style='text-align: left; padding: 12px;'>Status</th><th style='text-align: left; padding: 12px;'>Location</th></tr>";

foreach ($assets as $name => $path) {
    $result = checkFile($path, $_SERVER['DOCUMENT_ROOT'] . '/apsdreamhomefinal');
    $status = $result['exists'] 
        ? "<span style='color: green;'>✓ Available</span>" 
        : "<span style='color: red;'>❌ Not Found</span>";
    
    $type = $result['is_remote'] ? 'CDN' : 'Local';
    $location = $result['is_remote'] 
        ? "<a href='{$result['url']}' target='_blank' style='color: #0d6efd; text-decoration: none;'>{$result['url']}</a>"
        : "<span title='{$result['path']}'>{$result['url']}</span>";
    
    echo "<tr style='border-bottom: 1px solid #dee2e6;'>";
    echo "<td style='padding: 12px; border-bottom: 1px solid #dee2e6;'>$name</td>";
    echo "<td style='padding: 12px; border-bottom: 1px solid #dee2e6;'>$type</td>";
    echo "<td style='padding: 12px; border-bottom: 1px solid #dee2e6;'>$status</td>";
    echo "<td style='padding: 12px; border-bottom: 1px solid #dee2e6;'>$location</td>";
    echo "</tr>";
}
echo "</table>";

// Server configuration
echo "<h2>Server Configuration</h2>";
echo "<table class='table table-bordered'>";

echo "<tr><th>Setting</th><th>Value</th></tr>";

// Check mod_rewrite
$mod_rewrite = function_exists('apache_get_modules') ? in_array('mod_rewrite', apache_get_modules()) : false;
echo "<tr><td>mod_rewrite</td><td>" . ($mod_rewrite ? "<span style='color: green;'>enabled</span>" : "<span style='color: red;'>disabled</span>") . "</td></tr>";

// Document root
echo "<tr><td>Document Root</td><td>" . htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? 'Not set') . "</td></tr>";

// PHP Version
echo "<tr><td>PHP Version</td><td>" . phpversion() . "</td></tr>";

// Web Server
echo "<tr><td>Web Server</td><td>" . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "</td></tr>";

// Check .htaccess
$htaccess = @file_get_contents(__DIR__ . '/.htaccess');
if ($htaccess !== false) {
    echo "<tr><td>.htaccess</td><td><pre style='max-height: 100px; overflow: auto;'>" . htmlspecialchars($htaccess) . "</pre></td></tr>";
} else {
    echo "<tr><td>.htaccess</td><td><span style='color: orange;'>Not found or not readable</span></td></tr>";
}

echo "</table>";

// Add some basic styling
echo "<style>
    body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
    h1, h2, h3 { color: #333; }
    table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
    th { background-color: #f5f5f5; text-align: left; padding: 8px; }
    td { padding: 8px; border: 1px solid #ddd; vertical-align: top; }
    pre { margin: 0; white-space: pre-wrap; }
    .btn { display: inline-block; padding: 3px 8px; text-decoration: none; border-radius: 3px; font-size: 12px; }
    .btn-primary { background-color: #0d6efd; color: white; }
    .btn-primary:hover { background-color: #0b5ed7; }
</style>";
?>

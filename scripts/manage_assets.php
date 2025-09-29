<?php
// Asset Management and Redirection Script

// Configuration of preferred asset paths
$preferredAssets = [
    // JS Libraries
    'jquery' => '/vendor/jquery-3.6.0.min.js',
    'bootstrap' => '/vendor/bootstrap/bootstrap.min.js',
    'lightbox' => '/vendor/lightbox2/dist/js/lightbox.min.js',
    'select2' => '/vendor/select2/select2.min.js',
    
    // CSS Libraries
    'bootstrap_css' => '/vendor/bootstrap/bootstrap.min.css',
    'lightbox_css' => '/vendor/lightbox2/dist/css/lightbox.min.css',
    'select2_css' => '/vendor/select2/select2.min.css'
];

function getPreferredAsset($type, $fallback = null) {
    global $preferredAssets;
    return isset($preferredAssets[$type]) ? $preferredAssets[$type] : $fallback;
}

// Logging function for tracking asset redirections
function logAssetRedirect($oldPath, $newPath) {
    $logFile = __DIR__ . '/asset_redirect.log';
    $logEntry = date('Y-m-d H:i:s') . " | Old: $oldPath | New: $newPath\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// Main asset resolution function
function resolveAsset($requestedPath) {
    $assetMappings = [
        // Add potential duplicate paths here
        '/admin/assets/js/jquery-3.2.1.min.js' => getPreferredAsset('jquery'),
        '/backup_duplicates/js/jquery.js' => getPreferredAsset('jquery'),
        // Add more mappings as needed
    ];

    return isset($assetMappings[$requestedPath]) 
        ? $assetMappings[$requestedPath] 
        : $requestedPath;
}

// Usage example in other PHP files:
// require_once 'manage_assets.php';
// $asset = resolveAsset('/path/to/duplicate/asset.js');
?>

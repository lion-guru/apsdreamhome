<?php
// Duplicate Asset Cleanup and Management Script

$duplicateAssets = [
    // JS Libraries
    'jquery' => [
        '/admin/assets/js/jquery-3.2.1.min.js',
        '/backup_duplicates/js/jquery.js',
        // Add more duplicate paths
    ],
    'bootstrap' => [
        '/backup_duplicates/js/bootstrap.min.js',
        '/backup_duplicates/js/bootstrap.js',
        // Add more duplicate paths
    ],
    
    // CSS Libraries
    'bootstrap_css' => [
        '/backup_duplicates/css/bootstrap.min.css',
        '/backup_duplicates/css/bootstrap.css',
        // Add more duplicate paths
    ]
];

function cleanupDuplicateAssets() {
    global $duplicateAssets;
    $preferredAsset = '/vendor/preferred_assets/';
    $logFile = __DIR__ . '/asset_cleanup.log';
    
    foreach ($duplicateAssets as $type => $paths) {
        $keepFirst = true;
        foreach ($paths as $path) {
            if ($keepFirst) {
                // Keep the first asset, move to preferred location
                rename($path, $preferredAsset . basename($path));
                $keepFirst = false;
                
                // Log the action
                $logEntry = date('Y-m-d H:i:s') . " | Preserved: $path\n";
                file_put_contents($logFile, $logEntry, FILE_APPEND);
            } else {
                // Delete subsequent duplicates
                unlink($path);
                
                // Log the deletion
                $logEntry = date('Y-m-d H:i:s') . " | Deleted: $path\n";
                file_put_contents($logFile, $logEntry, FILE_APPEND);
            }
        }
    }
}

// Optional: Uncomment to run cleanup automatically
// cleanupDuplicateAssets();
?>

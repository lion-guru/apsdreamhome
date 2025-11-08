<?php
/**
 * Script to download and set up local copies of required assets
 */

// Create necessary directories if they don't exist
$dirs = [
    'assets/css',
    'assets/js',
    'assets/fonts',
    'assets/plugins/font-awesome/css',
    'assets/plugins/font-awesome/webfonts'
];

foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Assets to download
$assets = [
    // Bootstrap 5.3.0
    [
        'url' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
        'save_path' => 'assets/css/bootstrap.min.css'
    ],
    [
        'url' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
        'save_path' => 'assets/js/bootstrap.bundle.min.js'
    ],
    
    // Font Awesome 6.4.0
    [
        'url' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
        'save_path' => 'assets/plugins/font-awesome/css/all.min.css'
    ],
    [
        'url' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/fa-solid-900.woff2',
        'save_path' => 'assets/plugins/font-awesome/webfonts/fa-solid-900.woff2'
    ],
    [
        'url' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/fa-regular-400.woff2',
        'save_path' => 'assets/plugins/font-awesome/webfonts/fa-regular-400.woff2'
    ],
    [
        'url' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/fa-brands-400.woff2',
        'save_path' => 'assets/plugins/font-awesome/webfonts/fa-brands-400.woff2'
    ],
    
    // jQuery 3.6.0
    [
        'url' => 'https://code.jquery.com/jquery-3.6.0.min.js',
        'save_path' => 'assets/js/jquery.min.js'
    ],
    
    // Swiper 8.0.0
    [
        'url' => 'https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css',
        'save_path' => 'assets/plugins/swiper/swiper-bundle.min.css'
    ],
    [
        'url' => 'https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js',
        'save_path' => 'assets/plugins/swiper/swiper-bundle.min.js'
    ]
];

// Download function
function downloadFile($url, $savePath) {
    $content = @file_get_contents($url);
    if ($content === false) {
        return [
            'success' => false,
            'message' => "Failed to download: $url"
        ];
    }
    
    $dir = dirname($savePath);
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
    
    $result = file_put_contents($savePath, $content);
    if ($result === false) {
        return [
            'success' => false,
            'message' => "Failed to save: $savePath"
        ];
    }
    
    return [
        'success' => true,
        'message' => "Successfully downloaded: $url"
    ];
}

// Download all assets
header('Content-Type: text/plain');
echo "Starting asset download...\n\n";

foreach ($assets as $asset) {
    echo "Downloading {$asset['url']}... ";
    $result = downloadFile($asset['url'], $asset['save_path']);
    
    if ($result['success']) {
        echo "✓ Saved to {$asset['save_path']}\n";
    } else {
        echo "✗ Failed: {$result['message']}\n";
    }
}

echo "\nAsset download complete!\n";
?>

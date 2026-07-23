<?php
$cacheDir = __DIR__ . '/storage/cache';
if (is_dir($cacheDir)) {
    $files = glob($cacheDir . '/*.cache');
    $count = 0;
    foreach ($files as $file) {
        if (unlink($file)) {
            $count++;
        }
    }
    echo "Deleted $count cache files from storage/cache.\n";
} else {
    echo "Cache directory not found.\n";
}

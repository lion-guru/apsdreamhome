<?php
/**
 * APS Dream Home - Image Optimization Script
 */

require_once __DIR__ . '/config/paths.php';
require_once APP_PATH . '/Core/ImageOptimizer.php';

echo '🖼️ APS DREAM HOME - IMAGE OPTIMIZATION\n';
echo '=====================================\n\n';

$optimizer = new App\Core\ImageOptimizer();
$sourceDir = PUBLIC_PATH . '/assets/images';
$processed = 0;
$failed = 0;

// Process all images in source directory
$images = glob($sourceDir . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);

foreach ($images as $image) {
    $filename = basename($image);
    echo "Optimizing $filename...\n";

    if ($optimizer->optimize($image, $filename)) {
        echo "✅ $filename optimized\n";
        $processed++;
    } else {
        echo "❌ $filename failed\n";
        $failed++;
    }
}

echo "\n📊 OPTIMIZATION SUMMARY:\n";
echo "✅ Processed: $processed images\n";
echo "❌ Failed: $failed images\n";
echo "🎉 Image optimization completed!\n";

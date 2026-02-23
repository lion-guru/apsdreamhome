<?php

/**
 * Cache Warming Script for APS Dream Home
 * Pre-populates cache with frequently accessed data
 */

require_once __DIR__ . '/bootstrap/app.php';

use App\Services\Caching\CacheManager;

echo "🔄 Starting Cache Warming Process\n";
echo "================================\n\n";

try {
    $cacheManager = new CacheManager(null);

    echo "📊 Warming frequently accessed data...\n";

    // Warm cache with different priorities
    $cacheManager->warmCache();

    // Additional warming for specific contexts
    echo "🎯 Warming context-specific data...\n";

    // Warm dashboard data
    $cacheManager->predictiveWarm(null, ['page' => 'dashboard']);

    // Warm property listing data
    $cacheManager->predictiveWarm(null, [
        'page' => 'properties',
        'filters' => ['status' => 'available', 'featured' => true]
    ]);

    echo "✅ Cache warming completed successfully!\n";

    // Display cache statistics
    $stats = $cacheManager->getStats();
    echo "\n📈 Cache Statistics:\n";
    echo "-------------------\n";

    foreach ($stats['layers'] as $layer => $layerStats) {
        echo "• {$layer}: " . json_encode($layerStats) . "\n";
    }

    if (isset($stats['redis'])) {
        echo "• Redis: Connected clients: {$stats['redis']['connected_clients']}, Memory: " . round($stats['redis']['used_memory'] / 1024 / 1024, 2) . "MB\n";
    }

} catch (Exception $e) {
    echo "❌ Cache warming failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n🚀 Cache is now optimized and ready for high-performance operation!\n";

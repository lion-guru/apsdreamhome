<?php
/**
 * APS Dream Home - Cache Cleanup Script
 */

require_once __DIR__ . '/config/paths.php';
require_once APP_PATH . '/Core/Cache.php';

echo '🧹 APS DREAM HOME - CACHE CLEANUP\n';
echo '================================\n\n';

$cache = App\Core\Cache::getInstance();

echo 'Clearing expired cache files...\n';
$cache->clear();

echo '✅ Cache cleanup completed!\n';

<?php
require_once __DIR__ . '/vendor/autoload.php';
// Or if no composer, require bootstrap
if (file_exists(__DIR__ . '/bootstrap.php')) require_once __DIR__ . '/bootstrap.php';
elseif (file_exists(__DIR__ . '/app/Core/Autoloader.php')) {
    require_once __DIR__ . '/app/Core/Autoloader.php';
    \App\Core\Autoloader::register();
}

\App\Core\Cache::clear();
if (class_exists('\App\Services\Cache\HotPathCacheService')) {
    \App\Services\Cache\HotPathCacheService::flushAll();
}

echo "Cache cleared!\n";

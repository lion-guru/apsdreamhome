<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Core/Cache.php';
$key = 'admin_sidebar_all';
$safeKey = md5($key);
$cacheFile = __DIR__ . '/../storage/cache/' . $safeKey . '.cache';
if (file_exists($cacheFile)) {
    echo "Cache file exists! Size: " . filesize($cacheFile) . "\n";
    $content = file_get_contents($cacheFile);
    $data = json_decode($content, true);
    if (is_array($data) && isset($data['value']) && is_array($data['value'])) {
        echo "Items in cache: " . count($data['value']) . "\n";
    } else {
        echo "Cache content invalid or not array: " . substr($content, 0, 100) . "\n";
    }
} else {
    echo "Cache file does not exist.\n";
}?>
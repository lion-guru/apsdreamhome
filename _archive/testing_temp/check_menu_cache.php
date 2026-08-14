<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance()->getPdo();
$count = $db->query("SELECT COUNT(*) FROM admin_menu_items WHERE is_active = 1")->fetchColumn();
echo "Total active menu items: $count\n";

$cacheFile = __DIR__ . '/../storage/cache/admin_sidebar_all.cache';
if (file_exists($cacheFile)) {
    echo "Cache file exists!\n";
    echo "Size: " . filesize($cacheFile) . "\n";
    echo "Content snippet: " . substr(file_get_contents($cacheFile), 0, 100) . "\n";
} else {
    echo "Cache file does not exist.\n";
}?>
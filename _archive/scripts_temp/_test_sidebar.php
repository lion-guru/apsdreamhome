<?php
require_once __DIR__ . '/../vendor/autoload.php';

$svc = new \App\Services\AdminMenuService();
$items = $svc->getMenuItems('super_admin');
echo "Total items: " . count($items) . PHP_EOL;
$technology = array_filter($items, fn($i) => ($i['section'] ?? '') === 'technology');
echo "Technology section: " . count($technology) . " items" . PHP_EOL;
foreach ($technology as $t) {
    echo "  - {$t['name']} -> {$t['url']}" . PHP_EOL;
}
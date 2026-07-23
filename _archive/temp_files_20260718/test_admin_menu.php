<?php
require_once __DIR__ . '/vendor/autoload.php';

try {
    $menuService = new \App\Services\AdminMenuService();
    $items = $menuService->getMenuItems('admin');
    echo "Admin menu items count: " . count($items) . "\n";
    if (count($items) == 0) {
        echo "No items! Let's check Cache...\n";
    }
} catch (\Throwable $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

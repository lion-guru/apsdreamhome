<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Core/Database/Database.php';

session_start();
$_SESSION['admin_id'] = 1;
$_SESSION['role'] = 'admin';

try {
    $menuService = new \App\Services\AdminMenuService();
    $items = $menuService->getMenuItems('admin', 1);
    echo "Success! Retrieved " . count($items) . " menu items.\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}

<?php
@session_start();
$_SESSION['admin_id'] = 1;
$_SESSION['admin_role'] = 'super_admin';
$_SESSION['role'] = 'super_admin';

require __DIR__ . '/../vendor/autoload.php';

$menuService = new \App\Services\AdminMenuService();
$items = $menuService->getMenuItems();

echo "Role: " . \App\Http\Middleware\RBACManager::getUserRole() . "\n";
echo "Menu items count: " . count($items) . "\n";

$managerItems = $menuService->getMenuItems('manager');
echo "Manager menu items count: " . count($managerItems) . "\n";

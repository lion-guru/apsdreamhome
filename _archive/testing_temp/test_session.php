<?php
// Put this in admin dashboard view to see what it prints
session_start();
echo "Session ID: " . session_id() . "<br>";
echo "admin_id: " . ($_SESSION['admin_id'] ?? 'null') . "<br>";
echo "admin_role: " . ($_SESSION['admin_role'] ?? 'null') . "<br>";
echo "role: " . ($_SESSION['role'] ?? 'null') . "<br>";

$menuService = new \App\Services\AdminMenuService();
$items = $menuService->getMenuItems();
echo "Menu items count: " . count($items) . "<br>";

<?php
session_start();
$_SESSION['admin_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['admin_role'] = 'superadmin';
$_SESSION['csrf_token'] = 'test';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Core/Database/Database.php';

$page_title = 'Dashboard';
$active_page = 'dashboard';
$base = '/apsdreamhome';
$adminName = 'Admin';
$adminRole = 'superadmin';
$newLeadsCount = 0;
$newInquiriesCount = 0;

ob_start();
echo "<h1>Test Content</h1>";
$content = ob_get_clean();

ob_start();
include __DIR__ . '/app/views/admin/layouts/unified.php';
$html = ob_get_clean();
file_put_contents(__DIR__ . '/admin_dashboard_test.html', $html);
echo "Done";

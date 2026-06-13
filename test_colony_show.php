<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/public/index.php';

// Simulate the request
$_SERVER['REQUEST_URI'] = '/admin/colonies/2';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['id'] = '2';

// Simulate admin session
session_start();
$_SESSION['admin_id'] = 1;
$_SESSION['admin_role'] = 'super_admin';
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

require_once __DIR__ . '/app/Http/Controllers/Admin/ColonyController.php';

$controller = new \App\Http\Controllers\Admin\ColonyController();
$controller->show(2);

echo "SUCCESS";
<?php
require __DIR__ . '/../vendor/autoload.php';

$_SERVER['REQUEST_URI'] = '/user/dashboard';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTPS'] = 'off';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SESSION = [];
$_SESSION['user_id'] = 1;
$_SESSION['user_type'] = 'customer';
$_SESSION['user_email'] = 'test@example.com';
$_SESSION['user_name'] = 'Test User';

ob_start();
try {
    $ctrl = new \App\Http\Controllers\Front\UserController();
    $ctrl->dashboard();
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
}
$out = ob_get_clean();

echo "Output: " . strlen($out) . " bytes\n";
echo (strpos($out, 'header_new_v2') === false) ? "No broken header: PASS\n" : "STILL HAS header_new_v2: FAIL\n";
echo (strpos($out, 'Welcome') !== false || strpos($out, 'Dashboard') !== false) ? "Has dashboard content: PASS\n" : "Missing dashboard content: FAIL\n";
echo (strpos($out, '<!DOCTYPE html>') !== false) ? "Has DOCTYPE (full HTML): PASS\n" : "No DOCTYPE: INFO\n";
echo (strpos($out, 'header.php') === false && strpos($out, 'layouts/header') === false) ? "No layout includes in content: PASS\n" : "Still has layout includes: FAIL\n";
if (strlen($out) < 300) echo "Output preview: " . substr($out, 0, 300) . "\n";

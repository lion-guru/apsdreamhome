<?php
// Quick employee login helper for browser testing
// Usage: _emp_login.php?emp_id=4 (logs in as employee #4 = HR Manager)
define('APS_ROOT', __DIR__);
require_once APS_ROOT . '/config/bootstrap.php';
require_once APS_ROOT . '/vendor/autoload.php';

use App\Core\Database\Database;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$empId = $_GET['emp_id'] ?? null;
if (!$empId) {
    echo "Usage: _emp_login.php?emp_id=4\n";
    echo "Employees: 1=Marketing Director, 2=Digital Marketer, 3=Developer(IT), 4=HR Manager, 5=Ops Manager, 6=Sales Executive\n";
    exit;
}

$db = Database::getInstance();
$emp = $db->fetchOne("SELECT e.*, u.name, u.email, u.role FROM employees e INNER JOIN users u ON e.user_id = u.id WHERE e.id = ?", [$empId]);
if (!$emp) {
    echo "Employee #{$empId} not found!";
    exit;
}

// Clear any previous session data
session_unset();

// Set session like test_login does
$_SESSION['user_id'] = $emp['user_id'];
$_SESSION['role'] = 'employee';
$_SESSION['user_email'] = $emp['email'];
$_SESSION['user_name'] = $emp['name'];
$_SESSION['logged_in'] = true;
$_SESSION['employee_id'] = $emp['id'];

session_write_close();
header('Location: ' . BASE_URL . '/employee/dashboard');
exit;

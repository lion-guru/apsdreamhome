<?php
define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';

$users = [
    'Super Admin' => ['email' => 'admin@apsdreamhome.com', 'url' => '/admin/erp'],
    'Associate' => ['email' => 'rajesh.associate@apsdreamhome.test', 'url' => '/associate/dashboard'],
    'Freelancer Agent' => ['email' => 'sanjay.freelancer@apsdreamhome.test', 'url' => '/agent/dashboard'],
    'Employee Agent' => ['email' => 'pooja.employee@apsdreamhome.test', 'url' => '/agent/dashboard'],
    'Employee' => ['email' => 'emp_test@apsdreamhome.test', 'url' => '/employee/dashboard'],
    'Customer' => ['email' => 'customer_test@apsdreamhome.test', 'url' => '/user/dashboard'],
];

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

foreach ($users as $label => $info) {
    echo "=========================================================\n";
    echo "ROLE: {$label} ({$info['email']})\n";
    $u = $pdo->query("SELECT id, name, email, role FROM users WHERE email = '{$info['email']}'")->fetch(PDO::FETCH_ASSOC);
    echo "DB Role: {$u['role']} (ID: {$u['id']})\n";
    
    // Check if associate / agent / employee
    $ass = $pdo->query("SELECT id FROM associates WHERE user_id = {$u['id']}")->fetch(PDO::FETCH_ASSOC);
    $emp = $pdo->query("SELECT id, designation, department FROM employees WHERE user_id = {$u['id']}")->fetch(PDO::FETCH_ASSOC);
    echo "Associate record: " . ($ass ? "ID " . $ass['id'] : "None") . "\n";
    echo "Employee record: " . ($emp ? "ID " . $emp['id'] . ", Desig: " . $emp['designation'] : "None") . "\n";
}

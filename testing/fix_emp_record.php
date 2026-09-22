<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Core\Database\Database;

$db = Database::getInstance();
$emp = $db->fetchOne("SELECT * FROM employees WHERE user_id = 121194");
if (!$emp) {
    echo "NO EMP RECORD FOR 121194\n";
    $sample = $db->fetchOne("SELECT * FROM employees LIMIT 1");
    print_r(array_keys($sample ?: []));
    
    // Create employee record
    $db->execute("INSERT INTO employees (tenant_id, user_id, name, email, phone, role, department, designation, employee_code, status, created_at, updated_at) 
                  VALUES (1, 121194, 'Emp Test', 'emp_test@apsdreamhome.test', '9812345678', 'employee', 'Sales', 'Executive', 'EMP20261194', 'active', NOW(), NOW())");
    echo "CREATED EMPLOYEE RECORD FOR 121194!\n";
} else {
    echo "EMP RECORD EXISTS: ID " . $emp['id'] . "\n";
}

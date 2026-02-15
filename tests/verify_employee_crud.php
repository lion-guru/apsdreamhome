<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/core/autoload.php';
require_once BASE_PATH . '/app/Core/Config.php';
// Also need Env class if Config uses it.
// Config.php uses Env::get().
// Check if Env is autoloaded or needs require.
// Autoloader should handle App\Core\Env if it exists.

use App\Models\Employee;
use App\Core\Database;

echo "Starting Employee CRUD Test...\n";

try {
    $employeeModel = new Employee();
    
    // 1. Create
    echo "\n[TEST] Creating Employee...\n";
    $timestamp = time();
    $data = [
        'name' => 'Test Emp ' . $timestamp,
        'email' => 'test_' . $timestamp . '@example.com',
        'phone' => '1234567890',
        'password' => 'password123',
        'role_id' => 1, // Admin role
        'department_id' => 1, // General department
        'designation' => 'Tester',
        'salary' => 50000.00,
        'joining_date' => date('Y-m-d'),
        'status' => 'active',
        'address' => '123 Test St',
        'notes' => 'Created via test script'
    ];

    $employeeId = $employeeModel->createEmployee($data);
    echo "Employee created with ID: $employeeId\n";

    // 2. Read
    echo "\n[TEST] Reading Employee...\n";
    $emp = $employeeModel->getEmployeeById($employeeId);
    if ($emp && $emp['email'] === $data['email']) {
        echo "PASS: Employee found and email matches.\n";
        echo "Join Date: " . $emp['join_date'] . "\n";
        echo "Notes: " . $emp['notes'] . "\n";
    } else {
        echo "FAIL: Employee not found or email mismatch.\n";
        print_r($emp);
        exit(1);
    }

    // 3. Update
    echo "\n[TEST] Updating Employee...\n";
    $updateData = [
        'designation' => 'Senior Tester',
        'salary' => 60000.00,
        'join_date' => date('Y-m-d', strtotime('-1 day')),
        'notes' => 'Updated notes via test'
    ];

    $result = $employeeModel->updateEmployee($employeeId, $updateData);
    if ($result) {
        $updatedEmp = $employeeModel->getEmployeeById($employeeId);
        if ($updatedEmp['designation'] === 'Senior Tester' && 
            $updatedEmp['salary'] == 60000.00 &&
            $updatedEmp['join_date'] === $updateData['join_date'] &&
            $updatedEmp['notes'] === 'Updated notes via test') {
            echo "PASS: Update successful.\n";
        } else {
            echo "FAIL: Update failed verification.\n";
            print_r($updatedEmp);
        }
    } else {
        echo "FAIL: Update returned false.\n";
    }

    // 4. Delete (Soft Delete)
    echo "\n[TEST] Deleting Employee (Soft Delete)...\n";
    if (method_exists($employeeModel, 'deleteEmployee')) {
        $result = $employeeModel->deleteEmployee($employeeId);
        if ($result) {
            $deletedEmp = $employeeModel->getEmployeeById($employeeId);
            if ($deletedEmp['status'] === 'deleted') {
                echo "PASS: Employee soft deleted.\n";
            } else {
                echo "FAIL: Employee status is " . $deletedEmp['status'] . "\n";
            }
        } else {
            echo "FAIL: deleteEmployee returned false.\n";
        }
    } else {
        echo "SKIP: deleteEmployee method not found.\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

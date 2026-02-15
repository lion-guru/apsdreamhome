<?php
// scripts/migrate_employees.php

require_once __DIR__ . '/../app/core/Database.php';

use App\Core\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

echo "Starting employee migration...\n";

// Get all employees
$stmt = $conn->query("SELECT * FROM employees");
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($employees as $emp) {
    echo "Processing employee: {$emp['name']} ({$emp['email']})...\n";

    // Check if user exists
    $stmtUser = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $stmtUser->execute(['email' => $emp['email']]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    $userId = null;

    if ($user) {
        echo "  User found (ID: {$user['id']}). Linking...\n";
        $userId = $user['id'];
    } else {
        echo "  User not found. Creating user...\n";
        // Create user
        $stmtCreate = $conn->prepare("
            INSERT INTO users (name, email, phone, password, role, status, created_at, updated_at)
            VALUES (:name, :email, :phone, :password, :role, :status, NOW(), NOW())
        ");

        // Map role string to user role enum
        // employees.role is enum('employee','manager','supervisor','executive')
        // users.role is likely similar or varchar
        $userRole = 'employee';
        if ($emp['role'] == 'manager') $userRole = 'manager';
        if ($emp['role'] == 'admin') $userRole = 'admin'; // unlikely for employee table

        try {
            $stmtCreate->execute([
                'name' => $emp['name'],
                'email' => $emp['email'],
                'phone' => $emp['phone'] ?? '0000000000',
                'password' => $emp['password'], // Copy hashed password
                'role' => $userRole,
                'status' => $emp['status']
            ]);
            $userId = $conn->lastInsertId();
            echo "  User created (ID: $userId).\n";
        } catch (Exception $e) {
            echo "  Error creating user: " . $e->getMessage() . "\n";
            continue;
        }
    }

    // Determine Role ID
    // roles table: 1=admin, 2=agent, 3=customer, 4=manager
    $roleId = 2; // Default to Agent/Employee?
    if ($emp['role'] == 'manager') $roleId = 4;
    // If just 'employee', maybe map to 'agent' (2) or create new role?
    // Let's check roles table again.
    // 1=admin, 2=agent, 3=customer, 4=manager.
    // If employee is not manager, assign 2 (agent) for now.

    // Determine Department ID
    // departments table: 1=Sales, 2=Finance, 3=Support, 4=Billing, 5=Legal
    $deptId = null;
    $deptName = strtolower($emp['department'] ?? '');

    if (strpos($deptName, 'sales') !== false) $deptId = 1;
    elseif (strpos($deptName, 'finance') !== false) $deptId = 2;
    elseif (strpos($deptName, 'support') !== false) $deptId = 3;
    elseif (strpos($deptName, 'billing') !== false) $deptId = 4;
    elseif (strpos($deptName, 'legal') !== false) $deptId = 5;
    else $deptId = 1; // Default to Sales if unknown? Or NULL.

    // Update Employee record
    $stmtUpdate = $conn->prepare("
        UPDATE employees 
        SET user_id = :user_id,
            role_id = :role_id,
            department_id = :department_id
        WHERE id = :id
    ");

    $stmtUpdate->execute([
        'user_id' => $userId,
        'role_id' => $roleId,
        'department_id' => $deptId,
        'id' => $emp['id']
    ]);

    echo "  Employee updated.\n";
}

echo "Migration complete.\n";

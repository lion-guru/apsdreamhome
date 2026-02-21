<?php
// tools/consolidate_users.php

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/bootstrap.php';

use App\Core\Database;

echo "Starting User Consolidation & Cleanup...\n";
echo "----------------------------------------\n";

$db = Database::getInstance();
$conn = $db->getConnection();

// Helper to create backup directory
$backupDir = APP_ROOT . '/database/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

// 1. BACKUP
echo "1. Creating Backup...\n";
$tables = ['users', 'employees', 'customers'];
foreach ($tables as $table) {
    try {
        $stmt = $conn->query("SELECT * FROM $table");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        file_put_contents("$backupDir/{$table}_backup_" . date('Y-m-d_H-i-s') . ".json", json_encode($data, JSON_PRETTY_PRINT));
        echo "   ✔ Backup created for '$table' (" . count($data) . " records).\n";
    } catch (Exception $e) {
        echo "   ⚠ Failed to backup '$table': " . $e->getMessage() . "\n";
    }
}

// 2. CONSOLIDATE EMPLOYEES
echo "\n2. Consolidating Employees...\n";
$stmt = $conn->query("SELECT * FROM employees");
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($employees as $emp) {
    $empId = $emp['id'];
    $userId = $emp['user_id'] ?? null;
    $email = $emp['email'] ?? '';
    $name = $emp['name'] ?? 'Employee ' . $empId;
    
    if (!$userId) {
        // Create User
        if (empty($email)) {
            $email = "employee{$empId}@apsdreamhome.com"; // Fallback email
            echo "   ⚠ Employee ID $empId has no email. Generated: $email\n";
        }
        
        // Check if user exists by email
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $existingUser = $stmt->fetchColumn();
        
        if ($existingUser) {
            echo "   -> Linking Employee ID $empId to existing User ID $existingUser (Email match)\n";
            $userId = $existingUser;
        } else {
            echo "   -> Creating new User for Employee ID $empId ($email)\n";
            $passwordHash = password_hash('ChangeMe123!', PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, created_at, updated_at) VALUES (?, ?, ?, 'employee', NOW(), NOW())");
            $stmt->execute([$name, $email, $passwordHash]);
            $userId = $conn->lastInsertId();
        }
        
        // Update Employee with User ID
        $update = $conn->prepare("UPDATE employees SET user_id = ? WHERE id = ?");
        $update->execute([$userId, $empId]);
    } else {
        // Verify User exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo "   [ERROR] Employee ID $empId linked to non-existent User ID $userId. Unlinking.\n";
            $conn->exec("UPDATE employees SET user_id = NULL WHERE id = $empId");
            // Rerun logic next time or handle now? For now, just warn.
        } else {
            // Sync Data: If Employee Name/Email is empty, pull from User
            $updates = [];
            if (empty($emp['name']) && !empty($user['name'])) {
                $updates[] = "name = " . $conn->quote($user['name']);
            }
            if (empty($emp['email']) && !empty($user['email'])) {
                $updates[] = "email = " . $conn->quote($user['email']);
            }
            
            if (!empty($updates)) {
                $sql = "UPDATE employees SET " . implode(', ', $updates) . " WHERE id = $empId";
                $conn->exec($sql);
                echo "   ✔ Synced Employee ID $empId data from User ID $userId\n";
            }
        }
    }
}

// 3. CONSOLIDATE CUSTOMERS
echo "\n3. Consolidating Customers...\n";
$stmt = $conn->query("SELECT * FROM customers");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($customers as $cust) {
    $custId = $cust['id']; // Might be string (CUST-...)
    $userId = $cust['user_id'] ?? null;
    $email = $cust['email'] ?? '';
    $name = $cust['name'] ?? 'Customer ' . $custId;
    
    if (!$userId) {
        // Create User
        if (empty($email)) {
            echo "   ⚠ Customer ID $custId has no email. Skipping User creation (cannot create without email).\n";
            continue; 
        }
        
        // Check if user exists by email
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $existingUser = $stmt->fetchColumn();
        
        if ($existingUser) {
            echo "   -> Linking Customer ID $custId to existing User ID $existingUser (Email match)\n";
            $userId = $existingUser;
        } else {
            echo "   -> Creating new User for Customer ID $custId ($email)\n";
            $passwordHash = password_hash('ChangeMe123!', PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, created_at, updated_at) VALUES (?, ?, ?, 'customer', NOW(), NOW())");
            try {
                $stmt->execute([$name, $email, $passwordHash]);
                $userId = $conn->lastInsertId();
            } catch (Exception $e) {
                echo "   [ERROR] Failed to create user for Customer $custId: " . $e->getMessage() . "\n";
                continue;
            }
        }
        
        // Update Customer with User ID
        // Note: ID might be string, need to handle quoting properly
        $update = $conn->prepare("UPDATE customers SET user_id = ? WHERE id = ?");
        $update->execute([$userId, $custId]);
    } else {
        // Verify User
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Sync Data
            $updates = [];
            if (empty($cust['name']) && !empty($user['name'])) {
                $updates[] = "name = " . $conn->quote($user['name']);
            }
            if (empty($cust['email']) && !empty($user['email'])) {
                $updates[] = "email = " . $conn->quote($user['email']);
            }
            
            if (!empty($updates)) {
                $sql = "UPDATE customers SET " . implode(', ', $updates) . " WHERE id = " . $conn->quote($custId);
                $conn->exec($sql);
                echo "   ✔ Synced Customer ID $custId data from User ID $userId\n";
            }
        }
    }
}

// 4. CLEANUP SCHEMA (Remove Password Columns)
echo "\n4. Cleaning up Schema (Removing Redundant Columns)...\n";

function dropColumnIfExists($conn, $table, $column) {
    try {
        // Check if column exists
        $stmt = $conn->query("SHOW COLUMNS FROM $table LIKE '$column'");
        if ($stmt->fetch()) {
            echo "   Dropping '$column' from '$table'...\n";
            $conn->exec("ALTER TABLE $table DROP COLUMN $column");
            echo "   ✔ Dropped '$column' from '$table'.\n";
        } else {
            echo "   - Column '$column' not found in '$table' (already clean).\n";
        }
    } catch (Exception $e) {
        echo "   [ERROR] Failed to drop column: " . $e->getMessage() . "\n";
    }
}

dropColumnIfExists($conn, 'employees', 'password');
dropColumnIfExists($conn, 'customers', 'password');
// Also remove remember_token from employees/customers as it belongs to User
dropColumnIfExists($conn, 'employees', 'remember_token'); 

echo "\nConsolidation Complete!\n";

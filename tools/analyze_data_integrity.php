<?php
// tools/analyze_data_integrity.php

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/bootstrap.php';

use App\Core\Database;

echo "Starting Data Integrity Analysis...\n";
echo "----------------------------------------\n";

$db = Database::getInstance();
$conn = $db->getConnection();

// Helper to get row count
function getCount($conn, $table) {
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM $table");
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

// 1. Analyze Customers Table
echo "\n1. Analyzing 'customers' table (" . getCount($conn, 'customers') . " rows)...\n";
$stmt = $conn->query("SELECT * FROM customers");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($customers as $customer) {
    echo "  - Customer ID: {$customer['id']}, Name: {$customer['name']}, Email: {$customer['email']}\n";
    
    // Check if linked to user
    if (!empty($customer['user_id'])) {
        $userStmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $userStmt->execute([$customer['user_id']]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "    -> Linked to User ID: {$user['id']}\n";
            // Check for data consistency
            if ($customer['email'] !== $user['email']) {
                echo "    [WARNING] Email mismatch! Customer: {$customer['email']} vs User: {$user['email']}\n";
            }
            if ($customer['name'] !== $user['name']) {
                echo "    [WARNING] Name mismatch! Customer: {$customer['name']} vs User: {$user['name']}\n";
            }
            // Check if password column exists and is populated in customers
            if (!empty($customer['password'])) {
                 echo "    [CRITICAL] Redundant password stored in customers table!\n";
            }
        } else {
            echo "    [ERROR] Linked User ID {$customer['user_id']} NOT FOUND in users table!\n";
        }
    } else {
        // Check if email exists in users table
        $userStmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $userStmt->execute([$customer['email']]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "    -> Found existing User with same email (ID: {$user['id']}). Should be linked!\n";
        } else {
            echo "    -> No matching User found. Standalone customer record.\n";
        }
    }
}

// 2. Analyze Employees Table
echo "\n2. Analyzing 'employees' table (" . getCount($conn, 'employees') . " rows)...\n";
$stmt = $conn->query("SELECT * FROM employees");
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($employees as $employee) {
    echo "  - Employee ID: {$employee['id']}, Name: {$employee['name']}, Email: {$employee['email']}\n";
    
    if (!empty($employee['user_id'])) {
        $userStmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $userStmt->execute([$employee['user_id']]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "    -> Linked to User ID: {$user['id']}\n";
             if ($employee['email'] !== $user['email']) {
                echo "    [WARNING] Email mismatch! Employee: {$employee['email']} vs User: {$user['email']}\n";
            }
            if (!empty($employee['password'])) {
                 echo "    [CRITICAL] Redundant password stored in employees table!\n";
            }
        } else {
            echo "    [ERROR] Linked User ID {$employee['user_id']} NOT FOUND in users table!\n";
        }
    } else {
         $userStmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $userStmt->execute([$employee['email']]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "    -> Found existing User with same email (ID: {$user['id']}). Should be linked!\n";
        } else {
            echo "    -> No matching User found. Standalone employee record.\n";
        }
    }
}

// 3. Analyze Associates Table
echo "\n3. Analyzing 'associates' table (" . getCount($conn, 'associates') . " rows)...\n";
try {
    $stmt = $conn->query("SELECT * FROM associates");
    $associates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($associates as $associate) {
        // Associates table structure might vary, checking keys
        $name = $associate['name'] ?? 'N/A';
        $email = $associate['email'] ?? 'N/A';
        $userId = $associate['user_id'] ?? null;
        
        echo "  - Associate ID: {$associate['id']}, UserID: " . ($userId ?? 'NULL') . "\n";
        
        if ($userId) {
            $userStmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $userStmt->execute([$userId]);
            $user = $userStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                echo "    -> Linked to User ID: {$user['id']}\n";
            } else {
                echo "    [ERROR] Linked User ID {$userId} NOT FOUND in users table!\n";
            }
        } else {
             // Check if email column exists to match
             if ($email !== 'N/A') {
                $userStmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
                $userStmt->execute([$email]);
                $user = $userStmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    echo "    -> Found existing User with same email (ID: {$user['id']}). Should be linked!\n";
                }
             }
        }
    }
} catch (Exception $e) {
    echo "  [INFO] 'associates' table might have different structure or missing columns: " . $e->getMessage() . "\n";
}

echo "\nAnalysis Complete.\n";

<?php
// tools/consolidate_data.php

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/bootstrap.php';

use App\Core\Database;

echo "Starting Data Consolidation...\n";
echo "----------------------------------------\n";

$db = Database::getInstance();
$conn = $db->getConnection();

// Helper to generate a secure random password
function generatePassword($length = 10)
{
    return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*'), 0, $length);
}

// Helper to create a user
function createUser($conn, $name, $email, $role, $password)
{
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, name, email, password, role, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'active', NOW(), NOW())");
    // Generate a username from email or name
    $username = explode('@', $email)[0];

    // Ensure username is unique
    $i = 1;
    $originalUsername = $username;
    while (true) {
        $check = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $check->execute([$username]);
        if ($check->fetchColumn() == 0) break;
        $username = $originalUsername . $i++;
    }

    try {
        $stmt->execute([$username, $name, $email, $hashedPassword, $role]);
        return $conn->lastInsertId();
    } catch (Exception $e) {
        echo "    [ERROR] Failed to create user for $email: " . $e->getMessage() . "\n";
        return false;
    }
}

// 1. Consolidate Employees
echo "\n1. Consolidating Employees...\n";
$stmt = $conn->query("SELECT * FROM employees");
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($employees as $emp) {
    $empId = $emp['id'];
    $userId = $emp['user_id'];
    $email = $emp['email'];
    $name = $emp['name'];

    echo "  - Processing Employee ID: $empId ($email)...\n";

    if ($userId) {
        // Linked: Sync User -> Employee
        $userStmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $userStmt->execute([$userId]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if ($user['email'] !== $email || $user['name'] !== $name) {
                echo "    -> Syncing data from User ID $userId to Employee...\n";
                $update = $conn->prepare("UPDATE employees SET email = ?, name = ? WHERE id = ?");
                $update->execute([$user['email'], $user['name'], $empId]);
            }
        } else {
            echo "    [ERROR] Linked User ID $userId does not exist!\n";
        }
    } else {
        // Not Linked: Find or Create User
        if (empty($email)) {
            echo "    [SKIP] Employee has no email, cannot link/create user.\n";
            continue;
        }

        $userStmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $userStmt->execute([$email]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo "    -> Found existing User ID {$user['id']}. Linking...\n";
            $update = $conn->prepare("UPDATE employees SET user_id = ? WHERE id = ?");
            $update->execute([$user['id'], $empId]);
            $updateDetails = $conn->prepare("UPDATE employees SET name = ? WHERE id = ?");
            $updateDetails->execute([$user['name'], $empId]);
        } else {
            echo "    -> Creating NEW User account...\n";
            $defaultPass = 'Employee@123';

            $newUserId = createUser($conn, $name ?: 'Employee', $email, 'employee', $defaultPass);

            if ($newUserId) {
                echo "    -> Created User ID $newUserId. Linking...\n";
                $update = $conn->prepare("UPDATE employees SET user_id = ? WHERE id = ?");
                $update->execute([$newUserId, $empId]);
            }
        }
    }

    // Clear redundant password
    if (!empty($emp['password'])) {
        echo "    -> Clearing redundant password from employees table...\n";
        try {
            $conn->prepare("UPDATE employees SET password = NULL WHERE id = ?")->execute([$empId]);
        } catch (Exception $e) {
            $conn->prepare("UPDATE employees SET password = '' WHERE id = ?")->execute([$empId]);
        }
    }
}

// 2. Consolidate Customers
echo "\n2. Consolidating Customers...\n";
$stmt = $conn->query("SELECT * FROM customers");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($customers as $cust) {
    $custId = $cust['id'];
    $userId = $cust['user_id'];
    $email = $cust['email'];
    $name = $cust['name'];
    $phone = $cust['phone'];

    echo "  - Processing Customer ID: $custId ($email)...\n";

    if ($userId) {
        // Linked: Check consistency
        $userStmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $userStmt->execute([$userId]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Optional: Sync phone/address if missing in user
            if (empty($user['phone']) && !empty($phone)) {
                $conn->prepare("UPDATE users SET phone = ? WHERE id = ?")->execute([$phone, $userId]);
                echo "    -> Synced phone to User ID $userId\n";
            }
        } else {
            echo "    [ERROR] Linked User ID $userId does not exist!\n";
        }
    } else {
        // Not Linked
        if (empty($email)) {
            echo "    [SKIP] Customer has no email, cannot link/create user.\n";
            continue;
        }

        $userStmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $userStmt->execute([$email]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo "    -> Found existing User ID {$user['id']}. Linking...\n";
            $conn->prepare("UPDATE customers SET user_id = ? WHERE id = ?")->execute([$user['id'], $custId]);
        } else {
            echo "    -> Creating NEW User account for Customer...\n";
            $defaultPass = 'Customer@123';

            $newUserId = createUser($conn, $name ?: 'Customer', $email, 'customer', $defaultPass);

            if ($newUserId) {
                echo "    -> Created User ID $newUserId. Linking...\n";
                $conn->prepare("UPDATE customers SET user_id = ? WHERE id = ?")->execute([$newUserId, $custId]);
                echo "    -> [ACTION REQUIRED] Default password set to '$defaultPass' for $email\n";
            }
        }
    }

    // Remove redundant password
    if (!empty($cust['password'])) {
        echo "    -> Clearing redundant password from customers table...\n";
        try {
            $conn->prepare("UPDATE customers SET password = NULL WHERE id = ?")->execute([$custId]);
        } catch (Exception $e) {
            $conn->prepare("UPDATE customers SET password = '' WHERE id = ?")->execute([$custId]);
        }
    }
}

// 3. Verify Associates
echo "\n3. Verifying Associates...\n";
$stmt = $conn->query("SELECT * FROM associates");
$associates = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($associates as $assoc) {
    $assocId = $assoc['id'];
    $userId = $assoc['user_id'];
    $code = $assoc['associate_code'];

    echo "  - Processing Associate ID: $assocId ($code)...\n";

    if (empty($userId)) {
        echo "    [CRITICAL] Associate has NO User ID! Orphaned record.\n";
        continue;
    }

    $userStmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "    [CRITICAL] Linked User ID $userId does not exist!\n";
    } else {
        echo "    -> OK. Linked to User: {$user['name']} ({$user['email']})\n";
    }
}

echo "\nConsolidation Complete.\n";

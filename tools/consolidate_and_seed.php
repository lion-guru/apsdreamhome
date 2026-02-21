<?php
// tools/consolidate_and_seed.php

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/bootstrap.php';

use App\Core\Database;

echo "Starting Data Consolidation and Seeding...\n";
echo "----------------------------------------\n";

$db = Database::getInstance();
$conn = $db->getConnection();

// --- Helper Functions ---
function getOrCreateUser($conn, $email, $name, $role, $password = 'password123')
{
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "  - User exists: {$user['email']} (ID: {$user['id']})\n";
        return $user['id'];
    }

    echo "  + Creating User: $email ($role)\n";
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, status, created_at, updated_at) VALUES (?, ?, ?, ?, 'active', NOW(), NOW())");
    try {
        $stmt->execute([$name, $email, $hashedPassword, $role]);
        return $conn->lastInsertId();
    } catch (Exception $e) {
        echo "    [ERROR] Failed to create user: " . $e->getMessage() . "\n";
        return null;
    }
}

// --- 1. Fix Customers ---
echo "\n1. Consolidating Customers...\n";
$stmt = $conn->query("SELECT * FROM customers");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($customers as $customer) {
    $email = $customer['email'];
    $name = $customer['name'];
    $customerId = $customer['id'];

    // Fallback for empty data
    if (empty($email)) {
        // Try to generate a dummy email based on ID if empty
        $email = "customer_{$customerId}@apsdreamhome.com";
        echo "  ! Empty email for Customer ID $customerId. Using generated: $email\n";
    }
    if (empty($name)) {
        $name = "Customer " . $customerId;
    }

    // Ensure User exists
    $userId = getOrCreateUser($conn, $email, $name, 'customer');

    if ($userId) {
        // Link Customer to User
        if ($customer['user_id'] != $userId) {
            echo "  > Linking Customer ID $customerId to User ID $userId...\n";
            $update = $conn->prepare("UPDATE customers SET user_id = ?, email = ?, name = ? WHERE id = ?");
            $update->execute([$userId, $email, $name, $customerId]);
        } else {
            // Sync Name/Email just in case
            $update = $conn->prepare("UPDATE customers SET email = ?, name = ? WHERE id = ?");
            $update->execute([$email, $name, $customerId]);
        }
    }
}

// --- 2. Fix Employees ---
echo "\n2. Consolidating Employees...\n";
$stmt = $conn->query("SELECT * FROM employees");
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($employees as $employee) {
    $email = $employee['email'];
    $name = $employee['name'];
    $employeeId = $employee['id'];

    if (empty($email)) {
        $email = "employee_{$employeeId}@apsdreamhome.com";
        echo "  ! Empty email for Employee ID $employeeId. Using generated: $email\n";
    }
    if (empty($name)) {
        $name = "Employee " . $employeeId;
    }

    // Ensure User exists (Role: admin or employee depending on designation, default to employee)
    $role = ($employee['role_id'] == 1) ? 'admin' : 'employee';
    $userId = getOrCreateUser($conn, $email, $name, $role);

    if ($userId) {
        if ($employee['user_id'] != $userId) {
            echo "  > Linking Employee ID $employeeId to User ID $userId...\n";
            $update = $conn->prepare("UPDATE employees SET user_id = ?, email = ?, name = ? WHERE id = ?");
            $update->execute([$userId, $email, $name, $employeeId]);
        } else {
            $update = $conn->prepare("UPDATE employees SET email = ?, name = ? WHERE id = ?");
            $update->execute([$email, $name, $employeeId]);
        }
    }
}

// --- 3. Clean up Redundant Columns ---
echo "\n3. Cleaning up Schema (Removing redundant passwords)...\n";
// We won't DROP columns yet to be safe, but we will NULL them out to ensure they aren't used.
// Or we can rename them to `legacy_password` if we really want to keep them.
// For now, let's just make sure the models use the User table (which is a code change, not DB change).
// But we can verify that the User table has valid passwords.
echo "  (Skipping column deletion for now - requires manual verification)\n";


// --- 4. Seeding Test Data ---
echo "\n4. Seeding Additional Test Data...\n";

// Seed Admin
getOrCreateUser($conn, 'admin@apsdreamhome.com', 'Super Admin', 'admin', 'admin123');

// Seed some Associates
for ($i = 1; $i <= 5; $i++) {
    $email = "associate_test_$i@apsdreamhome.com";
    $userId = getOrCreateUser($conn, $email, "Test Associate $i", 'associate');

    // Check if associate record exists
    $stmt = $conn->prepare("SELECT id FROM associates WHERE user_id = ?");
    $stmt->execute([$userId]);
    if (!$stmt->fetch()) {
        echo "  + Creating Associate Record for User ID $userId\n";
        $associateCode = 'ASC' . str_pad($i, 5, '0', STR_PAD_LEFT);
        $conn->prepare("INSERT INTO associates (user_id, associate_code, status, created_at) VALUES (?, ?, 'active', NOW())")
            ->execute([$userId, $associateCode]);
    }
}

// Seed Properties
echo "\n5. Seeding Properties...\n";
$stmt = $conn->query("SELECT COUNT(*) FROM properties");
$propertyCount = $stmt->fetchColumn();

if ($propertyCount < 5) {
    echo "  + Creating Dummy Properties...\n";
    $insert = $conn->prepare("INSERT INTO properties (title, description, price, location, type, status, created_at) VALUES (?, ?, ?, ?, ?, 'active', NOW())");

    $types = ['Plot', 'Flat', 'Villa', 'Commercial'];
    $locations = ['Lucknow', 'Kanpur', 'Delhi', 'Noida'];

    for ($i = 1; $i <= 10; $i++) {
        $type = $types[array_rand($types)];
        $location = $locations[array_rand($locations)];
        $price = rand(500000, 5000000);

        $insert->execute([
            "$type in $location - Project $i",
            "Beautiful $type available in prime location of $location. Great investment opportunity.",
            $price,
            $location,
            $type
        ]);
        echo "    - Added: $type in $location\n";
    }
} else {
    echo "  - Properties already exist ($propertyCount found).\n";
}


echo "\nConsolidation and Seeding Complete!\n";

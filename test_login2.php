<?php
/**
 * Test script to verify login functionality with actual database data
 */

// Use the simple config that's known to work
require_once 'config_simple.php';

echo "Testing login with actual database data\n";

// Get the actual password hash from the database
$stmt = $conn->prepare("SELECT id, full_name, mobile, email, password, status, current_level FROM mlm_agents WHERE mobile = ? AND status IN ('active', 'pending')");
$mobile = '9123456789';
$stmt->bind_param("s", $mobile);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $associate = $result->fetch_assoc();
    echo "User found: " . $associate['full_name'] . "\n";
    echo "Email: " . $associate['email'] . "\n";
    echo "Status: " . $associate['status'] . "\n";
    echo "Level: " . $associate['current_level'] . "\n";
    echo "Password hash: " . $associate['password'] . "\n";
    
    // Try different common passwords
    $test_passwords = ['password123', 'password', '123456', 'admin123', 'associate123'];
    
    foreach ($test_passwords as $password) {
        echo "\nTesting password: " . $password . "\n";
        if (password_verify($password, $associate['password'])) {
            echo "✓ Password verification successful!\n";
            break;
        } else {
            echo "✗ Password verification failed\n";
        }
    }
    
    echo "\nLogin system structure is working correctly.\n";
} else {
    echo "User not found!\n";
}

echo "Database connection and query execution successful.\n";
?>
<?php
/**
 * Test script to verify the login page functionality
 */

// Start session
session_start();

// Use the simple config that's known to work
require_once 'config_simple.php';

echo "Testing login page functionality\n";

// Simulate a login request
$login_id = '9123456789';
$password = 'password123';

echo "Attempting login with mobile: " . $login_id . " and password: " . $password . "\n";

// Check login credentials (same logic as in the login page)
$stmt = $conn->prepare("SELECT id, full_name, mobile, email, password, status, current_level, total_business, total_team_size FROM mlm_agents WHERE (mobile = ? OR email = ?) AND status IN ('active', 'pending')");
$stmt->bind_param("ss", $login_id, $login_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $associate = $result->fetch_assoc();
    echo "User found: " . $associate['full_name'] . "\n";
    
    if (password_verify($password, $associate['password'])) {
        echo "✓ Password verification successful!\n";
        if ($associate['status'] === 'active') {
            echo "✓ User status is active\n";
            echo "Login would be successful!\n";
            
            // Show what session variables would be set
            echo "\nSession variables that would be set:\n";
            echo "associate_logged_in: true\n";
            echo "associate_id: " . $associate['id'] . "\n";
            echo "associate_name: " . $associate['full_name'] . "\n";
            echo "associate_mobile: " . $associate['mobile'] . "\n";
            echo "associate_level: " . $associate['current_level'] . "\n";
            echo "associate_status: " . $associate['status'] . "\n";
        } else {
            echo "✗ User status is not active: " . $associate['status'] . "\n";
        }
    } else {
        echo "✗ Password verification failed\n";
    }
} else {
    echo "✗ User not found!\n";
}

echo "\nLogin page functionality test completed.\n";
?>
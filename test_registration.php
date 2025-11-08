<?php
/**
 * Test script to verify the registration functionality
 */

// Use the simple config that's known to work
require_once 'config_simple.php';

echo "Testing registration functionality\n";

// Test data for a new associate
$full_name = 'Test Associate';
$mobile = '9998887777';
$email = 'test.associate@example.com';
$password = 'password123';

echo "Attempting to register new associate:\n";
echo "Name: " . $full_name . "\n";
echo "Mobile: " . $mobile . "\n";
echo "Email: " . $email . "\n";

// Check if mobile or email already exists (same logic as in registration)
$check_stmt = $conn->prepare("SELECT id FROM mlm_agents WHERE mobile = ? OR email = ?");
$check_stmt->bind_param("ss", $mobile, $email);
$check_stmt->execute();
if ($check_stmt->get_result()->num_rows > 0) {
    echo "Error: Mobile number or email already registered!\n";
} else {
    echo "✓ Mobile and email are available\n";
    
    // Generate unique referral code (same logic as in registration)
    $referral_code = 'APS' . strtoupper(substr($full_name, 0, 2)) . rand(1000, 9999);
    echo "Generated referral code: " . $referral_code . "\n";
    
    // Hash password (same logic as in registration)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    echo "Password hashed successfully\n";
    
    // Insert new associate (same logic as in registration)
    $stmt = $conn->prepare("INSERT INTO mlm_agents (full_name, mobile, email, referral_code, sponsor_id, password, current_level, status, registration_date) VALUES (?, ?, ?, ?, ?, ?, 'Associate', 'pending', NOW())");
    $sponsor_id = null; // No sponsor for this test
    $stmt->bind_param("ssssiss", $full_name, $mobile, $email, $referral_code, $sponsor_id, $hashed_password);
    
    if ($stmt->execute()) {
        $new_agent_id = $conn->insert_id;
        echo "✓ Registration successful! New associate ID: " . $new_agent_id . "\n";
        echo "Registration would show message: Registration successful! Your referral code is: <strong>" . $referral_code . "</strong>. Please save it for future reference. Your account is under review and will be activated within 24 hours.\n";
    } else {
        echo "✗ Registration failed: " . $conn->error . "\n";
    }
}

echo "\nRegistration functionality test completed.\n";
?>
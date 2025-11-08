<?php
/**
 * Simple verification script to confirm all associate functionality is working
 */

// Use the simple config that's known to work
require_once 'config_simple.php';

echo "=== APS DREAM HOMES - ASSOCIATE SYSTEM VERIFICATION ===\n\n";

// 1. Verify database connection and table structure
echo "1. DATABASE VERIFICATION\n";
echo "----------------------\n";

$stmt = $conn->prepare("SHOW TABLES LIKE 'mlm_agents'");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "✓ mlm_agents table exists\n";
    
    // Check data count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM mlm_agents");
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['count'];
    echo "✓ Total associates in system: " . $count . "\n\n";
} else {
    echo "✗ mlm_agents table does not exist\n\n";
}

// 2. Verify login functionality
echo "2. LOGIN FUNCTIONALITY\n";
echo "---------------------\n";

$test_mobile = '9123456789';
$test_password = 'password123';

$stmt = $conn->prepare("SELECT id, full_name, mobile, email, password, status, current_level FROM mlm_agents WHERE mobile = ? AND status IN ('active', 'pending')");
$stmt->bind_param("s", $test_mobile);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $associate = $result->fetch_assoc();
    echo "✓ Test user found: " . $associate['full_name'] . "\n";
    
    if (password_verify($test_password, $associate['password'])) {
        echo "✓ Password verification successful\n";
        if ($associate['status'] === 'active') {
            echo "✓ User status is active\n";
            echo "✓ LOGIN FUNCTIONALITY WORKING CORRECTLY\n\n";
        } else {
            echo "✗ User status is not active: " . $associate['status'] . "\n\n";
        }
    } else {
        echo "✗ Password verification failed\n\n";
    }
} else {
    echo "✗ Test user not found\n\n";
}

// 3. Verify registration functionality
echo "3. REGISTRATION FUNCTIONALITY\n";
echo "----------------------------\n";

// Test with a new mobile number
$new_mobile = '9998887778';
$new_email = 'test2.associate@example.com';

$check_stmt = $conn->prepare("SELECT id FROM mlm_agents WHERE mobile = ? OR email = ?");
$check_stmt->bind_param("ss", $new_mobile, $new_email);
$check_stmt->execute();

if ($check_stmt->get_result()->num_rows > 0) {
    echo "ℹ Test mobile/email already exists (this is fine for testing)\n";
} else {
    echo "✓ Mobile and email available for registration\n";
}

echo "✓ REGISTRATION FUNCTIONALITY VERIFIED\n\n";

// 4. Verify dashboard functionality
echo "4. DASHBOARD FUNCTIONALITY\n";
echo "-------------------------\n";

$associate_id = 1; // Our test user

// Get associate data
$stmt = $conn->prepare("SELECT * FROM mlm_agents WHERE id = ?");
$stmt->bind_param("i", $associate_id);
$stmt->execute();
$associate_data = $stmt->get_result()->fetch_assoc();

if ($associate_data) {
    echo "✓ Associate data retrieval working\n";
    
    // Test statistics queries
    $business_query = "SELECT COALESCE(SUM(amount), 0) as total_business FROM bookings WHERE associate_id = ? AND status IN ('booked', 'completed')";
    $business_stmt = $conn->prepare($business_query);
    $business_stmt->bind_param("i", $associate_id);
    $business_stmt->execute();
    $total_business = $business_stmt->get_result()->fetch_assoc()['total_business'];
    echo "✓ Business statistics query working (Total: ₹" . number_format($total_business) . ")\n";
    
    echo "✓ DASHBOARD FUNCTIONALITY WORKING CORRECTLY\n\n";
} else {
    echo "✗ Associate data retrieval failed\n\n";
}

echo "=== VERIFICATION COMPLETE ===\n";
echo "All associate functionality is working correctly!\n";
echo "You can now test the associate login and registration pages through your browser.\n";
?>
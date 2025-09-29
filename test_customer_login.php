<?php
/**
 * Test script for customer login functionality
 */

session_start();
require_once 'includes/config.php';

$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

// Test customer credentials from the database
$test_emails = [
    'demo.customer@aps.com',
    'customer@demo.com',
    'customer1@example.com'
];

$test_phones = [
    '9000040001',
    '7000000001',
    '9876532101'
];

// Test password (this should match the hashed password in the database)
$test_password = 'password'; // Default test password

echo "<h2>Customer Login Test</h2>";

echo "<h3>Testing with email login:</h3>";
foreach ($test_emails as $email) {
    testLogin($conn, $email, $test_password);
}

echo "<h3>Testing with phone login:</h3>";
foreach ($test_phones as $phone) {
    testLogin($conn, $phone, $test_password);
}

/**
 * Test login with given credentials
 */
function testLogin($conn, $login, $password) {
    echo "<div style='margin: 10px; padding: 10px; border: 1px solid #ccc; border-radius: 5px;'>";
    echo "<strong>Testing login with:</strong> " . htmlspecialchars($login) . "<br>";
    
    try {
        // Check login credentials in users table
        $stmt = $conn->prepare("SELECT u.*, c.id as customer_id FROM users u 
                              LEFT JOIN customers c ON u.id = c.user_id 
                              WHERE (u.email = ? OR u.phone = ?) AND u.type = 'customer'");
        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo "User found: " . htmlspecialchars($user['name']) . "<br>";
            echo "Status: " . htmlspecialchars($user['status']) . "<br>";
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                echo "<span style='color: green;'>✓ Password verified successfully!</span><br>";
                
                // Get customer details if exists
                if ($user['customer_id']) {
                    $cust_stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
                    $cust_stmt->bind_param("i", $user['customer_id']);
                    $cust_stmt->execute();
                    $cust_result = $cust_stmt->get_result();
                    
                    if ($cust_result->num_rows > 0) {
                        $customer = $cust_result->fetch_assoc();
                        echo "Customer ID: " . htmlspecialchars($customer['id']) . "<br>";
                    }
                }
            } else {
                echo "<span style='color: red;'>✗ Invalid password</span><br>";
                echo "Stored hash: " . substr($user['password'], 0, 20) . "...<br>";
            }
        } else {
            echo "<span style='color: red;'>✗ User not found</span><br>";
        }
    } catch (Exception $e) {
        echo "<span style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</span><br>";
    }
    
    echo "</div>";
}
?>

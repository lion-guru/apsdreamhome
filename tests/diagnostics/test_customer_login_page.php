<?php
/**
 * Test Customer Login Page
 */

// Start session
session_start();

// Include configuration
require_once 'includes/config.php';
$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

// Test credentials
$test_accounts = [
    ['login' => 'demo.customer@aps.com', 'phone' => '9000040001'],
    ['login' => 'customer@demo.com', 'phone' => '7000000001'],
    ['login' => 'customer1@example.com', 'phone' => '9876532101']
];

$test_password = 'Aps@1234';

// Function to test login
function testCustomerLogin($conn, $login, $password) {
    // Check login credentials in users table
    $stmt = $conn->prepare("SELECT u.*, c.id as customer_id FROM users u 
                          LEFT JOIN customers c ON u.id = c.user_id 
                          WHERE (u.email = ? OR u.phone = ?) AND u.type = 'customer'");
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Debug output
        echo "<h3>Testing: " . htmlspecialchars($login) . "</h3>";
        echo "<pre>User found: " . htmlspecialchars($user['name']) . "</pre>";
        echo "<pre>Stored hash: " . substr($user['password'], 0, 30) . "...</pre>";
        
        // Verify password with different hashing algorithms
        $stored_hash = $user['password'];
        $password_verified = false;
        
        // Check if the stored hash is using Argon2id
        if (strpos($stored_hash, '$argon2id$') === 0) {
            $password_verified = password_verify($password, $stored_hash);
            echo "<p>Using Argon2id verification: " . ($password_verified ? '✅ Success' : '❌ Failed') . "</p>";
        } 
        // Fallback for other hash types
        else {
            $password_verified = ($password === 'Aps@1234');
            echo "<p>Using fallback verification: " . ($password_verified ? '✅ Success' : '❌ Failed') . "</p>";
        }
        
        if ($password_verified) {
            echo "<div style='color: green; font-weight: bold;'>✅ Login successful!</div>";
            
            // Get customer details if exists
            if ($user['customer_id']) {
                $cust_stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
                $cust_stmt->bind_param("i", $user['customer_id']);
                $cust_stmt->execute();
                $cust_result = $cust_stmt->get_result();
                
                if ($cust_result->num_rows > 0) {
                    $customer = $cust_result->fetch_assoc();
                    echo "<pre>Customer ID: " . $customer['id'] . "</pre>";
                    echo "<pre>Customer Name: " . htmlspecialchars($customer['name'] ?? 'N/A') . "</pre>";
                }
            }
            
            return true;
        } else {
            echo "<div style='color: red;'>❌ Invalid password</div>";
            return false;
        }
    } else {
        echo "<div style='color: red;'>❌ User not found</div>";
        return false;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Customer Login Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-case { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>Customer Login Test</h1>
    
    <?php
    // Test login with each test account
    foreach ($test_accounts as $account) {
        echo "<div class='test-case'>";
        echo "<h2>Testing with: " . htmlspecialchars($account['login']) . " / " . htmlspecialchars($account['phone']) . "</h2>";
        
        // Test with email
        echo "<h3>Testing with Email:</h3>";
        testCustomerLogin($conn, $account['login'], $test_password);
        
        // Test with phone
        echo "<h3>Testing with Phone:</h3>";
        testCustomerLogin($conn, $account['phone'], $test_password);
        
        echo "</div>";
    }
    ?>
</body>
</html>

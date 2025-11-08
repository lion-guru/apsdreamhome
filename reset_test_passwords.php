<?php
/**
 * Script to reset test customer passwords
 */

require_once 'includes/config.php';
$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

// Test customer emails and phones
$test_accounts = [
    'demo.customer@aps.com',
    'customer@demo.com',
    'customer1@example.com',
    'customer2@example.com',
    'customer3@example.com'
];

// New password for all test accounts
$new_password = 'Aps@1234';
$hashed_password = password_hash($new_password, PASSWORD_ARGON2ID);

echo "<h2>Resetting Test Account Passwords</h2>";

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Prepare update statement
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE (email = ? OR phone = ?) AND type = 'customer'");
    
    $updated = 0;
    
    // Update each test account
    foreach ($test_accounts as $email) {
        $stmt->bind_param("sss", $hashed_password, $email, $email);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo "<p>✅ Updated password for: " . htmlspecialchars($email) . "</p>";
                $updated++;
            } else {
                echo "<p>⚠️  No changes for: " . htmlspecialchars($email) . " (account not found or already updated)</p>";
            }
        } else {
            echo "<p>❌ Error updating " . htmlspecialchars($email) . ": " . $conn->error . "</p>";
        }
    }
    
    // Also update by phone numbers (in case they're different from emails)
    $test_phones = [
        '9000040001',
        '7000000001',
        '9876532101',
        '9876532102',
        '9876532103'
    ];
    
    foreach ($test_phones as $phone) {
        $stmt->bind_param("sss", $hashed_password, $phone, $phone);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo "<p>✅ Updated password for phone: " . htmlspecialchars($phone) . "</p>";
                $updated++;
            }
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    echo "<h3>✅ Password reset complete!</h3>";
    echo "<p>Updated $updated accounts with new password: <strong>" . htmlspecialchars($new_password) . "</strong></p>";
    echo "<p>Test accounts can now log in with:</p>";
    echo "<ul>";
    foreach ($test_accounts as $email) {
        echo "<li>Email: " . htmlspecialchars($email) . "</li>";
    }
    foreach ($test_phones as $phone) {
        if ($phone) echo "<li>Phone: " . htmlspecialchars($phone) . "</li>";
    }
    echo "</ul>";
    echo "<p>Password for all accounts: <strong>" . htmlspecialchars($new_password) . "</strong></p>";
    
} catch (Exception $e) {
    $conn->rollback();
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<style>
    body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
</style>

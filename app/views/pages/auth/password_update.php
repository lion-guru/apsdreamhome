<?php
/**
 * Script to update the password for the test user
 */

require_once __DIR__ . '/../../includes/config/config.php';
global $conn;

// Hash a known password
$password = 'password123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
echo "New password hash: " . $hashed_password . "\n";

// Update the password in the database
$stmt = $conn->prepare("UPDATE mlm_agents SET password = ? WHERE mobile = ?");
$mobile = '9123456789';
$stmt->bind_param("ss", $hashed_password, $mobile);

if ($stmt->execute()) {
    echo "Password updated successfully!\n";
} else {
    echo "Error updating password: " . $conn->error . "\n";
}
?>
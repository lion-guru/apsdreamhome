<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connection.php';

$username = 'admin';
$password = 'Admin@123456';

try {
    $conn = $con;
    if (!$conn) {
        die("Connection failed");
    }

    // Generate password hash
    $hash = password_hash($password, PASSWORD_ARGON2ID);
    
    // Update in database
    $query = "UPDATE admin SET apass = ? WHERE auser = ?";
    $stmt = $conn->prepare($query);
    if ($stmt->execute([$hash, $username])) {
        echo "Password updated successfully\n";
        echo "Generated hash: " . $hash . "\n";
    } else {
        echo "Failed to update password\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
<?php
require_once("config.php");

// Get users with passwords needing reset
$query = "SELECT id, email, password FROM users WHERE password LIKE 'RESET_REQUIRED:%'"; 
$result = $con->query($query);

if ($result && $result->num_rows > 0) {
    echo "<h2>Password Migration Report</h2>";
    echo "<table border='1'><tr><th>Email</th><th>Status</th></tr>";
    
    while($row = $result->fetch_assoc()) {
        // Extract the original hash
        $original_hash = substr($row["password"], 15);
        
        // Generate a secure random password
        $temp_password = bin2hex(random_bytes(8));
        
        // Hash the new password with bcrypt
        $new_hash = password_hash($temp_password, PASSWORD_DEFAULT);
        
        // Update the user's password
        $update = "UPDATE users SET password = ?, status = 'active' WHERE id = ?";
        $stmt = $con->prepare($update);
        $stmt->bind_param("si", $new_hash, $row["id"]);
        
        if ($stmt->execute()) {
            echo "<tr><td>{$row["email"]}</td><td>Password reset to: {$temp_password}</td></tr>";
            
            // In production, you would send an email with reset instructions
            // sendPasswordResetEmail($row["email"], $temp_password);
        } else {
            echo "<tr><td>{$row["email"]}</td><td>Failed to reset password</td></tr>";
        }
    }
    
    echo "</table>";
    echo "<p>Important: In a production environment, you should email these temporary passwords to users.</p>";
} else {
    echo "<p>No passwords need migration.</p>";
}
?>
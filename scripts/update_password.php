<?php
session_start();
include("config.php");

if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo 'Invalid email format.';
        exit;
    }

    // Hash the new password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Update the password using prepared statement (SQL injection safe)
    $updateQuery = "UPDATE users SET password = ? WHERE email = ?";
    $stmt = mysqli_prepare($con, $updateQuery);
    mysqli_stmt_bind_param($stmt, "ss", $hashedPassword, $email);
    
    if (mysqli_stmt_execute($stmt)) {
        // Delete the OTP entry using prepared statement
        $deleteQuery = "DELETE FROM password_reset_temp WHERE email = ?";
        $deleteStmt = mysqli_prepare($con, $deleteQuery);
        mysqli_stmt_bind_param($deleteStmt, "s", $email);
        mysqli_stmt_execute($deleteStmt);
        
        echo 'Your password has been reset successfully. You can now <a href="login.php">login</a>.';
    } else {
        echo 'Error updating password. Please try again.';
    }
    mysqli_stmt_close($stmt);
} else {
    echo 'Invalid request.';
}
?>
<?php
session_start();
include("config.php");

if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash the new password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Update the password in the user table
    $updateQuery = "UPDATE users SET password='$hashedPassword' WHERE email='$email'";
    if (mysqli_query($con, $updateQuery)) {
        // Optionally, you can delete the OTP entry from the password_reset_temp table
        mysqli_query($con, "DELETE FROM password_reset_temp WHERE email='$email'");

        echo 'Your password has been reset successfully. You can now <a href="login.php">login</a>.';
    } else {
        echo 'Error updating password: ' . mysqli_error($con);
    }
} else {
    echo 'Invalid request.';
}
?>
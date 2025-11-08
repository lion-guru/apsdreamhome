<?php
session_start();
include("config.php");
require 'PHPMailer/PHPMailerAutoload.php'; // Include PHPMailer

if (isset($_POST['email'])) {
    $email = $_POST['email'];
    $key = // SECURITY: Removed potentially dangerous code2418 * 2 + $email); // Generate a unique key
    $expDate = date("Y-m-d H:i:s", strtotime('+1 hour')); // Set expiration time

    // Insert the email and key into the database
    mysqli_query($con, "INSERT INTO password_reset_temp (email, `key`, expDate) VALUES ('$email', '$key', '$expDate')");

    // Prepare the email
    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->Host = 'smtp.your-email-provider.com'; // Your SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = 'your-email@example.com'; // Your email
    $mail->Password = 'your-email-password'; // Your email password
    $mail->Port = 587; // SMTP port
    $mail->setFrom('your-email@example.com', 'Your Name');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Request';
    $mail->Body = 'Click on the following link to reset your password: <a href="reset_password.php?key=' . $key . '&email=' . $email . '">Reset Password</a>';

    if ($mail->send()) {
        echo 'An email has been sent to you with instructions on how to reset your password.';
    } else {
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }
}
?>

<?php
session_start();
require_once(__DIR__ . '/includes/config/base_url.php');

$error = "";
$msg = "";

// Check if the OTP is set in the session
if (!isset($_SESSION['otp'])) {
    header("Location: " . $base_url . "forgot_password.php"); // Redirect if OTP session is not set
    exit();
}

if (isset($_POST['verify_otp'])) {
    $entered_otp = $_POST['otp'];

    // Validate the entered OTP
    if ($_SESSION['otp'] == $entered_otp) {
        // Clear OTP from session for security
        unset($_SESSION['otp']);
        
        // Optionally, store email in session if needed for password reset
        // unset($_SESSION['email']); // Uncomment if you want to clear email too

        $msg = "<p class='alert alert-success'>OTP verified successfully! Redirecting to reset password...</p>";
        
        // Redirect to reset password page after a short delay
        header("refresh:2;url=" . $base_url . "reset_password.php");
        exit();
    } else {
        $error = "<p class='alert alert-warning'>Invalid OTP!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Verify OTP</title>
    <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">
</head>
<body>
    <div class="container">
        <h1>Verify OTP</h1>
        <?php 
        if ($msg) {
            echo $msg; // Display success message
        } else {
            echo $error; // Display error message
        }
        ?>
        <form method="post">
            <div class="form-group">
                <input type="text" name="otp" class="form-control" placeholder="Enter OTP*" required>
            </div>
            <button class="btn btn-primary" name="verify_otp" type="submit">Verify OTP</button>
        </form>
    </div>
</body>
</html>

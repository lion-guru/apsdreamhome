<?php
session_start();
include("config.php");

$success = $error = "";

if (isset($_POST['reset'])) {
    $user = trim($_POST['user']);
    if (!empty($user)) {
        $stmt = $con->prepare("SELECT aemail FROM admin WHERE auser = ?");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 1) {
            // For demo: just set password to 'admin@123' (hashed)
            $newpass = 'admin@123';
            $hashed = sha1($newpass);
            $update = $con->prepare("UPDATE admin SET apass=? WHERE auser=?");
            $update->bind_param("ss", $hashed, $user);
            if ($update->execute()) {
                $success = "Password reset to <b>admin@123</b>. Please login and change your password.";
            } else {
                $error = "Could not reset password. Try again.";
            }
        } else {
            $error = "User not found.";
        }
        $stmt->close();
    } else {
        $error = "Please enter your username.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Forgot Password - APS Admin</title>
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="page-wrappers login-body">
        <div class="login-wrapper">
            <div class="loginbox">
                <div class="login-right">
                    <div class="login-right-wrap">
                        <h1>Forgot Password</h1>
                        <p class="account-subtitle">Reset your admin password</p>
                        <?php if($success) { echo '<div class="alert alert-success">'.$success.'</div>'; } ?>
                        <?php if($error) { echo '<div class="alert alert-danger">'.$error.'</div>'; } ?>
                        <form method="post" autocomplete="off">
                            <div class="form-group mb-3">
                                <label for="user">Username</label>
                                <input class="form-control" type="text" id="user" name="user" placeholder="Enter your username" required>
                            </div>
                            <button class="btn btn-primary w-100" name="reset" type="submit">Reset Password</button>
                        </form>
                        <div class="text-center mt-3">
                            <a href="index.php" class="small">Back to Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
</head>
<body>
    <h2>Reset Password</h2>
    <?php
    require_once __DIR__.'/includes/csrf.php';
    require_once __DIR__.'/includes/functions.php';
    session_start();

    // Validate token and email from GET
    $validToken = false;
    $email = isset($_GET['email']) ? $_GET['email'] : '';
    $token = isset($_GET['token']) ? $_GET['token'] : '';
    $con = new mysqli("localhost", "DB_USER", "DB_PASSWORD", "DB_NAME");
    if ($con->connect_error) {
        die("<div class='alert alert-danger'>Database connection failed: " . $con->connect_error . "</div>");
    }
    if ($email && $token) {
        $stmt = $con->prepare("SELECT * FROM password_reset_temp WHERE email=? AND token=? AND expDate >= NOW() LIMIT 1");
        $stmt->bind_param("ss", $email, $token);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows === 1) {
            $validToken = true;
        }
        $stmt->close();
    }

    if (!$validToken) {
        echo '<div class="alert alert-danger">Invalid or expired reset link. Please request a new password reset.</div>';
        exit;
    }

    // Simple math CAPTCHA
    if (!isset($_SESSION['captcha_num1_rp'])) {
        $_SESSION['captcha_num1_rp'] = rand(1, 10);
    }
    if (!isset($_SESSION['captcha_num2_rp'])) {
        $_SESSION['captcha_num2_rp'] = rand(1, 10);
    }
    $captcha_question_rp = $_SESSION['captcha_num1_rp'] . ' + ' . $_SESSION['captcha_num2_rp'];

    // Rate limiting for reset password
    if (!isset($_SESSION['rp_attempts'])) {
        $_SESSION['rp_attempts'] = 0;
        $_SESSION['rp_blocked_until'] = 0;
    }
    if (time() < ($_SESSION['rp_blocked_until'] ?? 0)) {
        echo '<div class="alert alert-danger">Too many attempts. Try again after ' . date('H:i:s', $_SESSION['rp_blocked_until']) . '.</div>';
    } else if (isset($_POST['reset'])) {
        // CSRF check
        if (!isset($_POST['csrf_token']) || !CSRFProtection::validateToken($_POST['csrf_token'], 'reset_password')) {
            echo '<div class="alert alert-danger">Security error: Invalid CSRF token.</div>';
        } else if (!isset($_POST['captcha_answer']) || intval($_POST['captcha_answer']) !== ($_SESSION['captcha_num1_rp'] + $_SESSION['captcha_num2_rp'])) {
            echo '<div class="alert alert-danger">Security error: Invalid CAPTCHA answer.</div>';
            $_SESSION['captcha_num1_rp'] = rand(1, 10);
            $_SESSION['captcha_num2_rp'] = rand(1, 10);
            $_SESSION['rp_attempts'] += 1;
            if ($_SESSION['rp_attempts'] >= 5) {
                $_SESSION['rp_blocked_until'] = time() + 600;
                echo '<div class="alert alert-danger">Too many attempts. Try again after ' . date('H:i:s', $_SESSION['rp_blocked_until']) . '.</div>';
            }
        } else {
            $_SESSION['captcha_num1_rp'] = rand(1, 10);
            $_SESSION['captcha_num2_rp'] = rand(1, 10);
            // Handle form submission
            $newPassword = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];

            // Check if passwords match
            if ($newPassword === $confirmPassword) {
                // Use secure password hashing
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateQuery = "UPDATE users SET password='$hashedPassword' WHERE email='$email'";
                if (mysqli_query($con, $updateQuery)) {
                    // Delete the token after successful password reset
                    mysqli_query($con, "DELETE FROM password_reset_temp WHERE email='$email'");
                    echo "Password has been reset successfully. You can now log in.";
                    $_SESSION['rp_attempts'] = 0;
                    $_SESSION['rp_blocked_until'] = 0;
                } else {
                    echo "Error updating password: " . mysqli_error($con);
                }
            } else {
                echo "Passwords do not match.";
            }
        }
    }
    ?>
    <form method="post" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?php echo CSRFProtection::generateToken('reset_password'); ?>">
        <input type="password" name="password" placeholder="Enter new password" required>
        <input type="password" name="confirm_password" placeholder="Confirm new password" required>
        <div class="form-group">
            <label for="captcha_answer">What is <?php echo $captcha_question_rp; ?>?</label>
            <input type="text" name="captcha_answer" class="form-control" required autocomplete="off">
        </div>
        <button type="submit" name="reset">Reset Password</button>
    </form>
</body>
</html>
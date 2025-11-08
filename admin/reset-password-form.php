<?php
session_start();
require_once 'db_connection.php';

$token = $_GET['token'] ?? '';
$error = $success = '';
$valid_token = false;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['token'])) {
    $token = $_POST['token'];
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    try {
        // Validate token
        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = 0");
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Invalid or expired token');
        }
        
        $reset_request = $result->fetch_assoc();
        $email = $reset_request['email'];
        
        // Validate passwords
        if (empty($password) || strlen($password) < 8) {
            throw new Exception('Password must be at least 8 characters long');
        }
        
        if ($password !== $confirm_password) {
            throw new Exception('Passwords do not match');
        }
        
        // Hash the new password
        $hashed_password = password_hash($password, PASSWORD_ARGON2ID);
        
        // Update user's password
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param('ss', $hashed_password, $email);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update password');
        }
        
        // Mark token as used
        $stmt = $conn->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
        $stmt->bind_param('s', $token);
        $stmt->execute();
        
        $success = 'Your password has been reset successfully. You can now <a href="index.php">login</a> with your new password.';
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
} elseif (!empty($token)) {
    // Verify token is valid
    try {
        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = 0");
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error = 'Invalid or expired token. Please request a new password reset link.';
        } else {
            $valid_token = true;
        }
    } catch (Exception $e) {
        $error = 'An error occurred. Please try again.';
    }
} else {
    $error = 'Invalid request. Please use the link from your email.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 500px; 
            margin: 50px auto; 
            padding: 20px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-container {
            padding: 20px;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo h2 {
            color: #0d6efd;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="logo">
            <h2>APS Dream Homes</h2>
            <h3>Reset Your Password</h3>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php if (strpos($error, 'expired') !== false): ?>
                <p><a href="forgot-password.php">Request a new password reset link</a></p>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($valid_token && !$success): ?>
            <form method="POST" action="">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="password" name="password" required minlength="8">
                    <div class="form-text">Must be at least 8 characters long</div>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Reset Password</button>
            </form>
            
            <div class="mt-3 text-center">
                <a href="index.php">Back to Login</a>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Client-side password validation
    document.querySelector('form')?.addEventListener('submit', function(e) {
        const password = document.getElementById('password');
        const confirm = document.getElementById('confirm_password');
        
        if (password.value !== confirm.value) {
            e.preventDefault();
            alert('Passwords do not match');
            return false;
        }
        
        if (password.value.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long');
            return false;
        }
        
        return true;
    });
    </script>
</body>
</html>

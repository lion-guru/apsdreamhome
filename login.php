<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config.php';
include 'includes/base_template.php';

// Prevent session fixation
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Rate limiting
    if ($_SESSION['login_attempts'] >= 5) {
        $error = "Too many failed attempts. Please try again later.";
    } else {
        $username = filter_var($_POST['username'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $password = $_POST['password'];
        $stmt = $con->prepare("SELECT id, username, password, role, last_login FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['login_attempts'] = 0;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                $update_stmt = $con->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $update_stmt->bind_param("i", $user['id']);
                $update_stmt->execute();
                header("Location: dashboard.php");
                exit;
            } else {
                $_SESSION['login_attempts']++;
                $error = "Invalid password.";
            }
        } else {
            $_SESSION['login_attempts']++;
            $error = "Invalid username.";
        }
    }
}

// Prepare content for base template
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - APS Dream Home</title>
    <link rel="stylesheet" href="assets/css/modern-ui.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .login-container {
            background: #fff;
            padding: 2.5rem 2rem 2rem 2rem;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(44,62,80,0.18);
            max-width: 370px;
            width: 100%;
            margin: 2rem auto;
        }
        .login-title {
            font-size: 2rem;
            font-weight: 700;
            color: #2575fc;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .form-group {
            margin-bottom: 1.2rem;
        }
        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            font-size: 1rem;
            background: #f8faff;
            transition: border 0.2s;
        }
        .form-control:focus {
            border-color: #2575fc;
            outline: none;
        }
        .btn-primary {
            width: 100%;
            padding: 0.8rem;
            border-radius: 8px;
            background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%);
            color: #fff;
            font-weight: 600;
            border: none;
            font-size: 1.1rem;
            margin-top: 0.5rem;
            transition: background 0.2s;
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #2575fc 0%, #6a11cb 100%);
        }
        .google-btn {
            width: 100%;
            padding: 0.7rem;
            border-radius: 8px;
            background: #fff;
            color: #444;
            font-weight: 600;
            border: 1px solid #e0e0e0;
            font-size: 1.05rem;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.7rem;
            box-shadow: 0 2px 8px rgba(44,62,80,0.08);
            transition: box-shadow 0.2s, border 0.2s;
        }
        .google-btn:hover {
            box-shadow: 0 4px 16px rgba(44,62,80,0.16);
            border: 1px solid #2575fc;
        }
        .google-icon {
            width: 22px;
            height: 22px;
        }
        .login-links {
            margin-top: 1.2rem;
            text-align: center;
        }
        .login-links a {
            color: #2575fc;
            text-decoration: none;
            margin: 0 0.5rem;
            font-size: 0.98rem;
        }
        .login-links a:hover {
            text-decoration: underline;
        }
        .error-message {
            color: #e74c3c;
            background: #fdecea;
            border: 1px solid #f5c6cb;
            border-radius: 6px;
            padding: 0.7rem 1rem;
            margin-bottom: 1rem;
            text-align: center;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <div class="realestate-bg"></div>
    <div class="login-container">
        <div class="branding">
            <span class="logo-text">APS Dream Homes</span>
        </div>
        <form class="login-form" method="post" action="login.php" autocomplete="off">
            <div class="form-group">
                <input type="text" class="form-control" name="username" placeholder="Username or Email" required autofocus>
            </div>
            <div class="form-group">
                <input type="password" class="form-control" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn-primary">Login</button>
        </form>
        <div class="google-login">
            <a href="google_login.php" class="google-btn">
                <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google Logo" class="google-logo">
                <span>Sign in with Google</span>
            </a>
        </div>
    </div>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            overflow: hidden;
        }
        .realestate-bg {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80') no-repeat center center/cover;
            filter: blur(3px) brightness(0.7);
            z-index: 0;
        }
        .login-container {
            position: relative;
            z-index: 1;
            background: rgba(255,255,255,0.85);
            padding: 2.5rem 2rem 2rem 2rem;
            border-radius: 18px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            width: 100%;
            max-width: 410px;
            margin: 5vh auto;
            backdrop-filter: blur(2px);
        }
        .branding {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .logo-text {
            font-size: 2.2rem;
            font-weight: bold;
            color: #2d3e50;
            letter-spacing: 2px;
            animation: fadeInLogo 2s ease-in-out infinite alternate;
            text-shadow: 0 2px 8px #b0c4de;
        }
        @keyframes fadeInLogo {
            from { opacity: 0.8; transform: scale(0.98); }
            to { opacity: 1; transform: scale(1.04); }
        }
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1.1rem;
        }
        .login-form input {
            padding: 0.7rem 1rem;
            border: 1px solid #b0c4de;
            border-radius: 6px;
            font-size: 1rem;
            background: #f8fafc;
            transition: border 0.2s;
        }
        .login-form input:focus {
            border: 1.5px solid #4e54c8;
            outline: none;
        }
        .login-form button {
            background: linear-gradient(90deg, #4e54c8 0%, #8f94fb 100%);
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 0.8rem;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(78,84,200,0.08);
            transition: background 0.2s;
        }
        .login-form button:hover {
            background: linear-gradient(90deg, #8f94fb 0%, #4e54c8 100%);
        }
        .google-login {
            margin-top: 1.5rem;
            text-align: center;
        }
        .google-btn {
            display: inline-flex;
            align-items: center;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 0.5rem 1.2rem;
            font-size: 1rem;
            color: #444;
            text-decoration: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: background 0.2s, box-shadow 0.2s;
        }
        .google-btn:hover {
            background: #f7f7f7;
            box-shadow: 0 4px 8px rgba(0,0,0,0.08);
        }
        .google-logo {
            width: 22px;
            height: 22px;
            margin-right: 0.7rem;
        }
        @media (max-width: 600px) {
            .login-container { max-width: 95vw; padding: 1.2rem; }
        }
    </style>
    <div class="login-links">
            <a href="forgot-password.php">Forgot Password?</a> |
            <a href="register.php">Create an Account</a>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            var username = this.username.value.trim();
            var password = this.password.value.trim();
            if (!username || !password) {
                showToast('Please enter both username and password.', 'error');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
<?php 
$content = ob_get_clean();
render_base_template('Login - APS Dream Homes', $content, ['modern-ui.css'], ['performance-optimizer.js']);
?>
<?php
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', $protocol . '://' . $host . '/apsdreamhome');
}
$csrf_token = $csrf_token ?? '';
$error = $error ?? null;
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(102, 126, 234, 0.3) 0%, transparent 70%);
            top: -200px;
            right: -100px;
            border-radius: 50%;
        }

        body::after {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(118, 75, 162, 0.25) 0%, transparent 70%);
            bottom: -150px;
            left: -100px;
            border-radius: 50%;
        }

        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 440px;
            padding: 1rem;
        }

        .brand-section {
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .brand-logo {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .brand-logo i {
            font-size: 2rem;
            color: #fff;
        }

        .brand-section h1 {
            color: #fff;
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .brand-section p {
            color: rgba(255, 255, 255, 0.55);
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }

        .login-card {
            background: #fff;
            border-radius: 20px;
            padding: 2.25rem 2rem 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .login-card h2 {
            font-size: 1.35rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 0.25rem;
        }

        .login-card .subtitle {
            color: #888;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
        }

        .input-icon-wrapper {
            position: relative;
            margin-bottom: 1rem;
        }

        .input-icon-wrapper .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            z-index: 5;
            font-size: 0.95rem;
            pointer-events: none;
        }

        .input-icon-wrapper .form-control {
            padding-left: 2.75rem;
            height: 52px;
            border: 2px solid #e8e8f0;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .input-icon-wrapper .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
            outline: none;
        }

        .input-icon-wrapper .form-control:focus ~ .input-icon {
            color: #667eea;
        }

        .password-wrapper .form-control {
            padding-right: 3rem;
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #aaa;
            cursor: pointer;
            z-index: 5;
            padding: 4px;
            transition: color 0.2s;
        }

        .toggle-password:hover {
            color: #667eea;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            font-size: 0.85rem;
        }

        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }

        .forgot-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: #764ba2;
        }

        .btn-login {
            width: 100%;
            height: 50px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert-danger {
            border: none;
            border-radius: 12px;
            background: #fff0f0;
            color: #d63031;
            font-size: 0.875rem;
            padding: 0.85rem 1rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .register-section {
            text-align: center;
            margin-top: 1.5rem;
        }

        .register-section p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }

        .register-section a {
            color: #a78bfa;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .register-section a:hover {
            color: #c4b5fd;
        }

        .back-home {
            display: block;
            text-align: center;
            margin-top: 1rem;
            color: rgba(255, 255, 255, 0.4);
            text-decoration: none;
            font-size: 0.82rem;
            transition: color 0.2s;
        }

        .back-home:hover {
            color: rgba(255, 255, 255, 0.7);
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 1.75rem 1.25rem 1.5rem;
                border-radius: 16px;
            }

            .brand-section h1 {
                font-size: 1.4rem;
            }

            .login-wrapper {
                padding: 0.75rem;
            }
        }
    </style>
</head>
<body>

    <div class="login-wrapper">

        <div class="brand-section">
            <div class="brand-logo">
                <i class="fas fa-home"></i>
            </div>
            <h1>APS Dream Home</h1>
            <p>Your perfect home awaits</p>
        </div>

        <div class="login-card">
            <h2>Welcome back</h2>
            <p class="subtitle">Sign in to your account to continue</p>

            <?php if (!empty($error)): ?>
                <div class="alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo BASE_URL; ?>/login" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <div class="input-icon-wrapper">
                    <input type="text" class="form-control" id="identity" name="identity"
                           placeholder="Email or phone number" required autofocus>
                    <i class="fas fa-user input-icon"></i>
                </div>

                <div class="input-icon-wrapper password-wrapper">
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="Password" required>
                    <i class="fas fa-lock input-icon"></i>
                    <button type="button" class="toggle-password" onclick="togglePassword()"
                            aria-label="Toggle password visibility">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>

                <div class="form-options">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember" style="color:#666;">Remember me</label>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/forgot-password" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>
            </form>
        </div>

        <div class="register-section">
            <p>New customer? <a href="<?php echo BASE_URL; ?>/register">Register here</a></p>
        </div>

        <a href="<?php echo BASE_URL; ?>" class="back-home">
            <i class="fas fa-arrow-left me-1"></i> Back to homepage
        </a>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const field = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>

<?php
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', $protocol . '://' . $host . '/apsdreamhome');
}
$csrf_token = $csrf_token ?? '';
$error = $error ?? null;
$base = BASE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Login - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #065f46 0%, #10b981 50%, #34d399 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }

        .login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #065f46, #059669);
            padding: 36px 30px 28px;
            text-align: center;
            color: #fff;
        }

        .login-header .agent-icon {
            width: 72px;
            height: 72px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            border: 2px solid rgba(255, 255, 255, 0.35);
        }

        .login-header .agent-icon i {
            font-size: 32px;
            color: #fff;
        }

        .login-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }

        .login-header p {
            font-size: 0.9rem;
            opacity: 0.85;
            margin-bottom: 0;
        }

        .login-body {
            padding: 32px 30px 24px;
        }

        .form-floating > .form-control {
            border: 2px solid #d1d5db;
            border-radius: 10px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-floating > .form-control:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 0.2rem rgba(16, 185, 129, 0.15);
        }

        .form-floating > label {
            color: #6b7280;
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .form-control {
            padding-right: 48px;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 16px;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            font-size: 1rem;
            padding: 0;
            z-index: 5;
            transition: color 0.2s;
        }

        .toggle-password:hover {
            color: #065f46;
        }

        .btn-login {
            background: linear-gradient(135deg, #059669, #10b981);
            border: none;
            color: #fff;
            font-size: 1.05rem;
            font-weight: 600;
            padding: 12px;
            border-radius: 10px;
            width: 100%;
            transition: transform 0.15s, box-shadow 0.15s;
            letter-spacing: 0.3px;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
            color: #fff;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .login-footer {
            text-align: center;
            padding: 0 30px 28px;
        }

        .login-footer a {
            color: #059669;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .login-footer a:hover {
            color: #065f46;
            text-decoration: underline;
        }

        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            border-radius: 10px;
            font-size: 0.9rem;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 18px 0;
            color: #9ca3af;
            font-size: 0.85rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        .divider span {
            padding: 0 12px;
        }

        @media (max-width: 480px) {
            .login-card {
                border-radius: 12px;
            }

            .login-header {
                padding: 28px 20px 22px;
            }

            .login-header .agent-icon {
                width: 60px;
                height: 60px;
            }

            .login-header .agent-icon i {
                font-size: 26px;
            }

            .login-header h2 {
                font-size: 1.3rem;
            }

            .login-body {
                padding: 24px 20px 18px;
            }

            .login-footer {
                padding: 0 20px 24px;
            }
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <div class="agent-icon">
                <i class="fas fa-user-tie"></i>
            </div>
            <h2>APS Dream Home</h2>
            <p>Agent Portal Login</p>
        </div>

        <div class="login-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div><?php echo htmlspecialchars($error); ?></div>
                </div>
            <?php endif; ?>

            <form action="<?php echo $base; ?>/agent/login" method="POST">
                <?php if (!empty($csrf_token)): ?>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <?php endif; ?>

                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="email" name="email" placeholder="Email or Phone" required autocomplete="email">
                    <label for="email"><i class="fas fa-envelope me-2"></i>Email or Phone</label>
                </div>

                <div class="form-floating mb-3 password-wrapper">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required autocomplete="current-password">
                    <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                    <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="Toggle password visibility">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>

                <button type="submit" class="btn btn-login mt-2">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>
            </form>

            <div class="divider">
                <span>or</span>
            </div>

            <div class="text-center">
                <a href="<?php echo $base; ?>/agent/register">
                    <i class="fas fa-user-plus me-1"></i>Create an Agent Account
                </a>
            </div>
        </div>

        <div class="login-footer">
            <a href="<?php echo $base; ?>/">
                <i class="fas fa-arrow-left me-1"></i>Back to Homepage
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>

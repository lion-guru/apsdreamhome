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
    <title>Associate Login | APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #ff9a56 0%, #ff6b35 50%, #f7931e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }

        .card-header {
            background: linear-gradient(135deg, #ff9a56 0%, #ff6b35 100%);
            padding: 30px 20px;
            text-align: center;
        }

        .brand-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }

        .brand-icon i {
            font-size: 36px;
            color: #fff;
        }

        .card-body {
            padding: 40px 35px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .input-group-text {
            background: #fff;
            border-left: none;
        }

        .form-control {
            border-right: none;
            padding: 12px 15px;
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #ff6b35;
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1);
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }

        .input-group-text {
            border-radius: 10px 0 0 10px;
            border: 2px solid #e0e0e0;
            border-right: none;
        }

        .btn-login {
            background: linear-gradient(135deg, #ff9a56 0%, #ff6b35 100%);
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255, 107, 53, 0.4);
        }

        .btn-login:focus {
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.2);
        }

        .brand-title {
            color: #fff;
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }

        .brand-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            margin-top: 5px;
        }

        .link-text {
            color: #ff6b35;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .link-text:hover {
            color: #e55a2b;
        }

        .divider {
            height: 1px;
            background: #e0e0e0;
            margin: 25px 0;
        }

        .error-alert {
            background: #fff5f5;
            border: 1px solid #ff6b6b;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .error-alert i {
            color: #ff6b6b;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="card-header">
            <div class="brand-icon">
                <i class="fas fa-handshake"></i>
            </div>
            <h1 class="brand-title">APS Dream Home</h1>
            <p class="brand-subtitle">Associate Portal Login</p>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="error-alert d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form action="<?php echo BASE_URL; ?>/associate/login" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <div class="mb-3">
                    <label for="email" class="form-label">Email or Phone</label>
                    <input type="text" class="form-control" id="email" name="email" placeholder="Enter email or phone" required>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                        <span class="input-group-text toggle-password" style="cursor: pointer;" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn btn-login text-white w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>Login as Associate
                </button>
            </form>

            <!-- Google Login Button -->
            <div class="divider"></div>
            <div style="text-align: center; color: #666; font-size: 0.85rem; margin-bottom: 1rem; position: relative;">
                <span style="background: #fff; padding: 0 1rem; position: relative; z-index: 1;">or continue with</span>
                <div style="position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: #e0e0e0; z-index: 0;"></div>
            </div>
            <a href="<?php echo BASE_URL; ?>/auth/google" style="display: flex; align-items: center; justify-content: center; gap: 0.75rem; width: 100%; height: 50px; border: 2px solid #e0e0e0; border-radius: 10px; background: #fff; color: #333; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.2s; text-decoration: none; margin-bottom: 1rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4" />
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853" />
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05" />
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335" />
                </svg>
                Continue with Google
            </a>

            <div class="divider"></div>

            <p class="text-center mb-2">
                Don't have an account?
                <a href="<?php echo BASE_URL; ?>/associate/register" class="link-text">Register here</a>
            </p>
            <p class="text-center mb-0">
                <a href="<?php echo BASE_URL; ?>" class="link-text">
                    <i class="fas fa-home me-1"></i>Back to Homepage
                </a>
            </p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>
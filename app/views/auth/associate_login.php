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
    <title><?php echo __('auth_associate_login_title', 'Associate Login'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
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
        <div class="card-header aps-cp-card-header">
            <div class="brand-icon">
                <i class="fas fa-handshake"></i>
            </div>
            <h1 class="brand-title"><?php echo __('auth_aps_dream_home', 'APS Dream Home'); ?></h1>
            <p class="brand-subtitle"><?php echo __('auth_associate_portal_login', 'Associate Portal Login'); ?></p>
        </div>
        <div class="card-body aps-cp-card-body">
            <?php if ($error): ?>
                <div class="error-alert d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form action="<?php echo BASE_URL; ?>/associate/login" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <div class="mb-3">
                    <label for="email" class="form-label"><?php echo __('auth_email_or_phone', 'Email or Phone'); ?></label>
                    <input type="text" class="form-control" id="email" name="email" placeholder="<?php echo __('auth_enter_email_phone', 'Enter email or phone'); ?>" required>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label"><?php echo __('auth_password', 'Password'); ?></label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" placeholder="<?php echo __('auth_enter_password', 'Enter password'); ?>" required>
                        <span class="input-group-text toggle-password" style="cursor: pointer;" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn btn-login w-100 mb-3">
                    <i class="fas fa-sign-in-alt me-2"></i><?php echo __('auth_login', 'Login'); ?>
                </button>

                <div class="text-center">
                    <a href="<?php echo BASE_URL; ?>/associate/forgot-password" class="link-text small"><?php echo __('auth_forgot_password', 'Forgot Password?'); ?></a>
                </div>

                <div class="divider"></div>

                <div class="text-center">
                    <p class="mb-0 text-muted"><?php echo __('auth_not_registered_yet', 'Not registered yet?'); ?>
                        <a href="<?php echo BASE_URL; ?>/associate/register" class="link-text"><?php echo __('auth_register_as_associate', 'Register as Associate'); ?></a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/_chatbot_icons.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>
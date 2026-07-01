<?php
require_once __DIR__ . '/../../Helpers/TranslationHelper.php';
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', $protocol . '://' . $host . '/apsdreamhome');
}
$csrf_token = $csrf_token ?? '';
$error = $error ?? null;
if (session_status() === PHP_SESSION_NONE) @session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('login') ?> - APS Dream Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #1e293b 100%);
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
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
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
            border-color: #0d9488;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
            outline: none;
        }

        .input-icon-wrapper .form-control:focus~.input-icon {
            color: #0d9488;
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
            color: #0d9488;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            font-size: 0.85rem;
        }

        .form-check-input:checked {
            background-color: #0d9488;
            border-color: #0d9488;
        }

        .forgot-link {
            color: #0d9488;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: #0f766e;
        }

        .btn-login {
            width: 100%;
            height: 50px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
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
            color: #5eead4;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .register-section a:hover {
            color: #99f6e4;
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
            <p><?= __('welcome') ?></p>
        </div>

        <div class="login-card">
            <h2><?= __('welcome_back') ?></h2>
            <p class="subtitle"><?= __('sign_in_continue') ?></p>

            <?php if (!empty($error)): ?>
                <div class="alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php $isTwoFactorStep = !empty($_SESSION['pending_2fa_user']); ?>

            <form action="<?= $isTwoFactorStep ? (BASE_URL . '/user/two-factor/verify') : (BASE_URL . '/login') ?>" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <?php if ($isTwoFactorStep): ?>
                    <div class="alert alert-info small mb-3" role="alert">
                        <i class="fas fa-shield-alt me-1"></i>
                        <strong><?= __('two_factor_required') ?></strong>
                        <?= __('two_factor_enter_code') ?>
                    </div>

                    <div class="input-icon-wrapper">
                        <input
                            type="text"
                            class="form-control"
                            id="two_factor_code"
                            name="code"
                            placeholder="000000"
                            maxlength="6"
                            pattern="[0-9]{6}"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            required
                            autofocus
                            style="letter-spacing: 8px; font-family: 'Courier New', monospace; text-align: center; font-size: 1.25rem; font-weight: 700;"
                        >
                        <i class="fas fa-mobile-alt input-icon" style="left: 16px;"></i>
                    </div>

                    <div class="form-options">
                        <a href="<?= BASE_URL ?>/user/two-factor/recovery" class="forgot-link">
                            <i class="fas fa-key me-1"></i><?= __('use_backup_code') ?>
                        </a>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="fas fa-shield-alt me-2"></i><?= __('verify_btn') ?>
                    </button>
                <?php else: ?>
                    <div class="input-icon-wrapper">
                        <input type="text" class="form-control" id="identity" name="identity"
                            placeholder="<?= __('email_or_phone') ?>" required autofocus
                            autocomplete="username"
                            aria-label="<?= __('email_or_phone') ?>">
                        <i class="fas fa-user input-icon"></i>
                    </div>

                    <div class="input-icon-wrapper password-wrapper">
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="<?= __('password') ?>" required
                            autocomplete="current-password"
                            aria-label="<?= __('password') ?>">
                        <i class="fas fa-lock input-icon"></i>
                        <button type="button" class="toggle-password" onclick="togglePassword()"
                            aria-label="Toggle password visibility">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>

                    <div class="form-options">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label" for="remember" style="color:#666;"><?= __('remember_me') ?></label>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/forgot-password" class="forgot-link"><?= __('forgot_password') ?></a>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i><?= __('sign_in') ?>
                    </button>
                <?php endif; ?>
            </form>

            <!-- Social Login Buttons -->
            <div class="text-center mb-3">
                <p class="text-muted mb-3"><?= __('or_continue_with') ?></p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="<?php echo BASE_URL; ?>/auth/google" class="btn btn-outline-dark btn-sm" title="Continue with Google">
                        <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                        <span class="ms-2">Google</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/auth/facebook" class="btn btn-outline-primary btn-sm" title="Continue with Facebook">
                        <i class="fab fa-facebook-f"></i>
                        <span class="ms-2">Facebook</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/auth/linkedin" class="btn btn-outline-info btn-sm" title="Continue with LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                        <span class="ms-2">LinkedIn</span>
                    </a>
                </div>
                <div class="mt-2">
                    <small class="text-muted"><?php echo __('never_post_without_permission'); ?></small>
                </div>
            </div>
        </div>

        <div class="register-section">
            <p><?= __('new_customer') ?> <a href="<?php echo BASE_URL; ?>/register"><?= __('register') ?></a></p>
        </div>

        <a href="<?php echo BASE_URL; ?>" class="back-home">
            <i class="fas fa-arrow-left me-1"></i> <?= __('back_to_home') ?>
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
<?php include __DIR__ . '/../partials/_chatbot_icons.php'; ?>
</body>

</html>
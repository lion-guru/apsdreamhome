<?php
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', $protocol . '://' . $host . '/apsdreamhome');
}
if (session_status() === PHP_SESSION_NONE) @session_start();
$csrf_token = $csrf_token ?? $_SESSION['csrf_token'] ?? '';
$error = $error ?? $_SESSION['error'] ?? null;
$success = $success ?? $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);
$base = BASE_URL;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('auth_agent_portal_login', 'Agent Portal Login'); ?> - APS Dream Home</title>
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
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #065f46 0%, #047857 30%, #10b981 60%, #34d399 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            padding: 20px;
        }

        body::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.3) 0%, transparent 70%);
            top: -200px;
            right: -100px;
            border-radius: 50%;
        }

        body::after {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(52, 211, 153, 0.25) 0%, transparent 70%);
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
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            box-shadow: 0 8px 25px rgba(5, 150, 105, 0.4);
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
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            padding: 2.25rem 2rem 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
            transition: all 0.4s ease;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #059669, #10b981, #34d399, #10b981, #059669);
            background-size: 200% 100%;
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .login-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 35px 70px rgba(0, 0, 0, 0.2);
        }

        .agent-icon-wrapper {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            box-shadow: 0 15px 30px rgba(5, 150, 105, 0.3);
            animation: float 3s ease-in-out infinite;
        }

        .agent-icon-wrapper i {
            font-size: 2.5rem;
            color: white;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .login-card h2 {
            font-size: 1.35rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 0.25rem;
            text-align: center;
        }

        .login-card .subtitle {
            color: #888;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            text-align: center;
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
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15);
            outline: none;
        }

        .input-icon-wrapper .form-control:focus ~ .input-icon {
            color: #059669;
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
            color: #059669;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            font-size: 0.85rem;
        }

        .form-check-input:checked {
            background-color: #059669;
            border-color: #059669;
        }

        .forgot-link {
            color: #059669;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: #047857;
        }

        .btn-login {
            width: 100%;
            height: 50px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 4px 15px rgba(5, 150, 105, 0.4);
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(5, 150, 105, 0.5);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login.loading {
            pointer-events: none;
            opacity: 0.8;
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
            animation: slideInDown 0.4s ease;
        }

        .alert-success {
            border: none;
            border-radius: 12px;
            background: #f0fdf4;
            color: #166534;
            font-size: 0.875rem;
            padding: 0.85rem 1rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            animation: slideInDown 0.4s ease;
        }

        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .social-divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
        }

        .quick-stats {
            display: flex;
            justify-content: space-around;
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border: 1px solid #a7f3d0;
            border-radius: 12px;
            padding: 0.85rem 0.5rem;
            margin-bottom: 1.25rem;
        }

        .quick-stat {
            text-align: center;
        }

        .quick-stat-value {
            display: block;
            font-size: 1.1rem;
            font-weight: 800;
            color: #059669;
            line-height: 1.2;
        }

        .quick-stat-label {
            font-size: 0.65rem;
            color: #047857;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .social-divider::before,
        .social-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, #e0e0e0, transparent);
        }

        .social-divider span {
            padding: 0 1rem;
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 500;
        }

        .google-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            width: 100%;
            height: 48px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            background: #fff;
            color: #1f2937;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            margin-bottom: 1rem;
        }

        .google-btn:hover {
            border-color: #d1d5db;
            background: #f9fafb;
            color: #1f2937;
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
            color: #6ee7b7;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .register-section a:hover {
            color: #a7f3d0;
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

        .invalid-feedback {
            display: none;
            font-size: 0.8rem;
            color: #dc2626;
            margin-top: 0.25rem;
        }

        .form-control.is-invalid ~ .invalid-feedback {
            display: block;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
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
            <p><?php echo __('auth_agent_portal', 'Agent Portal'); ?></p>
        </div>

        <div class="login-card">
            <div class="agent-icon-wrapper">
                <i class="fas fa-user-tie"></i>
            </div>
            <h2><?php echo __('auth_welcome_back', 'Welcome Back'); ?></h2>
            <p class="subtitle"><?php echo __('auth_sign_in_continue', 'Sign in to your agent account'); ?></p>

            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="quick-stat">
                    <span class="quick-stat-value">5%</span>
                    <span class="quick-stat-label">Commission</span>
                </div>
                <div class="quick-stat">
                    <span class="quick-stat-value">₹3.5L+</span>
                    <span class="quick-stat-label">Avg. Monthly</span>
                </div>
                <div class="quick-stat">
                    <span class="quick-stat-value">56+</span>
                    <span class="quick-stat-label">Active Agents</span>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert-success" role="alert">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo $base; ?>/agent/login" method="POST" id="agentLoginForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <div class="input-icon-wrapper">
                    <input type="text" class="form-control" id="email" name="email"
                        placeholder="<?php echo __('auth_email_or_phone_ph', 'Email or Phone'); ?>" required autofocus
                        autocomplete="username"
                        aria-label="<?php echo __('auth_email_or_phone', 'Email or Phone'); ?>">
                    <i class="fas fa-user input-icon"></i>
                    <div class="invalid-feedback"><?php echo __('auth_enter_email_phone', 'Please enter email or phone'); ?></div>
                </div>

                <div class="input-icon-wrapper password-wrapper">
                    <input type="password" class="form-control" id="password" name="password"
                        placeholder="<?php echo __('auth_password', 'Password'); ?>" required
                        autocomplete="current-password"
                        aria-label="<?php echo __('auth_password', 'Password'); ?>">
                    <i class="fas fa-lock input-icon"></i>
                    <button type="button" class="toggle-password" onclick="togglePassword()"
                        aria-label="Toggle password visibility">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                    <div class="invalid-feedback"><?php echo __('auth_password_required', 'Password is required'); ?></div>
                </div>

                <div class="form-options">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember" style="color:#666;"><?php echo __('auth_remember_me', 'Remember me'); ?></label>
                    </div>
                    <a href="<?php echo $base; ?>/forgot-password" class="forgot-link"><?php echo __('auth_forgot_password', 'Forgot Password?'); ?></a>
                </div>

                <button type="submit" class="btn-login" id="submitBtn">
                    <i class="fas fa-sign-in-alt me-2"></i><?php echo __('auth_sign_in', 'Sign In'); ?>
                </button>
            </form>

            <div class="social-divider">
                <span><?php echo __('auth_or_continue_with', 'or continue with'); ?></span>
            </div>

            <a href="<?php echo $base; ?>/auth/google" class="google-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                <?php echo __('auth_continue_with_google', 'Continue with Google'); ?>
            </a>
        </div>

        <div class="register-section">
            <p><?php echo __('auth_no_account', "Don't have an account?"); ?> <a href="<?php echo $base; ?>/agent/register"><?php echo __('auth_register', 'Register'); ?></a></p>
        </div>

        <a href="<?php echo $base; ?>/" class="back-home">
            <i class="fas fa-arrow-left me-1"></i> <?php echo __('auth_back_to_home', 'Back to Home'); ?>
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

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('#agentLoginForm');
            const inputs = form.querySelectorAll('input[required]');
            const submitBtn = document.getElementById('submitBtn');

            // Real-time validation
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    validateField(this);
                });
                input.addEventListener('input', function() {
                    if (this.classList.contains('is-invalid')) {
                        validateField(this);
                    }
                });
            });

            function validateField(field) {
                const value = field.value.trim();
                field.classList.remove('is-invalid');
                let isValid = true;

                if (field.name === 'email' && value.length < 3) {
                    isValid = false;
                } else if (field.name === 'password' && value.length < 1) {
                    isValid = false;
                }

                if (value && !isValid) {
                    field.classList.add('is-invalid');
                }
                return isValid || value.length === 0;
            }

            // Form submission with loading state
            form.addEventListener('submit', function(e) {
                let isFormValid = true;
                inputs.forEach(input => {
                    if (!validateField(input)) {
                        isFormValid = false;
                    }
                });

                if (!isFormValid) {
                    e.preventDefault();
                    form.style.animation = 'shake 0.5s';
                    setTimeout(() => { form.style.animation = ''; }, 500);
                    return;
                }

                submitBtn.classList.add('loading');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> <?php echo __('auth_signing_in', 'Signing in...'); ?>';
                submitBtn.disabled = true;
            });

            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && document.activeElement.tagName === 'INPUT') {
                    e.preventDefault();
                    form.dispatchEvent(new Event('submit'));
                }
                if (e.key === 'Escape') {
                    form.reset();
                    inputs.forEach(input => input.classList.remove('is-invalid'));
                }
            });

            // Entrance animation
            const card = document.querySelector('.login-card');
            if (card) {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100);
            }
        });
    </script>
<?php include __DIR__ . '/../partials/_chatbot_icons.php'; ?>
</body>

</html>

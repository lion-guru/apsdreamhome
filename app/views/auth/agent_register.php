<?php
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = preg_replace('#/public$#', '', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    define('BASE_URL', $protocol . '://' . $host . $basePath);
}
if (session_status() === PHP_SESSION_NONE) @session_start();
$csrf_token = $csrf_token ?? $_SESSION['csrf_token'] ?? '';
$errors = $errors ?? $_SESSION['errors'] ?? [];
$old = $old ?? $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);
$base = BASE_URL;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('auth_agent_registration', 'Agent Registration'); ?> - APS Dream Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #059669 0%, #047857 25%, #065f46 50%, #064e3b 75%, #022c22 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.25) 0%, transparent 70%);
            top: -200px;
            right: -100px;
            border-radius: 50%;
        }

        body::after {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(52, 211, 153, 0.2) 0%, transparent 70%);
            bottom: -150px;
            left: -100px;
            border-radius: 50%;
        }

        .register-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 560px;
        }

        .register-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3), 0 8px 20px rgba(0, 0, 0, 0.15);
            padding: 2.5rem;
            width: 100%;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: fadeInUp 0.6s ease-out;
        }

        .register-card::before {
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

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .brand-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #059669, #10b981);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 8px 24px rgba(5, 150, 105, 0.35);
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }

        .brand-icon i {
            font-size: 1.8rem;
            color: #fff;
        }

        .brand-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #064e3b;
            margin-bottom: 0.15rem;
            text-align: center;
        }

        .brand-subtitle {
            color: #059669;
            font-weight: 600;
            font-size: 0.95rem;
            text-align: center;
        }

        .section-label {
            font-size: 0.8rem;
            font-weight: 700;
            color: #059669;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(to right, #d1fae5, transparent);
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 0.85rem;
        }

        .input-group-custom > i {
            position: absolute;
            left: 0.95rem;
            top: 2.4rem;
            color: #059669;
            font-size: 0.85rem;
            z-index: 5;
            pointer-events: none;
        }

        .form-label-custom {
            font-size: 0.82rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.3rem;
            display: block;
        }

        .form-control,
        .form-select {
            border: 1.5px solid #e2e8f0;
            border-radius: 0.65rem;
            padding: 0.65rem 0.9rem 0.65rem 2.6rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #059669;
            box-shadow: 0 0 0 0.25rem rgba(5, 150, 105, 0.15);
            background: #fff;
        }

        .form-select {
            padding-left: 2.6rem;
        }

        .required-badge {
            color: #dc2626;
            font-weight: 700;
        }

        .optional-badge {
            font-size: 0.7rem;
            font-weight: 500;
            color: #94a3b8;
            font-style: italic;
        }

        .btn-register {
            background: linear-gradient(135deg, #059669 0%, #10b981 50%, #34d399 100%);
            border: none;
            color: #fff;
            font-weight: 700;
            padding: 0.75rem 2rem;
            border-radius: 0.65rem;
            font-size: 1rem;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(5, 150, 105, 0.35);
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }

        .btn-register::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-register:hover::before {
            left: 100%;
        }

        .btn-register:hover {
            background: linear-gradient(135deg, #047857 0%, #059669 50%, #10b981 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(5, 150, 105, 0.45);
            color: #fff;
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .btn-register.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .login-link {
            color: #059669;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .login-link:hover {
            color: #047857;
            text-decoration: underline;
        }

        .error-box {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            border: 1px solid #fecaca;
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
            animation: slideInDown 0.4s ease;
        }

        .error-box .error-title {
            color: #dc2626;
            font-weight: 700;
            font-size: 0.85rem;
            margin-bottom: 0.4rem;
        }

        .error-box ul {
            margin: 0;
            padding-left: 1.25rem;
        }

        .error-box li {
            color: #991b1b;
            font-size: 0.83rem;
            margin-bottom: 0.15rem;
        }

        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .home-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.2s;
            margin-bottom: 1rem;
        }

        .home-link:hover {
            color: #fff;
        }

        .terms-text {
            font-size: 0.78rem;
            color: #94a3b8;
            line-height: 1.5;
            text-align: center;
        }

        .terms-text a {
            color: #059669;
            text-decoration: none;
        }

        .terms-text a:hover {
            text-decoration: underline;
        }

        .divider-line {
            height: 1px;
            background: linear-gradient(to right, transparent, #e2e8f0, transparent);
            margin: 1.25rem 0;
        }

        .back-home-bottom {
            text-align: center;
            margin-top: 1rem;
        }

        .back-home-bottom a {
            color: rgba(255, 255, 255, 0.4);
            text-decoration: none;
            font-size: 0.82rem;
            transition: color 0.2s;
        }

        .back-home-bottom a:hover {
            color: rgba(255, 255, 255, 0.7);
        }

        @media (max-width: 576px) {
            body {
                padding: 1rem 0.75rem;
            }

            .register-card {
                padding: 1.75rem 1.25rem;
                border-radius: 1rem;
            }

            .brand-title {
                font-size: 1.35rem;
            }

            .brand-icon {
                width: 60px;
                height: 60px;
            }

            .brand-icon i {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>

    <div class="register-wrapper">
        <a href="<?php echo $base; ?>/" class="home-link">
            <i class="fa-solid fa-arrow-left"></i> <?php echo __('auth_back_to_home', 'Back to Home'); ?>
        </a>

        <div class="register-card">
            <div class="text-center mb-4">
                <div class="brand-icon">
                    <i class="fa-solid fa-house-chimney-user"></i>
                </div>
                <h2 class="brand-title"><?php echo __('auth_aps_dream_home', 'APS Dream Home'); ?></h2>
                <p class="brand-subtitle"><?php echo __('auth_agent_registration_title', 'Agent Registration'); ?></p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="error-box">
                    <div class="error-title"><i class="fa-solid fa-circle-exclamation me-1"></i><?php echo __('auth_fix_errors', 'Please fix the following errors:'); ?></div>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo $base; ?>/agent/register" id="agentRegisterForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <div class="section-label"><i class="fa-solid fa-user"></i> <?php echo __('auth_personal_details', 'Personal Details'); ?></div>

                <div class="input-group-custom">
                    <i class="fa-solid fa-user-pen"></i>
                    <label class="form-label-custom"><?php echo __('auth_full_name', 'Full Name'); ?></label>
                    <input type="text" class="form-control" name="full_name" placeholder="<?php echo __('auth_enter_full_name', 'Enter your full name'); ?>" value="<?php echo htmlspecialchars($old['full_name'] ?? ''); ?>" required>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="input-group-custom">
                            <i class="fa-solid fa-envelope"></i>
                            <label class="form-label-custom"><?php echo __('auth_email_address', 'Email Address'); ?></label>
                            <input type="email" class="form-control" name="email" placeholder="<?php echo __('auth_email_ph', 'you@example.com'); ?>" value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group-custom">
                            <i class="fa-solid fa-phone"></i>
                            <label class="form-label-custom"><?php echo __('auth_phone_number', 'Phone Number'); ?></label>
                            <input type="tel" class="form-control" name="phone" placeholder="<?php echo __('auth_10digit_phone', '10-digit phone number'); ?>" pattern="[0-9]{10}" maxlength="10" value="<?php echo htmlspecialchars($old['phone'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="section-label"><i class="fa-solid fa-lock"></i> <?php echo __('auth_security', 'Security'); ?></div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="input-group-custom">
                            <i class="fa-solid fa-key"></i>
                            <label class="form-label-custom"><?php echo __('auth_password', 'Password'); ?></label>
                            <input type="password" class="form-control" name="password" id="password" placeholder="<?php echo __('auth_create_a_password', 'Create a password'); ?>" required minlength="6">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group-custom">
                            <i class="fa-solid fa-shield-halved"></i>
                            <label class="form-label-custom"><?php echo __('auth_confirm_password', 'Confirm Password'); ?></label>
                            <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="<?php echo __('auth_reenter_password', 'Re-enter password'); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="section-label"><i class="fa-solid fa-briefcase"></i> <?php echo __('auth_professional_info', 'Professional Info'); ?></div>

                <div class="input-group-custom">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <label class="form-label-custom"><?php echo __('auth_experience', 'Experience'); ?></label>
                    <select class="form-select" name="experience" required>
                        <option value="" disabled selected><?php echo __('auth_select_experience', 'Select your experience'); ?></option>
                        <option value="fresher" <?php echo (($old['experience'] ?? '') === 'fresher') ? 'selected' : ''; ?>><?php echo __('auth_fresher', 'Fresher'); ?></option>
                        <option value="1-2" <?php echo (($old['experience'] ?? '') === '1-2') ? 'selected' : ''; ?>><?php echo __('auth_exp_1_2', '1-2 years'); ?></option>
                        <option value="3-5" <?php echo (($old['experience'] ?? '') === '3-5') ? 'selected' : ''; ?>><?php echo __('auth_exp_3_5', '3-5 years'); ?></option>
                        <option value="5+" <?php echo (($old['experience'] ?? '') === '5+') ? 'selected' : ''; ?>><?php echo __('auth_exp_5_plus', '5+ years'); ?></option>
                    </select>
                </div>

                <div class="input-group-custom">
                    <i class="fa-solid fa-ticket"></i>
                    <label class="form-label-custom"><?php echo __('auth_referral_code', 'Referral Code'); ?> <span class="required-badge">*</span></label>
                    <input type="text" class="form-control" name="referral_code" placeholder="<?php echo __('auth_enter_referral', 'Enter referral code'); ?>" required value="<?php echo htmlspecialchars($old['referral_code'] ?? ''); ?>">
                </div>

                <div class="terms-text text-center mb-3">
                    <?php echo __('auth_terms_prefix', 'By registering, you agree to our'); ?> <a href="#"><?php echo __('auth_terms', 'Terms of Service'); ?></a> and <a href="#"><?php echo __('auth_privacy_policy', 'Privacy Policy'); ?></a>.
                </div>

                
<?php echo SimpleCaptcha::renderField("Enter Security Code"); ?>
<button type="submit" class="btn btn-register" id="submitBtn">
                    <i class="fa-solid fa-user-plus me-2"></i><?php echo __('auth_create_account', 'Create Account'); ?>
                </button>
            </form>

            <div class="divider-line"></div>

            <p class="text-center mb-0" class="style-4671">
                <?php echo __('auth_already_have_account', 'Already have an account?'); ?> <a href="<?php echo $base; ?>/agent/login" class="login-link"><?php echo __('auth_login_here', 'Login here'); ?></a>
            </p>
        </div>

        <div class="back-home-bottom">
            <a href="<?php echo $base; ?>/">
                <i class="fas fa-arrow-left me-1"></i> <?php echo __('auth_back_to_homepage', 'Back to Homepage'); ?>
            </a>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('agentRegisterForm');
            const submitBtn = document.getElementById('submitBtn');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');

            // Password match validation
            if (confirmPassword) {
                confirmPassword.addEventListener('blur', function() {
                    if (this.value && this.value !== password.value) {
                        this.style.borderColor = '#dc2626';
                    } else if (this.value) {
                        this.style.borderColor = '#059669';
                    }
                });
            }

            // Phone validation — strip non-digits
            const phoneInput = form.querySelector('input[name="phone"]');
            if (phoneInput) {
                phoneInput.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10);
                });
            }

            // Form submission with loading state
            form.addEventListener('submit', function(e) {
                // Validate password match
                if (password.value !== confirmPassword.value) {
                    e.preventDefault();
                    confirmPassword.style.borderColor = '#dc2626';
                    confirmPassword.focus();
                    return;
                }

                submitBtn.classList.add('loading');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> <?php echo __('auth_creating_account', 'Creating Account...'); ?>';
                submitBtn.disabled = true;
            });
        });
    </script>
<?php include __DIR__ . '/../partials/_chatbot_icons.php'; ?>
</body>

</html>

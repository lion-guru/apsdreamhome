<?php
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', $protocol . '://' . $host . '/apsdreamhome');
}
$csrf_token = $csrf_token ?? '';
$errors = $errors ?? [];
$old = $old ?? [];
$base = BASE_URL;
?>
<style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #059669 0%, #047857 25%, #065f46 50%, #064e3b 75%, #022c22 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .register-card {
            background: #ffffff;
            border-radius: 1.25rem;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3), 0 8px 20px rgba(0, 0, 0, 0.15);
            padding: 2.5rem;
            width: 100%;
            max-width: 560px;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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
        }

        .brand-subtitle {
            color: #059669;
            font-weight: 600;
            font-size: 0.95rem;
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
            background-position: left 0.9rem center, right 0.75rem center;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 0.85rem;
        }

        .input-group-custom>i {
            position: absolute;
            left: 0.95rem;
            top: 50%;
            transform: translateY(-50%);
            color: #059669;
            font-size: 0.85rem;
            z-index: 5;
            pointer-events: none;
        }

        .input-group-custom>i.icon-top {
            top: 2.4rem;
        }

        .form-label-custom {
            font-size: 0.82rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.3rem;
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

        .home-link {
            position: absolute;
            top: 1.25rem;
            left: 1.5rem;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.2s;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .home-link:hover {
            color: #fff;
        }

        .terms-text {
            font-size: 0.78rem;
            color: #94a3b8;
            line-height: 1.5;
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

            .home-link {
                top: 0.75rem;
                left: 1rem;
                font-size: 0.82rem;
            }
        }
    </style>
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

        <form method="POST" action="<?php echo $base; ?>/agent/register" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">

            <div class="section-label"><i class="fa-solid fa-user"></i> <?php echo __('auth_personal_details', 'Personal Details'); ?></div>

            <div class="input-group-custom">
                <i class="fa-solid fa-user-pen icon-top"></i>
                <label class="form-label-custom"><?php echo __('auth_full_name', 'Full Name'); ?></label>
                <input type="text" class="form-control" name="full_name" placeholder="<?php echo __('auth_enter_full_name', 'Enter your full name'); ?>" value="<?php echo htmlspecialchars($old['full_name'] ?? ''); ?>" required>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="input-group-custom">
                        <i class="fa-solid fa-envelope icon-top"></i>
                        <label class="form-label-custom"><?php echo __('auth_email_address', 'Email Address'); ?></label>
                        <input type="email" class="form-control" name="email" placeholder="<?php echo __('auth_email_ph', 'you@example.com'); ?>" value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group-custom">
                        <i class="fa-solid fa-phone icon-top"></i>
                        <label class="form-label-custom"><?php echo __('auth_phone_number', 'Phone Number'); ?></label>
                        <input type="tel" class="form-control" name="phone" placeholder="<?php echo __('auth_10digit_phone', '10-digit phone number'); ?>" pattern="[0-9]{10}" maxlength="10" value="<?php echo htmlspecialchars($old['phone'] ?? ''); ?>" required>
                    </div>
                </div>
            </div>

            <div class="section-label"><i class="fa-solid fa-lock"></i> <?php echo __('auth_security', 'Security'); ?></div>

            <div class="row">
                <div class="col-md-6">
                    <div class="input-group-custom">
                        <i class="fa-solid fa-key icon-top"></i>
                        <label class="form-label-custom"><?php echo __('auth_password', 'Password'); ?></label>
                        <input type="password" class="form-control" name="password" placeholder="<?php echo __('auth_create_a_password', 'Create a password'); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group-custom">
                        <i class="fa-solid fa-shield-halved icon-top"></i>
                        <label class="form-label-custom"><?php echo __('auth_confirm_password', 'Confirm Password'); ?></label>
                        <input type="password" class="form-control" name="confirm_password" placeholder="<?php echo __('auth_reenter_password', 'Re-enter password'); ?>" required>
                    </div>
                </div>
            </div>

            <div class="section-label"><i class="fa-solid fa-briefcase"></i> <?php echo __('auth_professional_info', 'Professional Info'); ?></div>

            <div class="input-group-custom">
                <i class="fa-solid fa-clock-rotate-left icon-top"></i>
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
                <i class="fa-solid fa-ticket icon-top"></i>
                <label class="form-label-custom"><?php echo __('auth_referral_code', 'Referral Code'); ?> <span class="required-badge">*</span></label>
                <input type="text" class="form-control" name="referral_code" placeholder="<?php echo __('auth_enter_referral', 'Enter referral code'); ?>" required value="<?php echo htmlspecialchars($old['referral_code'] ?? ''); ?>">
            </div>

            <div class="terms-text text-center mb-3">
                <?php echo __('auth_terms_prefix', 'By registering, you agree to our'); ?> <a href="#"><?php echo __('auth_terms', 'Terms of Service'); ?></a> and <a href="#"><?php echo __('auth_privacy_policy', 'Privacy Policy'); ?></a>.
            </div>

            <button type="submit" class="btn btn-register">
                <i class="fa-solid fa-user-plus me-2"></i><?php echo __('auth_create_account', 'Create Account'); ?>
            </button>
        </form>

        <div class="divider-line"></div>

        <p class="text-center mb-0" style="font-size: 0.9rem; color: #64748b;">
            <?php echo __('auth_already_have_account', 'Already have an account?'); ?> <a href="<?php echo $base; ?>/agent/login" class="login-link"><?php echo __('auth_login_here', 'Login here'); ?></a>
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php include __DIR__ . '/../partials/_chatbot_icons.php'; ?>
</body></html>
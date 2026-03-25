<?php

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Page title
$$page_title = 'Reset Password - APS Dream Home';

// Content for base layout
ob_start();
?>
<!-- Reset Password Section -->
<div class="reset-password-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="reset-password-card">
                    <div class="reset-header">
                        <div class="reset-icon">
                            <i class="fas fa-key"></i>
                        </div>
                        <h2 class="reset-title">Reset Password</h2>
                        <p class="reset-subtitle">Enter your new password below</p>
                    </div>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $_SESSION['error'];
                            unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $_SESSION['success'];
                            unset($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>auth/reset-password" method="POST" class="reset-form">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token ?? ''); ?>">

                        <div class="mb-4">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-2"></i>New Password
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-lg" id="password" name="password" required
                                    placeholder="Enter your new password" minlength="6">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                    <i class="fas fa-eye" id="password-toggle"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                At least 6 characters with letters, numbers, and symbols
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">
                                <i class="fas fa-lock me-2"></i>Confirm Password
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-lg" id="confirm_password" name="confirm_password" required
                                    placeholder="Confirm your new password">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye" id="confirm_password-toggle"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback" id="password-match-error">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                Passwords do not match
                            </div>
                        </div>

                        <div class="password-strength mb-4">
                            <label class="form-label">Password Strength</label>
                            <div class="strength-meter">
                                <div class="strength-bar" id="strength-bar"></div>
                            </div>
                            <small class="strength-text" id="strength-text">Enter a password</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-key me-2"></i>
                                Reset Password
                            </button>
                            <a href="<?= BASE_URL ?>/auth/login" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-arrow-left me-2"></i>
                                Back to Login
                            </a>
                        </div>
                    </form>

                    <div class="reset-footer">
                        <div class="security-info">
                            <i class="fas fa-shield-alt me-2"></i>
                            <span>Your password is encrypted and secure</span>
                        </div>
                        <div class="help-links">
                            <a href="<?= BASE_URL ?>/contact" class="help-link">
                                <i class="fas fa-headset me-1"></i>
                                Need Help?
                            </a>
                            <a href="<?= BASE_URL ?>/auth/forgot-password" class="help-link">
                                <i class="fas fa-question-circle me-1"></i>
                                Request Another Reset
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .reset-password-section {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2rem 0;
        display: flex;
        align-items: center;
    }

    .reset-password-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        overflow: hidden;
    }

    .reset-header {
        background: linear-gradient(135deg, #764ba2 0%, #f093fb 100%);
        color: white;
        padding: 2rem;
        text-align: center;
        margin-bottom: 2rem;
    }

    .reset-icon {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.5rem;
    }

    .reset-title {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .reset-subtitle {
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 0;
    }

    .reset-form {
        padding: 0 2rem 2rem 2rem;
    }

    .input-group {
        position: relative;
    }

    .input-group .btn {
        border-left: none;
        border-radius: 0 0.375rem 0.375rem 0;
    }

    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #764ba2;
        box-shadow: 0 0 0 0.2rem rgba(118, 75, 162, 0.25);
    }

    .password-strength {
        margin-top: 1rem;
    }

    .strength-meter {
        height: 6px;
        background: #e9ecef;
        border-radius: 3px;
        overflow: hidden;
        margin-bottom: 0.5rem;
    }

    .strength-bar {
        height: 100%;
        width: 0%;
        transition: all 0.3s ease;
        border-radius: 3px;
    }

    .strength-text {
        font-size: 0.875rem;
        color: #6c757d;
    }

    .reset-footer {
        background: #f8f9fa;
        padding: 1.5rem 2rem;
        border-top: 1px solid #e9ecef;
    }

    .security-info {
        color: #28a745;
        font-size: 0.875rem;
        margin-bottom: 1rem;
    }

    .help-links {
        display: flex;
        gap: 1rem;
        justify-content: center;
    }

    .help-link {
        color: #6c757d;
        text-decoration: none;
        font-size: 0.875rem;
        transition: color 0.3s ease;
    }

    .help-link:hover {
        color: #764ba2;
    }

    .invalid-feedback {
        display: none;
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .invalid-feedback.show {
        display: block;
    }

    /* Password strength colors */
    .strength-bar.weak {
        background: #dc3545;
        width: 25%;
    }

    .strength-bar.fair {
        background: #ffc107;
        width: 50%;
    }

    .strength-bar.good {
        background: #28a745;
        width: 75%;
    }

    .strength-bar.strong {
        background: #007bff;
        width: 100%;
    }

    .strength-text.weak {
        color: #dc3545;
    }

    .strength-text.fair {
        color: #ffc107;
    }

    .strength-text.good {
        color: #28a745;
    }

    .strength-text.strong {
        color: #007bff;
    }

    /* Responsive */
    @media (max-width: 576px) {
        .reset-password-section {
            padding: 1rem 0;
        }

        .reset-form {
            padding: 0 1.5rem 1.5rem 1.5rem;
        }

        .help-links {
            flex-direction: column;
            gap: 0.5rem;
        }
    }
</style>

<script>
    // Password visibility toggle
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const toggle = document.getElementById(fieldId + '-toggle');

        if (field.type === 'password') {
            field.type = 'text';
            toggle.classList.remove('fa-eye');
            toggle.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            toggle.classList.remove('fa-eye-slash');
            toggle.classList.add('fa-eye');
        }
    }

    // Password strength checker
    function checkPasswordStrength(password) {
        let strength = 0;
        let strengthText = 'Enter a password';
        let strengthClass = '';

        if (password.length >= 6) strength += 25;
        if (password.length >= 10) strength += 25;
        if (/[a-z]/.test(password)) strength += 12.5;
        if (/[A-Z]/.test(password)) strength += 12.5;
        if (/[0-9]/.test(password)) strength += 12.5;
        if (/[^a-zA-Z0-9]/.test(password)) strength += 12.5;

        if (password.length > 0) {
            if (strength <= 25) {
                strengthText = 'Weak';
                strengthClass = 'weak';
            } else if (strength <= 50) {
                strengthText = 'Fair';
                strengthClass = 'fair';
            } else if (strength <= 75) {
                strengthText = 'Good';
                strengthClass = 'good';
            } else {
                strengthText = 'Strong';
                strengthClass = 'strong';
            }
        }

        return {
            strength,
            strengthText,
            strengthClass
        };
    }

    // Update password strength
    function updatePasswordStrength() {
        const password = document.getElementById('password').value;
        const strengthBar = document.getElementById('strength-bar');
        const strengthText = document.getElementById('strength-text');

        const {
            strength,
            strengthText: text,
            strengthClass
        } = checkPasswordStrength(password);

        strengthBar.style.width = strength + '%';
        strengthBar.className = 'strength-bar ' + strengthClass;
        strengthText.textContent = text;
        strengthText.className = 'strength-text ' + strengthClass;
    }

    // Password confirmation validation
    function validatePasswordMatch() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const matchError = document.getElementById('password-match-error');

        if (confirmPassword && password !== confirmPassword) {
            matchError.classList.add('show');
            document.getElementById('confirm_password').classList.add('is-invalid');
        } else {
            matchError.classList.remove('show');
            document.getElementById('confirm_password').classList.remove('is-invalid');
        }
    }

    // Event listeners
    document.getElementById('password').addEventListener('input', function() {
        updatePasswordStrength();
        validatePasswordMatch();
    });

    document.getElementById('confirm_password').addEventListener('input', validatePasswordMatch);

    // Form validation
    document.querySelector('.reset-form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        if (password.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters long');
            return;
        }

        if (password !== confirmPassword) {
            e.preventDefault();
            document.getElementById('password-match-error').classList.add('show');
            return;
        }
    });
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/base.php';
echo $content;
?>
<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set page variables
$$page_title = 'Employee Login - APS Dream Home';
$page_description = 'Secure employee access to APS Dream Home portal';
$active_page = 'login';

// Include public header
require_once __DIR__ . '/../layouts/header.php';
?>

<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0 login-card">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <div class="employee-icon-wrapper">
                                <i class="fas fa-user-tie"></i>
                            </div>
                        </div>
                        <h2 class="card-title fw-bold gradient-text">Employee Login</h2>
                        <p class="text-muted">Welcome back! Access your employee portal</p>
                        <div class="divider">
                            <span>Employee Portal</span>
                        </div>
                    </div>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php
                            echo htmlspecialchars($_SESSION['error']);
                            unset($_SESSION['error']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php
                            echo htmlspecialchars($_SESSION['success']);
                            unset($_SESSION['success']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo BASE_URL; ?>/employee/login" id="employeeLoginForm" class="employee-login-form">
                        <?php
                        // Generate CSRF token if not exists
                        if (!isset($_SESSION['csrf_token'])) {
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        }
                        $csrf_token = $_SESSION['csrf_token'];
                        ?>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold">
                                <i class="fas fa-envelope me-2 text-primary"></i> Email Address
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-envelope text-primary"></i>
                                </span>
                                <input type="email" class="form-control border-start-0" id="email" name="email"
                                    placeholder="Enter your email" required
                                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">
                                <i class="fas fa-lock me-2 text-primary"></i> Password
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-lock text-primary"></i>
                                </span>
                                <input type="password" class="form-control border-start-0 border-end-0" id="password" name="password"
                                    placeholder="Enter your password" required>
                                <button class="btn btn-outline-secondary border-start-0" type="button" id="togglePassword">
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    <small>Remember me for 30 days</small>
                                </label>
                            </div>
                        </div>

                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-primary btn-lg login-btn">
                                <i class="fas fa-sign-in-alt me-2"></i> Sign In
                            </button>
                        </div>

                        <div class="text-center">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>
                                Protected by 256-bit encryption
                            </small>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-2">
                            <a href="<?php echo BASE_URL; ?>/forgot-password" class="text-decoration-none">
                                <i class="fas fa-question-circle me-1"></i> Forgot your password?
                            </a>
                        </p>
                        <p class="mb-0">
                            <small class="text-muted">
                                Need help? Contact HR at
                                <a href="mailto:hr@apsdreamhome.com" class="text-decoration-none">hr@apsdreamhome.com</a>
                            </small>
                        </p>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="<?php echo BASE_URL; ?>/" class="text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i> Back to Home
                </a>
            </div>
        </div>
    </div>
</main>

<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .login-card {
        border-radius: 20px;
        transition: all 0.4s ease;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        position: relative;
        overflow: hidden;
    }

    .login-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea, #764ba2, #667eea);
        background-size: 200% 100%;
        animation: shimmer 3s ease-in-out infinite;
    }

    @keyframes shimmer {
        0% {
            background-position: -200% 0;
        }

        100% {
            background-position: 200% 0;
        }
    }

    .login-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 35px 70px rgba(0, 0, 0, 0.2);
    }

    .employee-icon-wrapper {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        box-shadow: 0 15px 30px rgba(102, 126, 234, 0.3);
        animation: float 3s ease-in-out infinite;
    }

    .employee-icon-wrapper i {
        font-size: 2.5rem;
        color: white;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    .gradient-text {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .divider {
        display: flex;
        align-items: center;
        margin: 1.5rem 0;
    }

    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, transparent, #e0e0e0, transparent);
    }

    .divider span {
        padding: 0 1rem;
        font-size: 0.85rem;
        color: #6c757d;
        font-weight: 500;
    }

    .login-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        padding: 14px 28px;
        position: relative;
        overflow: hidden;
    }

    .login-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }

    .login-btn:hover::before {
        left: 100%;
    }

    .login-btn:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        transform: translateY(-2px);
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
    }

    .form-control {
        border-radius: 10px;
        border: 2px solid #e8e8e8;
        padding: 12px 16px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.15);
        background-color: #f8f9ff;
    }

    .input-group .input-group-text {
        border-radius: 10px 0 0 10px;
        border: 2px solid #e8e8e8;
        border-right: none;
        background-color: #f8f9fa;
    }

    .input-group .btn {
        border-radius: 0 10px 10px 0;
        border: 2px solid #e8e8e8;
        border-left: none;
        background-color: #f8f9fa;
    }

    .input-group .btn:hover {
        border-color: #667eea;
        background-color: #e8eaff;
    }

    .alert {
        border-radius: 12px;
        border: none;
        animation: slideInDown 0.4s ease;
        font-weight: 500;
    }

    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .login-card {
            margin: 1rem;
        }

        .employee-icon-wrapper {
            width: 60px;
            height: 60px;
        }

        .employee-icon-wrapper i {
            font-size: 2rem;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        if (togglePassword && passwordInput && toggleIcon) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                // Toggle icon with smooth transition
                if (type === 'text') {
                    toggleIcon.classList.remove('fa-eye');
                    toggleIcon.classList.add('fa-eye-slash');
                } else {
                    toggleIcon.classList.remove('fa-eye-slash');
                    toggleIcon.classList.add('fa-eye');
                }
            });
        }

        // Auto-focus email field with smooth animation
        setTimeout(() => {
            const emailField = document.getElementById('email');
            if (emailField) {
                emailField.focus();
                emailField.select();
            }
        }, 500);

        // Enhanced form validation
        const form = document.querySelector('#employeeLoginForm');
        const inputs = form.querySelectorAll('input[required]');
        const submitBtn = form.querySelector('button[type="submit"]');

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
            const fieldName = field.name;

            // Remove existing validation classes
            field.classList.remove('is-valid', 'is-invalid');

            // Validation rules
            let isValid = true;
            let errorMessage = '';

            switch (fieldName) {
                case 'email':
                    if (value.length < 3) {
                        isValid = false;
                        errorMessage = 'Email must be at least 3 characters';
                    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid email address';
                    }
                    break;

                case 'password':
                    if (value.length < 6) {
                        isValid = false;
                        errorMessage = 'Password must be at least 6 characters';
                    }
                    break;
            }

            // Add validation classes
            if (value && isValid) {
                field.classList.add('is-valid');
                removeFieldError(field);
            } else if (value && !isValid) {
                field.classList.add('is-invalid');
                showFieldError(field, errorMessage);
            } else {
                removeFieldError(field);
            }

            return isValid;
        }

        function showFieldError(field, message) {
            removeFieldError(field);

            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            errorDiv.style.fontSize = '0.875rem';
            errorDiv.style.marginTop = '0.25rem';

            field.parentNode.appendChild(errorDiv);
        }

        function removeFieldError(field) {
            const existingError = field.parentNode.querySelector('.invalid-feedback');
            if (existingError) {
                existingError.remove();
            }
        }

        // Form submission with loading state
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate all fields
            let isFormValid = true;
            inputs.forEach(input => {
                if (!validateField(input)) {
                    isFormValid = false;
                }
            });

            if (!isFormValid) {
                // Shake animation for invalid form
                form.style.animation = 'shake 0.5s';
                setTimeout(() => {
                    form.style.animation = '';
                }, 500);
                return;
            }

            // Add loading state
            submitBtn.classList.add('loading');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Authenticating...';
            submitBtn.disabled = true;

            // Submit form
            form.submit();
        });

        // Shake animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                20%, 40%, 60%, 80% { transform: translateX(5px); }
            }
        `;
        document.head.appendChild(style);

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Enter key to submit when focused on any input
            if (e.key === 'Enter' && document.activeElement.tagName === 'INPUT') {
                e.preventDefault();
                form.dispatchEvent(new Event('submit'));
            }

            // Escape key to clear form
            if (e.key === 'Escape') {
                form.reset();
                inputs.forEach(input => {
                    input.classList.remove('is-valid', 'is-invalid');
                    removeFieldError(input);
                });
            }
        });

        // Add subtle entrance animation
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

<?php
// Include public footer
require_once __DIR__ . '/../layouts/footer.php';
?>
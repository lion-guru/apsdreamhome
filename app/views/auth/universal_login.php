<?php

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set page variables
$page_title = 'Universal Login - APS Dream Home';
$page_description = 'Login with email, mobile number, or Google';
$active_page = 'login';

// Content for base layout
ob_start();
?>

<!-- Universal Login Section -->
<div class="universal-login-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="login-card">
                    <div class="login-header">
                        <div class="login-icon">
                            <i class="fas fa-sign-in-alt"></i>
                        </div>
                        <h2 class="login-title">Welcome Back</h2>
                        <p class="login-subtitle">Choose your preferred login method</p>
                    </div>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($_SESSION['error']);
                            unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($_SESSION['success']);
                            unset($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Login Method Tabs -->
                    <ul class="nav nav-tabs login-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#email-tab" type="button" role="tab">
                                <i class="fas fa-envelope me-2"></i>Email Login
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#mobile-tab" type="button" role="tab">
                                <i class="fas fa-mobile-alt me-2"></i>Mobile Login
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#google-tab" type="button" role="tab">
                                <i class="fab fa-google me-2"></i>Google Login
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content login-tab-content">
                        <!-- Email Login Tab -->
                        <div class="tab-pane fade show active" id="email-tab" role="tabpanel">
                            <form action="<?= BASE_URL ?>auth/login" method="POST" class="login-form">
                                <input type="hidden" name="login_type" value="email">

                                <div class="mb-3">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-2"></i>Email Address
                                    </label>
                                    <input type="email" class="form-control form-control-lg" id="email" name="email" required
                                        placeholder="Enter your email address" autocomplete="email">
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Password
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg" id="password" name="password" required
                                            placeholder="Enter your password" autocomplete="current-password">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                            <i class="fas fa-eye" id="password-toggle"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remember-email" name="remember">
                                        <label class="form-check-label" for="remember-email">
                                            Remember me for 30 days
                                        </label>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-sign-in-alt me-2"></i>Login with Email
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="switchToTab('mobile-tab')">
                                        <i class="fas fa-mobile-alt me-2"></i>Try Mobile Login
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Mobile Login Tab -->
                        <div class="tab-pane fade" id="mobile-tab" role="tabpanel">
                            <form action="<?= BASE_URL ?>auth/login" method="POST" class="login-form">
                                <input type="hidden" name="login_type" value="mobile">

                                <div class="mb-3">
                                    <label for="mobile" class="form-label">
                                        <i class="fas fa-mobile-alt me-2"></i>Mobile Number
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">+91</span>
                                        <input type="tel" class="form-control form-control-lg" id="mobile" name="mobile" required
                                            placeholder="9876543210" pattern="[0-9]{10}" maxlength="10">
                                    </div>
                                    <div class="form-text">Enter 10-digit mobile number without country code</div>
                                </div>

                                <div class="mb-3">
                                    <label for="mobile-password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Password / PIN
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg" id="mobile-password" name="password" required
                                            placeholder="Enter password or 4-digit PIN">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('mobile-password')">
                                            <i class="fas fa-eye" id="mobile-password-toggle"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remember-mobile" name="remember">
                                        <label class="form-check-label" for="remember-mobile">
                                            Remember me for 30 days
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="send-otp" name="send_otp">
                                        <label class="form-check-label" for="send-otp">
                                            Send OTP instead of password
                                        </label>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-sign-in-alt me-2"></i>Login with Mobile
                                    </button>
                                    <button type="button" class="btn btn-outline-info" onclick="requestOTP()">
                                        <i class="fas fa-key me-2"></i>Request OTP
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Google Login Tab -->
                        <div class="tab-pane fade" id="google-tab" role="tabpanel">
                            <div class="text-center mb-4">
                                <div class="google-login-info">
                                    <i class="fab fa-google fa-3x text-danger mb-3"></i>
                                    <h4>Google Sign-In</h4>
                                    <p class="text-muted">Quick and secure login with your Google account</p>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-danger btn-lg" onclick="loginWithGoogle()">
                                    <i class="fab fa-google me-2"></i>Continue with Google
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="switchToTab('email-tab')">
                                    <i class="fas fa-envelope me-2"></i>Use Email Instead
                                </button>
                            </div>

                            <div class="google-features">
                                <h6>Benefits of Google Login:</h6>
                                <ul class="feature-list">
                                    <li><i class="fas fa-check text-success me-2"></i>No password to remember</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Two-factor authentication</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Quick access</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Secure connection</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="quick-links">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="<?= BASE_URL ?>auth/forgot-password" class="quick-link">
                                    <i class="fas fa-key me-2"></i>Forgot Password?
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="<?= BASE_URL ?>auth/register" class="quick-link">
                                    <i class="fas fa-user-plus me-2"></i>Create New Account
                                </a>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <a href="<?= BASE_URL ?>agent/register" class="quick-link">
                                    <i class="fas fa-briefcase me-2"></i>Agent Registration
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="<?= BASE_URL ?>associate/register" class="quick-link">
                                    <i class="fas fa-handshake me-2"></i>Associate Registration
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .universal-login-section {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2rem 0;
        display: flex;
        align-items: center;
    }

    .login-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        overflow: hidden;
    }

    .login-header {
        background: linear-gradient(135deg, #764ba2 0%, #f093fb 100%);
        color: white;
        padding: 2rem;
        text-align: center;
        margin-bottom: 2rem;
    }

    .login-icon {
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

    .login-title {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .login-subtitle {
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 0;
    }

    .login-tabs {
        border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        margin-bottom: 2rem;
    }

    .login-tabs .nav-link {
        color: rgba(255, 255, 255, 0.7);
        border: none;
        border-radius: 10px 10px 0 0;
        padding: 1rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .login-tabs .nav-link:hover {
        color: white;
        background: rgba(255, 255, 255, 0.1);
    }

    .login-tabs .nav-link.active {
        color: white;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px 10px 0 0;
    }

    .login-tab-content {
        padding: 0 2rem 2rem 2rem;
    }

    .quick-links {
        background: #f8f9fa;
        padding: 1.5rem 2rem 2rem 2rem;
        border-top: 1px solid #e9ecef;
    }

    .quick-link {
        display: block;
        color: #6c757d;
        text-decoration: none;
        padding: 0.5rem 0;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .quick-link:hover {
        color: #764ba2;
        background: rgba(118, 75, 162, 0.1);
        text-decoration: none;
    }

    .google-features {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 10px;
        margin-top: 1rem;
    }

    .feature-list {
        list-style: none;
        padding: 0;
    }

    .feature-list li {
        padding: 0.5rem 0;
        color: #495057;
    }

    .input-group-text {
        background: #e9ecef;
        border: 1px solid #ced4da;
        border-radius: 0.375rem 0 0 0.375rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .universal-login-section {
            padding: 1rem 0;
        }

        .login-tabs .nav-link {
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
        }

        .login-tab-content {
            padding: 0 1rem 1rem 1rem;
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

    // Tab switching
    function switchToTab(tabId) {
        const tab = new bootstrap.Tab(document.getElementById(tabId));
        tab.show();

        // Update nav links
        document.querySelectorAll('.login-tabs .nav-link').forEach(link => {
            link.classList.remove('active');
        });
        document.querySelector(`[data-bs-target="${tabId}"]`).classList.add('active');
    }

    // Request OTP
    function requestOTP() {
        const mobile = document.getElementById('mobile').value;
        if (!mobile || mobile.length !== 10) {
            alert('Please enter a valid 10-digit mobile number');
            return;
        }

        // Check if OTP checkbox is checked
        const sendOTP = document.getElementById('send-otp');
        if (sendOTP && sendOTP.checked) {
            // Simulate OTP request
            alert(`OTP sent to +91${mobile}`);
            sendOTP.checked = true;
        } else {
            alert('Please check "Send OTP instead of password" to receive OTP');
        }
    }

    // Google login
    function loginWithGoogle() {
        // Simulate Google OAuth
        alert('Google login will be implemented with OAuth 2.0');
        window.location.href = '<?= BASE_URL ?>auth/google';
    }

    // Form validation
    document.querySelectorAll('.login-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const loginType = form.querySelector('input[name="login_type"]').value;

            if (loginType === 'mobile') {
                const mobile = form.querySelector('input[name="mobile"]').value;
                const password = form.querySelector('input[name="password"]').value;

                if (mobile.length !== 10) {
                    e.preventDefault();
                    alert('Please enter a valid 10-digit mobile number');
                    return;
                }

                if (password.length < 4) {
                    e.preventDefault();
                    alert('Password must be at least 4 characters');
                    return;
                }
            }

            if (loginType === 'email') {
                const email = form.querySelector('input[name="email"]').value;
                const password = form.querySelector('input[name="password"]').value;

                if (!email.includes('@')) {
                    e.preventDefault();
                    alert('Please enter a valid email address');
                    return;
                }

                if (password.length < 6) {
                    e.preventDefault();
                    alert('Password must be at least 6 characters');
                    return;
                }
            }
        });
    });

    // Auto-focus first input
    document.addEventListener('DOMContentLoaded', function() {
        const firstInput = document.querySelector('.tab-pane.active input');
        if (firstInput) {
            firstInput.focus();
        }
    });
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/base.php';
echo $content;
?>
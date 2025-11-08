<?php
/**
 * Login Page Template
 * Beautiful login form with modern design
 */

?>

<!-- Login Hero Section -->
<section class="auth-hero py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="auth-hero-content text-center text-white">
                    <h1 class="display-5 fw-bold mb-3">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Welcome Back
                    </h1>
                    <p class="lead mb-4">
                        Sign in to your APS Dream Home account to access exclusive features and manage your property journey.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Login Form Section -->
<section class="auth-form py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="auth-card">
                    <div class="auth-header text-center mb-4">
                        <h2 class="auth-title">
                            <i class="fas fa-user-circle text-primary me-2"></i>
                            Sign In
                        </h2>
                        <p class="text-muted">Enter your credentials to access your account</p>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo BASE_URL; ?>login/process" method="POST" class="auth-form-content">
                        <div class="mb-4">
                            <label for="email" class="form-label fw-medium">
                                <i class="fas fa-envelope text-primary me-2"></i>
                                Email Address
                            </label>
                            <input type="email"
                                   class="form-control form-control-lg"
                                   id="email"
                                   name="email"
                                   placeholder="Enter your email address"
                                   required
                                   autocomplete="email">
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-medium">
                                <i class="fas fa-lock text-primary me-2"></i>
                                Password
                            </label>
                            <div class="position-relative">
                                <input type="password"
                                       class="form-control form-control-lg"
                                       id="password"
                                       name="password"
                                       placeholder="Enter your password"
                                       required
                                       autocomplete="current-password">
                                <button type="button"
                                        class="btn btn-outline-secondary position-absolute top-50 end-0 translate-middle-y me-3 border-0 bg-transparent"
                                        onclick="togglePassword()">
                                    <i class="fas fa-eye" id="passwordToggle"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Remember me for 30 days
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 mb-4">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Sign In
                        </button>

                        <div class="auth-links text-center">
                            <p class="mb-3">
                                <a href="<?php echo BASE_URL; ?>forgot-password" class="text-decoration-none">
                                    Forgot your password?
                                </a>
                            </p>
                            <p class="mb-0">
                                Don't have an account?
                                <a href="<?php echo BASE_URL; ?>register" class="fw-bold text-decoration-none">
                                    Create one here
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="auth-features py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h3 class="section-title">
                    <i class="fas fa-star text-primary me-2"></i>
                    Why Choose APS Dream Home?
                </h3>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 text-center">
                <div class="feature-card">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-shield-alt fa-3x text-primary"></i>
                    </div>
                    <h5>Secure & Trusted</h5>
                    <p class="text-muted">
                        Your data is protected with enterprise-grade security and encryption.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 text-center">
                <div class="feature-card">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-home fa-3x text-success"></i>
                    </div>
                    <h5>Exclusive Properties</h5>
                    <p class="text-muted">
                        Access premium properties and exclusive deals not available elsewhere.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 text-center">
                <div class="feature-card">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-headset fa-3x text-warning"></i>
                    </div>
                    <h5>24/7 Support</h5>
                    <p class="text-muted">
                        Our expert support team is available round the clock to assist you.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Password visibility toggle
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('passwordToggle');

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

// Auto-focus on email field
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('email').focus();
});
</script>

<?php
/**
 * Registration Page Template
 * Beautiful registration form with modern design
 */

?>

<!-- Registration Hero Section -->
<section class="auth-hero py-5" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="auth-hero-content text-center text-white">
                    <h1 class="display-5 fw-bold mb-3">
                        <i class="fas fa-user-plus me-2"></i>
                        Join APS Dream Home
                    </h1>
                    <p class="lead mb-4">
                        Create your account to access exclusive properties, save favorites, and get personalized recommendations for your dream home.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Registration Form Section -->
<section class="auth-form py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="auth-card">
                    <div class="auth-header text-center mb-4">
                        <h2 class="auth-title">
                            <i class="fas fa-edit text-success me-2"></i>
                            Create Your Account
                        </h2>
                        <p class="text-muted">Fill in your details to get started</p>
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

                    <form action="<?php echo BASE_URL; ?>register/process" method="POST" class="auth-form-content">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-medium">
                                    <i class="fas fa-user text-success me-2"></i>
                                    Full Name
                                </label>
                                <input type="text"
                                       class="form-control form-control-lg"
                                       id="name"
                                       name="name"
                                       placeholder="Enter your full name"
                                       required
                                       autocomplete="name">
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label fw-medium">
                                    <i class="fas fa-envelope text-success me-2"></i>
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

                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-medium">
                                    <i class="fas fa-phone text-success me-2"></i>
                                    Phone Number
                                </label>
                                <input type="tel"
                                       class="form-control form-control-lg"
                                       id="phone"
                                       name="phone"
                                       placeholder="Enter your phone number"
                                       required
                                       autocomplete="tel">
                            </div>

                            <div class="col-md-6">
                                <label for="role" class="form-label fw-medium">
                                    <i class="fas fa-user-tag text-success me-2"></i>
                                    I am a
                                </label>
                                <select class="form-select form-select-lg" id="role" name="role" required>
                                    <option value="customer">Property Buyer</option>
                                    <option value="agent">Real Estate Agent</option>
                                    <option value="investor">Property Investor</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label fw-medium">
                                    <i class="fas fa-lock text-success me-2"></i>
                                    Password
                                </label>
                                <div class="position-relative">
                                    <input type="password"
                                           class="form-control form-control-lg"
                                           id="password"
                                           name="password"
                                           placeholder="Create a strong password"
                                           required
                                           autocomplete="new-password">
                                    <button type="button"
                                            class="btn btn-outline-secondary position-absolute top-50 end-0 translate-middle-y me-3 border-0 bg-transparent"
                                            onclick="togglePassword('password')">
                                        <i class="fas fa-eye" id="passwordToggle1"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    <small>Password must be at least 6 characters long</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label fw-medium">
                                    <i class="fas fa-lock text-success me-2"></i>
                                    Confirm Password
                                </label>
                                <div class="position-relative">
                                    <input type="password"
                                           class="form-control form-control-lg"
                                           id="confirm_password"
                                           name="confirm_password"
                                           placeholder="Confirm your password"
                                           required
                                           autocomplete="new-password">
                                    <button type="button"
                                            class="btn btn-outline-secondary position-absolute top-50 end-0 translate-middle-y me-3 border-0 bg-transparent"
                                            onclick="togglePassword('confirm_password')">
                                        <i class="fas fa-eye" id="passwordToggle2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 mt-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="<?php echo BASE_URL; ?>terms" class="text-decoration-none">Terms of Service</a>
                                    and <a href="<?php echo BASE_URL; ?>privacy" class="text-decoration-none">Privacy Policy</a>
                                </label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter">
                                <label class="form-check-label" for="newsletter">
                                    Subscribe to our newsletter for property updates and exclusive offers
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg w-100 mb-4">
                            <i class="fas fa-user-plus me-2"></i>
                            Create Account
                        </button>

                        <div class="auth-links text-center">
                            <p class="mb-0">
                                Already have an account?
                                <a href="<?php echo BASE_URL; ?>login" class="fw-bold text-decoration-none">
                                    Sign in here
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Benefits Section -->
<section class="auth-benefits py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h3 class="section-title">
                    <i class="fas fa-gift text-success me-2"></i>
                    What You'll Get
                </h3>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 text-center">
                <div class="benefit-card">
                    <div class="benefit-icon mb-3">
                        <i class="fas fa-search fa-3x text-primary"></i>
                    </div>
                    <h5>Advanced Search</h5>
                    <p class="text-muted">
                        Find properties using our powerful search and filtering system with location, price, and type filters.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 text-center">
                <div class="benefit-card">
                    <div class="benefit-icon mb-3">
                        <i class="fas fa-heart fa-3x text-danger"></i>
                    </div>
                    <h5>Save Favorites</h5>
                    <p class="text-muted">
                        Save your favorite properties and get notified when new matching properties become available.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 text-center">
                <div class="benefit-card">
                    <div class="benefit-icon mb-3">
                        <i class="fas fa-bell fa-3x text-warning"></i>
                    </div>
                    <h5>Personalized Alerts</h5>
                    <p class="text-muted">
                        Get personalized property recommendations and alerts based on your preferences and search history.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 text-center">
                <div class="benefit-card">
                    <div class="benefit-icon mb-3">
                        <i class="fas fa-comments fa-3x text-info"></i>
                    </div>
                    <h5>Direct Agent Contact</h5>
                    <p class="text-muted">
                        Contact property agents directly and schedule viewings for properties you're interested in.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 text-center">
                <div class="benefit-card">
                    <div class="benefit-icon mb-3">
                        <i class="fas fa-chart-line fa-3x text-success"></i>
                    </div>
                    <h5>Market Insights</h5>
                    <p class="text-muted">
                        Access market trends, price analysis, and neighborhood information to make informed decisions.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 text-center">
                <div class="benefit-card">
                    <div class="benefit-icon mb-3">
                        <i class="fas fa-mobile-alt fa-3x text-secondary"></i>
                    </div>
                    <h5>Mobile Access</h5>
                    <p class="text-muted">
                        Access all features on your mobile device with our responsive design that works perfectly on all screens.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Password visibility toggle
function togglePassword(inputId) {
    const passwordInput = document.getElementById(inputId);
    const toggleIcon = document.getElementById('passwordToggle' + (inputId === 'password' ? '1' : '2'));

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

// Password confirmation validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;

    if (password !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});

// Auto-focus on name field
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('name').focus();
});
</script>

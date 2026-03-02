<?php
/**
 * Login Page Template
 * Beautiful login form with modern design
 */

?>

<section class="py-5" style="background: radial-gradient(circle at top, rgba(14,165,233,.15), transparent 60%), #f8fafc;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-5 col-lg-6">
                <div class="text-center mb-4">
                    <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill px-3 py-1 mb-2">Welcome back</span>
                    <h1 class="fw-bold text-dark mb-2">Sign in to continue</h1>
                    <p class="text-secondary mb-0">Manage your saved properties, track bookings and receive personalised recommendations.</p>
                </div>
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="h4 fw-semibold mb-4 text-center">Account Login</h2>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <span><?php echo htmlspecialchars($error); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success d-flex align-items-center" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <span><?php echo htmlspecialchars($success); ?></span>
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo BASE_URL; ?>login/process" method="POST" class="row g-3">
                            <div class="col-12">
                                <label for="email" class="form-label fw-semibold text-secondary">Email address</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-primary-subtle text-primary-emphasis border-0">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required autocomplete="email">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label for="password" class="form-label fw-semibold text-secondary">Password</label>
                                    <a href="<?php echo BASE_URL; ?>forgot-password" class="small text-decoration-none">Forgot password?</a>
                                </div>
                                <div class="input-group input-group-lg position-relative">
                                    <span class="input-group-text bg-primary-subtle text-primary-emphasis border-0">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required autocomplete="current-password">
                                    <button type="button" class="btn btn-link text-decoration-none position-absolute top-50 end-0 translate-middle-y me-3" onclick="togglePassword()">
                                        <i class="fas fa-eye" id="passwordToggle"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">Remember me for 30 days</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg w-100">Sign In</button>
                            </div>

                            <div class="col-12 text-center">
                                <p class="mb-0 text-secondary">Don’t have an account?
                                    <a href="<?php echo BASE_URL; ?>register" class="fw-semibold text-decoration-none">Create one now</a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row g-4 mt-4">
                    <div class="col-md-4 text-center">
                        <div class="p-3 bg-white border rounded-4 shadow-sm h-100">
                            <i class="fas fa-shield-alt fa-2x text-primary mb-3"></i>
                            <h6 class="fw-semibold">Secure Access</h6>
                            <p class="small text-muted mb-0">Bank-grade encryption for every session.</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="p-3 bg-white border rounded-4 shadow-sm h-100">
                            <i class="fas fa-home fa-2x text-success mb-3"></i>
                            <h6 class="fw-semibold">Exclusive Listings</h6>
                            <p class="small text-muted mb-0">Access 500+ curated properties.</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="p-3 bg-white border rounded-4 shadow-sm h-100">
                            <i class="fas fa-headset fa-2x text-warning mb-3"></i>
                            <h6 class="fw-semibold">24/7 Support</h6>
                            <p class="small text-muted mb-0">Dedicated advisors at every step.</p>
                        </div>
                    </div>
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

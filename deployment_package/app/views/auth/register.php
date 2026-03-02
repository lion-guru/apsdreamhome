<?php include __DIR__ . '/../layouts/header.php'; ?>

<section class="py-5" style="background: radial-gradient(circle at top, rgba(34,197,94,.18), transparent 70%), #0f172a;">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-7">
                <div class="text-center text-white mb-4">
                    <span class="badge bg-success-subtle text-success-emphasis rounded-pill px-3 py-2 mb-3">Create account</span>
                    <h1 class="fw-bold mb-2">Join APS Dream Home</h1>
                    <p class="text-white-50 mb-0">Save favourites, receive curated alerts and manage bookings from a single dashboard.</p>
                </div>
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="h4 fw-semibold mb-3 text-center">Tell us about yourself</h2>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <span><?php echo htmlspecialchars($error); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success d-flex align-items-center" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <span><?php echo htmlspecialchars($success); ?></span>
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo BASE_URL; ?>register/process" method="POST" class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-semibold text-secondary">Full name</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="e.g. Rohan Sharma" required autocomplete="name">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold text-secondary">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="you@example.com" required autocomplete="email">
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-semibold text-secondary">Phone number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="9876543210" required autocomplete="tel">
                            </div>
                            <div class="col-md-6">
                                <label for="role" class="form-label fw-semibold text-secondary">I am a</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="customer">Property buyer</option>
                                    <option value="agent">Real estate agent</option>
                                    <option value="investor">Property investor</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-semibold text-secondary">Password</label>
                                <div class="input-group input-group-lg position-relative">
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Create a strong password" required autocomplete="new-password">
                                    <button type="button" class="btn btn-link text-decoration-none position-absolute top-50 end-0 translate-middle-y me-3" onclick="toggleAuthPassword('password','passwordToggle1')">
                                        <i class="fas fa-eye" id="passwordToggle1"></i>
                                    </button>
                                </div>
                                <div class="form-text">Minimum 6 characters with letters & numbers.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label fw-semibold text-secondary">Confirm password</label>
                                <div class="input-group input-group-lg position-relative">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required autocomplete="new-password">
                                    <button type="button" class="btn btn-link text-decoration-none position-absolute top-50 end-0 translate-middle-y me-3" onclick="toggleAuthPassword('confirm_password','passwordToggle2')">
                                        <i class="fas fa-eye" id="passwordToggle2"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">I agree to the <a href="<?php echo BASE_URL; ?>terms" class="text-decoration-none">Terms</a> and <a href="<?php echo BASE_URL; ?>privacy" class="text-decoration-none">Privacy Policy</a>.</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter">
                                    <label class="form-check-label" for="newsletter">Send me curated deals and property updates.</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-success btn-lg w-100">Create account</button>
                            </div>
                            <div class="col-12 text-center">
                                <p class="mb-0 text-secondary">Already a member? <a href="<?php echo BASE_URL; ?>login" class="fw-semibold text-decoration-none">Sign in</a></p>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row g-3 mt-4">
                    <div class="col-md-4">
                        <div class="p-3 bg-white border rounded-4 shadow-sm h-100 text-center">
                            <i class="fas fa-search text-primary fa-2x mb-2"></i>
                            <h6 class="fw-semibold">Smart search</h6>
                            <p class="small text-muted mb-0">Filter by locality, price, amenities and more.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-white border rounded-4 shadow-sm h-100 text-center">
                            <i class="fas fa-heart text-danger fa-2x mb-2"></i>
                            <h6 class="fw-semibold">Save favourites</h6>
                            <p class="small text-muted mb-0">Create wishlists and receive instant alerts.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-white border rounded-4 shadow-sm h-100 text-center">
                            <i class="fas fa-comments text-warning fa-2x mb-2"></i>
                            <h6 class="fw-semibold">Direct support</h6>
                            <p class="small text-muted mb-0">Dedicated advisors to guide your property journey.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script>
function toggleAuthPassword(inputId, toggleId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(toggleId);
    if (!input || !icon) return;
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    icon.classList.toggle('fa-eye', !isPassword);
    icon.classList.toggle('fa-eye-slash', isPassword);
}

document.addEventListener('DOMContentLoaded', function () {
    const nameField = document.getElementById('name');
    if (nameField) {
        nameField.focus();
    }

    const confirmField = document.getElementById('confirm_password');
    if (confirmField) {
        confirmField.addEventListener('input', function () {
            const password = document.getElementById('password').value;
            if (password !== this.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    }
});
</script>

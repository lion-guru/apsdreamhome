<?php $pageTitle = 'Register'; ?>
<?php $error = $error ?? ''; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                        <h4>Create Account</h4>
                        <p class="text-muted small">Register to get started with APS Dream Home</p>
                    </div>
                    <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <form method="post" action="<?= BASE_URL ?>register">
                        <div class="mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <div class="input-group"><span class="input-group-text"><i class="fas fa-user"></i></span><input type="text" class="form-control" name="name" required placeholder="Your full name"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <div class="input-group"><span class="input-group-text"><i class="fas fa-envelope"></i></span><input type="email" class="form-control" name="email" required placeholder="email@example.com"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <div class="input-group"><span class="input-group-text"><i class="fas fa-phone"></i></span><input type="tel" class="form-control" name="phone" required placeholder="+91 9XXXXXXXX"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="input-group"><span class="input-group-text"><i class="fas fa-lock"></i></span><input type="password" class="form-control" name="password" required minlength="6" placeholder="At least 6 characters"></div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group"><span class="input-group-text"><i class="fas fa-lock"></i></span><input type="password" class="form-control" name="confirm_password" required placeholder="Repeat password"></div>
                        </div>
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="agree" id="agree" required>
                            <label class="form-check-label small" for="agree">I agree to the <a href="<?= BASE_URL ?>terms" target="_blank">Terms & Conditions</a> and <a href="<?= BASE_URL ?>privacy" target="_blank">Privacy Policy</a></label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-user-plus me-2"></i>Register</button>
                    </form>
                    <hr class="my-4">
                    <p class="text-center mb-0 small">Already have an account? <a href="<?= BASE_URL ?>login">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../app/views/includes/header.php'; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm mt-5">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Set New Password</h2>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                    <?php endif; ?>

                    <form action="/auth/reset-password" method="POST">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token ?? ''); ?>">

                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="password" name="password" required
                                   placeholder="Enter your new password" minlength="6">
                            <div class="form-text">At least 6 characters</div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required
                                   placeholder="Confirm your new password">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Reset Password</button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-0">
                            <a href="/login" class="text-decoration-none">Back to Login</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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

    document.getElementById('password').addEventListener('input', function() {
        const confirmPassword = document.getElementById('confirm_password');
        const password = this.value;
        const confirmPasswordValue = confirmPassword.value;

        if (confirmPasswordValue && password !== confirmPasswordValue) {
            confirmPassword.setCustomValidity('Passwords do not match');
        } else {
            confirmPassword.setCustomValidity('');
        }
    });
</script>

<?php include '../app/views/includes/footer.php'; ?>

<?php include '../app/views/includes/header.php'; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm mt-5">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Reset Your Password</h2>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                    <?php endif; ?>

                    <form action="/auth/forgot-password" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required
                                   placeholder="Enter your registered email address">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Send Reset Link</button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-0">
                            <a href="/login" class="text-decoration-none">Back to Login</a> |
                            <a href="/register" class="text-decoration-none">Create Account</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../app/views/includes/footer.php'; ?>

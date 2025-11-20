<?php include __DIR__ . '/../includes/header.php'; ?>

<section class="py-5" style="background: radial-gradient(circle at top, rgba(14,165,233,.18), transparent 70%), #0f172a;">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-8">
                <div class="text-center text-white mb-4">
                    <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill px-3 py-2 mb-3">Reset password</span>
                    <h1 class="fw-bold mb-2">Forgot your password?</h1>
                    <p class="text-white-50 mb-0">Enter your registered email and we’ll send you a secure link to create a new password.</p>
                </div>
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success d-flex align-items-center" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <span><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
                            </div>
                        <?php endif; ?>

                        <form action="/auth/forgot-password" method="POST" class="vstack gap-3">
                            <div>
                                <label for="email" class="form-label fw-semibold text-secondary">Email address</label>
                                <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="name@example.com" required autocomplete="email">
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">Send reset link</button>
                            <p class="text-muted small mb-0 text-center">If the email is associated with an account, you’ll receive a reset link within a few minutes.</p>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-4 text-white-50">
                    <p class="mb-0">
                        <a href="/login" class="text-decoration-none text-white">Back to login</a>
                        <span class="mx-2">|</span>
                        <a href="/register" class="text-decoration-none text-white">Create account</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>

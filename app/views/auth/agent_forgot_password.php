<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h4 class="text-center mb-4"><i class="fas fa-key me-2"></i>Agent Password Reset</h4>
                    <p class="text-muted text-center mb-4">Enter your email to receive a password reset link.</p>
                    <form method="POST" action="<?= BASE_URL ?>/agent/forgot-password">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="<?= BASE_URL ?>/agent/login" class="text-decoration-none">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

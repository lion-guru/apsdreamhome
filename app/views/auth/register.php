<?php
/**
 * Generic register view — redirects to role-specific register pages.
 * Used by AuthenticationController as a unified entry point.
 */
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h4 class="text-center mb-4"><i class="fas fa-user-plus me-2"></i>Create Account</h4>
                    <form method="POST" action="<?= BASE_URL ?>/register">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($old['name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone *</label>
                                <input type="tel" name="phone" class="form-control" required value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password *</label>
                                <input type="password" name="password" class="form-control" required minlength="8">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm Password *</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Referral Code</label>
                                <input type="text" name="referral_code" class="form-control" value="<?= htmlspecialchars($_GET['ref'] ?? $old['referral_code'] ?? '') ?>">
                            </div>
                        </div>
                        <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger mt-3">
                            <?php foreach ($errors as $err): ?>
                            <div><?= htmlspecialchars($err) ?></div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary w-100 mt-4">Register</button>
                    </form>
                    <div class="text-center mt-3">
                        Already have an account? <a href="<?= BASE_URL ?>/login" class="text-decoration-none">Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

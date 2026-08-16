<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h4 class="mb-4"><i class="fas fa-key me-2 text-primary"></i>Change Password</h4>
                    <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error ?? '') ?></div><?php endif; ?>
                    <?php if (!empty($success)): ?><div class="alert alert-success"><?= htmlspecialchars($success ?? '') ?></div><?php endif; ?>
                    <form method="POST" action="<?= BASE_URL ?>/auth/change-password">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required minlength="8">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required minlength="8">
                        </div>
                        
<?php echo SimpleCaptcha::renderField("Enter Security Code"); ?>
<button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-2"></i>Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
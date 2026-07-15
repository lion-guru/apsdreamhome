<?php $pageTitle = __('auth_my_profile'); ?>
<?php $user = $user ?? []; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> Home</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>user/dashboard"><?= __('dashboard') ?></a></li><li class="breadcrumb-item active"><?= __('auth_my_profile') ?></li></ol></nav>
    <?php if (empty($user)): ?>
    <div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i><h6 class="text-muted"><?= __('auth_user_not_found') ?></h6><p class="text-muted small"><?= __('auth_user_not_found_desc') ?></p><a href="<?= BASE_URL ?>login" class="btn btn-primary btn-sm"><i class="fas fa-sign-in-alt me-1"></i><?= __('login') ?></a></div></div>
    <?php else: ?>
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body aps-cp-card-body">
                    <?php
                    $userId = (int)($user['id'] ?? $_SESSION['user_id'] ?? 0);
                    $photoUrl = !empty($user['profile_image']) ? BASE_URL . '/' . $user['profile_image'] : null;
                    $userName = $user['name'] ?? 'User';
                    $size = 'lg';
                    include __DIR__ . '/../shared/profile_photo_upload.php';
                    ?>
                    <h5 class="mt-3"><?= htmlspecialchars($user['name'] ?? '-') ?></h5>
                    <p class="text-muted small mb-1"><i class="fas fa-envelope me-1"></i><?= htmlspecialchars($user['email'] ?? '-') ?></p>
                    <p class="text-muted small mb-0"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($user['phone'] ?? '-') ?></p>
                    <hr>
                    <a href="<?= BASE_URL ?>user/dashboard" class="btn btn-outline-primary btn-sm w-100"><i class="fas fa-tachometer-alt me-1"></i><?= __('dashboard') ?></a>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-edit me-2"></i><?= __('auth_edit_profile') ?></h6></div>
                <div class="card-body aps-cp-card-body">
                    <form method="post" action="<?= BASE_URL ?>user/profile">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label"><?= __('register_label_name') ?></label>
                            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?= __('email_address') ?></label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly disabled>
                            <small class="text-muted"><?= __('auth_email_cannot_changed') ?></small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?= __('phone_number') ?></label>
                            <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                        </div>
                        <hr>
                        <h6><?= __('auth_change_password_page_title') ?></h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label"><?= __('auth_current_password') ?></label>
                                <input type="password" class="form-control" name="current_password" placeholder="<?= __('auth_leave_blank_keep') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><?= __('auth_new_password') ?></label>
                                <input type="password" class="form-control" name="new_password" placeholder="<?= __('auth_min_6_chars') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><?= __('auth_confirm_new_password') ?></label>
                                <input type="password" class="form-control" name="confirm_password" placeholder="<?= __('auth_repeat_new_password') ?>">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i><?= __('auth_update_profile') ?></button>
                    </form>
                </div>
            </div>
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i><?= __('auth_account_info') ?></h6></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <tr><th><?= __('auth_member_since') ?></th><td><?= htmlspecialchars($user['created_at'] ?? 'N/A') ?></td></tr>
                        <tr><th><?= __('auth_account_status') ?></th><td><span class="badge bg-success"><?= htmlspecialchars(ucfirst($user['status'] ?? 'Active')) ?></span></td></tr>
                        <tr><th><?= __('auth_last_login') ?></th><td><?= htmlspecialchars($user['last_login'] ?? 'N/A') ?></td></tr>
                    </table></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
$page_title = __('user_profile_title') . ' - APS Dream Home';
$extraHead = '<style>
    .profile-card { border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
</style>';
?>

<div class="content-area p-4">
    <div class="row">
        <div class="col-lg-9">
            <div class="card profile-card">
                <div class="card-header bg-white">
                    <h4 class="mb-0"><i class="fas fa-user-cog me-2 text-primary"></i><?= __('user_profile_heading') ?></h4>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i><?= __('user_profile_updated') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label"><?= __('user_profile_label_name') ?> *</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('user_profile_label_email') ?></label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled>
                                <small class="text-muted"><?= __('user_profile_email_locked') ?></small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label"><?= __('user_profile_label_phone') ?> *</label>
                                <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('user_profile_label_member_since') ?></label>
                                <input type="text" class="form-control" value="<?php echo date('d M Y', strtotime($user['created_at'] ?? 'now')); ?>" disabled>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3"><?= __('user_profile_password_heading') ?></h5>
                        <p class="text-muted small mb-3"><?= __('user_profile_password_hint') ?></p>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label"><?= __('user_profile_label_new_password') ?></label>
                                <input type="password" name="new_password" class="form-control" placeholder="<?= __('user_profile_ph_new_password') ?>" minlength="6">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('user_profile_label_confirm_password') ?></label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="<?= __('user_profile_ph_confirm_password') ?>">
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i><?= __('user_profile_button_save') ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-4 profile-card">
                <div class="card-body aps-cp-card-body">
                    <h5 class="mb-3"><i class="fas fa-shield-alt me-2 text-danger"></i><?= __('user_profile_security_heading') ?></h5>
                    <p class="text-muted"><?= __('user_profile_security_desc') ?></p>
                    <a href="<?php echo BASE_URL; ?>/user/logout" class="btn btn-outline-danger">
                        <i class="fas fa-sign-out-alt me-2"></i><?= __('user_profile_button_logout') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

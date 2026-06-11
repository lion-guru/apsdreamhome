<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush rounded-3">
                        <a href="<?php echo BASE_URL; ?>dashboard" class="list-group-item list-group-item-action py-3">
                            <i class="fas fa-tachometer-alt me-2"></i> <?= __('user_settings_nav_dashboard', null, 'Dashboard') ?>
                        </a>
                        <a href="<?php echo BASE_URL; ?>dashboard/profile" class="list-group-item list-group-item-action py-3">
                            <i class="fas fa-user me-2"></i> <?= __('user_settings_nav_profile', null, 'My Profile') ?>
                        </a>
                        <a href="<?php echo BASE_URL; ?>dashboard/settings" class="list-group-item list-group-item-action active py-3">
                            <i class="fas fa-cog me-2"></i> <?= __('user_settings_nav_settings', null, 'Settings') ?>
                        </a>
                        <a href="<?php echo BASE_URL; ?>notifications" class="list-group-item list-group-item-action py-3">
                            <i class="fas fa-bell me-2"></i> <?= __('user_settings_nav_notifications', null, 'Notifications') ?>
                        </a>
                        <form action="<?php echo BASE_URL; ?>logout" method="POST" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="submit" class="list-group-item list-group-item-action py-3 text-danger border-0 w-100 text-start">
                                <i class="fas fa-sign-out-alt me-2"></i> <?= __('user_settings_nav_logout', null, 'Logout') ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <h2 class="mb-4"><?= __('user_settings_page_title', null, 'Account Settings') ?></h2>
            
            <?php if (isset($data['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $data['success']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($data['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $data['error']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><?= __('user_settings_privacy_title', null, 'Privacy & Security') ?></h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form action="<?php echo BASE_URL; ?>dashboard/settings/update-security" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-4">
                            <label class="form-label fw-bold"><?= __('user_settings_email_notif', null, 'Email Notifications') ?></label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="emailNotif" checked>
                                <label class="form-check-label" for="emailNotif"><?= __('user_settings_email_notif_desc', null, 'Receive updates about new properties and projects') ?></label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold"><?= __('user_settings_2fa_label', null, 'Two-Factor Authentication') ?></label>
                            <p class="text-muted small"><?= __('user_settings_2fa_desc', null, 'Add an extra layer of security to your account.') ?></p>
                            <button type="button" class="btn btn-outline-primary btn-sm"><?= __('user_settings_2fa_enable', null, 'Enable 2FA') ?></button>
                        </div>

                        <hr>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary"><?= __('user_settings_save', null, 'Save Changes') ?></button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-3 border-danger-subtle">
                <div class="card-header bg-danger text-white py-3">
                    <h5 class="mb-0"><?= __('user_settings_danger_title', null, 'Danger Zone') ?></h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted"><?= __('user_settings_danger_desc', null, 'Once you delete your account, there is no going back. Please be certain.') ?></p>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                        <?= __('user_settings_delete_btn', null, 'Delete My Account') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= __('user_settings_modal_title', null, 'Delete Account') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?= __('user_settings_modal_desc', null, 'Are you sure you want to delete your account? This action cannot be undone.') ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('user_settings_modal_cancel', null, 'Cancel') ?></button>
                <form action="<?php echo BASE_URL; ?>dashboard/delete-account" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="btn btn-danger"><?= __('user_settings_modal_confirm', null, 'Yes, Delete Account') ?></button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

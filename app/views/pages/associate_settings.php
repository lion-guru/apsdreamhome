<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; } $phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '+91 92771 21112'); $emailDisplay = $sc('contact_email', 'support@apsdreamhome.com'); ?>
<?php
/**
 * Associate Settings Page
 */
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-cog me-2"></i><?= __('assoc_settings_heading', [], 'Settings') ?></h1>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success ?? ''); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error ?? ''); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Account Info -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i><?= __('assoc_settings_profile', [], 'Account Information') ?></h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <form action="<?= defined('BASE_URL') ? BASE_URL : '' ?>/associate/settings/update" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="mb-3">
                        <label class="form-label fw-bold"><?= __('assoc_label_name', [], 'Name') ?></label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($associate_name ?? ''); ?>" readonly>
                        <small class="text-muted">Contact admin to change name</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold"><?= __('assoc_label_email', [], 'Email') ?></label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($associate_email ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold"><?= __('assoc_label_phone', [], 'Phone') ?></label>
                        <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($associate_phone ?? ''); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i><?= __('assoc_update_btn', [], 'Update Profile') ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Notification Preferences -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-bell me-2"></i><?= __('assoc_settings_notifications', [], 'Notification Preferences') ?></h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <form action="<?= defined('BASE_URL') ? BASE_URL : '' ?>/associate/settings/notifications" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="email_leads" id="email_leads" <?php echo $notifications['email_leads'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="email_leads">
                            <i class="fas fa-envelope text-primary me-2"></i><?= __('assoc_notif_email_leads', [], 'Email me for new leads') ?>
                        </label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="email_commissions" id="email_commissions" <?php echo $notifications['email_commissions'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="email_commissions">
                            <i class="fas fa-money-bill-wave text-success me-2"></i><?= __('assoc_notif_commission', [], 'Email me for commission updates') ?>
                        </label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="sms_important" id="sms_important" <?php echo $notifications['sms_important'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="sms_important">
                            <i class="fas fa-sms text-warning me-2"></i><?= __('assoc_notif_sms', [], 'SMS for important updates') ?>
                        </label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="marketing_emails" id="marketing_emails" <?php echo $notifications['marketing_emails'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="marketing_emails">
                            <i class="fas fa-bullhorn text-info me-2"></i><?= __('assoc_notif_marketing', [], 'Marketing emails') ?>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-info text-white">
                        <i class="fas fa-save me-2"></i><?= __('assoc_save_btn', [], 'Save Preferences') ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Security Section -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-lock me-2"></i><?= __('assoc_change_password', [], 'Change Password') ?></h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <form action="<?= defined('BASE_URL') ? BASE_URL : '' ?>/associate/settings/password" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="mb-3">
                        <label class="form-label fw-bold"><?= __('assoc_label_current_password', [], 'Current Password') ?></label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold"><?= __('assoc_label_new_password', [], 'New Password') ?></label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold"><?= __('assoc_label_confirm_password', [], 'Confirm New Password') ?></label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key me-2"></i><?= __('assoc_update_password', [], 'Update Password') ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Support Section -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-headset me-2"></i><?= __('assoc_support_heading', [], 'Support') ?></h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <p class="text-muted"><?= __('assoc_support_desc', [], 'Need help? Contact our support team:') ?></p>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-phone text-success me-2"></i>
                        <strong><?= htmlspecialchars($phoneDisplay ?? '') ?></strong>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-envelope text-primary me-2"></i>
                        <strong><?= e($sc('contact_email', 'info@apsdreamhome.com')) ?></strong>
                    </li>
                    <li>
                        <i class="fas fa-clock text-warning me-2"></i>
                        Mon-Sat, 9AM-6PM
                    </li>
                </ul>
                <a href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/contact" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-paper-plane me-2"></i><?= __('assoc_contact_support', [], 'Contact Support') ?>
                </a>
            </div>
        </div>
    </div>
</div>

<?php
/**
 * Associate Settings Page
 */
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-cog me-2"></i>Settings</h1>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Account Info -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Account Information</h5>
            </div>
            <div class="card-body">
                <form action="/associate/settings/update" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Name</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($associate_name); ?>" readonly>
                        <small class="text-muted">Contact admin to change name</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($associate_email); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Phone</label>
                        <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($associate_phone); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Profile
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Notification Preferences -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Notification Preferences</h5>
            </div>
            <div class="card-body">
                <form action="/associate/settings/notifications" method="POST">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="email_leads" id="email_leads" <?php echo $notifications['email_leads'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="email_leads">
                            <i class="fas fa-envelope text-primary me-2"></i>Email me for new leads
                        </label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="email_commissions" id="email_commissions" <?php echo $notifications['email_commissions'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="email_commissions">
                            <i class="fas fa-money-bill-wave text-success me-2"></i>Email me for commission updates
                        </label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="sms_important" id="sms_important" <?php echo $notifications['sms_important'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="sms_important">
                            <i class="fas fa-sms text-warning me-2"></i>SMS for important updates
                        </label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="marketing_emails" id="marketing_emails" <?php echo $notifications['marketing_emails'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="marketing_emails">
                            <i class="fas fa-bullhorn text-info me-2"></i>Marketing emails
                        </label>
                    </div>
                    <button type="submit" class="btn btn-info text-white">
                        <i class="fas fa-save me-2"></i>Save Preferences
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
                <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Change Password</h5>
            </div>
            <div class="card-body">
                <form action="/associate/settings/password" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key me-2"></i>Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Support Section -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-headset me-2"></i>Support</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Need help? Contact our support team:</p>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-phone text-success me-2"></i>
                        <strong>+91 92771 21112</strong>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-envelope text-primary me-2"></i>
                        <strong>support@apsdreamhome.com</strong>
                    </li>
                    <li>
                        <i class="fas fa-clock text-warning me-2"></i>
                        Mon-Sat, 9AM-6PM
                    </li>
                </ul>
                <a href="/contact" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-paper-plane me-2"></i>Contact Support
                </a>
            </div>
        </div>
    </div>
</div>

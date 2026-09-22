<?php
/**
 * Associate Settings View
 * APS Dream Home - Associate Portal
 */
$page_title = $page_title ?? 'Account Settings - Associate Portal';
$user = $user ?? [];
$userName = $user['name'] ?? $_SESSION['user_name'] ?? 'Associate Partner';
$userEmail = $user['email'] ?? $_SESSION['user_email'] ?? '';
$userPhone = $user['phone'] ?? '';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';

// Parse notification preferences if stored as JSON, or set defaults
$notifs = [];
if (!empty($user['notification_preferences'])) {
    if (is_array($user['notification_preferences'])) {
        $notifs = $user['notification_preferences'];
    } else {
        $decoded = json_decode((string)$user['notification_preferences'], true);
        $notifs = is_array($decoded) ? $decoded : [];
    }
}

$emailLeads = $notifs['email_leads'] ?? 1;
$emailCommissions = $notifs['email_commissions'] ?? 1;
$smsImportant = $notifs['sms_important'] ?? 1;
$whatsappAlerts = $notifs['whatsapp_alerts'] ?? 1;
$marketingEmails = $notifs['marketing_emails'] ?? 0;
$twoFactorEnabled = !empty($user['two_factor_enabled']);

$success = $_SESSION['flash_success'] ?? $_SESSION['success'] ?? null;
$error = $_SESSION['flash_error'] ?? $_SESSION['error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['success'], $_SESSION['flash_error'], $_SESSION['error']);
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.assoc-settings-card {
    border: none;
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    margin-bottom: 24px;
    overflow: hidden;
}
.assoc-settings-header {
    background: #ffffff;
    border-bottom: 1px solid #f1f5f9;
    padding: 18px 24px;
}
.assoc-settings-body {
    padding: 24px;
}
.nav-settings-pills .nav-link {
    color: #64748b;
    border-radius: 10px;
    padding: 10px 18px;
    font-weight: 500;
    margin-bottom: 6px;
    transition: all 0.2s ease;
}
.nav-settings-pills .nav-link:hover {
    background: #f8fafc;
    color: #0f172a;
}
.nav-settings-pills .nav-link.active {
    background: linear-gradient(135deg, #0f172a 0%, #134e4a 100%);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(19, 78, 74, 0.25);
}
.custom-switch-card {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px 20px;
    background: #fafafa;
    transition: all 0.2s ease;
}
.custom-switch-card:hover {
    background: #ffffff;
    border-color: #cbd5e1;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
</style>

<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-cog me-2 text-primary"></i>Account Settings</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/associate/dashboard" class="text-decoration-none text-muted">Dashboard</a> <span class="mx-1 text-muted">/</span></li>
                    <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Settings</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= $baseUrl ?>/associate/profile" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="fas fa-user me-1"></i> View Profile
            </a>
        </div>
    </div>

    <!-- Flash Notifications -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Settings Nav Pills (Left Column) -->
        <div class="col-lg-3 col-md-4">
            <div class="assoc-settings-card p-3">
                <div class="d-flex align-items-center gap-3 p-2 mb-3 border-bottom pb-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 1.2rem;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div>
                        <div class="fw-bold small text-dark"><?= htmlspecialchars($userName) ?></div>
                        <div class="text-muted" style="font-size: 0.75rem;">Associate Partner</div>
                    </div>
                </div>

                <div class="nav flex-column nav-pills nav-settings-pills" id="settingsTab" role="tablist">
                    <button class="nav-link active text-start" id="tab-security-btn" data-bs-toggle="pill" data-bs-target="#tab-security" type="button" role="tab">
                        <i class="fas fa-lock me-2"></i> Security & Password
                    </button>
                    <button class="nav-link text-start" id="tab-notifications-btn" data-bs-toggle="pill" data-bs-target="#tab-notifications" type="button" role="tab">
                        <i class="fas fa-bell me-2"></i> Notifications
                    </button>
                    <button class="nav-link text-start" id="tab-privacy-btn" data-bs-toggle="pill" data-bs-target="#tab-privacy" type="button" role="tab">
                        <i class="fas fa-shield-alt me-2"></i> 2FA & Privacy
                    </button>
                    <button class="nav-link text-start" id="tab-support-btn" data-bs-toggle="pill" data-bs-target="#tab-support" type="button" role="tab">
                        <i class="fas fa-headset me-2"></i> Help & Support
                    </button>
                </div>
            </div>
        </div>

        <!-- Settings Content Panes (Right Column) -->
        <div class="col-lg-9 col-md-8">
            <div class="tab-content" id="settingsTabContent">
                <!-- 1. Security & Password Tab -->
                <div class="tab-pane fade show active" id="tab-security" role="tabpanel">
                    <div class="assoc-settings-card">
                        <div class="assoc-settings-header">
                            <h5 class="fw-bold mb-1"><i class="fas fa-key me-2 text-warning"></i>Change Password</h5>
                            <p class="text-muted small mb-0">Ensure your account uses a strong and unique password</p>
                        </div>
                        <div class="assoc-settings-body">
                            <form action="<?= $baseUrl ?>/associate/settings/password" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="action" value="change_password">

                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="current_password" class="form-label small fw-semibold">Current Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Enter your current password" required>
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePass('current_password')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="new_password" class="form-label small fw-semibold">New Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Minimum 8 characters" required minlength="8">
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePass('new_password')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="confirm_password" class="form-label small fw-semibold">Confirm New Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Repeat new password" required minlength="8">
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePass('confirm_password')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="p-3 bg-light rounded-3 small text-muted border">
                                            <div class="fw-semibold mb-1 text-dark"><i class="fas fa-info-circle me-1 text-primary"></i>Password Guidelines:</div>
                                            <ul class="mb-0 ps-3">
                                                <li>At least 8 characters long</li>
                                                <li>Include numbers, uppercase letters, and special symbols (@$!%*?&)</li>
                                                <li>Do not use simple words or previous passwords</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="col-12 text-end pt-2">
                                        <button type="submit" class="btn btn-warning px-4 rounded-pill fw-semibold shadow-sm">
                                            <i class="fas fa-lock me-2"></i>Update Password
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- 2. Notifications Tab -->
                <div class="tab-pane fade" id="tab-notifications" role="tabpanel">
                    <div class="assoc-settings-card">
                        <div class="assoc-settings-header">
                            <h5 class="fw-bold mb-1"><i class="fas fa-bell me-2 text-info"></i>Notification Preferences</h5>
                            <p class="text-muted small mb-0">Control how and when you receive portal alerts</p>
                        </div>
                        <div class="assoc-settings-body">
                            <form action="<?= $baseUrl ?>/associate/settings/notifications" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="action" value="update_notifications">

                                <div class="vstack gap-3 mb-4">
                                    <div class="custom-switch-card d-flex justify-content-between align-items-center">
                                        <label for="email_leads" class="mb-0 cursor-pointer flex-grow-1">
                                            <div class="fw-semibold text-dark"><i class="fas fa-user-plus text-primary me-2"></i>Lead Assignments & Inquiries</div>
                                            <div class="text-muted small">Receive immediate email alerts when a lead is assigned to you</div>
                                        </label>
                                        <div class="form-check form-switch m-0 fs-5">
                                            <input class="form-check-input" type="checkbox" name="email_leads" value="1" id="email_leads" <?= $emailLeads ? 'checked' : '' ?>>
                                        </div>
                                    </div>

                                    <div class="custom-switch-card d-flex justify-content-between align-items-center">
                                        <label for="email_commissions" class="mb-0 cursor-pointer flex-grow-1">
                                            <div class="fw-semibold text-dark"><i class="fas fa-hand-holding-usd text-success me-2"></i>Commission & Wallet Payouts</div>
                                            <div class="text-muted small">Notifications whenever commission is credited or payout is processed</div>
                                        </label>
                                        <div class="form-check form-switch m-0 fs-5">
                                            <input class="form-check-input" type="checkbox" name="email_commissions" value="1" id="email_commissions" <?= $emailCommissions ? 'checked' : '' ?>>
                                        </div>
                                    </div>

                                    <div class="custom-switch-card d-flex justify-content-between align-items-center">
                                        <label for="whatsapp_alerts" class="mb-0 cursor-pointer flex-grow-1">
                                            <div class="fw-semibold text-dark"><i class="fab fa-whatsapp text-success me-2"></i>WhatsApp Alerts</div>
                                            <div class="text-muted small">Receive instant site-visit confirmations and reminders via WhatsApp</div>
                                        </label>
                                        <div class="form-check form-switch m-0 fs-5">
                                            <input class="form-check-input" type="checkbox" name="whatsapp_alerts" value="1" id="whatsapp_alerts" <?= $whatsappAlerts ? 'checked' : '' ?>>
                                        </div>
                                    </div>

                                    <div class="custom-switch-card d-flex justify-content-between align-items-center">
                                        <label for="sms_important" class="mb-0 cursor-pointer flex-grow-1">
                                            <div class="fw-semibold text-dark"><i class="fas fa-sms text-warning me-2"></i>SMS for Critical Updates</div>
                                            <div class="text-muted small">Receive urgent OTPs and booking transaction receipts via SMS</div>
                                        </label>
                                        <div class="form-check form-switch m-0 fs-5">
                                            <input class="form-check-input" type="checkbox" name="sms_important" value="1" id="sms_important" <?= $smsImportant ? 'checked' : '' ?>>
                                        </div>
                                    </div>

                                    <div class="custom-switch-card d-flex justify-content-between align-items-center">
                                        <label for="marketing_emails" class="mb-0 cursor-pointer flex-grow-1">
                                            <div class="fw-semibold text-dark"><i class="fas fa-bullhorn text-info me-2"></i>Promotional & Marketing Updates</div>
                                            <div class="text-muted small">Updates about new project launches, colony expansions, and festivals</div>
                                        </label>
                                        <div class="form-check form-switch m-0 fs-5">
                                            <input class="form-check-input" type="checkbox" name="marketing_emails" value="1" id="marketing_emails" <?= $marketingEmails ? 'checked' : '' ?>>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-info text-white px-4 rounded-pill shadow-sm">
                                        <i class="fas fa-save me-2"></i>Save Preferences
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- 3. 2FA & Privacy Tab -->
                <div class="tab-pane fade" id="tab-privacy" role="tabpanel">
                    <div class="assoc-settings-card">
                        <div class="assoc-settings-header">
                            <h5 class="fw-bold mb-1"><i class="fas fa-shield-alt me-2 text-success"></i>Two-Factor Authentication & Privacy</h5>
                            <p class="text-muted small mb-0">Enhance the safety of your associate account</p>
                        </div>
                        <div class="assoc-settings-body">
                            <div class="p-3 bg-light rounded-3 mb-4 border">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div>
                                        <h6 class="fw-bold mb-1">Two-Factor Authentication (2FA)</h6>
                                        <p class="text-muted small mb-0">Require an SMS or Email OTP verification code every time you sign in</p>
                                    </div>
                                    <form action="<?= $baseUrl ?>/associate/settings/2fa" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="action" value="toggle_2fa">
                                        <input type="hidden" name="enable" value="<?= $twoFactorEnabled ? '0' : '1' ?>">
                                        <button type="submit" class="btn btn-sm <?= $twoFactorEnabled ? 'btn-outline-danger' : 'btn-outline-success' ?> rounded-pill px-3">
                                            <i class="fas <?= $twoFactorEnabled ? 'fa-toggle-on' : 'fa-toggle-off' ?> me-1"></i>
                                            <?= $twoFactorEnabled ? 'Disable 2FA' : 'Enable 2FA' ?>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="p-3 bg-light rounded-3 mb-4 border">
                                <h6 class="fw-bold mb-1 text-dark"><i class="fas fa-desktop me-2 text-primary"></i>Active Sessions</h6>
                                <p class="text-muted small mb-2">You are currently logged in from this browser session.</p>
                                <div class="d-flex justify-content-between align-items-center small text-muted">
                                    <span><i class="fas fa-map-marker-alt me-1"></i>Current IP: <?= htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1') ?></span>
                                    <span class="badge bg-success">Active Now</span>
                                </div>
                            </div>

                            <div class="p-3 border border-danger-subtle rounded-3 bg-danger bg-opacity-10">
                                <h6 class="fw-bold text-danger mb-1"><i class="fas fa-exclamation-triangle me-2"></i>Deactivate Account</h6>
                                <p class="text-muted small mb-2">To pause your associate account or transfer commissions, please raise a support ticket.</p>
                                <a href="<?= $baseUrl ?>/user/tickets" class="btn btn-sm btn-outline-danger rounded-pill">
                                    <i class="fas fa-ticket-alt me-1"></i>Raise Ticket
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Support Tab -->
                <div class="tab-pane fade" id="tab-support" role="tabpanel">
                    <div class="assoc-settings-card">
                        <div class="assoc-settings-header">
                            <h5 class="fw-bold mb-1"><i class="fas fa-headset me-2 text-primary"></i>Associate Support Helpdesk</h5>
                            <p class="text-muted small mb-0">Get in touch with the APS Dream Home partner care team</p>
                        </div>
                        <div class="assoc-settings-body">
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 border h-100">
                                        <div class="text-primary fs-4 mb-2"><i class="fas fa-phone-alt"></i></div>
                                        <h6 class="fw-bold mb-1">Associate Helpline</h6>
                                        <p class="text-muted small mb-2">Mon-Sat from 9:00 AM to 6:00 PM</p>
                                        <a href="tel:+919277121112" class="fw-bold text-primary text-decoration-none">+91 92771 21112</a>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 border h-100">
                                        <div class="text-success fs-4 mb-2"><i class="fab fa-whatsapp"></i></div>
                                        <h6 class="fw-bold mb-1">WhatsApp Partner Desk</h6>
                                        <p class="text-muted small mb-2">Instant assistance for site visits & bookings</p>
                                        <a href="https://api.whatsapp.com/send?phone=919277121112" target="_blank" class="fw-bold text-success text-decoration-none">Chat on WhatsApp</a>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                <a href="<?= $baseUrl ?>/user/tickets" class="btn btn-primary rounded-pill px-4">
                                    <i class="fas fa-ticket-alt me-2"></i>Open Support Ticket
                                </a>
                                <a href="<?= $baseUrl ?>/associate/documents" class="btn btn-outline-secondary rounded-pill px-3">
                                    <i class="fas fa-file-contract me-1"></i>Associate Guidelines
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
function togglePass(inputId) {
    const el = document.getElementById(inputId);
    if (!el) return;
    if (el.type === 'password') {
        el.type = 'text';
    } else {
        el.type = 'password';
    }
}
</script>

<?php $pageTitle = 'Email Settings'; ?>
<?php $config = $config ?? ['smtp_host' => '', 'smtp_port' => 587, 'smtp_user' => '', 'smtp_pass' => '', 'smtp_encryption' => 'tls', 'from_email' => '', 'from_name' => 'APS Dream Home']; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/settings">Settings</a></li><li class="breadcrumb-item active">Email Configuration</li></ol></nav>
    <div class="d-flex justify-content-between align-items-center mb-4"><h4 class="mb-0"><i class="fas fa-envelope me-2"></i>Email Settings</h4></div>
    <form method="post" action="<?= BASE_URL ?>admin/settings/email">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-server me-2"></i>SMTP Configuration</h6></div>
                    <div class="card-body aps-cp-card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">SMTP Host <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="smtp_host" value="<?= htmlspecialchars($config['smtp_host'] ?? '') ?>" required placeholder="smtp.example.com">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Port <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="smtp_port" value="<?= htmlspecialchars($config['smtp_port'] ?? 587) ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Encryption</label>
                                <select class="form-select" name="smtp_encryption">
                                    <option value="tls" <?= ($config['smtp_encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS</option>
                                    <option value="ssl" <?= ($config['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                    <option value="none" <?= ($config['smtp_encryption'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="smtp_user" value="<?= htmlspecialchars($config['smtp_user'] ?? '') ?>" required placeholder="email@example.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="smtp_pass" value="<?= htmlspecialchars($config['smtp_pass'] ?? '') ?>" required placeholder="********">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-envelope-open-text me-2"></i>Sender Details</h6></div>
                    <div class="card-body aps-cp-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">From Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="from_email" value="<?= htmlspecialchars($config['from_email'] ?? '') ?>" required placeholder="noreply@apsdreamhome.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">From Name</label>
                                <input type="text" class="form-control" name="from_name" value="<?= htmlspecialchars($config['from_name'] ?? 'APS Dream Home') ?>" placeholder="APS Dream Home">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Test Email</h6></div>
                    <div class="card-body aps-cp-card-body">
                        <p class="small text-muted">Send a test email to verify your SMTP configuration.</p>
                        <div class="mb-3">
                            <label class="form-label">Send To</label>
                            <input type="email" class="form-control" name="test_email" placeholder="your@email.com">
                        </div>
                        <button type="button" class="btn btn-outline-primary w-100 btn-sm" onclick="sendTestEmail()"><i class="fas fa-paper-plane me-1"></i>Send Test</button>
                        <hr>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-2"></i>Save Settings</button>
                    </div>
                </div>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Features</h6></div>
                    <div class="card-body aps-cp-card-body">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="email_notifications" id="email_notifications" value="1" <?= !empty($config['email_notifications']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="email_notifications">Email Notifications</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="welcome_email" id="welcome_email" value="1" <?= !empty($config['welcome_email']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="welcome_email">Welcome Emails</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="property_alerts" id="property_alerts" value="1" <?= !empty($config['property_alerts']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="property_alerts">Property Alerts</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="newsletter" id="newsletter" value="1" <?= !empty($config['newsletter']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="newsletter">Newsletter Campaigns</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function sendTestEmail() {
    var emailInput = document.querySelector('input[name="test_email"]');
    var email = emailInput ? emailInput.value.trim() : '';
    if (!email) { alert('Please enter a test email address.'); emailInput.focus(); return; }
    var btn = event.target.closest('button');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending...';
    fetch('<?= BASE_URL ?>admin/settings/email-config/test', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=' + encodeURIComponent('<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>') + '&test_email=' + encodeURIComponent(email)
    }).then(function(r){return r.json()}).then(function(d){
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Send Test';
        if (d.success) { alert('Test email sent successfully to ' + email); } else { alert('Failed: ' + (d.error || 'Unknown error')); }
    }).catch(function(){ btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Send Test'; alert('Network error. Please try again.'); });
}
</script>

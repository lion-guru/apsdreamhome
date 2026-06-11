<?php $pageTitle = 'SMTP Email Settings'; ?>
<?php
$smtp = $smtp ?? [
    'host' => 'smtp.gmail.com',
    'port' => '587',
    'username' => '',
    'password' => '',
    'encryption' => 'tls',
    'from_email' => 'noreply@apsdreamhome.com',
    'from_name' => 'APS Dream Home'
];
?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/settings">Settings</a></li>
            <li class="breadcrumb-item active">SMTP Email Settings</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-cog me-2"></i>SMTP Email Settings</h4>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($_SESSION['success']) ?><?php unset($_SESSION['success']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($_SESSION['error']) ?><?php unset($_SESSION['error']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <strong>SMTP Configuration</strong> — Configure your outgoing email server settings.
        APS Dream Home will use these settings to send transactional emails, notifications, and newsletters.
        <hr>
        <p class="mb-0 small">
            <i class="fas fa-shield-alt me-1"></i> Use TLS port 587 for secure connections.
            <i class="fas fa-key ms-3 me-1"></i> For Gmail, use an <strong>App Password</strong> (not your regular password).
            <i class="fas fa-question-circle ms-3 me-1"></i> Contact your hosting provider for SMTP credentials.
        </p>
    </div>

    <form method="post" action="<?= BASE_URL ?>admin/settings/smtp-save">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <?= \App\Helpers\SecurityHelper::csrfField() ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="fas fa-server me-2"></i>SMTP Server</h6>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <label class="form-label">SMTP Host <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="smtp_host" value="<?= htmlspecialchars($smtp['host'] ?? '') ?>" required placeholder="smtp.gmail.com">
                                <div class="form-text">e.g., smtp.gmail.com, smtp.office365.com, mail.yourdomain.com</div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Port <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="smtp_port" value="<?= htmlspecialchars($smtp['port'] ?? '587') ?>" required placeholder="587">
                                <div class="form-text">587 (TLS) or 465 (SSL)</div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Encryption</label>
                                <select class="form-select" name="smtp_encryption">
                                    <option value="tls" <?= ($smtp['encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS (Recommended)</option>
                                    <option value="ssl" <?= ($smtp['encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                    <option value="none" <?= ($smtp['encryption'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
                                </select>
                                <div class="form-text">TLS is the most secure and widely supported option.</div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="smtp_username" value="<?= htmlspecialchars($smtp['username'] ?? '') ?>" required placeholder="your@email.com" autocomplete="off">
                                <div class="form-text">Full email address for authentication.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="smtp_password" value="<?= htmlspecialchars($smtp['password'] ?? '') ?>" required placeholder="Enter SMTP password" autocomplete="off">
                                <div class="form-text">SMTP password or App Password.</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="fas fa-envelope-open-text me-2"></i>Default Sender</h6>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">From Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="smtp_from_email" value="<?= htmlspecialchars($smtp['from_email'] ?? '') ?>" required placeholder="noreply@apsdreamhome.com">
                                <div class="form-text">The "From" address for all outgoing emails.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">From Name</label>
                                <input type="text" class="form-control" name="smtp_from_name" value="<?= htmlspecialchars($smtp['from_name'] ?? 'APS Dream Home') ?>" placeholder="APS Dream Home">
                                <div class="form-text">The display name recipients will see.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Actions</h6>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-save me-2"></i>Save Settings
                        </button>
                        <button type="button" class="btn btn-outline-secondary w-100 mb-3" onclick="testSmtpConnection()">
                            <i class="fas fa-vial me-2"></i>Test Connection
                        </button>
                        <a href="<?= BASE_URL ?>admin/settings/email" class="btn btn-outline-info w-100">
                            <i class="fas fa-envelope me-2"></i>Advanced Email Settings
                        </a>
                    </div>
                </div>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Common Providers</h6>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <ul class="list-unstyled mb-0 small">
                            <li class="mb-2"><i class="fab fa-google text-danger me-2"></i><strong>Gmail:</strong> smtp.gmail.com:587 (TLS)</li>
                            <li class="mb-2"><i class="fab fa-microsoft text-primary me-2"></i><strong>Outlook/Office365:</strong> smtp.office365.com:587 (TLS)</li>
                            <li class="mb-2"><i class="fas fa-envelope text-success me-2"></i><strong>SendGrid:</strong> smtp.sendgrid.net:587 (TLS)</li>
                            <li class="mb-2"><i class="fas fa-envelope text-warning me-2"></i><strong>Mailgun:</strong> smtp.mailgun.org:587 (TLS)</li>
                            <li class="mb-2"><i class="fas fa-server text-secondary me-2"></i><strong>cPanel:</strong> mail.yourdomain.com:465 (SSL)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function testSmtpConnection() {
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Testing...';
    fetch('<?= BASE_URL ?>admin/settings/smtp-test', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ test: true })
    })
    .then(r => r.json())
    .then(d => {
        const cls = d.success ? 'success' : 'danger';
        const icon = d.success ? 'check-circle' : 'exclamation-circle';
        const html = `<div class="alert alert-${cls} alert-dismissible fade show mt-3"><i class="fas fa-${icon} me-2"></i>${d.message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
        document.querySelector('form').insertAdjacentHTML('beforebegin', html);
    })
    .catch(() => {
        document.querySelector('form').insertAdjacentHTML('beforebegin', '<div class="alert alert-danger alert-dismissible fade show mt-3"><i class="fas fa-times-circle me-2"></i>Connection test failed.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-vial me-2"></i>Test Connection';
    });
}
</script>

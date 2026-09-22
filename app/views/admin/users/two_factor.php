<?php $layout = "admin/layouts/admin"; $active_page = "index"; ?>
<?php $csrf = $_SESSION['csrf_token'] ?? ''; ?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Two-Factor Authentication: <?= htmlspecialchars($user['name'] ?? 'User') ?></h1>
        <p class="text-muted mb-0">
            <a href="<?= BASE_URL ?>/admin/users/<?= $user['id'] ?>" class="text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i>Back to User
            </a> &middot;
            <strong><?= htmlspecialchars($user['email'] ?? '') ?></strong> (<?= ucfirst($user['role'] ?? 'user') ?>)
        </p>
    </div>
</div>

<?php if (isset($success) && $success): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i><?php echo e($success); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <!-- 2FA Status Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>2FA Status</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-1">
                            <?php if (!empty($user['two_factor_enabled'])): ?>
                            <span class="badge bg-success">Enabled</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Disabled</span>
                            <?php endif; ?>
                        </h5>
                        <p class="text-muted mb-0">Two-factor authentication adds an extra layer of security by requiring a code from an authenticator app.</p>
                    </div>
                    <div>
                        <?php if (!empty($user['two_factor_enabled'])): ?>
                        <button class="btn btn-outline-danger" onclick="toggle2FA('disable')" id="disable2FABtn">
                            <i class="fas fa-toggle-off me-1"></i>Disable 2FA
                        </button>
                        <button class="btn btn-outline-info ms-2" onclick="regenerateBackupCodes()">
                            <i class="fas fa-key me-1"></i>Regenerate Backup Codes
                        </button>
                        <?php else: ?>
                        <button class="btn btn-primary" onclick="toggle2FA('enable')" id="enable2FABtn">
                            <i class="fas fa-toggle-on me-1"></i>Enable 2FA
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code / Setup (shown when enabling) -->
        <div id="setupSection" class="card border-0 shadow-sm mb-4 d-none">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-qrcode me-2"></i>Setup Authenticator App</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div id="qrCodeContainer"></div>
                </div>
                <div class="alert alert-info">
                    <strong>Scan the QR code</strong> with your authenticator app (Google Authenticator, Authy, Microsoft Authenticator, etc.)
                </div>
                <div class="mb-3">
                    <label class="form-label">Secret Key (manual entry)</label>
                    <div class="input-group">
                        <input type="text" class="form-control font-monospace" id="secretKey" readonly>
                        <button class="btn btn-outline-secondary" onclick="copySecret()">Copy</button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Enter 6-digit code from app</label>
                    <input type="text" class="form-control" id="verifyCode" maxlength="6" placeholder="000000" style="letter-spacing: 0.5em; text-align: center;">
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" onclick="verifyAndEnable()"><i class="fas fa-check me-1"></i>Verify & Enable</button>
                    <button class="btn btn-outline-secondary" onclick="cancelSetup()">Cancel</button>
                </div>
            </div>
        </div>

        <!-- Backup Codes -->
        <div id="backupCodesSection" class="card border-0 shadow-sm mb-4 d-none">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-key me-2"></i>Backup Codes</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Save these codes in a safe place. Each code can be used once if you lose access to your authenticator app.</p>
                <div class="row" id="backupCodesContainer"></div>
                <div class="mt-3">
                    <button class="btn btn-outline-secondary" onclick="downloadBackupCodes()"><i class="fas fa-download me-1"></i>Download</button>
                    <button class="btn btn-outline-secondary ms-2" onclick="printBackupCodes()"><i class="fas fa-print me-1"></i>Print</button>
                    <button class="btn btn-outline-danger ms-2" onclick="regenerateBackupCodes()"><i class="fas fa-redo me-1"></i>Regenerate</button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Info Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>About 2FA</h5>
            </div>
            <div class="card-body">
                <ul class="mb-0 small">
                    <li><strong>TOTP (Time-based One-Time Password)</strong> - Codes change every 30 seconds</li>
                    <li><strong>Compatible apps:</strong> Google Authenticator, Authy, Microsoft Authenticator, 1Password, Bitwarden</li>
                    <li><strong>Backup codes:</strong> 8 one-time codes for emergency access</li>
                    <li><strong>Recovery:</strong> If you lose your device, use a backup code or contact admin</li>
                </ul>
                <hr>
                <h6>Current Status</h6>
                <dl class="row small mb-0">
                    <dt class="col-6">2FA Enabled</dt>
                    <dd class="col-6"><?= !empty($user['two_factor_enabled']) ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?></dd>
                    <dt class="col-6">Secret Set</dt>
                    <dd class="col-6"><?= !empty($user['two_factor_secret']) ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?></dd>
                    <dt class="col-6">Backup Codes</dt>
                    <dd class="col-6"><?= !empty($user['two_factor_backup_codes']) ? '<span class="badge bg-success">Set</span>' : '<span class="badge bg-secondary">Not Set</span>' ?></dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<script>
const BASE_URL = '<?= BASE_URL ?>';
const CSRF = '<?= $csrf ?>';
const USER_ID = <?= $user['id'] ?>;

function toggle2FA(action) {
    if (action === 'enable') {
        // Show setup section
        document.getElementById('setupSection').classList.remove('d-none');
        document.getElementById('enable2FABtn').disabled = true;
        document.getElementById('disable2FABtn').style.display = 'none';
        
        // Generate QR code
        fetch(BASE_URL + '/admin/users/' + USER_ID + '/two-factor', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
            body: 'csrf_token=' + encodeURIComponent(CSRF) + '&action=enable'
        }).then(r => r.json()).then(d => {
            if (d.success) {
                document.getElementById('secretKey').value = d.secret;
                // Generate QR code using QRCode.js or similar
                const qrContainer = document.getElementById('qrCodeContainer');
                qrContainer.innerHTML = '';
                if (window.QRCode) {
                    new QRCode(qrContainer, {
                        text: d.qr,
                        width: 200,
                        height: 200
                    });
                } else {
                    qrContainer.innerHTML = '<img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(d.qr) + '" alt="QR Code">';
                }
            }
        });
    } else if (action === 'disable') {
        if (!confirm('Disable 2FA for this user? This will remove their secret and backup codes.')) return;
        
        fetch(BASE_URL + '/admin/users/' + USER_ID + '/two-factor', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
            body: 'csrf_token=' + encodeURIComponent(CSRF) + '&action=disable'
        }).then(r => r.json()).then(d => {
            if (d.success) {
                showToast('2FA disabled', 'success');
                location.reload();
            } else {
                showToast(d.message || 'Failed', 'danger');
            }
        });
    }
}

function regenerateBackupCodes() {
    fetch(BASE_URL + '/admin/users/' + USER_ID + '/two-factor', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: 'csrf_token=' + encodeURIComponent(CSRF) + '&action=regenerate_backup'
    }).then(r => r.json()).then(d => {
        if (d.success) {
            showBackupCodes(d.codes);
            showToast('Backup codes regenerated', 'success');
        } else {
            showToast(d.message || 'Failed', 'danger');
        }
    });
}

function showBackupCodes(codes) {
    const container = document.getElementById('backupCodesContainer');
    container.innerHTML = '';
    codes.forEach((code, i) => {
        const col = document.createElement('div');
        col.className = 'col-6 mb-2';
        col.innerHTML = `<code class="d-block p-2 bg-light border rounded text-center font-monospace" style="font-size: 0.9rem;">${code}</code>`;
        container.appendChild(col);
    });
    document.getElementById('backupCodesSection').classList.remove('d-none');
}

function verifyAndEnable() {
    const code = document.getElementById('verifyCode').value;
    if (!code || code.length !== 6) {
        showToast('Enter 6-digit code', 'warning');
        return;
    }
    
    // In a real implementation, verify the TOTP code here
    // For now, we'll just enable it
    fetch(BASE_URL + '/admin/users/' + USER_ID + '/two-factor', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: 'csrf_token=' + encodeURIComponent(CSRF) + '&action=enable&code=' + code
    }).then(r => r.json()).then(d => {
        if (d.success) {
            showToast('2FA enabled successfully', 'success');
            location.reload();
        } else {
            showToast(d.message || 'Invalid code', 'danger');
        }
    });
}

function cancelSetup() {
    document.getElementById('setupSection').classList.add('d-none');
    document.getElementById('enable2FABtn').disabled = false;
    document.getElementById('disable2FABtn').style.display = '';
    
    fetch(BASE_URL + '/admin/users/' + USER_ID + '/two-factor', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: 'csrf_token=' + encodeURIComponent(CSRF) + '&action=disable'
    }).then(r => r.json()).then(d => {
        // Cleanup secret
    });
}

function copySecret() {
    const input = document.getElementById('secretKey');
    input.select();
    document.execCommand('copy');
    showToast('Secret copied!', 'success');
}

function downloadBackupCodes() {
    const codes = document.querySelectorAll('#backupCodesContainer code');
    const text = Array.from(codes).map(c => c.textContent).join('\n');
    const blob = new Blob([text], {type: 'text/plain'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'backup-codes-' + USER_ID + '.txt';
    a.click();
    URL.revokeObjectURL(url);
}

function printBackupCodes() {
    window.print();
}
</script>
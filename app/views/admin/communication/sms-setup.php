<?php

/**
 * SMS Gateway Setup (MSG91)
 */
$page_title = $page_title ?? 'SMS Gateway Setup';
$settings = $settings ?? [];
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1"><i class="fas fa-sms me-2 text-warning"></i>SMS Gateway Setup (MSG91)</h1>
                <p class="text-muted">Configure MSG91 SMS gateway for transactional and promotional messaging</p>
            </div>
            <a href="<?= BASE_URL ?>/docs/COMMUNICATION_SETUP_GUIDE.md#-sms-gateway-setup-msg91" target="_blank" class="btn btn-outline-warning text-dark">
                <i class="fas fa-book me-1"></i> View Setup Guide
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title h6 fw-bold mb-0">MSG91 Configuration</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/communication/sms-setup">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Enable SMS Gateway</label>
                            <select name="sms_enabled" class="form-select">
                                <option value="0" <?= ($settings['sms_enabled'] ?? '') === '0' ? 'selected' : '' ?>>Disabled</option>
                                <option value="1" <?= ($settings['sms_enabled'] ?? '') === '1' ? 'selected' : '' ?>>Enabled</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">MSG91 Auth Key</label>
                            <div class="input-group">
                                <input type="password" name="sms_api_key" class="form-control" 
                                       value="<?= htmlspecialchars($settings['sms_api_key'] ?? '') ?>" 
                                       id="smsApiKey" placeholder="Enter your MSG91 Auth Key">
                                <button class="btn btn-outline-secondary" type="button" 
                                        onclick="var p=document.getElementById('smsApiKey');p.type=p.type==='password'?'text':'password'">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Get this from <a href="https://msg91.com" target="_blank">msg91.com</a> → API → Auth Key</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sender ID</label>
                            <input type="text" name="sms_sender_id" class="form-control" 
                                   value="<?= htmlspecialchars($settings['sms_sender_id'] ?? '') ?>" 
                                   placeholder="APSDRM" maxlength="6">
                            <div class="form-text">6-character sender ID (approved by MSG91). Example: APSDRM, DREAMHM</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Default Route</label>
                            <select name="sms_route" class="form-select">
                                <option value="transactional" <?= ($settings['sms_route'] ?? '') === 'transactional' ? 'selected' : '' ?>>Transactional (OTP, alerts, notifications)</option>
                                <option value="promotional" <?= ($settings['sms_route'] ?? '') === 'promotional' ? 'selected' : '' ?>>Promotional (marketing, offers)</option>
                            </select>
                            <div class="form-text">Transactional route has higher deliverability but requires DLT registration</div>
                        </div>

                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>MSG91 Setup Instructions</h6>
                            <ol class="mb-0">
                                <li>Sign up at <a href="https://msg91.com" target="_blank">msg91.com</a></li>
                                <li>Complete KYC and DLT registration</li>
                                <li>Create a Sender ID (6 characters)</li>
                                <li>Get your Auth Key from API section</li>
                                <li>Configure template IDs for transactional messages</li>
                                <li>Test with the form below</li>
                            </ol>
                        </div>

                        <button type="submit" class="btn btn-warning text-dark">
                            <i class="fas fa-save me-1"></i> Save SMS Settings
                        </button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="card-title h6 fw-bold mb-0">Quick Test</h5>
                </div>
                <div class="card-body">
                    <form id="smsTestForm" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Test Phone Number</label>
                            <input type="text" name="to" class="form-control" placeholder="+91XXXXXXXXXX" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Test Message</label>
                            <input type="text" name="message" class="form-control" placeholder="Test SMS from APS Dream Home" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-outline-warning text-dark" id="smsTestBtn">
                                <i class="fas fa-paper-plane me-1"></i> Send Test SMS
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title h6 fw-bold mb-0">Features Enabled</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Transactional SMS (OTP, alerts)</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Promotional SMS (offers, campaigns)</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Automated greetings</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Bulk campaign sending</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Delivery reports</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Communication logs</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h5 class="card-title h6 fw-bold mb-0">Free Tier Limits</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-1"><i class="fas fa-gift text-warning me-2"></i> 300 SMS/month free</li>
                        <li class="mb-1"><i class="fas fa-gift text-warning me-2"></i> 100 SMS/day free</li>
                        <li class="mb-1"><i class="fas fa-info-circle text-info me-2"></i> Sender ID approval required</li>
                        <li class="mb-1"><i class="fas fa-info-circle text-info me-2"></i> DLT registration mandatory</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('smsTestForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('smsTestBtn');
    var originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sending...';
    btn.disabled = true;

    var formData = new FormData();
    formData.append('channel', 'sms');
    formData.append('to', this.querySelector('[name="to"]').value);
    formData.append('message', this.querySelector('[name="message"]').value);
    formData.append('csrf_token', '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>');

    fetch('<?= BASE_URL ?>/admin/communication/test-send', {
        method: 'POST',
        body: formData
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            alert('Success: ' + data.message);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(function() {
        alert('Network error occurred');
    })
    .finally(function() {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});
</script>
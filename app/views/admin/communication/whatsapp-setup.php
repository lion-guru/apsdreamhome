<?php

/**
 * WhatsApp Business Setup
 */
$page_title = $page_title ?? 'WhatsApp Business Setup';
$settings = $settings ?? [];
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1"><i class="fab fa-whatsapp me-2 text-success"></i>WhatsApp Business Setup</h1>
                <p class="text-muted">Configure WhatsApp Business API for automated messaging and customer communication</p>
            </div>
            <a href="<?= BASE_URL ?>/docs/COMMUNICATION_SETUP_GUIDE.md#-whatsapp-business-api-setup" target="_blank" class="btn btn-outline-success">
                <i class="fas fa-book me-1"></i> View Setup Guide
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title h6 fw-bold mb-0">WhatsApp Business API Configuration</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/communication/whatsapp-setup">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Enable WhatsApp Business API</label>
                            <select name="whatsapp_api_enabled" class="form-select">
                                <option value="0" <?= ($settings['whatsapp_api_enabled'] ?? '') === '0' ? 'selected' : '' ?>>Disabled</option>
                                <option value="1" <?= ($settings['whatsapp_api_enabled'] ?? '') === '1' ? 'selected' : '' ?>>Enabled</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">WhatsApp Business Phone Number</label>
                            <input type="text" name="whatsapp_business_phone" class="form-control" 
                                   value="<?= htmlspecialchars($settings['whatsapp_business_phone'] ?? '') ?>" 
                                   placeholder="+91XXXXXXXXXX">
                            <div class="form-text">Enter the phone number registered with WhatsApp Business API in E.164 format</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">API Access Token</label>
                            <div class="input-group">
                                <input type="password" name="whatsapp_api_token" class="form-control" 
                                       value="<?= htmlspecialchars($settings['whatsapp_api_token'] ?? '') ?>" 
                                       id="whatsappToken" placeholder="Enter your WhatsApp API token">
                                <button class="btn btn-outline-secondary" type="button" 
                                        onclick="var p=document.getElementById('whatsappToken');p.type=p.type==='password'?'text':'password'" aria-label="WhatsApp"><i class="fas fa-eye"></i></button>
                            </div>
                            <div class="form-text">Get this from <a href="https://developers.facebook.com/" target="_blank">Meta for Developers</a> → Your App → WhatsApp → API Setup</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Webhook Verified</label>
                            <select name="whatsapp_webhook_verified" class="form-select">
                                <option value="0" <?= ($settings['whatsapp_webhook_verified'] ?? '') === '0' ? 'selected' : '' ?>>Not Verified</option>
                                <option value="1" <?= ($settings['whatsapp_webhook_verified'] ?? '') === '1' ? 'selected' : '' ?>>Verified</option>
                            </select>
                            <div class="form-text">Set to "Verified" after configuring webhook at <code><?= BASE_URL ?>/api/communication/whatsapp-webhook</code></div>
                        </div>

                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Webhook URL</h6>
                            <p class="mb-1">Configure this webhook URL in your Meta App Dashboard:</p>
                            <code class="d-block bg-light p-2 rounded"><?= BASE_URL ?>/api/communication/whatsapp-webhook</code>
                            <p class="mb-0 mt-2">Events to subscribe: <code>messages</code>, <code>message_template_status_update</code></p>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i> Save WhatsApp Settings
                        </button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="card-title h6 fw-bold mb-0">Quick Test</h5>
                </div>
                <div class="card-body">
                    <form id="whatsappTestForm" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Test Phone Number</label>
                            <input type="text" name="to" class="form-control" placeholder="+91XXXXXXXXXX" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Test Message</label>
                            <input type="text" name="message" class="form-control" placeholder="Hello from APS Dream Home!" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-outline-success" id="whatsappTestBtn">
                                <i class="fas fa-paper-plane me-1"></i> Send Test WhatsApp
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title h6 fw-bold mb-0">Setup Guide</h5>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li class="mb-2">Create a <a href="https://business.facebook.com/" target="_blank">Meta Business Account</a></li>
                        <li class="mb-2">Go to <a href="https://developers.facebook.com/" target="_blank">Meta for Developers</a> and create an App</li>
                        <li class="mb-2">Add <strong>WhatsApp</strong> product to your app</li>
                        <li class="mb-2">Get your <strong>Phone Number ID</strong> and <strong>Access Token</strong></li>
                        <li class="mb-2">Configure webhook URL: <code><?= BASE_URL ?>/api/communication/whatsapp-webhook</code></li>
                        <li class="mb-2">Subscribe to <code>messages</code> webhook events</li>
                        <li class="mb-2">Verify webhook and enter credentials above</li>
                    </ol>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h5 class="card-title h6 fw-bold mb-0">Features Enabled</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Auto-reply to incoming messages</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> AI-powered lead qualification</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Birthday/festival greetings</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Bulk campaign sending</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Message templates management</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Communication logs & analytics</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('whatsappTestForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('whatsappTestBtn');
    var originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sending...';
    btn.disabled = true;

    var formData = new FormData();
    formData.append('channel', 'whatsapp');
    formData.append('to', this.querySelector('[name="to"]').value);
    formData.append('message', this.querySelector('[name="message"]').value);
    formData.append('csrf_token', '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>');

    showLoader();
    fetch('<?= BASE_URL ?>/admin/communication/test-send', {
        method: 'POST',
        body: formData
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            showToast('Success: ' + data.message, 'success');
        } else {
            showToast('Error: ' + data.message, 'danger');
        }
    })
    .catch(function() {
        showToast('Network error occurred', 'danger');
    })
    .finally(function() {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
).finally(() => hideLoader());
</script>
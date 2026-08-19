<?php

/**
 * Telegram Bot Setup
 */
$page_title = $page_title ?? 'Telegram Bot Setup';
$settings = $settings ?? [];
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1"><i class="fab fa-telegram me-2 text-primary"></i>Telegram Bot Setup</h1>
                <p class="text-muted">Configure Telegram Bot for automated messaging and customer communication</p>
            </div>
            <a href="<?= BASE_URL ?>/docs/COMMUNICATION_SETUP_GUIDE.md#-telegram-bot-setup" target="_blank" class="btn btn-outline-primary">
                <i class="fas fa-book me-1"></i> View Setup Guide
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title h6 fw-bold mb-0">Telegram Bot Configuration</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/communication/telegram-setup">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Bot Token</label>
                            <div class="input-group">
                                <input type="password" name="telegram_bot_token" class="form-control" 
                                       value="<?= htmlspecialchars($settings['telegram_bot_token'] ?? '') ?>" 
                                       id="telegramToken" placeholder="123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ">
                                <button class="btn btn-outline-secondary" type="button" 
                                        onclick="var p=document.getElementById('telegramToken');p.type=p.type==='password'?'text':'password'">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Get this from <a href="https://t.me/BotFather" target="_blank">@BotFather</a> on Telegram</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Bot Username</label>
                            <input type="text" name="telegram_bot_username" class="form-control" 
                                   value="<?= htmlspecialchars($settings['telegram_bot_username'] ?? '') ?>" 
                                   placeholder="your_bot_username">
                            <div class="form-text">The username you set when creating the bot (without @)</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Webhook URL</label>
                            <div class="input-group">
                                <input type="text" name="telegram_webhook_url" class="form-control" 
                                       value="<?= htmlspecialchars($settings['telegram_webhook_url'] ?? BASE_URL . '/api/communication/telegram-webhook') ?>" 
                                       id="telegramWebhook" readonly>
                                <button class="btn btn-outline-secondary" type="button" 
                                        onclick="navigator.clipboard.writeText(document.getElementById('telegramWebhook').value);this.innerHTML='<i class=\'fas fa-check\'></i> Copied';setTimeout(()=>this.innerHTML='<i class=\'fas fa-copy\'></i> Copy',2000)">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Webhook Verified</label>
                            <select name="telegram_webhook_verified" class="form-select">
                                <option value="0" <?= ($settings['telegram_webhook_verified'] ?? '') === '0' ? 'selected' : '' ?>>Not Verified</option>
                                <option value="1" <?= ($settings['telegram_webhook_verified'] ?? '') === '1' ? 'selected' : '' ?>>Verified</option>
                            </select>
                            <div class="form-text">Set to "Verified" after setting webhook via <code>https://api.telegram.org/bot<TOKEN>/setWebhook?url=...</code></div>
                        </div>

                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Setup Instructions</h6>
                            <ol class="mb-0">
                                <li>Message <a href="https://t.me/BotFather" target="_blank">@BotFather</a> on Telegram</li>
                                <li>Send <code>/newbot</code> and follow instructions</li>
                                <li>Copy the <strong>API Token</strong> and <strong>Bot Username</strong></li>
                                <li>Paste credentials above and save</li>
                                <li>Run this command to set webhook:<br>
                                    <code class="d-block bg-light p-2 rounded mt-1">https://api.telegram.org/bot<YOUR_TOKEN>/setWebhook?url=<?= BASE_URL ?>/api/communication/telegram-webhook</code>
                                </li>
                            </ol>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Telegram Settings
                        </button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="card-title h6 fw-bold mb-0">Quick Test</h5>
                </div>
                <div class="card-body">
                    <form id="telegramTestForm" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Chat ID</label>
                            <input type="text" name="to" class="form-control" placeholder="123456789" required>
                            <div class="form-text">Get this by messaging your bot then visiting: <code>https://api.telegram.org/bot<TOKEN>/getUpdates</code></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Test Message</label>
                            <input type="text" name="message" class="form-control" placeholder="Hello from APS Dream Home!" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-outline-primary" id="telegramTestBtn">
                                <i class="fas fa-paper-plane me-1"></i> Send Test Telegram
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
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Auto-reply to messages</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Lead qualification via chat</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Automated greetings</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Broadcast campaigns</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Inline keyboards & buttons</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Communication logs</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h5 class="card-title h6 fw-bold mb-0">Bot Commands</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">Suggested commands for your bot:</p>
                    <pre class="bg-light p-2 rounded small"><code>/start - Welcome message
/help - Show help
/properties - Browse properties
/contact - Contact sales
/offers - Current offers
/book - Schedule site visit</code></pre>
                    <p class="text-muted small mb-0">Set these in @BotFather using <code>/setcommands</code></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('telegramTestForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('telegramTestBtn');
    var originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sending...';
    btn.disabled = true;

    var formData = new FormData();
    formData.append('channel', 'telegram');
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
});
</script>
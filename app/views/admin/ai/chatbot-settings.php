<?php
$config = $config ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Chatbot Settings</h2>
                <p class="text-muted mb-0">Configure chatbot behavior and appearance</p>
            </div>
            <a href="<?php echo $base; ?>/admin/chatbot" class="btn btn-outline-secondary">Back</a>
        </div>

        <form method="post" action="<?php echo $base; ?>/admin/chatbot/settings">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <div class="row g-4">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white"><h5 class="mb-0">General Settings</h5></div>
                        <div class="card-body aps-cp-card-body">
                            <div class="mb-3">
                                <label class="form-label">Bot Name</label>
                                <input type="text" name="bot_name" class="form-control" value="<?php echo htmlspecialchars($config['bot_name'] ?? 'APS Assistant'); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Welcome Message</label>
                                <textarea name="welcome_message" class="form-control" rows="3"><?php echo htmlspecialchars($config['welcome_message'] ?? 'Welcome to APS Dream Home! How can I help you today?'); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fallback Message</label>
                                <textarea name="fallback_message" class="form-control" rows="2"><?php echo htmlspecialchars($config['fallback_message'] ?? "Sorry, I didn't understand that. Please contact our team."); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Default Language</label>
                                <select name="language" class="form-select">
                                    <option value="en" <?php echo ($config['language'] ?? 'en') === 'en' ? 'selected' : ''; ?>>English</option>
                                    <option value="hi" <?php echo ($config['language'] ?? '') === 'hi' ? 'selected' : ''; ?>>Hindi</option>
                                </select>
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" name="is_active" class="form-check-input" value="1" id="activeCheck" <?php echo !isset($config['is_active']) || $config['is_active'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="activeCheck">Enable Chatbot</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white"><h5 class="mb-0">API Configuration</h5></div>
                        <div class="card-body aps-cp-card-body">
                            <div class="mb-3">
                                <label class="form-label">Provider</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($config['provider'] ?? 'N/A'); ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Webhook URL</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($config['webhook_url'] ?? 'Not configured'); ?>" readonly>
                            </div>
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-2"></i>Save Settings</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>


<?php
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
$settings = $settings ?? [];
$isConfigured = $isConfigured ?? false;
$messageCount = $messageCount ?? 0;
$webhookUrl = rtrim($base, '/') . '/api/communication/whatsapp-webhook';
?>

<div class="container-fluid px-4">
    <h4 class="mb-4"><i class="fab fa-whatsapp me-2 text-success"></i>WhatsApp Settings</h4>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">API Configuration</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form method="post" action="<?php echo $base; ?>/admin/whatsapp/settings">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Phone Number ID <span class="text-danger">*</span></label>
                            <input type="text" name="whatsapp_phone_number_id" class="form-control"
                                   value="<?php echo htmlspecialchars($settings['whatsapp_phone_number_id'] ?? $_ENV['WHATSAPP_PHONE_NUMBER_ID'] ?? ''); ?>"
                                   placeholder="e.g. 123456789012345">
                            <div class="form-text">Meta Business WhatsApp Phone Number ID</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Access Token <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="whatsapp_access_token" class="form-control" id="waToken"
                                       value="<?php echo htmlspecialchars($settings['whatsapp_access_token'] ?? ''); ?>"
                                       placeholder="Long-lived access token">
                                <button class="btn btn-outline-secondary" type="button" onclick="const t=document.getElementById('waToken'); t.type=t.type==='password'?'text':'password'">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Paste your permanent/long-lived Meta access token</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Business Account ID</label>
                            <input type="text" name="whatsapp_business_account_id" class="form-control"
                                   value="<?php echo htmlspecialchars($settings['whatsapp_business_account_id'] ?? $_ENV['WHATSAPP_BUSINESS_ACCOUNT_ID'] ?? ''); ?>"
                                   placeholder="e.g. 123456789012345">
                            <div class="form-text">Meta WhatsApp Business Account ID (for template sync)</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Webhook Verify Token</label>
                            <input type="text" name="whatsapp_webhook_verify_token" class="form-control"
                                   value="<?php echo htmlspecialchars($settings['whatsapp_webhook_verify_token'] ?? $_ENV['WHATSAPP_WEBHOOK_VERIFY_TOKEN'] ?? 'apsdreamhome_webhook_2026'); ?>">
                            <div class="form-text">Set this same token in Meta App Dashboard webhook settings</div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Settings
                        </button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-paper-plane me-1"></i> Test Message</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form method="post" action="<?php echo $base; ?>/admin/whatsapp/test">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="test_phone" class="form-control" placeholder="+919XXXXXXXXX" required>
                            <div class="form-text">Include country code (e.g. +919XXXXXXXXX)</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="test_message" class="form-control" rows="2" placeholder="Leave blank to use default test message"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fab fa-whatsapp me-1"></i> Send Test Message
                        </button>
                    </form>
                    <?php if (!$isConfigured): ?>
                        <div class="alert alert-info mt-3 mb-0 py-2">
                            <i class="fas fa-info-circle me-1"></i> No API credentials configured. Test messages will be <strong>logged to the database</strong> but not actually sent.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-1"></i> Status</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <?php if ($isConfigured): ?>
                                <span class="badge bg-success fs-6"><i class="fas fa-check-circle me-1"></i> Connected</span>
                            <?php else: ?>
                                <span class="badge bg-secondary fs-6"><i class="fas fa-times-circle me-1"></i> Disconnected</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="table-responsive"><table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Messages Logged:</td>
                            <td class="text-end fw-bold"><?php echo $messageCount; ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">API Version:</td>
                            <td class="text-end"><?php echo htmlspecialchars($_ENV['WHATSAPP_API_VERSION'] ?? 'v18.0'); ?></td>
                        </tr>
                    </table></div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-link me-1"></i> Webhook URL</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted small mb-2">Set this URL in your Meta App Dashboard &rarr; WhatsApp &rarr; Configuration &rarr; Webhook:</p>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($webhookUrl); ?>" readonly onclick="this.select()">
                        <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(this.previousElementSibling.value);this.textContent='Copied!';setTimeout(()=>this.textContent='Copy',2000)">
                            Copy
                        </button>
                    </div>
                    <div class="mt-2 small text-muted">
                        <strong>Verify Token:</strong> <code><?php echo htmlspecialchars($settings['whatsapp_webhook_verify_token'] ?? $_ENV['WHATSAPP_WEBHOOK_VERIFY_TOKEN'] ?? 'apsdreamhome_webhook_2026'); ?></code>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-list me-1"></i> Quick Links</h5>
                </div>
                <div class="card-body py-2">
                    <a href="<?php echo $base; ?>/admin/whatsapp/templates" class="btn btn-outline-primary btn-sm w-100 mb-2">
                        <i class="fas fa-template me-1"></i> Manage Templates
                    </a>
                    <a href="https://developers.facebook.com/docs/whatsapp/cloud-api" target="_blank" class="btn btn-outline-info btn-sm w-100 mb-2">
                        <i class="fas fa-external-link-alt me-1"></i> Meta Cloud API Docs
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

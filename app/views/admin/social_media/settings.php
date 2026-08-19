<?php
$page_title = $page_title ?? 'Social Media Settings';
$configs = $configs ?? [];
$csrf = $_SESSION['csrf_token'] ?? '';
$get = fn($k) => htmlspecialchars($configs[$k] ?? '');
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-key me-2"></i>Social Media API Settings</h2>
    <a href="<?= BASE_URL ?>/admin/social-media" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Accounts</a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Platform API Credentials</h5></div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/admin/social-media/settings/save">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

                    <h6 class="text-primary"><i class="fab fa-facebook me-1"></i> Facebook / Instagram</h6>
                    <div class="mb-3">
                        <label class="form-label">App ID</label>
                        <input type="text" name="fb_app_id" class="form-control" value="<?= $get('fb_app_id') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">App Secret</label>
                        <input type="password" name="fb_app_secret" class="form-control" value="<?= $get('fb_app_secret') ?>" placeholder="Leave blank to keep existing">
                    </div>

                    <hr>
                    <h6 class="text-info"><i class="fab fa-linkedin me-1"></i> LinkedIn</h6>
                    <div class="mb-3">
                        <label class="form-label">Client ID</label>
                        <input type="text" name="li_client_id" class="form-control" value="<?= $get('li_client_id') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Client Secret</label>
                        <input type="password" name="li_client_secret" class="form-control" value="<?= $get('li_client_secret') ?>" placeholder="Leave blank to keep existing">
                    </div>

                    <hr>
                    <h6 class="text-success"><i class="fab fa-whatsapp me-1"></i> WhatsApp Business</h6>
                    <div class="mb-3">
                        <label class="form-label">Phone Number ID</label>
                        <input type="text" name="wa_phone_id" class="form-control" value="<?= $get('wa_phone_id') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Access Token</label>
                        <input type="password" name="wa_token" class="form-control" value="<?= $get('wa_token') ?>" placeholder="Leave blank to keep existing">
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Settings</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark"><i class="fas fa-shield-alt me-1"></i> Security Note</div>
            <div class="card-body small">
                <p>API secrets are stored in the <code>site_content</code> table. For production, consider moving these to environment variables or an encrypted vault.</p>
                <p class="mb-0">These credentials are used by the lead sync engine when polling Facebook Lead Gen Forms, LinkedIn, and WhatsApp Business APIs.</p>
            </div>
        </div>
    </div>
</div>

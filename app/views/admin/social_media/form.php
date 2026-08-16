<?php
$page_title = $page_title ?? 'Social Account';
$platforms = $platforms ?? [];
$account = $account ?? null;
$csrf = $_SESSION['csrf_token'] ?? '';
$isEdit = !empty($account);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-plug me-2"></i><?= $isEdit ? 'Edit' : 'Connect' ?> Social Account</h2>
    <a href="<?= BASE_URL ?>/admin/social-media" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?= $isEdit ? "/admin/social-media/update/{$account['id']}" : '/admin/social-media/store' ?>">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

                    <div class="mb-3">
                        <label class="form-label">Platform</label>
                        <select name="platform" class="form-select" <?= $isEdit ? 'disabled' : 'required' ?>>
                            <option value="">Select Platform</option>
                            <?php foreach ($platforms as $key => $label): ?>
                                <option value="<?= $key ?>" <?= $isEdit && $account['platform'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($isEdit): ?><input type="hidden" name="platform" value="<?= $account['platform'] ?>"><?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Account Name</label>
                        <input type="text" name="account_name" class="form-control" value="<?= $isEdit ? htmlspecialchars($account['account_name'] ?? '') : '' ?>" placeholder="e.g. APS Dream Home Official" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Platform Account / Page ID</label>
                        <input type="text" name="account_id" class="form-control" value="<?= $isEdit ? htmlspecialchars($account['account_id'] ?? '') : '' ?>" placeholder="Facebook Page ID or Business Manager ID" <?= $isEdit ? 'disabled' : 'required' ?>>
                        <?php if ($isEdit): ?><input type="hidden" name="account_id" value="<?= htmlspecialchars($account['account_id'] ?? '') ?>"><?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Account Type</label>
                        <select name="account_type" class="form-select">
                            <option value="personal" <?= $isEdit && ($account['account_type'] ?? '') === 'personal' ? 'selected' : '' ?>>Personal</option>
                            <option value="business_page" <?= $isEdit && ($account['account_type'] ?? 'business_page') === 'business_page' ? 'selected' : '' ?>>Business Page</option>
                            <option value="group" <?= $isEdit && ($account['account_type'] ?? '') === 'group' ? 'selected' : '' ?>>Group</option>
                            <option value="ads_account" <?= $isEdit && ($account['account_type'] ?? '') === 'ads_account' ? 'selected' : '' ?>>Ads Account</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Access Token <small class="text-muted">(long-lived)</small></label>
                        <textarea name="access_token" class="form-control" rows="3" placeholder="Paste the platform access token"><?= $isEdit ? htmlspecialchars($account['access_token'] ?? '') : '' ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Refresh Token</label>
                            <input type="text" name="refresh_token" class="form-control" value="<?= $isEdit ? htmlspecialchars($account['refresh_token'] ?? '') : '' ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Token Expires At</label>
                            <input type="datetime-local" name="token_expires_at" class="form-control" value="<?= $isEdit && !empty($account['token_expires_at']) ? date('Y-m-d\TH:i', strtotime($account['token_expires_at'])) : '' ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="connected" <?= $isEdit && ($account['status'] ?? 'connected') === 'connected' ? 'selected' : '' ?>>Connected</option>
                            <option value="expired" <?= $isEdit && ($account['status'] ?? '') === 'expired' ? 'selected' : '' ?>>Expired</option>
                            <option value="revoked" <?= $isEdit && ($account['status'] ?? '') === 'revoked' ? 'selected' : '' ?>>Revoked</option>
                            <option value="error" <?= $isEdit && ($account['status'] ?? '') === 'error' ? 'selected' : '' ?>>Error</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> <?= $isEdit ? 'Update' : 'Connect' ?> Account</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-info">
            <div class="card-header bg-info text-white"><i class="fas fa-info-circle me-1"></i> Setup Help</div>
            <div class="card-body small">
                <p><strong>Facebook / Instagram:</strong> Use Graph API long-lived tokens from your Facebook App. Add App ID & Secret in <a href="<?= BASE_URL ?>/admin/social-media/settings">Settings</a>.</p>
                <p><strong>LinkedIn:</strong> Requires client ID & secret for Lead Gen Forms.</p>
                <p><strong>WhatsApp Business:</strong> Configure Phone ID & token from Meta Business Manager.</p>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'Feature Settings') ?></h1>
    </div>
    <form method="post" action="<?= $base ?? BASE_URL ?>/features/settings/update">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom"><h5 class="mb-0">General</h5></div>
                    <div class="card-body aps-cp-card-body">
                        <div class="mb-3">
                            <label class="form-label">Default Feature Status</label>
                            <select name="config[default_status]" class="form-select">
                                <option value="active" <?= ($config['default_status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= ($config['default_status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="beta" <?= ($config['default_status'] ?? '') === 'beta' ? 'selected' : '' ?>>Beta</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Max Features Per Page</label>
                            <input type="number" name="config[per_page]" class="form-control" value="<?= (int)($config['per_page'] ?? 20) ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom"><h5 class="mb-0">Notifications</h5></div>
                    <div class="card-body aps-cp-card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="config[notify_on_change]" value="1" <?= ($config['notify_on_change'] ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label">Notify on feature status change</label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="config[auto_deprecate]" value="1" <?= ($config['auto_deprecate'] ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label">Auto-deprecate unused features (90 days)</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Settings</button>
        </div>
    </form>
</div>
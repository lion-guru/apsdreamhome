<?php $extraHead = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">'; ?>
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit API Key</h5>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>/admin/api-keys/update/<?= $api_key['id'] ?>" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Service Name *</label>
                                <select name="service_name" class="form-select" required>
                                    <?php foreach ($providers as $p): ?>
                                        <option value="<?= $p ?>" <?= ($api_key['service_name'] == $p) ? 'selected' : '' ?>><?= $p ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Key Name *</label>
                                <input type="text" name="key_name" class="form-control" value="<?= htmlspecialchars(api_key['key_name'] ?? '') ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">API Key Value *</label>
                            <input type="text" name="key_value" class="form-control" value="<?= htmlspecialchars(api_key['key_value'] ?? '') ?>" required>
                            <small class="text-muted">Current value shown. Enter new value to update.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Key Type</label>
                                <select name="key_type" class="form-select">
                                    <option value="api_key" <?= ($api_key['key_type'] == 'api_key') ? 'selected' : '' ?>>API Key</option>
                                    <option value="token" <?= ($api_key['key_type'] == 'token') ? 'selected' : '' ?>>Token</option>
                                    <option value="password" <?= ($api_key['key_type'] == 'password') ? 'selected' : '' ?>>Password</option>
                                    <option value="certificate" <?= ($api_key['key_type'] == 'certificate') ? 'selected' : '' ?>>Certificate</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?= $api_key['is_active'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($api_key['description'] ?? '') ?></textarea>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?= BASE_URL ?>/admin/api-keys" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update API Key
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

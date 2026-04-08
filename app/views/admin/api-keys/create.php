<?php $extraHead = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">'; ?>
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Add New API Key</h5>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>/admin/api-keys/store" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Service Name *</label>
                                <select name="service_name" class="form-select" required>
                                    <option value="">Select Service</option>
                                    <?php foreach ($providers as $p): ?>
                                        <option value="<?= $p ?>"><?= $p ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Key Name *</label>
                                <input type="text" name="key_name" class="form-control" placeholder="e.g. OPENAI_API_KEY" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">API Key Value *</label>
                            <input type="text" name="key_value" class="form-control" placeholder="sk-..." required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Key Type</label>
                                <select name="key_type" class="form-select">
                                    <option value="api_key">API Key</option>
                                    <option value="token">Token</option>
                                    <option value="password">Password</option>
                                    <option value="certificate">Certificate</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Notes about this API key..."></textarea>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?= BASE_URL ?>/admin/api-keys" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save API Key
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

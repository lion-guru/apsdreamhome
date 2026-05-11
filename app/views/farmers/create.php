<?php $pageTitle = 'Add Farmer'; ?>
<?php $errors = $errors ?? []; $old = $old ?? []; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>farmers/dashboard">Farmers</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>farmers/list">List</a></li><li class="breadcrumb-item active">Add Farmer</li></ol></nav>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Register New Farmer</h5></div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-1"></i><?php foreach ($errors as $e): ?><p class="mb-0"><?= htmlspecialchars($e) ?></p><?php endforeach; ?></div>
                    <?php endif; ?>
                    <form method="post" action="<?= BASE_URL ?>farmers/create">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Farmer Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>" required placeholder="Full name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($old['phone'] ?? '') ?>" required placeholder="+91 9XXXXXXXX">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Village / Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="village" value="<?= htmlspecialchars($old['village'] ?? '') ?>" required placeholder="Village name">
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Land Owned (acres) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="land_acres" value="<?= htmlspecialchars($old['land_acres'] ?? '') ?>" step="0.01" min="0" required placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Primary Crop</label>
                                <input type="text" class="form-control" name="primary_crop" value="<?= htmlspecialchars($old['primary_crop'] ?? '') ?>" placeholder="e.g. Wheat, Rice">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="active" <?= ($old['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= ($old['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Additional Notes</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Any additional information about the farmer"><?= htmlspecialchars($old['notes'] ?? '') ?></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Farmer</button>
                            <a href="<?= BASE_URL ?>farmers/list" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

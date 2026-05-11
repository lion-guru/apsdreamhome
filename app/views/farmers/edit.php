<?php $pageTitle = 'Edit Farmer'; ?>
<?php $farmer = $farmer ?? null; $errors = $errors ?? []; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>farmers/dashboard">Farmers</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>farmers/list">List</a></li><li class="breadcrumb-item active">Edit Farmer</li></ol></nav>
    <?php if (!$farmer): ?>
    <div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i><h6 class="text-muted">Farmer not found</h6><a href="<?= BASE_URL ?>farmers/list" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to List</a></div></div>
    <?php else: ?>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Farmer: <?= htmlspecialchars($farmer['name'] ?? '') ?></h5></div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger"><?php foreach ($errors as $e): ?><p class="mb-0"><?= htmlspecialchars($e) ?></p><?php endforeach; ?></div>
                    <?php endif; ?>
                    <form method="post" action="<?= BASE_URL ?>farmers/edit/<?= $farmer['id'] ?? 0 ?>">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Farmer Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($farmer['name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($farmer['phone'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Village / Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="village" value="<?= htmlspecialchars($farmer['village'] ?? '') ?>" required>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Land Owned (acres) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="land_acres" value="<?= htmlspecialchars($farmer['land_acres'] ?? 0) ?>" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Primary Crop</label>
                                <input type="text" class="form-control" name="primary_crop" value="<?= htmlspecialchars($farmer['primary_crop'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="active" <?= ($farmer['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= ($farmer['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Additional Notes</label>
                            <textarea class="form-control" name="notes" rows="3"><?= htmlspecialchars($farmer['notes'] ?? '') ?></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Farmer</button>
                            <a href="<?= BASE_URL ?>farmers/show/<?= $farmer['id'] ?? 0 ?>" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

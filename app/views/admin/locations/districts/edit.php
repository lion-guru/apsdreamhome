<?php $pageTitle = 'Edit District'; ?>
<?php $district = $district ?? null; $states = $states ?? []; $errors = $errors ?? []; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/locations">Locations</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/locations/districts">Districts</a></li><li class="breadcrumb-item active">Edit District</li></ol></nav>
    <?php if (!$district): ?>
    <div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i><h6 class="text-muted">District not found</h6><a href="<?= BASE_URL ?>admin/locations/districts" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Districts</a></div></div>
    <?php else: ?>
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit District: <?= htmlspecialchars($district['name'] ?? '') ?></h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger"><?php foreach ($errors as $e): ?><p class="mb-0"><?= htmlspecialchars($e ?? '') ?></p><?php endforeach; ?></div>
                    <?php endif; ?>
                    <form method="post" action="<?= BASE_URL ?>admin/locations/districts/edit/<?= $district['id'] ?? 0 ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">District Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($district['name'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">State <span class="text-danger">*</span></label>
                            <select class="form-select" name="state_id" required>
                                <option value="">Select State...</option>
                                <?php foreach ($states as $s): ?>
                                <option value="<?= $s['id'] ?? 0 ?>" <?= ($district['state_id'] ?? '') == ($s['id'] ?? '') ? 'selected' : '' ?>><?= htmlspecialchars($s['name'] ?? '-') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="active" <?= ($district['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= ($district['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update District</button>
                            <a href="<?= BASE_URL ?>admin/locations/districts" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'Edit Feature') ?></h1>
        <a href="<?= $base ?? BASE_URL ?>/features" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body aps-cp-card-body">
            <form method="post" action="<?= $base ?? BASE_URL ?>/features/update">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="id" value="<?= (int)($feature['id'] ?? 0) ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Feature Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($feature['name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Version</label>
                        <input type="text" name="version" class="form-control" value="<?= htmlspecialchars($feature['version'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($feature['description'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= ($feature['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($feature['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            <option value="beta" <?= ($feature['status'] ?? '') === 'beta' ? 'selected' : '' ?>>Beta</option>
                            <option value="deprecated" <?= ($feature['status'] ?? '') === 'deprecated' ? 'selected' : '' ?>>Deprecated</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update Feature</button>
                </div>
            </form>
        </div>
    </div>
</div>
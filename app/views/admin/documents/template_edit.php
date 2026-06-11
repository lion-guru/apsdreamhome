<?php $page_title = $page_title ?? 'Edit Template'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-edit me-2"></i>Edit Template</h1>
        <a href="<?= BASE_URL ?>/admin/documents/templates" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <?php if (!empty($template)): ?>
        <div class="card shadow-sm">
            <div class="card-body aps-cp-card-body">
                <form method="POST" action="<?= BASE_URL ?>/admin/documents/templates/update/<?= (int)$template['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Template Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($template['name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <?php foreach (['document','agreement','letter','certificate','report'] as $opt): ?>
                                    <option value="<?= $opt ?>" <?= ($template['type'] ?? '') === $opt ? 'selected' : '' ?>><?= ucfirst($opt) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">-- None --</option>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= (int)$cat['id'] ?>" <?= ((int)($template['category_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($template['description'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content <span class="text-danger">*</span></label>
                        <textarea name="content" class="form-control" rows="12" required><?= htmlspecialchars($template['content'] ?? '') ?></textarea>
                        <small class="text-muted">Use {{placeholders}} for dynamic values (e.g. {{customer_name}}, {{date}})</small>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" value="1" id="isActive" <?= ($template['is_active'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="isActive">Active</label>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Template</button>
                    <a href="<?= BASE_URL ?>/admin/documents/templates" class="btn btn-outline-secondary ms-2">Cancel</a>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Template not found</h5>
            <a href="<?= BASE_URL ?>/admin/documents/templates" class="btn btn-primary mt-3">Back to Templates</a>
        </div>
    <?php endif; ?>
</div>

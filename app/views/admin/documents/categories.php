<?php $page_title = $page_title ?? 'Document Categories'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-tags me-2"></i>Document Categories</h1>
        <a href="<?= BASE_URL ?>/admin/documents" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <?php if (!empty($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_type'] ?? 'info') ?> alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_message'] ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php $_SESSION['flash_message'] = ''; $_SESSION['flash_type'] = ''; endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header aps-cp-card-header"><i class="fas fa-plus me-2"></i>Add Category</div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/documents/categories/store">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control" placeholder="Auto-generated if empty">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Parent Category</label>
                            <select name="parent_id" class="form-select">
                                <option value="">-- None (Top Level) --</option>
                                <?php if (!empty($parents)): ?>
                                    <?php foreach ($parents as $p): ?>
                                        <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name'] ?? '') ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Create Category</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header aps-cp-card-header"><i class="fas fa-list me-2"></i>All Categories</div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($categories)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr><th>Name</th><th>Slug</th><th>Parent</th><th>Status</th><th>Actions</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $cat): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($cat['name'] ?? '') ?></td>
                                            <td><code><?= htmlspecialchars($cat['slug'] ?? '') ?></code></td>
                                            <td><?= htmlspecialchars($cat['parent_name'] ?? '-') ?></td>
                                            <td><span class="badge bg-<?= ($cat['is_active'] ?? 0) ? 'success' : 'secondary' ?>"><?= ($cat['is_active'] ?? 0) ? 'Active' : 'Inactive' ?></span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#editCat<?= (int)$cat['id'] ?>"><i class="fas fa-edit"></i></button>
                                                <form method="POST" action="<?= BASE_URL ?>/admin/documents/categories/delete/<?= (int)$cat['id'] ?>" class="style-71727" data-aps-confirm="Delete this category?">
                                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Delete"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                        <tr class="collapse" id="editCat<?= (int)$cat['id'] ?>">
                                            <td colspan="5" class="bg-light">
                                                <form method="POST" action="<?= BASE_URL ?>/admin/documents/categories/update/<?= (int)$cat['id'] ?>" class="row g-2">
                                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    <div class="col-md-3"><input type="text" name="name" class="form-control form-control-sm" value="<?= htmlspecialchars($cat['name'] ?? '') ?>" required></div>
                                                    <div class="col-md-2"><input type="text" name="slug" class="form-control form-control-sm" value="<?= htmlspecialchars($cat['slug'] ?? '') ?>"></div>
                                                    <div class="col-md-3">
                                                        <select name="parent_id" class="form-select form-select-sm">
                                                            <option value="">None</option>
                                                            <?php foreach ($parents as $p): ?>
                                                                <option value="<?= (int)$p['id'] ?>" <?= ((int)$cat['parent_id'] === (int)$p['id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['name'] ?? '') ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-check mt-2">
                                                            <input type="checkbox" name="is_active" class="form-check-input" value="1" id="activeCat<?= (int)$cat['id'] ?>" <?= ($cat['is_active'] ?? 0) ? 'checked' : '' ?>>
                                                            <label class="form-check-label" for="activeCat<?= (int)$cat['id'] ?>">Active</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2"><button type="submit" class="btn btn-sm btn-success w-100"><i class="fas fa-save"></i> Update</button></div>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-3">No categories yet. Create one to organize your documents.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

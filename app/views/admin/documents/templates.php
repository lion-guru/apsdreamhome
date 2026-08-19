<?php $page_title = $page_title ?? 'Document Templates'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-file-code me-2"></i>Document Templates</h1>
        <a href="<?= BASE_URL ?>/admin/documents" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <?php if (!empty($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_type'] ?? 'info') ?> alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_message'] ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php $_SESSION['flash_message'] = ''; $_SESSION['flash_type'] = ''; endif; ?>

    <div class="row">
        <div class="col-md-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header aps-cp-card-header"><i class="fas fa-plus me-2"></i>Create Template</div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/documents/templates/store">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Template Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select">
                                    <option value="document">Document</option>
                                    <option value="agreement">Agreement</option>
                                    <option value="letter">Letter</option>
                                    <option value="certificate">Certificate</option>
                                    <option value="report">Report</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-select">
                                    <option value="">-- None --</option>
                                    <?php if (!empty($categories)): ?>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name'] ?? '') ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content <span class="text-danger">*</span></label>
                            <textarea name="content" class="form-control" rows="8" required placeholder="Use {{placeholders}} for dynamic values"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Create Template</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header aps-cp-card-header"><i class="fas fa-list me-2"></i>All Templates</div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($templates)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr><th>Name</th><th>Type</th><th>Category</th><th>Status</th><th>Actions</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($templates as $t): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($t['name'] ?? '') ?></td>
                                            <td><span class="badge bg-info"><?= htmlspecialchars($t['type'] ?? 'document') ?></span></td>
                                            <td><?= htmlspecialchars($t['category_name'] ?? '-') ?></td>
                                            <td><span class="badge bg-<?= ($t['is_active'] ?? 0) ? 'success' : 'secondary' ?>"><?= ($t['is_active'] ?? 0) ? 'Active' : 'Inactive' ?></span></td>
                                            <td>
                                                <a href="<?= BASE_URL ?>/admin/documents/templates/edit/<?= (int)$t['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                                <form method="POST" action="<?= BASE_URL ?>/admin/documents/templates/delete/<?= (int)$t['id'] ?>" class="style-71727" onsubmit="return confirm('Delete this template?');">
                                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Delete"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-3">No templates yet. Create a template for reusable document generation.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

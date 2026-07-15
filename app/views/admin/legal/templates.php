<?php
$templates = $templates ?? [];
$categories = $categories ?? [];
$merge_fields = $merge_fields ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-file me-2 text-primary"></i>Document Templates</h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/legal/ai-composer" class="btn btn-outline-info btn-sm me-1"><i class="fas fa-magic me-1"></i>AI Composer</a>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createTplModal"><i class="fas fa-plus me-1"></i>New Template</button>
        </div>
    </div>

    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">Category</label>
                    <select name="category_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($_GET['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All</option>
                        <option value="active" <?= ($_GET['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="draft" <?= ($_GET['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="archived" <?= ($_GET['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search templates..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                </div>
                <div class="col-md-1">
                    <button class="btn btn-sm btn-outline-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($templates)): ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-file fa-3x mb-3"></i>
            <p>No templates found. <a href="#" data-bs-toggle="modal" data-bs-target="#createTplModal">Create one</a>.</p>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($templates as $t): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="aps-cp-card h-100">
                        <div class="aps-cp-card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0"><i class="<?= htmlspecialchars($t['category_icon'] ?? 'fas fa-file') ?> me-2 text-primary"></i><?= htmlspecialchars($t['name'] ?? '') ?></h6>
                                <span class="badge bg-<?= $t['status'] === 'active' ? 'success' : ($t['status'] === 'draft' ? 'secondary' : 'warning') ?>"><?= $t['status'] ?></span>
                            </div>
                            <small class="text-muted d-block mb-2"><?= htmlspecialchars($t['category_name'] ?? 'Uncategorized') ?> | v<?= (int)($t['version'] ?? 1) ?> | Lang: <?= strtoupper($t['language'] ?? 'EN') ?></small>
                            <p class="small text-muted mb-2"><?= htmlspecialchars(substr($t['description'] ?? '', 0, 120)) ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted"><?= (int)($t['usage_count'] ?? 0) ?> uses</small>
                                <div class="d-flex gap-1">
                                    <a href="<?= BASE_URL ?>/admin/legal/templates/<?= $t['id'] ?>/edit" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                    <?php if ($t['status'] !== 'archived'): ?>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/legal/templates/<?= $t['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Archive this template?')">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-archive"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createTplModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/legal/templates/create">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="modal-header"><h5 class="modal-title">New Template</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Name</label><input type="text" name="name" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Category</label><select name="category_id" class="form-select"><option value="">None</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name'] ?? '') ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-6"><label class="form-label">Status</label><select name="status" class="form-select"><option value="draft">Draft</option><option value="active">Active</option></select></div>
                        <div class="col-md-6"><label class="form-label">Language</label><select name="language" class="form-select"><option value="en">English</option><option value="hi">Hindi</option></select></div>
                        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                        <div class="col-12">
                            <label class="form-label">Content (HTML with merge fields like {{customer_name}})</label>
                            <small class="text-muted d-block mb-1">Available fields: <?php $all = []; foreach ($merge_fields as $group => $fields) { foreach ($fields as $key => $label) { $all[] = $key; } } echo implode(', ', $all); ?></small>
                            <textarea name="content" class="form-control font-monospace" rows="12" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

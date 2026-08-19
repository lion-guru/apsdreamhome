<?php
$categories = $categories ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-tags me-2 text-primary"></i>Document Categories</h2>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createCatModal"><i class="fas fa-plus me-1"></i>New Category</button>
    </div>

    <div class="row g-3">
        <?php if (empty($categories)): ?>
            <div class="col-12"><div class="text-center text-muted py-5"><i class="fas fa-folder-open fa-3x mb-3"></i><p>No categories found</p></div></div>
        <?php else: ?>
            <?php foreach ($categories as $cat): ?>
                <div class="col-md-4 col-lg-3">
                    <div class="aps-cp-card h-100">
                        <div class="aps-cp-card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><i class="<?= htmlspecialchars($cat['icon'] ?? 'fas fa-folder') ?> me-2 text-primary"></i><?= htmlspecialchars($cat['name'] ?? '') ?></h6>
                                    <small class="text-muted d-block mb-2"><?= htmlspecialchars(substr($cat['description'] ?? '', 0, 100)) ?></small>
                                    <span class="badge bg-info"><?= (int)($cat['template_count'] ?? 0) ?> templates</span>
                                </div>
                            </div>
                            <div class="mt-3 pt-2 border-top d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCatModal<?= $cat['id'] ?>"><i class="fas fa-edit"></i></button>
                                <form method="POST" action="<?= BASE_URL ?>/admin/legal/categories/<?= $cat['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this category?')">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <button class="btn btn-sm btn-outline-danger" aria-label="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Modal -->
                <div class="modal fade" id="editCatModal<?= $cat['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="<?= BASE_URL ?>/admin/legal/categories/<?= $cat['id'] ?>/update">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <div class="modal-header"><h5 class="modal-title">Edit Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body">
                                    <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($cat['name'] ?? '') ?>" required></div>
                                    <div class="mb-3"><label class="form-label">Slug</label><input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($cat['slug'] ?? '') ?>"></div>
                                    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($cat['description'] ?? '') ?></textarea></div>
                                    <div class="mb-3"><label class="form-label">Icon (FontAwesome)</label><input type="text" name="icon" class="form-control" value="<?= htmlspecialchars($cat['icon'] ?? 'fas fa-file-contract') ?>"></div>
                                    <div class="mb-3"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="<?= (int)($cat['sort_order'] ?? 0) ?>"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createCatModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/legal/categories/create">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="modal-header"><h5 class="modal-title">New Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Slug (auto-generated from name)</label><input type="text" name="slug" class="form-control" placeholder="leave blank to auto-generate"></div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                    <div class="mb-3"><label class="form-label">Icon (FontAwesome class)</label><input type="text" name="icon" class="form-control" value="fas fa-file-contract"></div>
                    <div class="mb-3"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="0"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

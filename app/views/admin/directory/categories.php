<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-tags me-2"></i>Directory Categories</h1>
        <a href="<?= BASE_URL ?>/admin/directory" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Directory</a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0">Add/Edit Category</h5></div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="mb-3">
                            <label class="form-label">Name *</label>
                            <input aria-label="Name" type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Slug *</label>
                            <input aria-label="Slug" type="text" name="slug" class="form-control" required placeholder="e.g. plumbing">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea aria-label="Description" name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Icon (FontAwesome class)</label>
                            <input aria-label="Icon" type="text" name="icon" class="form-control" value="fas fa-building" placeholder="fas fa-wrench">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sort Order</label>
                            <input aria-label="Sort order" type="number" name="sort_order" class="form-control" value="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-check-label"><input aria-label="Is active" type="checkbox" name="is_active" value="1" checked> Active</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Save Category</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-dark">
                                <tr><th scope="col">ID</th><th scope="col">Icon</th><th scope="col">Name</th><th scope="col">Slug</th><th scope="col">Listings</th><th scope="col">Active</th><th scope="col">Order</th><th scope="col">Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php if (empty($categories ?? [])): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No categories found</h5>
                                        <p class="text-muted mb-3">Create your first directory category using the form on the left.</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($categories as $c): ?>
                                <tr>
                                    <td><?= $c['id'] ?></td>
                                    <td><i class="<?= htmlspecialchars($c['icon'] ?? 'fas fa-building') ?>"></i></td>
                                    <td><?= htmlspecialchars($c['name'] ?? '') ?></td>
                                    <td><code><?= htmlspecialchars($c['slug'] ?? '') ?></code></td>
                                    <td><?= $c['listing_count'] ?? 0 ?></td>
                                    <td><span class="badge bg-<?= $c['is_active'] ? 'success' : 'danger' ?>"><?= $c['is_active'] ? 'Yes' : 'No' ?></span></td>
                                    <td><?= $c['sort_order'] ?></td>
                                    <td>
                                        <form action="<?= BASE_URL ?>/admin/directory/delete-category/<?= $c['id'] ?>" method="POST" class="d-inline" data-aps-confirm="Delete category?"><?= \App\Helpers\SecurityHelper::csrfField() ?><button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Delete"><i class="fas fa-trash"></i></button></form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

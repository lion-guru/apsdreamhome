<?php
/**
 * Plot Categories Management View
 * Data: $page_title
 */
$page_title = $page_title ?? 'Plot Categories';
$categories = $categories ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-cog me-2"></i><?= htmlspecialchars($page_title ?? '') ?></h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
            <i class="fas fa-plus me-1"></i> Add Category
        </button>
    </div>

    <div class="card aps-cp-card">
        <div class="card-header aps-cp-card-header d-flex justify-content-between align-items-center">
            <i class="fas fa-list me-2"></i>Categories
            <span class="badge bg-primary"><?= count($categories) ?></span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($categories)): ?>
            <div class="text-center py-5">
                <i class="fas fa-tags fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No Categories Yet</h4>
                <p class="text-muted">Create your first plot category.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                    <i class="fas fa-plus me-1"></i> Add Category
                </button>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Plots Count</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td>
                                <div class="fw-medium"><?= htmlspecialchars($cat['name'] ?? '') ?></div>
                                <small class="text-muted">ID: <?= $cat['id'] ?? '' ?></small>
                            </td>
                            <td><code><?= htmlspecialchars($cat['code'] ?? '') ?></code></td>
                            <td><?= htmlspecialchars($cat['description'] ?? '') ?></td>
                            <td>
                                <span class="aps-cp-badge badge bg-<?= ($cat['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($cat['status'] ?? 'inactive') ?>
                                </span>
                            </td>
                            <td><?= number_format($cat['plots_count'] ?? 0) ?></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="editCategory(<?= htmlspecialchars(json_encode($cat)) ?>)" title="Edit"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-outline-danger" onclick="deleteCategory(<?= $cat['id'] ?? 0 ?>)" title="Delete"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Create/Edit Category Modal -->
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/plots/categories/store" id="categoryForm">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add Plot Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="id" id="category_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="category_name" required placeholder="e.g., Residential Plots">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="code" id="category_code" required placeholder="e.g., RES_PLOT">
                        <div class="form-text">Unique code for system reference (uppercase, underscores)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="category_description" rows="3" placeholder="Category description..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select class="form-select" name="status" id="category_status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCategory(cat) {
    document.getElementById('category_id').value = cat.id || '';
    document.getElementById('category_name').value = cat.name || '';
    document.getElementById('category_code').value = cat.code || '';
    document.getElementById('category_description').value = cat.description || '';
    document.getElementById('category_status').value = cat.status || 'active';
    
    document.querySelector('#createCategoryModal .modal-title').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Plot Category';
    document.querySelector('#categoryForm').action = '<?= BASE_URL ?>/admin/plots/categories/update';
    
    new bootstrap.Modal(document.getElementById('createCategoryModal')).show();
}

function deleteCategory(id) {
    if (confirm('Delete this category? This cannot be undone.')) {
        window.location.href = '<?= BASE_URL ?>/admin/plots/categories/delete/' + id;
    }
}

// Reset modal on close
document.getElementById('createCategoryModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('categoryForm').reset();
    document.getElementById('category_id').value = '';
    document.querySelector('#createCategoryModal .modal-title').innerHTML = '<i class="fas fa-plus-circle me-2"></i>Add Plot Category';
    document.querySelector('#categoryForm').action = '<?= BASE_URL ?>/admin/plots/categories/store';
});
</script>
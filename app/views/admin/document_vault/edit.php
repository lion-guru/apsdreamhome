<?php
/** Admin Document Vault - Edit Category */

$page_title = $page_title ?? 'Edit Document Category';
$mode = 'edit';
$category = $category ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>"></style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-tags me-2 text-primary"></i><?= $page_title ?></h4>
            <p class="text-muted mb-0 small">Edit document category</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/document-vault/categories" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Categories
        </a>
    </div>

    <form method="POST" id="categoryForm" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <input type="hidden" name="mode" value="edit">
        <input type="hidden" name="category_id" value="<?= $category['id'] ?>">

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-tags me-2"></i>Category Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($category['name'] ?? '') ?>" placeholder="e.g., Identity Proof">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" required value="<?= htmlspecialchars($category['code'] ?? '') ?>" readonly>
                        <div class="form-text">Code cannot be changed after creation</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Category description..."><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Mandatory</label>
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="is_mandatory" value="1" id="is_mandatory" <?= !empty($category['is_mandatory']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_mandatory">Required for all employees</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Retention (Years)</label>
                        <input type="number" min="1" max="99" name="retention_years" class="form-control" value="<?= htmlspecialchars($category['retention_years'] ?? 7) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Max File Size (MB)</label>
                        <input type="number" min="1" max="100" name="max_size_mb" class="form-control" value="<?= htmlspecialchars($category['max_size_mb'] ?? 10) ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-file me-2"></i>File Settings</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Allowed Types</label>
                        <input type="text" name="allowed_types" class="form-control" value="<?= htmlspecialchars($category['allowed_types'] ? implode(', ', json_decode($category['allowed_types'], true)) : 'pdf,jpg,jpeg,png') ?>" placeholder="pdf,jpg,jpeg,png,doc,docx">
                        <div class="form-text">Comma-separated extensions</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Max File Size (MB)</label>
                        <input type="number" min="1" max="100" name="max_size_mb" class="form-control" value="<?= htmlspecialchars($category['max_size_mb'] ?? 10) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Requires Expiry</label>
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="requires_expiry" value="1" id="requires_expiry" <?= !empty($category['requires_expiry']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="requires_expiry">Document expires</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Expiry Alert (Days)</label>
                        <input type="number" min="1" max="365" name="expiry_alert_days" class="form-control" value="<?= htmlspecialchars($category['expiry_alert_days'] ?? 30) ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-toggle-on me-2"></i>Status</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" <?= empty($category['is_active']) ? '' : 'checked' ?>>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Sort Order</label>
                        <input type="number" min="0" name="sort_order" class="form-control" value="<?= htmlspecialchars($category['sort_order'] ?? 0) ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="<?= BASE_URL ?>/admin/document-vault/categories" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save me-1"></i> Update Category
            </button>
        </div>
    </form>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
document.getElementById('categoryForm').addEventListener('submit', function(e) {
    if (!this.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
    }
    this.classList.add('was-validated');
});
</script>
<?php $page_title = $page_title ?? 'Upload Document'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-upload me-2"></i>Upload Document</h1>
        <a href="<?= BASE_URL ?>/admin/documents" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <?php if (!empty($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_type'] ?? 'info') ?> alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_message'] ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php $_SESSION['flash_message'] = ''; $_SESSION['flash_type'] = ''; endif; ?>

    <div class="card shadow-sm">
        <div class="card-body aps-cp-card-body">
            <form method="POST" action="<?= BASE_URL ?>/admin/documents/store" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Document Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Document Type</label>
                        <select name="type" class="form-select">
                            <option value="property">Property Document</option>
                            <option value="customer">Customer Document</option>
                            <option value="legal">Legal Document</option>
                            <option value="agreement">Agreement</option>
                            <option value="kyc">KYC Document</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
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
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Document Sub-Type</label>
                        <select name="doc_type_id" class="form-select">
                            <option value="">-- None --</option>
                            <?php if (!empty($doc_types)): ?>
                                <?php foreach ($doc_types as $dt): ?>
                                    <option value="<?= (int)$dt['id'] ?>"><?= htmlspecialchars($dt['name'] ?? '') ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Select File <span class="text-danger">*</span></label>
                    <input type="file" name="document_file" class="form-control" required>
                    <small class="text-muted">Allowed: PDF, DOC, DOCX, JPG, PNG (Max 10MB)</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Related To</label>
                    <select name="related_type" class="form-select" id="relatedType">
                        <option value="">-- None --</option>
                        <option value="property">Property</option>
                        <option value="customer">Customer</option>
                        <option value="project">Project</option>
                        <option value="lead">Lead</option>
                    </select>
                </div>
                <div class="mb-3" id="relatedIdField" class="style-2248">
                    <label class="form-label">Related ID</label>
                    <input type="number" name="related_id" class="form-control" placeholder="Enter ID number">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i>Upload Document</button>
                <a href="<?= BASE_URL ?>/admin/documents" class="btn btn-outline-secondary ms-2">Cancel</a>
            </form>
        </div>
    </div>
</div>
<script>
document.getElementById('relatedType')?.addEventListener('change', function() {
    document.getElementById('relatedIdField').style.display = this.value ? 'block' : 'none';
});
</script>

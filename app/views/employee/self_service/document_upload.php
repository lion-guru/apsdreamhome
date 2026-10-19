<?php
/** Employee Self-Service - Upload Document */

$page_title = $page_title ?? 'Upload Document';
$categories = $categories ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>"></style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-upload me-2 text-primary"></i>Upload Document</h4>
            <p class="text-muted mb-0 small">Upload a new document for verification</p>
        </div>
        <a href="<?= BASE_URL ?>/employee/self-service/documents" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Documents
        </a>
    </div>

    <form method="POST" enctype="multipart/form-data" id="uploadForm" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

        <!-- Document Details -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-file-alt me-2"></i>Document Details</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required id="categorySelect">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" data-requires-expiry="<?= $cat['requires_expiry'] ? '1' : '0' ?>" data-allowed="<?= htmlspecialchars($cat['allowed_types'] ?? '[]') ?>" data-max-size="<?= $cat['max_size_mb'] ?? 10 ?>">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Document Name <span class="text-danger">*</span></label>
                        <input type="text" name="document_name" class="form-control" required placeholder="e.g., Aadhaar Card, Passport, Offer Letter">
                    </div>
                    <div class="col-md-6" id="expiryDateGroup" style="display: none;">
                        <label class="form-label fw-semibold">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control" id="expiryDate">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Issuing Authority</label>
                        <input type="text" name="issuing_authority" class="form-control" placeholder="e.g., UIDAI, Passport Office, Company HR">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Document Reference Number</label>
                        <input type="text" name="document_number_ref" class="form-control" placeholder="e.g., Aadhaar No., Passport No., PAN No.">
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="is_confidential" value="1" id="is_confidential">
                            <label class="form-check-label" for="is_confidential">Confidential Document</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Access Level</label>
                        <select name="access_level" class="form-select">
                            <option value="self" selected>Self Only</option>
                            <option value="hr">HR & Management</option>
                            <option value="all">All Authorized</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- File Upload -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-file-upload me-2"></i>Upload File</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">File <span class="text-danger">*</span></label>
                    <input type="file" name="document_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required id="documentFile">
                    <div class="form-text" id="fileHelp">Allowed: PDF, JPG, PNG, DOC, DOCX. Max size: 10 MB</div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="<?= BASE_URL ?>/employee/self-service/documents" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-upload me-1"></i> Upload Document
            </button>
        </div>
    </form>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
// Update expiry date field visibility based on category
document.getElementById('categorySelect')?.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const requiresExpiry = selectedOption.dataset.requiresExpiry === '1';
    document.getElementById('expiryDateGroup').style.display = requiresExpiry ? 'block' : 'none';
});

// Update file help text based on category
document.getElementById('categorySelect')?.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const maxSize = selectedOption.dataset.maxSize || 10;
    const allowed = selectedOption.dataset.allowed;
    let allowedTypes = 'PDF, JPG, PNG, DOC, DOCX';
    try {
        const types = JSON.parse(selectedOption.dataset.allowed || '[]');
        if (types.length > 0) {
            allowedTypes = types.map(t => t.toUpperCase()).join(', ');
        }
    } catch (e) {}
    document.getElementById('fileHelp').textContent = 'Allowed: ' + allowedTypes + '. Max size: ' + maxSize + ' MB';
});

// File size validation
document.getElementById('documentFile')?.addEventListener('change', function() {
    const selectedOption = document.getElementById('categorySelect').options[document.getElementById('categorySelect').selectedIndex];
    const maxSize = parseInt(selectedOption?.dataset?.maxSize || 10) * 1024 * 1024;
    const file = this.files[0];
    if (file && file.size > maxSize) {
        alert('File size exceeds maximum allowed (' + (maxSize / 1024 / 1024) + ' MB)');
        this.value = '';
    }
});

// Form validation
document.getElementById('uploadForm')?.addEventListener('submit', function(e) {
    if (!this.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
    }
    this.classList.add('was-validated');
});
</script>
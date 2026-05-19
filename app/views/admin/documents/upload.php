<div class="container-fluid py-4">
    <h1 class="h3 mb-4"><i class="fas fa-upload me-2"></i>Upload Document</h1>
    
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/admin/documents/store" enctype="multipart/form-data">
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
                <div class="mb-3" id="relatedIdField" style="display:none;">
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

<?php
$pageTitle = $pageTitle ?? 'Upload File';
$base = $base ?? (defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'));
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-upload me-2 text-primary"></i>Upload File</h1>
        <a href="<?= $base ?>/admin/files" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">File Upload</h6></div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= $base ?>/admin/files/upload" enctype="multipart/form-data">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Select File <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control" required>
                            <small class="text-muted">Max file size: 50MB. Supported: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, GIF</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="general">General</option>
                                <option value="property">Property</option>
                                <option value="document">Document</option>
                                <option value="payment">Payment</option>
                                <option value="user">User</option>
                                <option value="report">Report</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Brief description of the file"></textarea>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="is_public" class="form-check-input" id="isPublic" value="1">
                            <label class="form-check-label" for="isPublic">Make file publicly accessible</label>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i>Upload</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-info">Upload Rules</h6></div>
                <div class="card-body small">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-1"></i> Max file size: 50MB</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-1"></i> Allowed: PDF, DOC, DOCX, XLS, XLSX</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-1"></i> Allowed: JPG, PNG, GIF, WEBP</li>
                        <li class="mb-2"><i class="fas fa-exclamation-triangle text-warning me-1"></i> Malicious files will be rejected</li>
                        <li class="mb-2"><i class="fas fa-exclamation-triangle text-warning me-1"></i> Keep filenames clean (no special chars)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

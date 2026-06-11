<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'Import Data') ?></h1>
        <a href="<?= $base ?? BASE_URL ?>/admin/import-export/dashboard" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="fas fa-upload me-2"></i>Upload File</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form action="<?= $base ?? BASE_URL ?>/admin/import-export/import" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Entity Type</label>
                            <select name="import_type" class="form-select" required>
                                <option value="">Select...</option>
                                <option value="properties">Properties</option>
                                <option value="leads">Leads</option>
                                <option value="khatabook_sales">Khatabook / Sales Records</option>
                                <option value="users">users</option>
                                <option value="users">Users</option>
                                <option value="contacts">Contacts</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">File (CSV, XLSX, JSON)</label>
                            <input type="file" name="file" class="form-control" accept=".csv,.xlsx,.json" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Import Strategy</label>
                            <select name="strategy" class="form-select">
                                <option value="insert">Insert New Only</option>
                                <option value="upsert">Insert or Update</option>
                                <option value="replace">Replace All</option>
                            </select>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="skip_errors" class="form-check-input" id="skipErrors" checked>
                            <label class="form-check-label" for="skipErrors">Skip rows with errors</label>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i>Start Import</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Instructions</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <ul class="mb-0">
                        <li>Supported formats: CSV, XLSX, JSON</li>
                        <li>First row must contain column headers</li>
                        <li>Required columns vary by entity type</li>
                        <li>Max file size: 10MB</li>
                        <li>For large imports, use CSV format for best performance</li>
                    </ul>
                    <hr>
                    <a href="<?= $base ?? BASE_URL ?>/admin/import-export/sample" class="btn btn-outline-primary btn-sm"><i class="fas fa-download me-1"></i>Download Sample</a>
                </div>
            </div>
        </div>
    </div>
</div>

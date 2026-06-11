<?php $page_title = 'Import Leads'; ?>
<div class="container-fluid py-4">
    <h2 class="mb-4"><i class="fas fa-file-import me-2"></i>Import Leads</h2>
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>Upload a CSV file with leads. Columns: <code>name, email, phone, source, status, city, notes</code>
                    </div>
                    <form method="POST" action="<?= BASE_URL ?>/admin/leads/import" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="mb-3">
                            <label class="form-label">CSV File *</label>
                            <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Default Source</label>
                            <select name="default_source" class="form-select">
                                <option value="csv_import">CSV Import</option>
                                <option value="manual">Manual</option>
                                <option value="website">Website</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i>Import</button>
                        <a href="<?= BASE_URL ?>/admin/leads" class="btn btn-secondary ms-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-download me-2"></i>Download Template</h6></div>
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted">Download a sample CSV file with the correct headers and format.</p>
                    <a href="<?= BASE_URL ?>/admin/leads/import/template" class="btn btn-outline-primary"><i class="fas fa-file-csv me-1"></i>Download Template</a>
                </div>
            </div>
        </div>
    </div>
</div>

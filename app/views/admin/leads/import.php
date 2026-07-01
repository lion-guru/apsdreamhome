<?php
$page_title = $page_title ?? 'Import Leads from CSV';
$base = defined('BASE_URL') ? BASE_URL : '';
?>

<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0"><i class="fas fa-file-csv me-2 text-success"></i>Import Leads from CSV</h4>
        <a href="<?= $base ?>/admin/leads" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Leads</a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="m-0"><i class="fas fa-upload me-2"></i>Upload CSV File</h5></div>
                <div class="card-body">
                    <form action="<?= $base ?>/admin/leads/import/preview" method="POST" enctype="multipart/form-data" id="importForm">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Select CSV File</label>
                            <input type="file" class="form-control form-control-lg" name="csv_file" accept=".csv" required id="csvFile">
                            <small class="text-muted">Maximum 500 rows per import. File must be CSV format.</small>
                        </div>

                        <div id="filePreview" style="display:none;">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <span id="fileInfo"></span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg" id="previewBtn" disabled>
                            <i class="fas fa-eye me-2"></i>Preview & Validate
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="m-0"><i class="fas fa-table me-2"></i>CSV Format Guide</h5></div>
                <div class="card-body">
                    <p class="small text-muted mb-3">Your CSV should have headers in the first row. Supported columns:</p>
                    <table class="table table-sm table-bordered mb-3">
                        <thead class="table-light">
                            <tr><th>Column</th><th>Required</th><th>Example</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code>name</code></td><td><span class="badge bg-danger">Yes</span></td><td>Ravi Kumar</td></tr>
                            <tr><td><code>email</code></td><td><span class="badge bg-secondary">No</span></td><td>ravi@email.com</td></tr>
                            <tr><td><code>phone</code></td><td><span class="badge bg-warning text-dark">One required</span></td><td>9876543210</td></tr>
                            <tr><td><code>source</code></td><td><span class="badge bg-secondary">No</span></td><td>website, google, referral</td></tr>
                            <tr><td><code>budget</code></td><td><span class="badge bg-secondary">No</span></td><td>5000000</td></tr>
                            <tr><td><code>budget_range</code></td><td><span class="badge bg-secondary">No</span></td><td>50L-75L</td></tr>
                            <tr><td><code>property_interest</code></td><td><span class="badge bg-secondary">No</span></td><td>Plot, Flat</td></tr>
                            <tr><td><code>location_preference</code></td><td><span class="badge bg-secondary">No</span></td><td>Mathura</td></tr>
                            <tr><td><code>priority</code></td><td><span class="badge bg-secondary">No</span></td><td>high, medium, low</td></tr>
                            <tr><td><code>notes</code></td><td><span class="badge bg-secondary">No</span></td><td>Interested in corner plot</td></tr>
                            <tr><td><code>company</code></td><td><span class="badge bg-secondary">No</span></td><td>ABC Corp</td></tr>
                            <tr><td><code>city</code></td><td><span class="badge bg-secondary">No</span></td><td>Mathura</td></tr>
                            <tr><td><code>state</code></td><td><span class="badge bg-secondary">No</span></td><td>UP</td></tr>
                        </tbody>
                    </table>
                    <div class="text-muted small">
                        <p class="mb-1"><i class="fas fa-check-circle text-success me-1"></i> All leads auto-scored on import</p>
                        <p class="mb-1"><i class="fas fa-check-circle text-success me-1"></i> Activity logged for each lead</p>
                        <p class="mb-0"><i class="fas fa-check-circle text-success me-1"></i> Assigned to current admin</p>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-download me-2 text-primary"></i>Sample Template</h6>
                    <p class="small text-muted">Download a sample CSV to get started:</p>
                    <a href="data:text/csv;charset=utf-8,name,email,phone,source,budget,property_interest,location_preference,priority,notes%0ARavi Kumar,ravi@email.com,9876543210,website,5000000,Plot,Mathura,high,Interested in corner plot%0AGeeta Devi,geeta@email.com,9876543211,referral,7500000,Flat,Agra,medium,Looking for 2BHK" download="lead_import_template.csv" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-download me-1"></i>Download Template CSV
                        </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('csvFile').addEventListener('change', function(e) {
    var file = e.target.files[0];
    if (file) {
        document.getElementById('filePreview').style.display = 'block';
        document.getElementById('fileInfo').textContent = 'Selected: ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
        document.getElementById('previewBtn').disabled = false;
    }
});
</script>

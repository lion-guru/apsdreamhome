<?php
/** Admin Org Chart Import */

$page_title = $page_title ?? 'Import Org Chart';
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>"></style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-upload me-2 text-primary"></i>Import Org Chart</h4>
            <p class="text-muted mb-0 small">Import organizational chart from JSON file</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/org-chart" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Org Chart
        </a>
    </div>

    <!-- Instructions -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-info-circle me-2"></i>Import Instructions</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <h6><i class="fas fa-info-circle me-2"></i>Expected JSON Format</h6>
                <pre class="small mb-0"><code>{
  "exported_at": "2026-10-18 10:00:00",
  "tenant_id": 1,
  "chart": [
    {
      "employee_id": 1,
      "parent_id": null,
      "position_title": "CEO",
      "department": "Executive",
      "level": 0,
      "sort_order": 0,
      "is_vacant": false,
      "vacant_since": null,
      "custom_data": {},
      "is_active": true,
      "children": [
        {
          "employee_id": 2,
          "parent_id": 1,
          "position_title": "CTO",
          "department": "Technology",
          "level": 1,
          "sort_order": 0,
          "is_vacant": false,
          "vacant_since": null,
          "custom_data": {},
          "is_active": true,
          "children": []
        }
      ]
    }
  ]
}</code></pre>
            </div>
            <ul class="small mt-3 mb-0">
                <li>Export from another system using the "Export" button</li>
                <li>Import will REPLACE all existing org chart data</li>
                <li>Employee IDs must match existing employees in this system</li>
                <li>Custom data field is optional</li>
            </ul>
        </div>
    </div>

    <!-- Upload Form -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-file-import me-2"></i>Upload File</h5>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data" id="importForm" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">JSON File <span class="text-danger">*</span></label>
                    <input type="file" name="chart_file" class="form-control" accept=".json" required>
                    <div class="form-text">Exported org chart JSON file</div>
                </div>
                
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="dry_run" id="dry_run" value="1">
                    <label class="form-check-label" for="dry_run">Dry run only (validate without importing)</label>
                </div>
                
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="<?= BASE_URL ?>/admin/org-chart" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-upload me-1"></i> Import Org Chart
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Section -->
    <div id="resultsSection" class="d-none">
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-chart-bar me-2"></i>Import Results</h5>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card stat-card border-0 shadow-sm bg-success text-white">
                            <div class="card-body text-center">
                                <div class="fw-bold fs-3" id="importedCount">0</div>
                                <div class="text-white-50 small">Imported</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card border-0 shadow-sm bg-warning text-white">
                            <div class="card-body text-center">
                                <div class="fw-bold fs-4" id="skippedCount">0</div>
                                <div class="text-white-50 small">Skipped</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card border-0 shadow-sm bg-danger text-white">
                            <div class="card-body text-center">
                                <div class="fw-bold fs-4" id="errorCount">0</div>
                                <div class="text-white-50 small">Errors</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="errorsContainer" class="d-none">
                    <h6 class="fw-semibold mb-3 text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Errors</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Node</th>
                                    <th>Error</th>
                                </tr>
                            </thead>
                            <tbody id="errorsTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
document.getElementById('importForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Importing...';
    
    const formData = new FormData(this);
    formData.append('csrf_token', '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>');
    
    try {
        const response = await fetch('<?= BASE_URL ?>/admin/org-chart/import', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        document.getElementById('resultsSection').classList.remove('d-none');
        document.getElementById('importedCount').textContent = data.imported || 0;
        document.getElementById('skippedCount').textContent = data.skipped || 0;
        document.getElementById('errorCount').textContent = data.errors?.length || 0;
        
        if (data.errors && data.errors.length > 0) {
            document.getElementById('errorsContainer').classList.remove('d-none');
            const tbody = document.getElementById('errorsTableBody');
            tbody.innerHTML = '';
            data.errors.forEach(err => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${err.node || '-'}</td><td class="text-danger">${err.error}</td>`;
                document.getElementById('errorsTableBody').appendChild(tr);
            });
        } else {
            document.getElementById('errorsContainer').classList.add('d-none');
        }
        
        if (data.success) {
            setTimeout(() => location.reload(), 2000);
        }
    } catch (err) {
        alert('Error: ' + err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});
</script>
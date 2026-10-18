<?php
/**
 * Holiday Calendar Bulk Import
*/

$page_title = $page_title ?? 'Bulk Import Holidays';
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.import-section { border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.5rem; margin-bottom: 1.5rem; background: #fafafa; }
.sample-row { font-family: monospace; font-size: 0.85rem; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-upload me-2 text-primary"></i>Bulk Import Holidays</h4>
            <p class="text-muted mb-0 small">Import multiple holidays from CSV or JSON file</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/holiday-calendar" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Calendar
        </a>
    </div>

    <!-- Instructions -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-info-circle me-2"></i>Import Instructions</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <h6><i class="fas fa-info-circle me-2"></i>Supported Formats</h6>
                <ul class="mb-0 small">
                    <li><strong>CSV:</strong> Columns: name, date, type, region, description, is_half_day, is_working_day, is_recurring, recurrence_rule</li>
                    <li><strong>JSON:</strong> Array of objects with same fields</li>
                </ul>
            </div>
            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <h6 class="fw-semibold">CSV Example:</h6>
                    <pre class="sample-row bg-light p-3 rounded"><code>name,date,type,region,description,is_half_day,is_working_day,is_recurring,recurrence_rule
New Year's Day,2026-01-01,national,IN,New Year Day,0,0,1,FREQ=YEARLY
Republic Day,2026-01-26,national,IN,Republic Day,0,0,1,FREQ=YEARLY
Holi,2026-03-14,religious,IN,Festival of Colors,0,0,1,
</code></pre>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-semibold">JSON Example:</h6>
                    <pre class="sample-row bg-light p-3 rounded"><code>[
  {
    "name": "New Year's Day",
    "date": "2026-01-01",
    "type": "national",
    "region": "IN",
    "description": "New Year Day",
    "is_half_day": 0,
    "is_working_day": 0,
    "is_recurring": 1,
    "recurrence_rule": "FREQ=YEARLY"
  }
]</code></pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Form -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-file-import me-2"></i>Upload File</h5>
        </div>
        <div class="card-body">
            <form method="POST" id="importForm" enctype="multipart/form-data" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">File <span class="text-danger">*</span></label>
                    <input type="file" name="holiday_file" class="form-control" accept=".csv,.json" required>
                    <div class="form-text">CSV or JSON file (max 2MB)</div>
                </div>
                
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="skip_duplicates" id="skip_duplicates" value="1" checked>
                    <label class="form-check-label" for="skip_duplicates">Skip duplicates (based on name + date)</label>
                </div>
                
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="dry_run" id="dry_run" value="1">
                    <label class="form-check-label" for="dry_run">Dry run only (validate without saving)</label>
                </div>
                
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="<?= BASE_URL ?>/admin/holiday-calendar" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-upload me-1"></i> Import Holidays
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
                                <div class="fw-bold fs-3" id="createdCount">0</div>
                                <div class="text-white-50 small">Created</div>
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
                                    <th>Row</th>
                                    <th>Field</th>
                                    <th>Error</th>
                                </tr>
                            </thead>
                            <tbody id="errorsTableBody"></tbody>
                        </table>
                    </div>
                </div>

                <div id="createdList" class="d-none">
                    <h6 class="fw-semibold mb-3 text-success"><i class="fas fa-check-circle me-2"></i>Successfully Created</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Region</th>
                                </tr>
                            </thead>
                            <tbody id="createdTableBody"></tbody>
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
        const response = await fetch('<?= BASE_URL ?>/admin/holiday-calendar/bulk-import', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        document.getElementById('resultsSection').classList.remove('d-none');
        document.getElementById('createdCount').textContent = data.created || 0;
        document.getElementById('skippedCount').textContent = data.skipped || 0;
        document.getElementById('errorCount').textContent = data.errors?.length || 0;
        
        if (data.errors && data.errors.length > 0) {
            document.getElementById('errorsContainer').classList.remove('d-none');
            const tbody = document.getElementById('errorsTableBody');
            tbody.innerHTML = '';
            data.errors.forEach(err => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${err.row}</td><td>${err.field || '-'}</td><td class="text-danger">${err.error}</td>`;
                document.getElementById('errorsTableBody').appendChild(tr);
            }
        } else {
            document.getElementById('errorsContainer').classList.add('d-none');
        }
        
        if (data.created && data.created.length > 0) {
            document.getElementById('createdList').classList.remove('d-none');
            const tbody = document.getElementById('createdTableBody');
            tbody.innerHTML = '';
            (data.created || []).forEach(item => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${item.name}</td><td>${item.date}</td><td>${item.type}</td><td>${item.region || '-'}</td>`;
                document.getElementById('createdTableBody').appendChild(tr);
            }
        } else {
            document.getElementById('createdList').classList.add('d-none');
        }
        
    } catch (err) {
        alert('Error: ' + err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});
</script>
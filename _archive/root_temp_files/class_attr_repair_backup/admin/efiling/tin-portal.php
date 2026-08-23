<?php
$page_title = $page_title ?? 'TIN Portal';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-server me-2 text-danger"></i><?= htmlspecialchars($page_title ?? '') ?></h4>
        <span class="text-muted">TIN-NSDL API Integration | FY <?= htmlspecialchars($fy ?? '') ?> | Q<?= htmlspecialchars($quarter ?? '') ?></span>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/admin/efiling/tds" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>TDS Filing</a>
        <a href="<?= BASE_URL ?>/admin/efiling" class="btn btn-outline-secondary btn-sm"><i class="fas fa-home me-1"></i>Dashboard</a>
    </div>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_success'] ?? ''); unset($_SESSION['flash_success']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_error'] ?? ''); unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>

<!-- Connection Status -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <?php if ($tin_status['connected']): ?>
                            <div class="rounded-circle bg-success bg-opacity-10 p-3"><i class="fas fa-plug text-success"></i></div>
                        <?php else: ?>
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3"><i class="fas fa-plug text-warning"></i></div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="text-muted small">TIN Connection</div>
                        <div class="fw-bold"><?= $tin_status['connected'] ? 'Connected' : 'Disconnected' ?></div>
                        <div class="small text-muted"><?= htmlspecialchars($tin_status['message'] ?? '') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <?php if ($tin_status['mode'] === 'test'): ?>
                            <div class="rounded-circle bg-info bg-opacity-10 p-3"><i class="fas fa-flask text-info"></i></div>
                        <?php else: ?>
                            <div class="rounded-circle bg-success bg-opacity-10 p-3"><i class="fas fa-rocket text-success"></i></div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="text-muted small">Mode</div>
                        <div class="fw-bold"><?= ucfirst($tin_status['mode']) ?></div>
                        <div class="small text-muted"><?= $tin_status['mode'] === 'test' ? 'Mock responses only' : 'Live API calls' ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="rounded-circle bg-secondary bg-opacity-10 p-3"><i class="fas fa-certificate text-secondary"></i></div>
                    </div>
                    <div>
                        <div class="text-muted small">TAN</div>
                        <div class="fw-bold"><?= htmlspecialchars($tin_status['tan'] ?: 'Not Set') ?></div>
                        <div class="small text-muted">Tax Deduction Account Number</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Submit Actions -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom"><h6 class="mb-0"><i class="fas fa-file-invoice-dollar me-2 text-danger"></i>Submit Form 26Q (Domestic)</h6></div>
            <div class="card-body aps-cp-card-body">
                <form id="tin26qForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="form_type" value="26Q">
                    <div class="mb-3">
                        <label class="form-label small">Financial Year</label>
                        <select name="fy" class="form-select form-select-sm">
                            <?php foreach ($fy_list as $val => $label): ?>
                                <option value="<?= $val ?>" <?= $val === $fy ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Quarter</label>
                        <select name="quarter" class="form-select form-select-sm">
                            <?php foreach (['Q1','Q2','Q3','Q4'] as $q): ?>
                                <option value="<?= $q ?>" <?= $q === $quarter ? 'selected' : '' ?>><?= $q ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-danger btn-sm w-100">
                        <i class="fas fa-cloud-upload-alt me-1"></i>Submit Form 26Q
                    </button>
                </form>
                <div id="tin26qResult" class="mt-3" class="style-2248"></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom"><h6 class="mb-0"><i class="fas fa-file-invoice me-2 text-warning"></i>Submit Form 27Q (NRI)</h6></div>
            <div class="card-body aps-cp-card-body">
                <form id="tin27qForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="form_type" value="27Q">
                    <div class="mb-3">
                        <label class="form-label small">Financial Year</label>
                        <select name="fy" class="form-select form-select-sm">
                            <?php foreach ($fy_list as $val => $label): ?>
                                <option value="<?= $val ?>" <?= $val === $fy ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Quarter</label>
                        <select name="quarter" class="form-select form-select-sm">
                            <?php foreach (['Q1','Q2','Q3','Q4'] as $q): ?>
                                <option value="<?= $q ?>" <?= $q === $quarter ? 'selected' : '' ?>><?= $q ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-warning btn-sm w-100">
                        <i class="fas fa-cloud-upload-alt me-1"></i>Submit Form 27Q
                    </button>
                </form>
                <div id="tin27qResult" class="mt-3" class="style-2248"></div>
            </div>
        </div>
    </div>
</div>

<!-- TDS Filing History -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-history me-2"></i>TDS Filing History</h6>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($submissions)): ?>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Type</th><th>Period</th><th>Status</th><th>Records</th><th>TDS Amount</th><th>Portal Ref</th><th>Date</th></tr>
                </thead>
                <tbody>
                <?php foreach ($submissions as $s): ?>
                    <tr>
                        <td><span class="badge bg-danger">TDS Return</span></td>
                        <td class="small">Q<?= $s['quarter'] ?> <?= $s['financial_year'] ?></td>
                        <td><span class="badge bg-<?= $s['status'] === 'submitted' ? 'success' : ($s['status'] === 'accepted' ? 'info' : ($s['status'] === 'rejected' ? 'danger' : 'secondary')) ?>"><?= ucfirst($s['status']) ?></span></td>
                        <td><?= $s['total_records'] ?></td>
                        <td>?<?= number_format($s['total_amount'], 0) ?></td>
                        <td class="small text-muted"><?= $s['portal_reference'] ?? '-' ?></td>
                        <td class="small"><?= date('d M Y', strtotime($s['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="text-center py-4 text-muted"><i class="fas fa-inbox me-2"></i>No TDS filings yet</div>
        <?php endif; ?>
    </div>
</div>

<!-- Form16A Download -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom"><h6 class="mb-0"><i class="fas fa-download me-2"></i>Form 16A Download</h6></div>
    <div class="card-body aps-cp-card-body">
        <form id="form16aDownload" class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label small">Token Number</label>
                <input type="text" name="token" class="form-control form-control-sm" placeholder="Enter TDS token number">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                    <i class="fas fa-download me-1"></i>Download Form 16A
                </button>
            </div>
        </form>
        <div id="form16aResult" class="mt-3" class="style-2248"></div>
    </div>
</div>

<script>
document.querySelectorAll('#tin26qForm, #tin27qForm').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = form.querySelector('button[type="submit"]');
        var resultId = form.id === 'tin26qForm' ? 'tin26qResult' : 'tin27qResult';
        var resultDiv = document.getElementById(resultId);
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Submitting...';
        resultDiv.style.display = 'none';

        fetch('<?= BASE_URL ?>/admin/efiling/tin/submit', {
            method: 'POST',
            body: new FormData(form)
        }).then(function(r) { return r.json(); }).then(function(data) {
        .catch(err => console.error('Request failed:', err));
            btn.disabled = false;
            var formType = form.querySelector('input[name="form_type"]').value;
            btn.innerHTML = '<i class="fas fa-cloud-upload-alt me-1"></i>Submit ' + formType;
            resultDiv.style.display = 'block';
            if (data.success) {
                var d = data.data || {};
                resultDiv.innerHTML = '<div class="alert alert-success py-2 mb-0"><i class="fas fa-check me-1"></i>' +
                    (d.message || d.acknowledgment_number || d.token_number || 'Submitted') + '</div>';
            } else {
                resultDiv.innerHTML = '<div class="alert alert-danger py-2 mb-0"><i class="fas fa-times me-1"></i>' +
                    (data.error || 'Submission failed') + '</div>';
            }
        }).catch(function() {
            btn.disabled = false;
            var formType = form.querySelector('input[name="form_type"]').value;
            btn.innerHTML = '<i class="fas fa-cloud-upload-alt me-1"></i>Submit ' + formType;
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = '<div class="alert alert-danger py-2 mb-0">Network error</div>';
        });
    });
});

document.getElementById('form16aDownload').addEventListener('submit', function(e) {
    e.preventDefault();
    var token = this.querySelector('input[name="token"]').value.trim();
    var resultDiv = document.getElementById('form16aResult');
    if (!token) { resultDiv.style.display = 'block'; resultDiv.innerHTML = '<div class="alert alert-warning py-2 mb-0">Enter a token number</div>'; return; }

    fetch('<?= BASE_URL ?>/admin/efiling/tin/status/' + encodeURIComponent(token))
        .catch(err => console.error('Request failed:', err));
        .then(function(r) { return r.json(); }).then(function(data) {
            resultDiv.style.display = 'block';
            if (data.success) {
                var d = data.data || {};
                resultDiv.innerHTML = '<div class="alert alert-info py-2 mb-0"><strong>Status:</strong> ' + (d.status || 'N/A') +
                    ' | <strong>Form:</strong> ' + (d.form_type || '16A') +
                    ' | <strong>Date:</strong> ' + (d.filing_date || d.processed_date || 'N/A') + '</div>';
            } else {
                resultDiv.innerHTML = '<div class="alert alert-danger py-2 mb-0">' + (data.error || 'Not found') + '</div>';
            }
        }).catch(function() {
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = '<div class="alert alert-danger py-2 mb-0">Network error</div>';
        });
});
</script>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';

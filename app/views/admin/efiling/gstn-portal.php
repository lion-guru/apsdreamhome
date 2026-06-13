<?php
$page_title = $page_title ?? 'GSTN Portal';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-cloud-upload-alt me-2 text-primary"></i><?= htmlspecialchars($page_title) ?></h4>
        <span class="text-muted">GSTN API Integration | FY <?= htmlspecialchars($fy) ?></span>
    </div>
    <div class="d-flex gap-2">
        <a href="/admin/efiling/gst" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>GST Filing</a>
        <a href="/admin/efiling" class="btn btn-outline-secondary btn-sm"><i class="fas fa-home me-1"></i>Dashboard</a>
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
                        <div class="text-muted small">TIN (TDS)</div>
                        <div class="fw-bold"><?= $tin_status['connected'] ? 'Connected' : 'Disconnected' ?></div>
                        <div class="small text-muted"><?= htmlspecialchars($tin_status['message']) ?></div>
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
                        <?php if ($gstn_status['connected']): ?>
                            <div class="rounded-circle bg-success bg-opacity-10 p-3"><i class="fas fa-plug text-success"></i></div>
                        <?php else: ?>
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3"><i class="fas fa-plug text-warning"></i></div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="text-muted small">GSTN (GST)</div>
                        <div class="fw-bold"><?= $gstn_status['connected'] ? 'Connected' : 'Disconnected' ?></div>
                        <div class="small text-muted"><?= htmlspecialchars($gstn_status['message']) ?></div>
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
                        <?php if ($gstn_status['mode'] === 'test'): ?>
                            <div class="rounded-circle bg-info bg-opacity-10 p-3"><i class="fas fa-flask text-info"></i></div>
                        <?php else: ?>
                            <div class="rounded-circle bg-success bg-opacity-10 p-3"><i class="fas fa-rocket text-success"></i></div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="text-muted small">Mode</div>
                        <div class="fw-bold"><?= ucfirst($gstn_status['mode']) ?></div>
                        <div class="small text-muted"><?= $gstn_status['mode'] === 'test' ? 'Mock responses only' : 'Live API calls' ?></div>
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
            <div class="card-header bg-white border-bottom"><h6 class="mb-0"><i class="fas fa-file-export me-2 text-primary"></i>Submit GSTR-1</h6></div>
            <div class="card-body aps-cp-card-body">
                <form id="gstr1SubmitForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="return_type" value="gstr1">
                    <div class="mb-3">
                        <label class="form-label small">Financial Year</label>
                        <select name="fy" class="form-select form-select-sm">
                            <?php foreach ($fy_list as $val => $label): ?>
                                <option value="<?= $val ?>" <?= $val === $fy ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small">Month</label>
                            <select name="month" class="form-select form-select-sm">
                                <?php foreach ($months as $num => $name): ?>
                                    <option value="<?= $num ?>"><?= $name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Year</label>
                            <input type="number" name="year" class="form-control form-control-sm" value="<?= date('Y') ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-cloud-upload-alt me-1"></i>Submit GSTR-1 to GSTN
                    </button>
                </form>
                <div id="gstr1Result" class="mt-3" style="display:none;"></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom"><h6 class="mb-0"><i class="fas fa-file-alt me-2 text-success"></i>Submit GSTR-3B</h6></div>
            <div class="card-body aps-cp-card-body">
                <form id="gstr3bSubmitForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="return_type" value="gstr3b">
                    <div class="mb-3">
                        <label class="form-label small">Financial Year</label>
                        <select name="fy" class="form-select form-select-sm">
                            <?php foreach ($fy_list as $val => $label): ?>
                                <option value="<?= $val ?>" <?= $val === $fy ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small">Month</label>
                            <select name="month" class="form-select form-select-sm">
                                <?php foreach ($months as $num => $name): ?>
                                    <option value="<?= $num ?>"><?= $name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Year</label>
                            <input type="number" name="year" class="form-control form-control-sm" value="<?= date('Y') ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm w-100">
                        <i class="fas fa-cloud-upload-alt me-1"></i>Submit GSTR-3B to GSTN
                    </button>
                </form>
                <div id="gstr3bResult" class="mt-3" style="display:none;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Filing History -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-history me-2"></i>GST Filing History</h6>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($submissions)): ?>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Type</th><th>Period</th><th>Status</th><th>Records</th><th>Amount</th><th>Portal Ref</th><th>Date</th></tr>
                </thead>
                <tbody>
                <?php foreach ($submissions as $s): ?>
                    <tr>
                        <td><span class="badge bg-<?= $s['submission_type'] === 'gstr1' ? 'primary' : 'success' ?>"><?= strtoupper($s['submission_type']) ?></span></td>
                        <td class="small"><?= $s['period_month'] ? $months[$s['period_month']] . ' ' . $s['period_year'] : '-' ?></td>
                        <td><span class="badge bg-<?= $s['status'] === 'submitted' ? 'success' : ($s['status'] === 'accepted' ? 'info' : ($s['status'] === 'rejected' ? 'danger' : 'secondary')) ?>"><?= ucfirst($s['status']) ?></span></td>
                        <td><?= $s['total_records'] ?></td>
                        <td>₹<?= number_format($s['total_amount'], 0) ?></td>
                        <td class="small text-muted"><?= $s['portal_reference'] ?? '-' ?></td>
                        <td class="small"><?= date('d M Y', strtotime($s['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="text-center py-4 text-muted"><i class="fas fa-inbox me-2"></i>No GST filings yet</div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('#gstr1SubmitForm, #gstr3bSubmitForm').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = form.querySelector('button[type="submit"]');
        var resultId = form.id === 'gstr1SubmitForm' ? 'gstr1Result' : 'gstr3bResult';
        var resultDiv = document.getElementById(resultId);
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Submitting...';
        resultDiv.style.display = 'none';

        fetch('/admin/efiling/gstn/submit', {
            method: 'POST',
            body: new FormData(form)
        }).then(function(r) { return r.json(); }).then(function(data) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-cloud-upload-alt me-1"></i>Submit';
            resultDiv.style.display = 'block';
            if (data.success) {
                resultDiv.innerHTML = '<div class="alert alert-success py-2 mb-0"><i class="fas fa-check me-1"></i>' +
                    (data.message || data.reference_number || data.acknowledgment_number || 'Submitted') + '</div>';
            } else {
                resultDiv.innerHTML = '<div class="alert alert-danger py-2 mb-0"><i class="fas fa-times me-1"></i>' +
                    (data.error || 'Submission failed') + '</div>';
            }
        }).catch(function(err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-cloud-upload-alt me-1"></i>Submit';
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = '<div class="alert alert-danger py-2 mb-0">Network error</div>';
        });
    });
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';

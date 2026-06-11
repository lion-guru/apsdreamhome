<?php
$page_title = $page_title ?? 'Submission Detail';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-file-alt me-2"></i><?= htmlspecialchars($page_title) ?></h4>
        <span class="text-muted">Submission #<?= $submission['id'] ?></span>
    </div>
    <a href="/admin/efiling/submissions" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Submissions</a>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
<?php endif; ?>

<!-- Submission Info -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Submission Details</h6></div>
    <div class="card-body aps-cp-card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label small text-muted">Type</label>
                <div class="fw-bold"><span class="badge bg-<?= $submission['submission_type'] === 'gstr1' ? 'primary' : ($submission['submission_type'] === 'tds_return' ? 'danger' : 'secondary') ?>"><?= strtoupper($submission['submission_type']) ?></span></div>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Financial Year</label>
                <div class="fw-bold"><?= htmlspecialchars($submission['financial_year']) ?></div>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Quarter</label>
                <div class="fw-bold"><?= $submission['quarter'] ? "Q{$submission['quarter']}" : '-' ?></div>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Status</label>
                <div><span class="badge bg-<?= $submission['status'] === 'accepted' ? 'success' : ($submission['status'] === 'rejected' ? 'danger' : ($submission['status'] === 'submitted' ? 'primary' : 'secondary')) ?> fs-6"><?= ucfirst($submission['status']) ?></span></div>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Total Records</label>
                <div class="fw-bold"><?= $submission['total_records'] ?></div>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Total Amount</label>
                <div class="fw-bold">₹<?= number_format($submission['total_amount'], 0) ?></div>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Filing Date</label>
                <div class="fw-bold"><?= date('d M Y', strtotime($submission['filing_date'])) ?></div>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Created At</label>
                <div class="fw-bold"><?= date('d M Y H:i', strtotime($submission['created_at'])) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Portal Details -->
<?php if ($submission['portal_reference'] || $submission['arn_number'] || $submission['acknowledgment_number']): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom"><h6 class="mb-0"><i class="fas fa-check-double me-2"></i>Portal Details</h6></div>
    <div class="card-body aps-cp-card-body">
        <div class="row g-3">
            <?php if ($submission['portal_reference']): ?>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Portal Reference</label>
                    <div class="fw-bold"><?= htmlspecialchars($submission['portal_reference']) ?></div>
                </div>
            <?php endif; ?>
            <?php if ($submission['arn_number']): ?>
                <div class="col-md-3">
                    <label class="form-label small text-muted">ARN Number</label>
                    <div class="fw-bold"><?= htmlspecialchars($submission['arn_number']) ?></div>
                </div>
            <?php endif; ?>
            <?php if ($submission['acknowledgment_number']): ?>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Acknowledgment Number</label>
                    <div class="fw-bold"><?= htmlspecialchars($submission['acknowledgment_number']) ?></div>
                </div>
            <?php endif; ?>
            <?php if ($submission['filing_mode']): ?>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Filing Mode</label>
                    <div class="fw-bold"><?= htmlspecialchars($submission['filing_mode']) ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Error Details -->
<?php if ($submission['status'] === 'rejected' && $submission['error_message']): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom"><h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2 text-danger"></i>Error Details</h6></div>
    <div class="card-body aps-cp-card-body"><p class="mb-0 text-danger"><?= nl2br(htmlspecialchars($submission['error_message'])) ?></p></div>
</div>
<?php endif; ?>

<!-- Response JSON -->
<?php if ($submission['portal_response_json']): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom"><h6 class="mb-0"><i class="fas fa-code me-2"></i>Portal Response</h6></div>
    <div class="card-body aps-cp-card-body">
        <pre class="bg-light p-3 rounded small mb-0" style="max-height:300px;overflow:auto"><?= htmlspecialchars($submission['portal_response_json']) ?></pre>
    </div>
</div>
<?php endif; ?>

<!-- Actions -->
<?php if (in_array($submission['status'], ['draft', 'prepared'])): ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom"><h6 class="mb-0"><i class="fas fa-cogs me-2"></i>Actions</h6></div>
    <div class="card-body d-flex gap-2">
        <form method="POST" action="/admin/efiling/submissions/<?= $submission['id'] ?>/update-status" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="status" value="submitted">
            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Mark as submitted to the portal?')"><i class="fas fa-paper-plane me-1"></i>Mark Submitted</button>
        </form>
        <form method="POST" action="/admin/efiling/submissions/<?= $submission['id'] ?>/update-status" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="status" value="rejected">
            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Mark as rejected?')"><i class="fas fa-times me-1"></i>Mark Rejected</button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/unified.php';
?>

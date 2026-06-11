<div class="aps-cp-card">
    <div class="aps-cp-card-header">
        <span>NOC Detail #<?= (int)($noc['id'] ?? 0) ?></span>
        <a href="<?= BASE_URL ?>/admin/legal/noc-index" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to All Records
        </a>
    </div>
    <div class="aps-cp-card-body">
        <?php if (empty($noc)): ?>
        <div class="alert alert-warning">NOC not found.</div>
        <?php else: $n = $noc; ?>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="fw-medium text-muted small">NOC Type</label>
                <div><?= htmlspecialchars($n['noc_type'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="col-md-4">
                <label class="fw-medium text-muted small">Booking</label>
                <div><?= htmlspecialchars($n['booking_number'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="col-md-4">
                <label class="fw-medium text-muted small">Status</label>
                <div>
                    <span class="badge fs-6 bg-<?= match($n['status'] ?? '') { 'approved' => 'success', 'pending' => 'warning', 'blocked' => 'danger', 'rejected' => 'danger', default => 'secondary' } ?>">
                        <?= htmlspecialchars($n['status'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
            </div>

            <div class="col-md-6">
                <label class="fw-medium text-muted small">Customer</label>
                <div><?= htmlspecialchars($n['customer_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="col-md-6">
                <label class="fw-medium text-muted small">Plot</label>
                <div><?= htmlspecialchars($n['plot_number'] ?? '—', ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($n['colony_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?>)</div>
            </div>

            <div class="col-md-6">
                <label class="fw-medium text-muted small">Requested By</label>
                <div><?= htmlspecialchars($n['requested_by_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="col-md-6">
                <label class="fw-medium text-muted small">Requested At</label>
                <div><?= htmlspecialchars($n['created_at'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
            </div>

            <?php if (!empty($n['approved_by_name'])): ?>
            <div class="col-md-6">
                <label class="fw-medium text-muted small">Approved By</label>
                <div><?= htmlspecialchars($n['approved_by_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="col-md-6">
                <label class="fw-medium text-muted small">Approved At</label>
                <div><?= htmlspecialchars($n['approved_at'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($n['noc_document_url'])): ?>
            <div class="col-12">
                <label class="fw-medium text-muted small">NOC Document</label>
                <div>
                    <a href="<?= htmlspecialchars($n['noc_document_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-file-pdf"></i> View Document
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($n['rejection_reason'])): ?>
            <div class="col-12">
                <label class="fw-medium text-muted small">Rejection / Block Reason</label>
                <div class="text-danger"><?= htmlspecialchars($n['rejection_reason'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($n['remarks'])): ?>
            <div class="col-12">
                <label class="fw-medium text-muted small">Remarks</label>
                <div><?= nl2br(htmlspecialchars($n['remarks'], ENT_QUOTES, 'UTF-8')) ?></div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

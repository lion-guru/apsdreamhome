<div class="aps-cp-card">
    <div class="aps-cp-card-header">
        <span>Registry Detail #<?= (int)($registry['id'] ?? 0) ?></span>
        <a href="<?= BASE_URL ?>/admin/legal/noc-index" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to All Records
        </a>
    </div>
    <div class="aps-cp-card-body">
        <?php if (empty($registry)): ?>
        <div class="alert alert-warning">Registry not found.</div>
        <?php else: $r = $registry; ?>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="fw-medium text-muted small">Registration No.</label>
                <div><?= htmlspecialchars($r['registration_no'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="col-md-4">
                <label class="fw-medium text-muted small">Booking</label>
                <div><?= htmlspecialchars($r['booking_number'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="col-md-4">
                <label class="fw-medium text-muted small">Status</label>
                <div>
                    <span class="badge fs-6 bg-<?= match($r['status'] ?? '') { 'completed' => 'success', 'pending' => 'warning', 'failed' => 'danger', default => 'secondary' } ?>">
                        <?= htmlspecialchars($r['status'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
            </div>

            <div class="col-md-6">
                <label class="fw-medium text-muted small">Customer</label>
                <div><?= htmlspecialchars($r['customer_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="col-md-6">
                <label class="fw-medium text-muted small">Plot</label>
                <div><?= htmlspecialchars($r['plot_number'] ?? '—', ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($r['colony_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?>)</div>
            </div>

            <div class="col-md-6">
                <label class="fw-medium text-muted small">Sub-Registrar Office</label>
                <div><?= htmlspecialchars($r['sub_registrar_office'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="col-md-6">
                <label class="fw-medium text-muted small">Registration Date</label>
                <div><?= htmlspecialchars($r['registration_date'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
            </div>

            <div class="col-md-3">
                <label class="fw-medium text-muted small">Stamp Duty</label>
                <div>₹<?= number_format((float)($r['stamp_duty_amount'] ?? 0), 2) ?></div>
            </div>
            <div class="col-md-3">
                <label class="fw-medium text-muted small">Registration Fee</label>
                <div>₹<?= number_format((float)($r['registration_fee'] ?? 0), 2) ?></div>
            </div>
            <div class="col-md-3">
                <label class="fw-medium text-muted small">Other Charges</label>
                <div>₹<?= number_format((float)($r['other_charges'] ?? 0), 2) ?></div>
            </div>
            <div class="col-md-3">
                <label class="fw-medium text-muted small">Total Cost</label>
                <div class="fw-bold">₹<?= number_format((float)($r['total_registry_cost'] ?? 0), 2) ?></div>
            </div>

            <div class="col-12">
                <label class="fw-medium text-muted small">Document</label>
                <div>
                    <?php if (!empty($r['document_url'])): ?>
                    <a href="<?= htmlspecialchars($r['document_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-file-pdf"></i> View Document
                    </a>
                    <?php else: ?>
                    <span class="text-muted">No document uploaded</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-12">
                <label class="fw-medium text-muted small">Associate</label>
                <div><?= htmlspecialchars($r['associate_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
            </div>

            <?php if (!empty($r['rejection_reason'])): ?>
            <div class="col-12">
                <label class="fw-medium text-muted small">Rejection Reason</label>
                <div class="text-danger"><?= htmlspecialchars($r['rejection_reason'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($r['notes'])): ?>
            <div class="col-12">
                <label class="fw-medium text-muted small">Notes</label>
                <div><?= nl2br(htmlspecialchars($r['notes'], ENT_QUOTES, 'UTF-8')) ?></div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

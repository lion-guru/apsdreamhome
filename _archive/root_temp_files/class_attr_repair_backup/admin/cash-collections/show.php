<?php
$page_title = $page_title ?? 'Collection Details';
$page_heading = $page_heading ?? 'Collection Receipt Details';
$collection = $collection ?? [];
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-file-invoice-dollar me-2"></i>Collection #<?= $collection['id'] ?? 0 ?></h2>
            <p class="text-muted mb-0">Receipt details and verification</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/cash-collections" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Collection Information</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Receipt ID</div>
                        <div class="col-sm-8"><strong>#<?= $collection['id'] ?? '' ?></strong></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Collector</div>
                        <div class="col-sm-8"><strong><?= htmlspecialchars($collection['collector_name'] ?? 'N/A') ?></strong></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Customer</div>
                        <div class="col-sm-8"><?= htmlspecialchars($collection['customer_name'] ?? '') ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Amount</div>
                        <div class="col-sm-8"><h4 class="text-success mb-0">₹<?= number_format($collection['amount'] ?? 0) ?></h4></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Collection Date</div>
                        <div class="col-sm-8"><?= date('d M Y', strtotime($collection['collection_date'] ?? 'now')) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Payment Method</div>
                        <div class="col-sm-8"><span class="badge bg-light text-dark"><?= ucfirst($collection['payment_method'] ?? 'cash') ?></span></div>
                    </div>
                    <?php if (!empty($collection['reference_number'])): ?>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Reference Number</div>
                        <div class="col-sm-8"><?= htmlspecialchars($collection['reference_number'] ?? '') ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($collection['booking_number'])): ?>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Linked Booking</div>
                        <div class="col-sm-8"><?= htmlspecialchars($collection['booking_number'] ?? '') ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Status</div>
                        <div class="col-sm-8">
                            <?php
                            $colors = ['submitted' => 'warning', 'verified' => 'success', 'rejected' => 'danger', 'reconciled' => 'info'];
                            ?>
                            <span class="badge bg-<?= $colors[$collection['status']] ?? 'secondary' ?> px-3 py-2"><?= ucfirst($collection['status'] ?? '') ?></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4 text-muted">Notes</div>
                        <div class="col-sm-8"><?= nl2br(htmlspecialchars($collection['notes'] ?? '-')) ?></div>
                    </div>
                </div>
            </div>

            <?php if (!empty($collection['receipt_photo'])): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-image me-2"></i>Receipt Photo</h5></div>
                <div class="card-body text-center">
                    <img src="<?= BASE_URL . '/storage/' . htmlspecialchars($collection['receipt_photo'] ?? '') ?>" alt="Receipt" class="img-fluid rounded" class="style-35589">
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Verification</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (($collection['status'] ?? '') === 'submitted'): ?>
                        <p class="text-muted small">This receipt is pending verification.</p>
                        <div class="d-grid gap-2">
                            <form method="POST" action="<?= BASE_URL ?>/admin/cash-collections/verify">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? $_SESSION['csrf_token'] ?? '' ?>">
                                <input type="hidden" name="id" value="<?= $collection['id'] ?? 0 ?>">
                                <button type="submit" class="btn btn-success w-100"><i class="fas fa-check me-2"></i>Verify Receipt</button>
                            </form>
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#rejectForm">
                                <i class="fas fa-times me-2"></i>Reject Receipt
                            </button>
                        </div>
                        <div class="collapse mt-3" id="rejectForm">
                            <form method="POST" action="<?= BASE_URL ?>/admin/cash-collections/reject">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? $_SESSION['csrf_token'] ?? '' ?>">
                                <input type="hidden" name="id" value="<?= $collection['id'] ?? 0 ?>">
                                <div class="mb-2">
                                    <textarea class="form-control" name="reason" rows="3" placeholder="Rejection reason..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger w-100">Confirm Reject</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="row mb-2">
                            <div class="col-6 text-muted">Verified by</div>
                            <div class="col-6"><strong><?= htmlspecialchars($collection['verified_by_name'] ?? 'N/A') ?></strong></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6 text-muted">Verified at</div>
                            <div class="col-6"><?= $collection['verified_at'] ? date('d M Y H:i', strtotime($collection['verified_at'])) : 'N/A' ?></div>
                        </div>
                        <?php if (!empty($collection['rejection_reason'])): ?>
                        <div class="row">
                            <div class="col-6 text-muted">Rejection reason</div>
                            <div class="col-6 text-danger"><?= htmlspecialchars($collection['rejection_reason'] ?? '') ?></div>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-clock me-2"></i>Timeline</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row mb-2">
                        <div class="col-6 text-muted small">Submitted</div>
                        <div class="col-6 small"><?= $collection['created_at'] ? date('d M Y H:i', strtotime($collection['created_at'])) : 'N/A' ?></div>
                    </div>
                    <?php if (!empty($collection['updated_at']) && $collection['updated_at'] !== $collection['created_at']): ?>
                    <div class="row">
                        <div class="col-6 text-muted small">Last Updated</div>
                        <div class="col-6 small"><?= date('d M Y H:i', strtotime($collection['updated_at'])) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/layouts/admin.php';

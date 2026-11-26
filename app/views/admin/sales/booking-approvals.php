<?php
$bookings = $bookings ?? [];
$pagination = $pagination ?? [];
$filters = $filters ?? [];
$pendingCount = $pendingCount ?? 0;
$csrf_token = $_SESSION['csrf_token'] ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';

$statusBadge = function ($s) {
    $map = [
        'pending'           => 'bg-warning text-dark',
        'approved'          => 'bg-success text-white',
        'rejected'          => 'bg-danger text-white',
        'token_paid'        => 'bg-info text-dark',
        'agreement_signed'  => 'bg-primary text-white',
        'emi_active'        => 'bg-warning text-dark',
        'fully_paid'        => 'bg-success text-white',
        'cancelled'         => 'bg-danger text-white',
    ];
    return $map[$s] ?? 'bg-secondary text-white';
};
?>
<div class="container-fluid px-0">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0 py-2 px-3 bg-light rounded-3 small">
            <li class="breadcrumb-item"><a href="<?= $base ?>/admin/dashboard" class="text-decoration-none text-muted"><i class="fas fa-home me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= $base ?>/admin/sales" class="text-decoration-none text-muted">Sales Management</a></li>
            <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Maker-Checker Booking Approvals</li>
        </ol>
    </nav>

    <div class="aps-cp-card mb-4 border-0 shadow-sm">
        <div class="aps-cp-card-header d-flex justify-content-between align-items-center bg-white border-bottom py-3 px-4">
            <div>
                <h5 class="m-0 fw-bold text-dark"><i class="fas fa-clipboard-check text-primary me-2"></i><?= __('sale_booking_approvals') ?? 'Maker-Checker Booking Approvals' ?></h5>
                <small class="text-muted">Review, verify legal deeds, and authorize doorstep plot booking applications before allotment.</small>
            </div>
            <div>
                <span class="badge bg-warning text-dark fs-6 px-3 py-2 rounded-pill"><i class="fas fa-clock me-1"></i><?= $pendingCount ?> Pending Review</span>
            </div>
        </div>
        <div class="aps-cp-card-body p-4">
            <!-- Filter Tabs -->
            <ul class="nav nav-pills mb-3" role="tablist">
                <li class="nav-item me-2">
                    <a class="nav-link <?= ($filters['status'] ?? 'pending') === 'pending' ? 'active' : '' ?> rounded-pill px-3" href="<?= $base ?>/admin/sales/approvals?status=pending">
                        <i class="fas fa-hourglass-half me-1"></i>Pending Review (<?= $pendingCount ?>)
                    </a>
                </li>
                <li class="nav-item me-2">
                    <a class="nav-link <?= ($filters['status'] ?? '') === 'approved' ? 'active' : '' ?> rounded-pill px-3" href="<?= $base ?>/admin/sales/approvals?status=approved">
                        <i class="fas fa-check-circle me-1"></i>Approved & Allotted
                    </a>
                </li>
                <li class="nav-item me-2">
                    <a class="nav-link <?= ($filters['status'] ?? '') === 'rejected' ? 'active' : '' ?> rounded-pill px-3" href="<?= $base ?>/admin/sales/approvals?status=rejected">
                        <i class="fas fa-times-circle me-1"></i>Rejected / Cancelled
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($filters['status'] ?? '') === 'all' ? 'active' : '' ?> rounded-pill px-3" href="<?= $base ?>/admin/sales/approvals?status=all">
                        <i class="fas fa-list me-1"></i>All Applications
                    </a>
                </li>
            </ul>

            <!-- Search -->
            <form method="get" class="row g-2 mb-4 bg-light p-3 rounded-3 border">
                <input type="hidden" name="status" value="<?= htmlspecialchars($filters['status'] ?? 'pending') ?>">
                <div class="col-md-7">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input aria-label="Search" type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Search by booking #, customer name, phone, plot # or code..." class="form-control border-start-0 ps-0">
                    </div>
                </div>
                <div class="col-md-5 d-flex gap-2">
                    <button class="btn btn-sm btn-primary px-3" type="submit"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a href="<?= $base ?>/admin/sales/approvals" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>

            <!-- Bookings Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 border">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th scope="col" style="min-width: 140px;">Booking #</th>
                            <th scope="col" style="min-width: 170px;">Customer</th>
                            <th scope="col" style="min-width: 160px;">Colony & Plot</th>
                            <th scope="col" style="min-width: 160px;">Associate Partner</th>
                            <th scope="col" style="min-width: 140px;" class="text-end">Token / Value</th>
                            <th scope="col" style="min-width: 120px;" class="text-center">Legal Deed</th>
                            <th scope="col" style="min-width: 110px;" class="text-center">Approval</th>
                            <th scope="col" style="min-width: 100px;">Date</th>
                            <th scope="col" style="min-width: 130px;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bookings)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 text-secondary opacity-50"></i>
                                    <p class="m-0 fs-6">No booking applications found in this queue.</p>
                                </td>
                            </tr>
                        <?php else: foreach ($bookings as $b):
                            $apStatus = $b['approval_status'] ?? 'pending';
                            $bid = (int)$b['id'];
                            $tokenAmt = (float)($b['booking_amount'] ?? 0);
                            $agreementVal = (float)($b['agreement_value'] ?? $b['total_plot_value'] ?? 0);
                            $areaSqft = (float)($b['plot_area'] ?? 0);
                            $areaGaj = round($areaSqft / 9, 1);
                            $docs = $b['documents'] ?? [];
                            $receipts = $b['receipts'] ?? [];
                            $paymentMode = !empty($receipts[0]['payment_mode']) ? strtoupper($receipts[0]['payment_mode']) : 'DIGITAL';
                        ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">
                                        <a href="<?= $base ?>/admin/sales/bookings/<?= $bid ?>" class="text-decoration-none text-primary">
                                            <?= htmlspecialchars($b['booking_number'] ?? "#{$bid}") ?>
                                        </a>
                                    </div>
                                    <span class="badge bg-light text-secondary border small">
                                        <i class="fas fa-tag me-1"></i><?= ucfirst(htmlspecialchars($b['channel'] ?? 'associate')) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($b['customer_name'] ?? '—') ?></div>
                                    <div class="small text-muted"><i class="fas fa-phone-alt me-1 text-secondary"></i><?= htmlspecialchars($b['customer_phone'] ?? '—') ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($b['colony_name'] ?? 'APS Colony') ?></div>
                                    <div class="small text-secondary">
                                        Plot #<?= htmlspecialchars($b['plot_code'] ?? '—') ?>
                                        <?php if ($areaSqft > 0): ?>
                                            (<?= number_format($areaSqft) ?> sq.ft / <?= $areaGaj ?> Gaj)
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($b['associate_name'] ?? 'Direct Company') ?></div>
                                    <?php if (!empty($b['associate_phone']) && $b['associate_phone'] !== '—'): ?>
                                        <div class="small text-muted"><i class="fas fa-phone-alt me-1 text-secondary"></i><?= htmlspecialchars($b['associate_phone']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="fw-bold text-success">₹<?= number_format($tokenAmt) ?></div>
                                    <div class="small text-muted">Total: ₹<?= number_format($agreementVal) ?></div>
                                    <span class="badge bg-light text-dark border extra-small mt-1"><?= htmlspecialchars($paymentMode) ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($docs)): ?>
                                        <?php foreach ($docs as $doc): ?>
                                            <a href="<?= $base ?>/<?= htmlspecialchars(ltrim($doc['file_path'], '/')) ?>" target="_blank" class="badge bg-primary text-white text-decoration-none py-1 px-2 d-inline-block mb-1" title="View Uploaded Document">
                                                <i class="fas fa-file-contract me-1"></i>Signed Deed
                                            </a>
                                        <?php endforeach; ?>
                                    <?php elseif (strpos($b['notes'] ?? '', 'Master Deed: Accepted') !== false): ?>
                                        <span class="badge bg-info text-dark" title="Master Deed Terms Confirmed Digitally">
                                            <i class="fas fa-check-double me-1"></i>Accepted
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">Standard</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $statusBadge($apStatus) ?> px-2 py-1">
                                        <?= ucfirst(htmlspecialchars($apStatus)) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="small text-muted"><?= htmlspecialchars($b['booking_date'] ?? date('Y-m-d', strtotime($b['created_at'] ?? 'now'))) ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($apStatus === 'pending' || $apStatus === null): ?>
                                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal<?= $bid ?>" title="Approve & Allot Plot">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $bid ?>" title="Reject & Release Plot">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                        <a href="<?= $base ?>/associate/booking/<?= $bid ?>/receipt" target="_blank" class="btn btn-outline-info" title="Official Allotment Receipt & EMI Schedule">
                                            <i class="fas fa-receipt"></i>
                                        </a>
                                        <a href="<?= $base ?>/admin/sales/bookings/<?= $bid ?>" class="btn btn-outline-secondary" title="View Full Booking Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                    <?php if ($apStatus !== 'pending' && $apStatus !== null && !empty($b['approval_notes'])): ?>
                                        <div class="extra-small text-muted text-truncate mt-1" style="max-width: 140px;" title="<?= htmlspecialchars($b['approval_notes']) ?>">
                                            <?= htmlspecialchars($b['approval_notes']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (($pagination['pages'] ?? 1) > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination pagination-sm justify-content-center">
                        <?php for ($p = 1; $p <= (int)($pagination['pages'] ?? 1); $p++): ?>
                            <li class="page-item <?= ($p === (int)($pagination['page'] ?? 1)) ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $p])) ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Approve/Reject Modals -->
<?php foreach ($bookings as $b):
    $apStatus = $b['approval_status'] ?? 'pending';
    if ($apStatus !== 'pending' && $apStatus !== null) continue;
    $bid = (int)$b['id'];
    $tokenAmt = (float)($b['booking_amount'] ?? 0);
    $agreementVal = (float)($b['agreement_value'] ?? $b['total_plot_value'] ?? 0);
?>
<!-- Approve Modal -->
<div class="modal fade" id="approveModal<?= $bid ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="<?= $base ?>/admin/sales/approvals/<?= $bid ?>/approve">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div class="modal-header bg-success text-white py-3">
                    <h6 class="modal-title fw-bold"><i class="fas fa-check-circle me-2"></i>Authorize Booking Allotment</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="bg-light p-3 rounded-3 mb-3 border">
                        <div class="row g-2 small">
                            <div class="col-6"><span class="text-muted">Booking #:</span> <strong><?= htmlspecialchars($b['booking_number'] ?? "#{$bid}") ?></strong></div>
                            <div class="col-6"><span class="text-muted">Customer:</span> <strong><?= htmlspecialchars($b['customer_name'] ?? '—') ?></strong></div>
                            <div class="col-6"><span class="text-muted">Colony:</span> <strong><?= htmlspecialchars($b['colony_name'] ?? 'APS Colony') ?></strong></div>
                            <div class="col-6"><span class="text-muted">Plot Code:</span> <strong><?= htmlspecialchars($b['plot_code'] ?? '—') ?></strong></div>
                            <div class="col-6"><span class="text-muted">Token Paid:</span> <strong class="text-success">₹<?= number_format($tokenAmt) ?></strong></div>
                            <div class="col-6"><span class="text-muted">Agreement Value:</span> <strong>₹<?= number_format($agreementVal) ?></strong></div>
                        </div>
                    </div>
                    <div class="alert alert-success small mb-3">
                        <i class="fas fa-info-circle me-2"></i><strong>Action Effect:</strong> Approving will confirm plot allotment, mark token receipt cleared, and advance the booking to confirmed status.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Senior Maker-Checker Approval Notes</label>
                        <textarea aria-label="Approval notes" class="form-control form-control-sm" name="approval_notes" rows="3" placeholder="Verified token payment and customer deed. Approved for allotment."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm px-4 fw-semibold"><i class="fas fa-check me-1"></i> Authorize & Allot</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal<?= $bid ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="<?= $base ?>/admin/sales/approvals/<?= $bid ?>/reject">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div class="modal-header bg-danger text-white py-3">
                    <h6 class="modal-title fw-bold"><i class="fas fa-times-circle me-2"></i>Reject Booking & Release Plot</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-danger small mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i><strong>Critical Warning:</strong> Rejecting will cancel this booking draft and <u>immediately release Plot #<?= htmlspecialchars($b['plot_code'] ?? '—') ?> back to Available</u> for other customers and associates.
                    </div>
                    <div class="bg-light p-3 rounded-3 mb-3 border small">
                        <p class="mb-1"><strong>Booking:</strong> <?= htmlspecialchars($b['booking_number'] ?? "#{$bid}") ?> (<?= htmlspecialchars($b['customer_name'] ?? '') ?>)</p>
                        <p class="mb-0"><strong>Property:</strong> <?= htmlspecialchars($b['colony_name'] ?? 'APS Colony') ?> - Plot #<?= htmlspecialchars($b['plot_code'] ?? '—') ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Rejection Reason *</label>
                        <textarea aria-label="Rejection reason" class="form-control form-control-sm" name="rejection_reason" rows="3" placeholder="Why is this booking application being rejected? (e.g. Unverified token payment, duplicate submission)" required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm px-4 fw-semibold"><i class="fas fa-times me-1"></i> Reject & Release Plot</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

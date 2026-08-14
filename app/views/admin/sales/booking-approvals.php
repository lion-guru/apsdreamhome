<?php
$bookings = $bookings ?? [];
$pagination = $pagination ?? [];
$filters = $filters ?? [];
$pendingCount = $pendingCount ?? 0;
$csrf_token = $_SESSION['csrf_token'] ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';

$statusBadge = function ($s) {
    $map = [
        'pending'  => 'bg-warning text-dark',
        'approved' => 'bg-success',
        'rejected' => 'bg-danger',
        'token_paid'        => 'bg-info',
        'agreement_signed'  => 'bg-primary',
        'emi_active'        => 'bg-warning text-dark',
        'fully_paid'        => 'bg-success',
        'cancelled'         => 'bg-danger',
    ];
    return $map[$s] ?? 'bg-secondary';
};
?>
<div class="aps-cp-card mb-3">
    <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0"><i class="fas fa-clipboard-check me-2"></i><?= __('sale_booking_approvals') ?? 'Booking Approvals' ?></h5>
        <span class="badge bg-warning text-dark" class="style-47175"><?= $pendingCount ?> Pending</span>
    </div>
    <div class="aps-cp-card-body">
        <!-- Filter Tabs -->
        <ul class="nav nav-pills mb-3" role="tablist">
            <li class="nav-item">
                <a class="nav-link <?= ($filters['status'] ?? 'pending') === 'pending' ? 'active' : '' ?>" href="<?= $base ?>/admin/sales/approvals?status=pending">Pending</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($filters['status'] ?? '') === 'approved' ? 'active' : '' ?>" href="<?= $base ?>/admin/sales/approvals?status=approved">Approved</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($filters['status'] ?? '') === 'rejected' ? 'active' : '' ?>" href="<?= $base ?>/admin/sales/approvals?status=rejected">Rejected</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($filters['status'] ?? '') === 'all' ? 'active' : '' ?>" href="<?= $base ?>/admin/sales/approvals?status=all">All</a>
            </li>
        </ul>

        <!-- Search -->
        <form method="get" class="row g-2 mb-3">
            <input type="hidden" name="status" value="<?= htmlspecialchars($filters['status'] ?? 'pending') ?>">
            <div class="col-md-6">
                <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Search by booking #, customer, plot..." class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <button class="btn btn-sm btn-primary" type="submit"><i class="fas fa-search me-1"></i>Search</button>
                <a href="<?= $base ?>/admin/sales/approvals" class="btn btn-sm btn-link">Reset</a>
            </div>
        </form>

        <!-- Bookings Table -->
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead>
                    <tr>
                        <th>Booking #</th>
                        <th>Customer</th>
                        <th>Plot</th>
                        <th>Associate</th>
                        <th>Value</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">No bookings found.</td></tr>
                    <?php else: foreach ($bookings as $b):
                        $apStatus = $b['approval_status'] ?? 'pending';
                    ?>
                        <tr>
                            <td>
                                <a href="<?= $base ?>/admin/sales/bookings/<?= (int)$b['id'] ?>" class="fw-bold">
                                    <?= htmlspecialchars($b['booking_number'] ?? '') ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($b['customer_name'] ?? 'â€”') ?></td>
                            <td><?= htmlspecialchars($b['plot_code'] ?? 'â€”') ?></td>
                            <td><?= htmlspecialchars($b['associate_name'] ?? 'â€”') ?></td>
                            <td class="text-end">â‚¹<?= number_format((float)($b['agreement_value'] ?? 0)) ?></td>
                            <td><span class="badge <?= $statusBadge($apStatus) ?>"><?= ucfirst($apStatus) ?></span></td>
                            <td><?= htmlspecialchars($b['booking_date'] ?? '') ?></td>
                            <td>
                                <?php if ($apStatus === 'pending' || $apStatus === null): ?>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal<?= (int)$b['id'] ?>" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal<?= (int)$b['id'] ?>" title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted" class="style-64777"><?= htmlspecialchars($b['approval_notes'] ?? 'â€”') ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (($pagination['pages'] ?? 1) > 1): ?>
            <nav>
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

<!-- Approve/Reject Modals -->
<?php foreach ($bookings as $b):
    $apStatus = $b['approval_status'] ?? 'pending';
    if ($apStatus !== 'pending' && $apStatus !== null) continue;
    $bid = (int)$b['id'];
?>
<!-- Approve Modal -->
<div class="modal fade" id="approveModal<?= $bid ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= $base ?>/admin/sales/approvals/<?= $bid ?>/approve">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div class="modal-header bg-success text-white">
                    <h6 class="modal-title"><i class="fas fa-check-circle me-2"></i>Approve Booking</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Approve booking <strong><?= htmlspecialchars($b['booking_number'] ?? '') ?></strong> for customer <strong><?= htmlspecialchars($b['customer_name'] ?? '') ?></strong>?</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Approval Notes</label>
                        <textarea class="form-control" name="approval_notes" rows="3" placeholder="Optional notes about approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check me-1"></i> Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal<?= $bid ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= $base ?>/admin/sales/approvals/<?= $bid ?>/reject">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div class="modal-header bg-danger text-white">
                    <h6 class="modal-title"><i class="fas fa-times-circle me-2"></i>Reject Booking</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Reject booking <strong><?= htmlspecialchars($b['booking_number'] ?? '') ?></strong> for customer <strong><?= htmlspecialchars($b['customer_name'] ?? '') ?></strong>?</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Rejection Reason *</label>
                        <textarea class="form-control" name="rejection_reason" rows="3" placeholder="Why is this booking being rejected?" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-times me-1"></i> Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

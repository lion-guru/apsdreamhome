<?php
/**
 * Admin Leadership Allowances & Site Visit Reimbursements View
 */
$claims = $claims ?? [];
$currentType = $current_type ?? '';
$totalPending = 0;
$totalPaid = 0;
$totalOffice = 0;
$totalVisits = 0;

foreach ($claims as $c) {
    $amt = (float)($c['amount'] ?? 0);
    $st  = $c['status'] ?? '';
    if ($st === 'claimed' || $st === 'qualified') {
        $totalPending += $amt;
    } elseif ($st === 'paid') {
        $totalPaid += $amt;
    }
    if (($c['allowance_type'] ?? '') === 'office_support') {
        $totalOffice += $amt;
    } else {
        $totalVisits += $amt;
    }
}
?>

<div class="container-fluid px-4 py-3">

    <!-- PAGE HEADER -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h4 mb-1 text-dark fw-bold">
                <i class="fas fa-hand-holding-usd text-primary me-2"></i> Leadership Expense Fund & Site Visit Claims
            </h2>
            <p class="text-muted small mb-0">
                Review, approve, and disburse branch office maintenance and site visit travel allowances directly into associate wallets.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?php echo BASE_URL; ?>/admin/mlm-realestate/salary" class="btn btn-outline-warning btn-sm">
                <i class="fas fa-money-check-alt me-1"></i> Salary Tracker
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/land-inventory/brokers" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-users-cog me-1"></i> Land Brokers
            </a>
        </div>
    </div>

    <!-- FLASH NOTIFICATIONS -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- STATS CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted small fw-semibold">Pending Claims</span>
                    <h3 class="h4 fw-bold text-warning mb-0">₹<?php echo number_format($totalPending); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted small fw-semibold">Total Paid to Wallets</span>
                    <h3 class="h4 fw-bold text-success mb-0">₹<?php echo number_format($totalPaid); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted small fw-semibold">Office Support Total</span>
                    <h3 class="h4 fw-bold text-primary mb-0">₹<?php echo number_format($totalOffice); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span class="text-muted small fw-semibold">Site Visit Fuel Total</span>
                    <h3 class="h4 fw-bold text-info mb-0">₹<?php echo number_format($totalVisits); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- FILTER BUTTONS -->
    <div class="d-flex align-items-center gap-2 mb-3">
        <span class="small text-muted fw-semibold me-1">Filter Type:</span>
        <a href="<?php echo BASE_URL; ?>/admin/mlm-realestate/allowances" 
           class="btn btn-sm <?php echo empty($currentType) ? 'btn-primary' : 'btn-outline-secondary'; ?>">
            All Claims (<?php echo count($claims); ?>)
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/mlm-realestate/allowances?type=office_support" 
           class="btn btn-sm <?php echo $currentType === 'office_support' ? 'btn-primary' : 'btn-outline-secondary'; ?>">
            Office Support
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/mlm-realestate/allowances?type=site_visit_fuel" 
           class="btn btn-sm <?php echo $currentType === 'site_visit_fuel' ? 'btn-primary' : 'btn-outline-secondary'; ?>">
            Site Visit Fuel
        </a>
    </div>

    <!-- CLAIMS TABLE -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr style="font-size: 12px;">
                            <th>ID</th>
                            <th>Associate Details</th>
                            <th>Claim Type</th>
                            <th>Month / Target</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 13px;">
                        <?php if (!empty($claims)): ?>
                            <?php foreach ($claims as $c): ?>
                                <tr>
                                    <td><span class="badge bg-light text-secondary border">#<?php echo (int)$c['id']; ?></span></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($c['user_name'] ?? 'Associate'); ?></strong>
                                        <br><small class="text-muted"><i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($c['user_phone'] ?? '—'); ?> &bull; Rank: <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($c['rank_name'] ?? 'Ass.'); ?></span></small>
                                    </td>
                                    <td>
                                        <?php if (($c['allowance_type'] ?? '') === 'office_support'): ?>
                                            <span class="badge bg-primary"><i class="fas fa-building me-1"></i> Office Support</span>
                                        <?php else: ?>
                                            <span class="badge bg-success"><i class="fas fa-gas-pump me-1"></i> Site Visit Fuel</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($c['month_year'] ?? '—'); ?>
                                        <?php if (($c['allowance_type'] ?? '') === 'office_support'): ?>
                                            <br><small class="text-muted">Target: ₹<?php echo number_format((float)($c['target_volume'] ?? 0)); ?></small>
                                        <?php elseif (!empty($c['site_visit_id'])): ?>
                                            <br><small class="text-muted">Client: <?php echo htmlspecialchars($c['visitor_name'] ?? 'Visit #' . $c['site_visit_id']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-success fw-bold">₹<?php echo number_format((float)($c['amount'] ?? 0)); ?></td>
                                    <td>
                                        <?php
                                            $st = $c['status'] ?? 'claimed';
                                            if ($st === 'paid') {
                                                echo '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Paid</span>';
                                            } elseif ($st === 'approved') {
                                                echo '<span class="badge bg-info text-dark">Approved</span>';
                                            } elseif ($st === 'claimed') {
                                                echo '<span class="badge bg-warning text-dark">Pending Review</span>';
                                            } elseif ($st === 'rejected') {
                                                echo '<span class="badge bg-danger">Rejected</span>';
                                            } else {
                                                echo '<span class="badge bg-secondary">' . htmlspecialchars(ucfirst($st)) . '</span>';
                                            }
                                        ?>
                                    </td>
                                    <td class="text-muted small" style="max-width: 200px;">
                                        <?php echo htmlspecialchars($c['notes'] ?? '—'); ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($st === 'claimed' || $st === 'qualified'): ?>
                                            <div class="btn-group btn-group-sm">
                                                <!-- Approve Form -->
                                                <form action="<?php echo BASE_URL; ?>/admin/mlm-realestate/allowances/<?php echo (int)$c['id']; ?>/approve" method="POST" class="d-inline">
                                                    <?php echo \App\Helpers\SecurityHelper::csrfField(); ?>
                                                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Approve this claim and credit ₹<?php echo number_format((float)($c['amount'] ?? 0)); ?> directly to associate wallet?');">
                                                        <i class="fas fa-check me-1"></i> Approve & Pay
                                                    </button>
                                                </form>
                                                <!-- Reject Modal Trigger -->
                                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="openRejectModal(<?php echo (int)$c['id']; ?>)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        <?php elseif ($st === 'paid'): ?>
                                            <span class="text-success small"><i class="fas fa-check-double me-1"></i> Wallet Disbursed</span>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No allowance claims found in this view.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- REJECT MODAL -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectForm" action="" method="POST">
                <?php echo \App\Helpers\SecurityHelper::csrfField(); ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="fas fa-times-circle me-1"></i> Reject Allowance Claim
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Reason for Rejection</label>
                        <textarea name="reason" class="form-control form-control-sm" rows="3" required placeholder="State reason (e.g. invalid visit proof, target discrepancy)..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">Reject Claim</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openRejectModal(id) {
    document.getElementById('rejectForm').action = '<?php echo BASE_URL; ?>/admin/mlm-realestate/allowances/' + id + '/reject';
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}
</script>


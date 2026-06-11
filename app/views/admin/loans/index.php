<?php
$page_title = $page_title ?? 'Loan Management';
$activeLoans = $activeLoans ?? 0;
$completedLoans = $completedLoans ?? 0;
$defaultedLoans = $defaultedLoans ?? 0;
$totalDisbursed = $totalDisbursed ?? 0;
$totalEmiAmount = $totalEmiAmount ?? 0;
$overdueCount = $overdueCount ?? 0;
$overdueAmount = $overdueAmount ?? 0;
$penaltyAmount = $penaltyAmount ?? 0;
$emiPlans = $emiPlans ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-hand-holding-usd me-2 text-primary"></i>Loan Management</h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/emi/calculator" class="btn btn-outline-primary btn-sm"><i class="fas fa-calculator me-1"></i>EMI Calculator</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-success rounded-pill p-2"><i class="fas fa-check-circle"></i></span></div>
                    <div><div class="aps-cp-stat-label">Active Loans</div><div class="aps-cp-stat-value"><?= $activeLoans ?></div><div class="aps-cp-stat-meta">Completed: <?= $completedLoans ?></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-primary rounded-pill p-2"><i class="fas fa-rupee-sign"></i></span></div>
                    <div><div class="aps-cp-stat-label">Total Disbursed</div><div class="aps-cp-stat-value">₹<?= number_format($totalDisbursed/100000,1) ?>L</div><div class="aps-cp-stat-meta">Monthly EMI: ₹<?= number_format($totalEmiAmount/1000,1) ?>K</div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-danger rounded-pill p-2"><i class="fas fa-exclamation-circle"></i></span></div>
                    <div><div class="aps-cp-stat-label">Overdue</div><div class="aps-cp-stat-value text-danger"><?= $overdueCount ?></div><div class="aps-cp-stat-meta">₹<?= number_format($overdueAmount/1000,1) ?>K outstanding</div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-warning rounded-pill p-2"><i class="fas fa-gavel"></i></span></div>
                    <div><div class="aps-cp-stat-label">Penalties</div><div class="aps-cp-stat-value text-warning">₹<?= number_format($penaltyAmount) ?></div><div class="aps-cp-stat-meta">Accrued penalties</div></div>
                </div>
            </div></div>
        </div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-header"><i class="fas fa-list me-2"></i>Loan Portfolio</div>
        <div class="aps-cp-card-body">
            <?php if (empty($emiPlans)): ?>
                <div class="text-center text-muted py-4"><i class="fas fa-hand-holding-usd fa-2x mb-2"></i><p>No EMI plans found</p></div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>ID</th><th>Customer</th><th>Property</th><th>Amount</th><th>EMI</th><th>Rate</th><th>Tenure</th><th>Status</th><th>Start</th></tr></thead>
                        <tbody>
                        <?php foreach ($emiPlans as $ep): ?>
                            <tr>
                                <td>#<?= $ep['id'] ?></td>
                                <td><strong><?= htmlspecialchars($ep['customer_name'] ?? 'N/A') ?></strong></td>
                                <td class="small"><?= htmlspecialchars(($ep['colony_name'] ?? '') . ' - ' . ($ep['plot_no'] ?? '')) ?></td>
                                <td>₹<?= number_format($ep['total_amount']/100000,1) ?>L</td>
                                <td>₹<?= number_format($ep['emi_amount']) ?></td>
                                <td><?= $ep['interest_rate'] ?>%</td>
                                <td><?= $ep['tenure_months'] ?>m</td>
                                <td><span class="aps-cp-badge badge bg-<?= $ep['status'] === 'active' ? 'success' : ($ep['status'] === 'defaulted' ? 'danger' : ($ep['status'] === 'completed' ? 'info' : 'secondary')) ?>"><?= ucfirst(htmlspecialchars($ep['status'])) ?></span></td>
                                <td class="text-muted small"><?= htmlspecialchars($ep['start_date']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

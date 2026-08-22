<?php
$page_title = $page_title ?? 'Loan Details';
$loan = $loan ?? [];
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-hand-holding-usd text-warning me-2"></i> Loan: <code><?php echo htmlspecialchars($loan['loan_number'] ?? 'N/A'); ?></code></h4>
        <a href="<?php echo BASE_URL; ?>/admin/farmers/loans" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <?php if ($msg = \App\Core\Session::flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo e($msg); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($msg = \App\Core\Session::flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?php echo e($msg); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Loan Info</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-borderless">
                        <tr><td class="text-muted">Loan #</td><td><strong><code><?php echo htmlspecialchars($loan['loan_number'] ?? 'N/A'); ?></code></strong></td></tr>
                        <tr><td class="text-muted">Farmer</td><td><strong><?php echo htmlspecialchars($loan['farmer_name'] ?? 'N/A'); ?></strong></td></tr>
                        <tr><td class="text-muted">Mobile</td><td><?php echo htmlspecialchars($loan['farmer_mobile'] ?? ''); ?></td></tr>
                        <tr><td class="text-muted">Status</td>
                            <td><?php $s = $loan['status'] ?? ''; ?>
                                <?php if (in_array($s, ['disbursed','active'])): ?><span class="badge bg-success"><?php echo ucfirst($s); ?></span>
                                <?php elseif ($s === 'sanctioned'): ?><span class="badge bg-primary">Sanctioned</span>
                                <?php elseif ($s === 'closed'): ?><span class="badge bg-info">Closed</span>
                                <?php elseif ($s === 'defaulted'): ?><span class="badge bg-danger">Defaulted</span>
                                <?php elseif ($s === 'applied'): ?><span class="badge bg-secondary">Applied</span>
                                <?php else: ?><span class="badge bg-light text-dark"><?php echo htmlspecialchars($s ?? ''); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr><td class="text-muted">Purpose</td><td><?php echo htmlspecialchars($loan['purpose'] ?? '-'); ?></td></tr>
                        <tr><td class="text-muted">Sanction Date</td><td><?php echo htmlspecialchars($loan['sanction_date'] ?? '-'); ?></td></tr>
                        <tr><td class="text-muted">Disbursement Date</td><td><?php echo htmlspecialchars($loan['disbursement_date'] ?? '-'); ?></td></tr>
                        <tr><td class="text-muted">Maturity Date</td><td><?php echo htmlspecialchars($loan['maturity_date'] ?? '-'); ?></td></tr>
                    </table></div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Financial Details</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-borderless">
                        <tr><td class="text-muted">Loan Amount</td><td><strong>₹<?php echo number_format($loan['loan_amount'] ?? 0); ?></strong></td></tr>
                        <tr><td class="text-muted">Interest Rate</td><td><?php echo htmlspecialchars($loan['interest_rate'] ?? '0'); ?>%</td></tr>
                        <tr><td class="text-muted">Tenure</td><td><?php echo htmlspecialchars($loan['loan_tenure'] ?? '0'); ?> months</td></tr>
                        <tr><td class="text-muted">EMI Amount</td><td><strong>₹<?php echo number_format($loan['emi_amount'] ?? 0); ?></strong></td></tr>
                        <tr><td class="text-muted">Outstanding</td><td class="text-danger"><strong>₹<?php echo number_format($loan['outstanding_amount'] ?? 0); ?></strong></td></tr>
                    </table></div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($loan['collateral_type'] ?? ''): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Collateral & Guarantor</h5></div>
        <div class="card-body aps-cp-card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Collateral</h6>
                    <p class="mb-1"><strong>Type:</strong> <?php echo htmlspecialchars($loan['collateral_type'] ?? ''); ?></p>
                    <p><strong>Value:</strong> ₹<?php echo number_format($loan['collateral_value'] ?? 0); ?></p>
                </div>
                <div class="col-md-6">
                    <h6>Guarantor</h6>
                    <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($loan['guarantor_name'] ?? ''); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($loan['guarantor_phone'] ?? ''); ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($loan['repayment_schedule'] ?? ''): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Repayment Schedule</h5></div>
        <div class="card-body aps-cp-card-body">
            <pre class="mb-0"><?php echo htmlspecialchars($loan['repayment_schedule'] ?? ''); ?></pre>
        </div>
    </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-edit me-2"></i>Update Status</h5></div>
        <div class="card-body aps-cp-card-body">
            <form method="post" action="<?php echo BASE_URL; ?>/admin/farmers/loans/update-status/<?php echo $loan['id'] ?? 0; ?>" class="row g-3">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="applied" <?php echo ($loan['status'] ?? '') === 'applied' ? 'selected' : ''; ?>>Applied</option>
                        <option value="sanctioned" <?php echo ($loan['status'] ?? '') === 'sanctioned' ? 'selected' : ''; ?>>Sanctioned</option>
                        <option value="disbursed" <?php echo ($loan['status'] ?? '') === 'disbursed' ? 'selected' : ''; ?>>Disbursed</option>
                        <option value="active" <?php echo ($loan['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="closed" <?php echo ($loan['status'] ?? '') === 'closed' ? 'selected' : ''; ?>>Closed</option>
                        <option value="defaulted" <?php echo ($loan['status'] ?? '') === 'defaulted' ? 'selected' : ''; ?>>Defaulted</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Disbursement Date</label>
                    <input type="date" name="disbursement_date" class="form-control" value="<?php echo htmlspecialchars($loan['disbursement_date'] ?? ''); ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

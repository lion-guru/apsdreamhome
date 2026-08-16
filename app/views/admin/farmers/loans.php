<?php
$page_title = $page_title ?? 'Farmer Loans';
$loans = $loans ?? [];
$totalLoans = $total_loans ?? 0;
$sanctionedCount = $sanctioned_count ?? 0;
$activeCount = $active_count ?? 0;
$closedCount = $closed_count ?? 0;
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-hand-holding-usd text-warning me-2"></i> Farmer Loans</h4>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addLoanModal"><i class="fas fa-plus me-1"></i>New Loan</button>
    </div>

    <?php if ($msg = \App\Core\Session::flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo $msg; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($msg = \App\Core\Session::flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?php echo $msg; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6"><div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3"><div class="bg-primary bg-opacity-10 text-primary rounded p-3"><i class="fas fa-credit-card fa-2x"></i></div></div>
                <div><h6 class="text-muted mb-1">Total Loans</h6><h3 class="mb-0"><?php echo $totalLoans; ?></h3></div>
            </div>
        </div></div></div>
        <div class="col-xl-3 col-md-6"><div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3"><div class="bg-info bg-opacity-10 text-info rounded p-3"><i class="fas fa-check fa-2x"></i></div></div>
                <div><h6 class="text-muted mb-1">Sanctioned</h6><h3 class="mb-0"><?php echo $sanctionedCount; ?></h3></div>
            </div>
        </div></div></div>
        <div class="col-xl-3 col-md-6"><div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3"><div class="bg-success bg-opacity-10 text-success rounded p-3"><i class="fas fa-play-circle fa-2x"></i></div></div>
                <div><h6 class="text-muted mb-1">Active/Disbursed</h6><h3 class="mb-0"><?php echo $activeCount; ?></h3></div>
            </div>
        </div></div></div>
        <div class="col-xl-3 col-md-6"><div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3"><div class="bg-secondary bg-opacity-10 text-secondary rounded p-3"><i class="fas fa-check-double fa-2x"></i></div></div>
                <div><h6 class="text-muted mb-1">Closed</h6><h3 class="mb-0"><?php echo $closedCount; ?></h3></div>
            </div>
        </div></div></div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-list me-2"></i>All Loans</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Loan#</th><th>Farmer</th><th>Amount</th><th>Interest</th><th>EMI</th><th>Status</th><th>Sanction Date</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($loans as $l): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($l['loan_number'] ?? 'N/A'); ?></code></td>
                            <td><strong><?php echo htmlspecialchars($l['farmer_name'] ?? 'N/A'); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars($l['farmer_mobile'] ?? ''); ?></small></td>
                            <td><strong>₹<?php echo number_format($l['loan_amount'] ?? 0); ?></strong></td>
                            <td><?php echo htmlspecialchars($l['interest_rate'] ?? '0'); ?>%</td>
                            <td>₹<?php echo number_format($l['emi_amount'] ?? 0); ?></td>
                            <td>
                                <?php $s = $l['status'] ?? ''; ?>
                                <?php if (in_array($s, ['disbursed','active'])): ?><span class="badge bg-success"><?php echo ucfirst($s); ?></span>
                                <?php elseif ($s === 'sanctioned'): ?><span class="badge bg-primary">Sanctioned</span>
                                <?php elseif ($s === 'closed'): ?><span class="badge bg-info">Closed</span>
                                <?php elseif ($s === 'defaulted'): ?><span class="badge bg-danger">Defaulted</span>
                                <?php elseif ($s === 'applied'): ?><span class="badge bg-secondary">Applied</span>
                                <?php else: ?><span class="badge bg-light text-dark"><?php echo htmlspecialchars($s ?? ''); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($l['sanction_date'] ?? ''); ?></td>
                            <td class="text-nowrap">
                                <a href="<?php echo BASE_URL; ?>/admin/farmers/loans/<?php echo $l['id']; ?>" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($loans)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No loans found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Loan Modal -->
<div class="modal fade" id="addLoanModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="<?php echo BASE_URL; ?>/admin/farmers/loans/store">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-header"><h5 class="modal-title">New Loan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Farmer ID</label>
                            <input type="number" name="farmer_id" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Loan Number</label>
                            <input type="text" name="loan_number" class="form-control" placeholder="LN-">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Loan Amount</label>
                            <input type="number" step="0.01" name="loan_amount" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Interest Rate (%)</label>
                            <input type="number" step="0.01" name="interest_rate" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tenure (months)</label>
                            <input type="number" name="loan_tenure" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">EMI Amount</label>
                            <input type="number" step="0.01" name="emi_amount" class="form-control">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Purpose</label>
                            <input type="text" name="purpose" class="form-control" placeholder="Loan purpose">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sanction Date</label>
                            <input type="date" name="sanction_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Maturity Date</label>
                            <input type="date" name="maturity_date" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Collateral Type</label>
                            <input type="text" name="collateral_type" class="form-control" placeholder="e.g., Land, Property">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Collateral Value</label>
                            <input type="number" step="0.01" name="collateral_value" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Guarantor Name</label>
                            <input type="text" name="guarantor_name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Guarantor Phone</label>
                            <input type="text" name="guarantor_phone" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Loan</button>
                </div>
            </form>
        </div>
    </div>
</div>

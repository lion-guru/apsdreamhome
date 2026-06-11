<?php $pageTitle = 'EMI Calculator'; ?>
<?php $result = $result ?? null; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>payments">Payments</a></li>
            <li class="breadcrumb-item active">EMI Calculator</li>
        </ol>
    </nav>
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-calculator me-2"></i>EMI Calculator</h5></div>
                <div class="card-body aps-cp-card-body">
                    <form method="post" action="<?= BASE_URL ?>payments/emi-calculator">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Loan Amount (₹)</label>
                            <input type="number" class="form-control" name="loan_amount" id="loan_amount" value="<?= htmlspecialchars($_POST['loan_amount'] ?? 500000) ?>" min="1000" step="1000">
                            <input type="range" class="form-range mt-2" min="10000" max="10000000" step="10000" value="<?= htmlspecialchars($_POST['loan_amount'] ?? 500000) ?>" oninput="document.getElementById('loan_amount').value=this.value">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Interest Rate (% p.a.)</label>
                            <input type="number" class="form-control" name="interest_rate" id="interest_rate" value="<?= htmlspecialchars($_POST['interest_rate'] ?? 8.5) ?>" min="1" max="30" step="0.1">
                            <input type="range" class="form-range mt-2" min="1" max="30" step="0.1" value="<?= htmlspecialchars($_POST['interest_rate'] ?? 8.5) ?>" oninput="document.getElementById('interest_rate').value=this.value">
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Tenure (Years)</label>
                            <input type="number" class="form-control" name="tenure" id="tenure" value="<?= htmlspecialchars($_POST['tenure'] ?? 20) ?>" min="1" max="40">
                            <input type="range" class="form-range mt-2" min="1" max="40" value="<?= htmlspecialchars($_POST['tenure'] ?? 20) ?>" oninput="document.getElementById('tenure').value=this.value">
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-calculator me-2"></i>Calculate EMI</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <?php if ($result): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>EMI Breakdown</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="bg-primary-subtle p-3 rounded text-center"><small class="text-muted d-block">Monthly EMI</small><h3 class="mb-0 text-primary">₹<?= number_format($result['emi'] ?? 0) ?></h3></div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-info-subtle p-3 rounded text-center"><small class="text-muted d-block">Total Interest</small><h3 class="mb-0 text-info">₹<?= number_format($result['total_interest'] ?? 0) ?></h3></div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-success-subtle p-3 rounded text-center"><small class="text-muted d-block">Total Payment</small><h3 class="mb-0 text-success">₹<?= number_format($result['total_payment'] ?? 0) ?></h3></div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-sm table-bordered table-responsive">
                            <thead class="table-light"><tr><th>Year</th><th>Principal Paid</th><th>Interest Paid</th><th>Balance</th></tr></thead>
                            <tbody><?php foreach (($result['schedule'] ?? []) as $row): ?>
                                <tr><td><?= $row['year'] ?></td><td>₹<?= number_format($row['principal_paid']) ?></td><td>₹<?= number_format($row['interest_paid']) ?></td><td>₹<?= number_format($row['balance']) ?></td></tr>
                            <?php endforeach; ?></tbody>
                        </table></div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5"><i class="fas fa-calculator fa-3x text-muted mb-3"></i><p class="text-muted">Enter loan details and click Calculate to see EMI breakdown</p></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

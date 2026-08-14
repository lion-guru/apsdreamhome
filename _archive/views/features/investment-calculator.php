<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'Investment Calculator') ?></h1>
        <a href="<?= $base ?? BASE_URL ?>/features/dashboard" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="row">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><h5 class="mb-0">Input Parameters</h5></div>
                <div class="card-body aps-cp-card-body">
                    <form id="calcForm">
    <?php echo CSRFProtection::csrfField(); ?>
                        <div class="mb-3">
                            <label class="form-label">Property Price (INR)</label>
                            <input type="number" name="price" class="form-control" value="<?= (int)($input['price'] ?? 5000000) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Down Payment (%)</label>
                            <input type="range" name="down_payment" class="form-range" min="10" max="50" value="<?= (int)($input['down_payment'] ?? 20) ?>" oninput="this.nextElementSibling.value=this.value+'%'">
                            <output><?= (int)($input['down_payment'] ?? 20) ?>%</output>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Interest Rate (%)</label>
                            <input type="number" name="interest_rate" class="form-control" step="0.1" value="<?= htmlspecialchars($input['interest_rate'] ?? '8.5') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Loan Tenure (Years)</label>
                            <select name="tenure" class="form-select">
                                <?php foreach ([5,10,15,20,25,30] as $y): ?>
                                    <option value="<?= htmlspecialchars($y, ENT_QUOTES, 'UTF-8') ?>" <?= ((int)($input['tenure'] ?? 20) === $y) ? 'selected' : '' ?>><?= htmlspecialchars($y, ENT_QUOTES, 'UTF-8') ?> years</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Expected Appreciation (%)</label>
                            <input type="number" name="appreciation" class="form-control" step="0.5" value="<?= htmlspecialchars($input['appreciation'] ?? '10') ?>">
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-calculator me-1"></i>Calculate</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <?php if (!empty($result)): ?>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom"><h5 class="mb-0">Results</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="bg-light rounded p-3 text-center"><small class="text-muted d-block">Monthly EMI</small><strong class="fs-4">₹<?= number_format((int)($result['emi'] ?? 0)) ?></strong></div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light rounded p-3 text-center"><small class="text-muted d-block">Total Interest</small><strong class="fs-4">₹<?= number_format((int)($result['total_interest'] ?? 0)) ?></strong></div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light rounded p-3 text-center"><small class="text-muted d-block">Total Payment</small><strong class="fs-4">₹<?= number_format((int)($result['total_payment'] ?? 0)) ?></strong></div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light rounded p-3 text-center"><small class="text-muted d-block">ROI (10 yr)</small><strong class="fs-4"><?= round((float)($result['roi'] ?? 0), 1) ?>%</strong></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><h5 class="mb-0">Amortization Schedule</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height:300px">
                        <div class="table-responsive"><table class="table table-hover table-sm mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Year</th><th>Principal Paid</th><th>Interest Paid</th><th>Balance</th></tr></thead>
                            <tbody>
                                <?php if (!empty($result['schedule'])): ?>
                                    <?php foreach ($result['schedule'] as $s): ?>
                                        <tr><td><?= (int)($s['year'] ?? 0) ?></td><td>₹<?= number_format((int)($s['principal'] ?? 0)) ?></td><td>₹<?= number_format((int)($s['interest'] ?? 0)) ?></td><td>₹<?= number_format((int)($s['balance'] ?? 0)) ?></td></tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-muted py-3">No data.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5 text-muted">
                    <i class="fas fa-calculator fa-4x d-block mb-3"></i>
                    <p>Enter investment parameters and click Calculate to see results.</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
document.getElementById('calcForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const params = new URLSearchParams(new FormData(this));
    window.location.href = '<?= $base ?? BASE_URL ?>/features/investment-calculator?' + params.toString();
});
</script>

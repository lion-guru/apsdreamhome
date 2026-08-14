<?php
$calculation = $calculation ?? null;
$offers = $offers ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-calculator me-2 text-primary"></i>Loan Calculator</h2>
        <a href="<?= BASE_URL ?>/admin/company-loans" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-sliders-h me-2"></i>Calculate Loan</div>
                <div class="aps-cp-card-body">
                    <form method="GET" id="calcForm">
    <?php echo CSRFProtection::csrfField(); ?>
                        <div class="mb-3">
                            <label class="form-label">Loan Amount (₹)</label>
                            <input type="number" name="amount" class="form-control" value="<?= htmlspecialchars($_GET['amount'] ?? '1000000') ?>" min="10000" step="10000">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Interest Rate (% p.a.)</label>
                            <input type="number" name="rate" class="form-control" value="<?= htmlspecialchars($_GET['rate'] ?? '10') ?>" min="0" max="36" step="0.5">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tenure (Months)</label>
                            <input type="number" name="tenure" class="form-control" value="<?= htmlspecialchars($_GET['tenure'] ?? '60') ?>" min="1" max="240">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Interest-Free Months (Offer)</label>
                            <input type="number" name="interest_free_months" class="form-control" value="<?= htmlspecialchars($_GET['interest_free_months'] ?? '0') ?>" min="0" max="60">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Select Offer (prefill)</label>
                            <select class="form-select" id="calcOfferSelect">
                                <option value="">Custom</option>
                                <?php foreach ($offers as $o): ?>
                                    <option value="<?= $o['interest_free_months'] ?>" data-months="<?= $o['interest_free_months'] ?>">
                                        <?= htmlspecialchars($o['name']) ?> (<?= $o['interest_free_months'] ?> months free)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <input type="hidden" name="calculate" value="1">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-calculator me-1"></i>Calculate</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <?php if ($calculation): ?>
                <div class="aps-cp-card">
                    <div class="aps-cp-card-header bg-primary text-white"><i class="fas fa-chart-bar me-2"></i>Comparison: Standard vs Offer</div>
                    <div class="aps-cp-card-body">
                        <div class="row g-3 text-center mb-4">
                            <div class="col-md-4">
                                <div class="border rounded p-3">
                                    <small class="text-muted">Standard EMI</small>
                                    <div class="fw-bold h4">₹<?= number_format($calculation['normal_emi']) ?></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 border-success">
                                    <small class="text-success">Offer EMI</small>
                                    <div class="fw-bold h4 text-success">₹<?= number_format($calculation['offer_emi']) ?></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 bg-success text-white">
                                    <small>You Save</small>
                                    <div class="fw-bold h4">₹<?= number_format($calculation['total_savings']) ?></div>
                                    <small><?= $calculation['savings_percentage'] ?>% less</small>
                                </div>
                            </div>
                        </div>

                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr><th></th><th>Standard Loan</th><th>With Offer</th><th>Difference</th></tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>EMI Amount</td>
                                    <td>₹<?= number_format($calculation['normal_emi']) ?></td>
                                    <td class="text-success">₹<?= number_format($calculation['offer_emi']) ?></td>
                                    <td class="text-success">-₹<?= number_format($calculation['normal_emi'] - $calculation['offer_emi']) ?></td>
                                </tr>
                                <tr>
                                    <td>Total Payable</td>
                                    <td>₹<?= number_format($calculation['normal_total_payable']) ?></td>
                                    <td class="text-success">₹<?= number_format($calculation['offer_total_payable']) ?></td>
                                    <td class="text-success">-₹<?= number_format($calculation['normal_total_payable'] - $calculation['offer_total_payable']) ?></td>
                                </tr>
                                <tr>
                                    <td>Total Interest</td>
                                    <td>₹<?= number_format($calculation['normal_total_interest']) ?></td>
                                    <td class="text-success">₹<?= number_format($calculation['offer_total_interest']) ?></td>
                                    <td class="text-success">-₹<?= number_format($calculation['normal_total_interest'] - $calculation['offer_total_interest']) ?></td>
                                </tr>
                                <tr>
                                    <td>Original Price</td>
                                    <td colspan="3">₹<?= number_format($calculation['original_price']) ?></td>
                                </tr>
                                <tr class="table-info">
                                    <th>Interest Waived</th>
                                    <th colspan="3" class="text-success">₹<?= number_format($calculation['waived_amount']) ?> (<?= $calculation['interest_free_months'] ?> months interest-free)</th>
                                </tr>
                            </tbody>
                        </table>

                        <div class="alert alert-info mb-0">
                            <strong><i class="fas fa-info-circle me-1"></i>How it works:</strong>
                            With this offer, the first <strong><?= $calculation['interest_free_months'] ?> months</strong> are interest-free — your full EMI goes toward principal.
                            <?php if (($calculation['normal_months'] ?? 0) > 0): ?>
                                After that, standard interest applies for the remaining <strong><?= $calculation['normal_months'] ?> months</strong>.
                            <?php endif; ?>
                            <br><small class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Note: If 3 consecutive EMIs are missed during the interest-free period, interest will be applied from the date of default.</small>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="aps-cp-card">
                    <div class="aps-cp-card-body text-center text-muted py-5">
                        <i class="fas fa-calculator fa-4x mb-3"></i>
                        <p>Enter loan parameters on the left and click Calculate to see the comparison between standard loan and interest-free offer.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('calcOfferSelect').addEventListener('change', function() {
    const months = this.value;
    if (months) {
        document.querySelector('input[name="interest_free_months"]').value = months;
    }
});
</script>

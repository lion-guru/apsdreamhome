<?php
$customers = $customers ?? [];
$plots = $plots ?? [];
$offers = $offers ?? [];
$early_incentives = $early_incentives ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-plus-circle me-2 text-primary"></i>Create New Loan</h2>
        <a href="<?= BASE_URL ?>/admin/company-loans" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Loans</a>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/admin/company-loans/create" id="loanForm">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

        <div class="row g-4">
            <!-- Customer Details -->
            <div class="col-md-6">
                <div class="aps-cp-card">
                    <div class="aps-cp-card-header"><i class="fas fa-user me-2"></i>Customer Details</div>
                    <div class="aps-cp-card-body">
                        <div class="mb-3">
                            <label class="form-label">Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select" required>
                                <option value="">Select Customer</option>
                                <?php foreach ($customers as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name'] . ' (' . $c['phone'] . ')') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Plot / Property</label>
                            <select name="property_id" class="form-select">
                                <option value="">Select Plot (optional)</option>
                                <?php foreach ($plots as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['colony_name'] . ' - Plot ' . $p['plot_no'] . ' (₹' . number_format($p['total_price'] / 100000, 1) . 'L)') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Loan Purpose</label>
                            <input type="text" name="purpose" class="form-control" placeholder="e.g., Plot purchase financing" maxlength="500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loan Terms -->
            <div class="col-md-6">
                <div class="aps-cp-card">
                    <div class="aps-cp-card-header"><i class="fas fa-sliders-h me-2"></i>Loan Terms</div>
                    <div class="aps-cp-card-body">
                        <div class="mb-3">
                            <label class="form-label">Loan Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" name="loan_amount" id="loanAmount" class="form-control" min="10000" step="1000" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Interest Rate (% p.a.)</label>
                                <input type="number" name="interest_rate" id="interestRate" class="form-control" value="10" min="0" max="36" step="0.5">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Interest Type</label>
                                <select name="interest_type" class="form-select">
                                    <option value="reducing">Reducing Balance</option>
                                    <option value="fixed">Fixed Rate</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tenure (Months) <span class="text-danger">*</span></label>
                            <input type="number" name="tenure_months" id="tenureMonths" class="form-control" min="1" max="240" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Offer Selection -->
            <div class="col-md-12">
                <div class="aps-cp-card">
                    <div class="aps-cp-card-header"><i class="fas fa-tags me-2"></i>Promotional Offer (Optional)</div>
                    <div class="aps-cp-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <select name="offer_id" id="offerSelect" class="form-select">
                                        <option value="">No Offer (Standard Interest)</option>
                                        <?php foreach ($offers as $o): ?>
                                            <option value="<?= $o['id'] ?>" data-months="<?= $o['interest_free_months'] ?>" data-type="<?= $o['offer_type'] ?>">
                                                <?= htmlspecialchars($o['name'] ?? '') ?> (<?= $o['interest_free_months'] ?> months interest-free)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div id="offerPreview" class="alert alert-info d-none mb-0">
                                    <strong>Offer Preview:</strong>
                                    <span id="offerPreviewText">Select an offer to see details</span>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <label class="form-label">Interest-Free Months</label>
                                <input type="number" name="interest_free_months" id="interestFreeMonths" class="form-control" value="0" min="0" max="60">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Notes / Remarks</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Internal notes about this loan"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calculation Preview -->
        <div class="aps-cp-card mt-4" id="calculationPreview" class="style-2248">
            <div class="aps-cp-card-header"><i class="fas fa-calculator me-2"></i>Loan Calculation Preview</div>
            <div class="aps-cp-card-body">
                <div class="row g-3 text-center" id="calcResults">
                    <div class="col-md-3"><div class="border rounded p-3 bg-light"><small class="text-muted">Monthly EMI</small><div class="fw-bold h4" id="calcEMI">₹0</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 bg-light"><small class="text-muted">Total Payable</small><div class="fw-bold h4" id="calcTotal">₹0</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 bg-light"><small class="text-muted">Total Interest</small><div class="fw-bold h4" id="calcInterest">₹0</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 bg-success text-white"><small>Interest Savings</small><div class="fw-bold h4" id="calcSavings">₹0</div></div></div>
                </div>
            </div>
        </div>

        <div class="mt-4 text-end">
            <a href="<?= BASE_URL ?>/admin/company-loans" class="btn btn-outline-secondary me-2">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check me-1"></i>Create Loan</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('loanAmount');
    const rateInput = document.getElementById('interestRate');
    const tenureInput = document.getElementById('tenureMonths');
    const offerSelect = document.getElementById('offerSelect');
    const freeMonthsInput = document.getElementById('interestFreeMonths');
    const preview = document.getElementById('calculationPreview');

    function updateCalculation() {
        const amount = parseFloat(amountInput.value) || 0;
        const rate = parseFloat(rateInput.value) || 0;
        const tenure = parseInt(tenureInput.value) || 0;
        const freeMonths = parseInt(freeMonthsInput.value) || 0;

        if (amount > 0 && tenure > 0) {
            const monthlyRate = (rate / 12) / 100;
            let emi = 0;
            if (monthlyRate > 0) {
                const pow = Math.pow(1 + monthlyRate, tenure);
                emi = amount * monthlyRate * pow / (pow - 1);
            } else {
                emi = amount / tenure;
            }
            const totalPayable = emi * tenure;
            const totalInterest = totalPayable - amount;

            // Offer savings (simplified)
            const normalMonths = tenure - freeMonths;
            let offerTotal = totalPayable;
            let savings = 0;
            if (freeMonths > 0) {
                const interestPerMonth = totalInterest / tenure;
                savings = interestPerMonth * freeMonths;
                offerTotal = totalPayable - savings;
            }

            document.getElementById('calcEMI').textContent = '₹' + Math.round(emi).toLocaleString();
            document.getElementById('calcTotal').textContent = '₹' + Math.round(offerTotal).toLocaleString();
            document.getElementById('calcInterest').textContent = '₹' + Math.round(offerTotal - amount).toLocaleString();
            document.getElementById('calcSavings').textContent = '₹' + Math.round(savings).toLocaleString();
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    }

    amountInput.addEventListener('input', updateCalculation);
    rateInput.addEventListener('input', updateCalculation);
    tenureInput.addEventListener('input', updateCalculation);
    freeMonthsInput.addEventListener('input', updateCalculation);

    offerSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const months = selected.dataset.months || 0;
        document.getElementById('interestFreeMonths').value = months;
        updateCalculation();

        const preview = document.getElementById('offerPreview');
        if (this.value) {
            preview.classList.remove('d-none');
            document.getElementById('offerPreviewText').textContent = selected.text + ' — ' + months + ' months interest-free';
        } else {
            preview.classList.add('d-none');
        }
    });
});
</script>
